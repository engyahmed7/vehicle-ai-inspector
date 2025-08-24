<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Services\InsuranceCardParser;
use App\Services\MvrParserService;
// use App\Services\CheckrService;
use App\Services\VisionService;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CarImageController extends Controller
{
    protected $vision;
    protected $insuranceCardParser;
    protected $mvrParserService;
    // protected $checkrService;

    public function __construct(VisionService $vision, InsuranceCardParser $insuranceCardParser, MvrParserService $mvrParserService)
    {
        $this->vision = $vision;
        $this->insuranceCardParser = $insuranceCardParser;
        $this->mvrParserService = $mvrParserService;
        // $this->checkrService = $checkrService;
    }

    public function index()
    {
        return view('upload');
    }

    public function analyze(Request $request)
    {
        $data = [];

        foreach ($request->file('images') as $type => $image) {
            try {
                $cloudinary = new Cloudinary();

                $uploadResult = $cloudinary->uploadApi()->upload($image->getPathname(), [
                    'folder' => 'cars',
                    'public_id' => uniqid('car_' . $type . '_'),
                    'resource_type' => 'image'
                ]);

                $cloudinaryUrl = $uploadResult['secure_url'];
                $data[$type]['image_url'] = $cloudinaryUrl;
                $data[$type]['cloudinary_id'] = $uploadResult['public_id'];

                $ocrResults = $this->vision->detectTextFromUrl($cloudinaryUrl);

                if (empty($ocrResults)) {
                    $data[$type]['error'] = 'No text detected';
                    continue;
                }

                $fullText = is_array($ocrResults) ? ($ocrResults[0] ?? '') : $ocrResults;

                if (in_array($type, ['front', 'rear', 'license_close'])) {
                    $data[$type]['license_plate'] = $this->extractLicensePlate($fullText);
                }

                if ($type === 'dashboard') {
                    $data[$type]['odometer'] = $this->extractOdometer($fullText);
                    $data[$type]['fuel_level'] = $this->detectFuelLevel($cloudinaryUrl, $fullText);
                }

                if ($type === 'vin_area') {
                    $vin = $this->extractVin($fullText);

                    if ($vin) {
                        $data[$type]['vin'] = $vin;
                        $vehicleData = $this->getVehicleDataFromVin($vin);
                        $data[$type]['vehicle_info'] = $vehicleData;

                        $vehicleYear = (int)($vehicleData['basic_info']['Year'] ?? 0);
                        $currentYear = now()->year;

                        $data[$type]['vehicle_preview'] = $vehicleData['summary'] ?? 'No summary available';

                        if ($vehicleYear > 0) {
                            $vehicleAge = $currentYear - $vehicleYear;
                            $isEligible = $vehicleAge < 10;
                            $data[$type]['vehicle_age_eligible'] = $isEligible
                                ? '✅ Eligible (' . $vehicleAge . ' years old)'
                                : '❌ Not eligible (' . $vehicleAge . ' years old)';
                        } else {
                            $data[$type]['vehicle_age_eligible'] = 'Unknown (Missing model year)';
                        }
                    }
                }


                if ($type === 'insurance_card') {
                    $data[$type]['insurance_details'] = $this->insuranceCardParser->extractInsuranceDetails($fullText);
                }

                if ($type === 'mvr') {
                    $data[$type]['mvr_details'] = $this->mvrParserService->extractMvrDetails($fullText);
                }



            } catch (\Exception $e) {
                $data[$type]['error'] = 'Processing error: ' . $e->getMessage();
            }
        }

        Log::info('response json ' .  json_encode($data));

        $car = $this->saveCarData($data, $request);

        // return response()->json($data);
        return view('upload-results', compact('data', 'car'));
    }

    private function extractLicensePlate($ocrText)
    {
        $patterns = [
            '/\b[A-Z0-9]{2,3}-?[A-Z0-9]{3,4}\b/',
            '/\b[A-Z0-9]{7}\b/',
            '/\b\d{3,4}\s?[A-Z]{3}\b/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $ocrText, $matches)) {
                return $matches[0];
            }
        }

        return 'Not found';
    }

    private function extractOdometer($ocrText)
    {
        $patterns = [
            '/(?:odo|km|mi|mileage|odometer)[\s:]*(\d{1,6})/i',
            '/\b(\d{4,6})\s?(?:km|mi)\b/i',
            '/\b(\d{1,3},\d{3})\b/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $ocrText, $matches)) {
                return str_replace(',', '', $matches[1]);
            }
        }

        preg_match_all('/\d{4,6}/', $ocrText, $matches);
        if (!empty($matches[0])) {
            return max($matches[0]);
        }

        return 'Unknown';
    }

    private function detectFuelLevel($imagePath, $ocrText)
    {
        $ocrFuel = $this->extractFuelLevelFromText($ocrText);
        if ($ocrFuel !== 'Unknown') {
            return $ocrFuel;
        }

        return $this->detectFuelLevelFromImage($imagePath);
    }

    private function extractFuelLevelFromText($ocrText)
    {
        if (preg_match('/\b(\d{1,3})%\b/', $ocrText, $matches)) {
            $level = (int)$matches[1];
            return $this->convertToFuelLevel($level);
        }

        $fractions = [
            '/(\d)\/(\d)/' => function ($matches) {
                return round(($matches[1] / $matches[2]) * 100);
            },
            '/half|1\/2|½/' => 50,
            '/quarter|1\/4|¼/' => 25,
            '/three quarters|3\/4|¾/' => 75,
            '/full|max/' => 100,
            '/empty|min/' => 0
        ];

        foreach ($fractions as $pattern => $converter) {
            if (is_callable($converter)) {
                if (preg_match($pattern, $ocrText, $matches)) {
                    $level = $converter($matches);
                    return $this->convertToFuelLevel($level);
                }
            } elseif (preg_match($pattern, $ocrText)) {
                return $this->convertToFuelLevel($converter);
            }
        }

        return 'Unknown';
    }

    private function convertToFuelLevel($percentage)
    {
        if ($percentage >= 87) return 'Full';
        if ($percentage >= 62) return '3/4';
        if ($percentage >= 37) return '1/2';
        if ($percentage >= 12) return '1/4';
        return 'Empty';
    }

    private function detectFuelLevelFromImage($path)
    {
        $result = trim(shell_exec("python3 scripts/enhanced_detect_fuel_level.py " . escapeshellarg($path) . " 2>&1"));

        if (strpos($result, 'ERROR:') === 0) {
            return 'Unknown (Image Analysis Failed)';
        }

        return $result ?: 'Unknown';
    }

    private function extractVin($ocrText)
    {
        $fullText = is_array($ocrText) ? implode(' ', $ocrText) : $ocrText;

        Log::info("VIN OCR Full Text: " . $fullText);

        $cleanText = strtoupper($fullText);
        $cleanText = str_replace(['O', 'Q', 'I'], ['0', '0', '1'], $cleanText);
        $cleanText = preg_replace('/[^A-HJ-NPR-Z0-9]/', '', $cleanText);

        preg_match_all('/[A-HJ-NPR-Z0-9]{17}/', $cleanText, $matches);

        if (!empty($matches[0])) {
            foreach ($matches[0] as $candidateVin) {
                $vehicleData = $this->getVehicleDataFromVin($candidateVin);

                if (!empty($vehicleData['make']) || !empty($vehicleData['model']) || !empty($vehicleData['year'])) {
                    Log::info("Valid VIN found: " . $candidateVin);
                    return $candidateVin;
                }
            }
        }

        return $matches[0][0] ?? null;
    }

    private function getVehicleDataFromVin($vin)
    {
        $url = "https://vpic.nhtsa.dot.gov/api/vehicles/decodevinvalues/{$vin}?format=json";
        $response = Http::get($url);

        if ($response->failed()) {
            return ['error' => 'Unable to reach NHTSA API'];
        }

        $data = $response->json();
        if (!empty($data['Results'][0])) {
            $result = $data['Results'][0];

            $filterUnknown = function ($arr) {
                return array_filter($arr, function ($v) {
                    return $v !== 'Unknown';
                });
            };

            $basicInfo = $filterUnknown([
                'Make'         => $result['Make'] ?: 'Unknown',
                'Model'        => $result['Model'] ?: 'Unknown',
                'Year'         => $result['ModelYear'] ?: 'Unknown',
                'Body Class'   => $result['BodyClass'] ?: 'Unknown',
                'Vehicle Type' => $result['VehicleType'] ?: 'Unknown',
            ]);
            $specs = $filterUnknown([
                'Fuel Type'           => $result['FuelTypePrimary'] ?: 'Unknown',
                'Transmission Style'  => $result['TransmissionStyle'] ?: 'Unknown',
                'Transmission Speeds' => $result['TransmissionSpeeds'] ?: 'Unknown',
                'Seat Belts (All)'    => $result['SeatBeltsAll'] ?: 'Unknown',
            ]);
            $manufacturing = $filterUnknown([
                'Company Name' => $result['PlantCompanyName'] ?: 'Unknown',
                'Country'      => $result['PlantCountry'] ?: 'Unknown',
            ]);

            $summaryParts = [];
            if (!empty($basicInfo['Make']))         $summaryParts[] = $basicInfo['Make'];
            if (!empty($basicInfo['Model']))        $summaryParts[] = $basicInfo['Model'];
            if (!empty($basicInfo['Year']))         $summaryParts[] = '(' . $basicInfo['Year'] . ')';
            if (!empty($basicInfo['Body Class']))   $summaryParts[] = $basicInfo['Body Class'];
            if (!empty($basicInfo['Vehicle Type'])) $summaryParts[] = $basicInfo['Vehicle Type'];
            $summary = $summaryParts ? implode(' ', $summaryParts) : null;

            return array_filter([
                'basic_info'     => $basicInfo,
                'specs'          => $specs,
                'manufacturing'  => $manufacturing,
                'summary'        => $summary,
            ]);
        }
        return ['error' => 'No vehicle data found for VIN'];
    }


    private function saveCarData($data, $request)
    {
        $primaryImage = $data['front'] ?? $data[array_key_first($data)] ?? null;

        $carData = [
            'user_id' => Auth::id(),
            'images_data' => $data,
            'image_url' => $primaryImage['image_url'] ?? null,
            'cloudinary_id' => $primaryImage['cloudinary_id'] ?? null,
        ];

        $vinData = $data['vin_area'] ?? null;
        if ($vinData) {
            $carData['vin'] = $vinData['vin'] ?? null;

            if (isset($vinData['vehicle_info'])) {
                $carData['vehicle_info'] = $vinData['vehicle_info'];
                $basicInfo = $vinData['vehicle_info']['basic_info'] ?? [];
                $carData['make'] = $basicInfo['Make'] ?? null;
                $carData['model'] = $basicInfo['Model'] ?? null;
                $carData['year'] = $basicInfo['Year'] ?? null;
                $carData['body_class'] = $basicInfo['Body Class'] ?? null;
                $carData['vehicle_type'] = $basicInfo['Vehicle Type'] ?? null;

                $specs = $vinData['vehicle_info']['specs'] ?? [];
                $carData['fuel_type'] = $specs['Fuel Type'] ?? null;
                $carData['transmission_style'] = $specs['Transmission Style'] ?? null;
            }

            $carData['vehicle_age_eligible'] = $vinData['vehicle_age_eligible'] ?? null;
        }

        foreach ($data as $type => $typeData) {
            if (isset($typeData['license_plate'])) {
                $carData['license_plate'] = $typeData['license_plate'];
            }
            if (isset($typeData['odometer'])) {
                $carData['odometer'] = $typeData['odometer'];
            }
            if (isset($typeData['fuel_level'])) {
                $carData['fuel_level'] = $this->convertFuelLevelToInteger($typeData['fuel_level']);
            }
        }

        if (isset($data['insurance_card']['insurance_details'])) {
            $carData['insurance_details'] = $data['insurance_card']['insurance_details'];
        }

        if (isset($data['mvr']['mvr_details'])) {
            $carData['mvr_details'] = $data['mvr']['mvr_details'];
        }

        return Car::create($carData);
    }

    private function convertFuelLevelToInteger($fuelLevel)
    {
        $fuelLevelMap = [
            'Full' => 100,
            '3/4' => 75,
            '1/2' => 50,
            '1/4' => 25,
            'Empty' => 0,
            'Unknown' => null,
        ];

        return $fuelLevelMap[$fuelLevel] ?? null;
    }

    public function getCarResults($carId)
    {
        $car = Car::with('user')->findOrFail($carId);
        return response()->json($car);
    }

    public function getUserCars()
    {
        $cars = Car::where('user_id', Auth::id())
                  ->orderBy('created_at', 'desc')
                  ->get();
        return response()->json($cars);
    }

    public function showResults($carId)
    {
        $car = Car::where('user_id', Auth::id())
                  ->findOrFail($carId);

        return view('upload-results', [
            'data' => $car->images_data,
            'car' => $car
        ]);
    }

    //mvr
    public function uploadMvr($ocrText)
    {

        $details = $this->mvrParserService->extractMvrDetails($ocrText);

        return response()->json([
            'mvr_details' => $details
        ]);
    }
}

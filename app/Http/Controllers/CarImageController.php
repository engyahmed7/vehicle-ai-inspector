<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\VisionService;
use Cloudinary\Cloudinary;

class CarImageController extends Controller
{
    protected $vision;

    public function __construct(VisionService $vision)
    {
        $this->vision = $vision;
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

                if ($type === 'dashboard' || $type === 'vin_area') {
                    $vin = $this->extractVin($fullText);

                    if ($vin) {
                        $data[$type]['vin'] = $vin;
                        $vehicleData = $this->getVehicleDataFromVin($vin);
                        $data[$type]['vehicle_info'] = $vehicleData;
                    } else {
                        $data[$type]['vin'] = 'Not found';
                    }
                }


                Car::create([
                    'image_url' => $cloudinaryUrl,
                    'cloudinary_id' => $uploadResult['public_id'],
                    'license_plate' => $data[$type]['license_plate'] ?? null,
                    'odometer' => $data[$type]['odometer'] ?? null,
                    'fuel_level' => $data[$type]['fuel_level'] ?? null,
                ]);
            } catch (\Exception $e) {
                $data[$type]['error'] = 'Processing error: ' . $e->getMessage();
            }
        }

        return response()->json($data);
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
        if (preg_match('/\b([A-HJ-NPR-Z0-9]{17})\b/', strtoupper($ocrText), $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function getVehicleDataFromVin($vin)
    {
        $url = "https://vpic.nhtsa.dot.gov/api/vehicles/decodevinvalues/{$vin}?format=json";
        $response = @file_get_contents($url);

        if ($response === FALSE) {
            return ['error' => 'Unable to reach NHTSA API'];
        }

        $data = json_decode($response, true);
        if (!empty($data['Results'][0])) {
            return [
                'make' => $data['Results'][0]['Make'] ?? null,
                'model' => $data['Results'][0]['Model'] ?? null,
                'year' => $data['Results'][0]['ModelYear'] ?? null,
                'body_class' => $data['Results'][0]['BodyClass'] ?? null,
                'vehicle_type' => $data['Results'][0]['VehicleType'] ?? null,
            ];
        }

        return ['error' => 'No vehicle data found for VIN'];
    }
}

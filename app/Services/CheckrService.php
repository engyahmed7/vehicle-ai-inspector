<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class CheckrService
{
    protected $baseUrl;
    protected $apiKey;
    protected $isStaging;

    public function __construct()
    {
        $this->isStaging = config('services.checkr.staging', true);
        $this->baseUrl = $this->isStaging ?
            'https://api.checkr-staging.com/v1' :
            'https://api.checkr.com/v1';
        $this->apiKey = config('services.checkr.api_key');

        if (empty($this->apiKey)) {
            throw new Exception('Checkr API key is not configured. Please set CHECKR_API_KEY in your .env file.');
        }
    }

    /**
     * Make HTTP request to Checkr API
     */
    protected function request($method, $endpoint, $data = [])
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, '')
                ->acceptJson()
                ->contentType('application/json')
                ->$method("{$this->baseUrl}{$endpoint}", $data);

            if (!$response->successful()) {
                Log::error('Checkr API Error', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'endpoint' => $endpoint
                ]);

                throw new Exception("Checkr API request failed: " . $response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Checkr Service Error', [
                'message' => $e->getMessage(),
                'endpoint' => $endpoint,
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Create a new candidate
     */
    public function createCandidate($candidateData)
    {
        $requiredFields = ['first_name', 'last_name', 'email', 'dob'];

        foreach ($requiredFields as $field) {
            if (empty($candidateData[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }

        if (!$this->isValidDate($candidateData['dob'])) {
            throw new Exception("Invalid date format for dob. Use YYYY-MM-DD format.");
        }

        if (isset($candidateData['driver_license_number']) && isset($candidateData['driver_license_state'])) {
            $candidateData['driver_license_state'] = strtoupper($candidateData['driver_license_state']);
        }

        return $this->request('post', '/candidates', $candidateData);
    }

    /**
     * Get candidate details
     */
    public function getCandidate($candidateId)
    {
        return $this->request('get', "/candidates/{$candidateId}");
    }

    /**
     * Create a new report (background check)
     */
    public function createReport($candidateId, $package = 'mvr_only')
    {
        $reportData = [
            'candidate_id' => $candidateId,
            'package' => $package
        ];

        return $this->request('post', '/reports', $reportData);
    }

    /**
     * Get report details
     */
    public function getReport($reportId)
    {
        return $this->request('get', "/reports/{$reportId}");
    }

    /**
     * Create a report with MVR package
     */
    public function createMvrCheck($candidateId, $package = 'mvr_only')
    {
        $reportData = [
            'candidate_id' => $candidateId,
            'package' => $package
        ];

        return $this->request('post', '/reports', $reportData);
    }

    /**
     * Get MVR report details
     */
    public function getMvr($reportId)
    {
        return $this->request('get', "/reports/{$reportId}?include=motor_vehicle_report");
    }

    /**
     * Create complete MVR workflow (candidate + MVR check)
     */
    public function runMvrCheck($candidateData, $driverLicenseNumber, $driverLicenseState)
    {
        try {
            $candidateData['driver_license_number'] = $driverLicenseNumber;
            $candidateData['driver_license_state'] = strtoupper($driverLicenseState);

            $candidate = $this->createCandidate($candidateData);
            $candidateId = $candidate['id'];

            Log::info('Checkr candidate created', ['candidate_id' => $candidateId]);

            $mvr = $this->createMvrCheck($candidateId, 'mvr_only');

            Log::info('Checkr MVR check initiated', [
                'candidate_id' => $candidateId,
                'report_id' => $mvr['id']
            ]);

            return [
                'candidate' => $candidate,
                'mvr' => $mvr,
                'status' => 'initiated'
            ];
        } catch (Exception $e) {
            Log::error('MVR check workflow failed', [
                'error' => $e->getMessage(),
                'candidate_data' => $candidateData
            ]);
            throw $e;
        }
    }

    /**
     * Get MVR check status and results
     */
    public function getMvrResults($reportId)
    {
        $report = $this->getMvr($reportId);

        $status = $report['status'] ?? 'unknown';
        $results = [
            'id' => $report['id'],
            'status' => $status,
            'completed_at' => $report['completed_at'] ?? null,
            'candidate_id' => $report['candidate_id'],
        ];

        if ($status === 'complete' && isset($report['motor_vehicle_report'])) {
            $mvr = $report['motor_vehicle_report'];
            $results['driving_violations'] = $mvr['violations'] ?? [];
            $results['license_status'] = $mvr['license_status'] ?? null;
            $results['license_class'] = $mvr['license_class'] ?? null;
            $results['license_expiration_date'] = $mvr['license_expiration_date'] ?? null;
            $results['restrictions'] = $mvr['restrictions'] ?? [];
        }

        return $results;
    }

    /**
     * List all candidates
     */
    public function listCandidates($page = 1, $perPage = 20)
    {
        return $this->request('get', '/candidates', [
            'page' => $page,
            'per_page' => $perPage
        ]);
    }

    /**
     * List all reports for a candidate
     */
    public function listCandidateMvrs($candidateId)
    {
        return $this->request('get', '/reports', [
            'candidate_id' => $candidateId
        ]);
    }

    /**
     * Get webhook events
     */
    public function listWebhookEvents($page = 1, $perPage = 20)
    {
        return $this->request('get', '/webhook_events', [
            'page' => $page,
            'per_page' => $perPage
        ]);
    }

    /**
     * Validate date format (YYYY-MM-DD)
     */
    private function isValidDate($date)
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) &&
            strtotime($date) !== false;
    }

    /**
     * Get sample/mock candidate data for testing
     */
    public static function getSampleCandidateData()
    {
        return [
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john.smith@example.com',
            'dob' => '1990-01-01',
            'phone' => '555-555-5555',
            'zipcode' => '90210'
        ];
    }

    /**
     * Validate US state code
     */
    private function isValidStateCode($state)
    {
        $validStates = [
            'AL',
            'AK',
            'AZ',
            'AR',
            'CA',
            'CO',
            'CT',
            'DE',
            'FL',
            'GA',
            'HI',
            'ID',
            'IL',
            'IN',
            'IA',
            'KS',
            'KY',
            'LA',
            'ME',
            'MD',
            'MA',
            'MI',
            'MN',
            'MS',
            'MO',
            'MT',
            'NE',
            'NV',
            'NH',
            'NJ',
            'NM',
            'NY',
            'NC',
            'ND',
            'OH',
            'OK',
            'OR',
            'PA',
            'RI',
            'SC',
            'SD',
            'TN',
            'TX',
            'UT',
            'VT',
            'VA',
            'WA',
            'WV',
            'WI',
            'WY',
            'DC'
        ];

        return in_array(strtoupper($state), $validStates);
    }
}

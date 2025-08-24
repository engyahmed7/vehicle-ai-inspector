<?php

namespace App\Http\Controllers;

use App\Services\CheckrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class MvrController extends Controller
{
    protected $checkrService;

    public function __construct(CheckrService $checkrService)
    {
        $this->checkrService = $checkrService;
    }

    /**
     * Show MVR check form
     */
    public function index()
    {
        return view('mvr.index');
    }

    /**
     * Create a new MVR check
     */
    public function createMvrCheck(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'dob' => 'required|date_format:Y-m-d|before:today',
                'driver_license_number' => 'required|string|max:50',
                'driver_license_state' => 'required|string|size:2',
                'phone' => 'nullable|string|max:20',
                'zipcode' => 'nullable|string|max:10'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $candidateData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'dob' => $request->dob,
                'phone' => $request->phone,
                'zipcode' => $request->zipcode
            ];

            $result = $this->checkrService->runMvrCheck(
                $candidateData,
                $request->driver_license_number,
                $request->driver_license_state
            );

            return response()->json([
                'success' => true,
                'message' => 'MVR check initiated successfully',
                'data' => [
                    'candidate_id' => $result['candidate']['id'],
                    'report_id' => $result['mvr']['id'],
                    'status' => $result['mvr']['status'],
                    'estimated_completion' => 'Results typically available within 1-24 hours'
                ]
            ]);

        } catch (Exception $e) {
            Log::error('MVR check creation failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->except(['driver_license_number'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate MVR check: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get MVR check results
     */
    public function getMvrResults($reportId)
    {
        try {
            $results = $this->checkrService->getMvrResults($reportId);

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (Exception $e) {
            Log::error('Failed to retrieve MVR results', [
                'report_id' => $reportId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve MVR results: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get candidate details
     */
    public function getCandidate($candidateId)
    {
        try {
            $candidate = $this->checkrService->getCandidate($candidateId);

            return response()->json([
                'success' => true,
                'data' => $candidate
            ]);

        } catch (Exception $e) {
            Log::error('Failed to retrieve candidate', [
                'candidate_id' => $candidateId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve candidate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * List all MVR checks for a candidate
     */
    public function listCandidateMvrs($candidateId)
    {
        try {
            $mvrs = $this->checkrService->listCandidateMvrs($candidateId);

            return response()->json([
                'success' => true,
                'data' => $mvrs
            ]);

        } catch (Exception $e) {
            Log::error('Failed to list candidate MVRs', [
                'candidate_id' => $candidateId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to list candidate MVRs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test endpoint with sample data
     */
    public function testMvr()
    {
        try {
            $sampleData = CheckrService::getSampleCandidateData();
            $driverLicense = 'D12345678';
            $licenseState = 'CA';

            $result = $this->checkrService->runMvrCheck(
                $sampleData,
                $driverLicense,
                $licenseState
            );

            return response()->json([
                'success' => true,
                'message' => 'Test MVR check completed successfully',
                'data' => $result
            ]);

        } catch (Exception $e) {
            Log::error('Test MVR check failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Test MVR check failed: ' . $e->getMessage(),
                'staging_note' => 'Make sure CHECKR_STAGING=true in your .env file'
            ], 500);
        }
    }

    /**
     * Webhook endpoint to receive status updates from Checkr
     */
    public function webhook(Request $request)
    {
        try {
            $webhookSecret = config('services.checkr.webhook_secret');
            if ($webhookSecret) {
                $signature = $request->header('X-Checkr-Signature');
                if (!$this->verifyWebhookSignature($request->getContent(), $signature, $webhookSecret)) {
                    return response()->json(['error' => 'Invalid signature'], 401);
                }
            }

            $payload = $request->json()->all();
            $eventType = $payload['type'] ?? 'unknown';

            Log::info('Checkr webhook received', [
                'event_type' => $eventType,
                'payload' => $payload
            ]);

            switch ($eventType) {
                case 'mvr.completed':
                    $this->handleMvrCompleted($payload);
                    break;
                case 'mvr.failed':
                    $this->handleMvrFailed($payload);
                    break;
                default:
                    Log::info('Unhandled webhook event type', ['type' => $eventType]);
            }

            return response()->json(['status' => 'received'], 200);

        } catch (Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle MVR completed webhook
     */
    private function handleMvrCompleted($payload)
    {
        $mvrId = $payload['data']['id'] ?? null;
        if ($mvrId) {
            Log::info('MVR check completed', ['mvr_id' => $mvrId]);
        }
    }

    /**
     * Handle MVR failed webhook
     */
    private function handleMvrFailed($payload)
    {
        $mvrId = $payload['data']['id'] ?? null;
        if ($mvrId) {
            Log::error('MVR check failed', ['mvr_id' => $mvrId, 'payload' => $payload]);
        }
    }

    /**
     * Verify webhook signature
     */
    private function verifyWebhookSignature($payload, $signature, $secret)
    {
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }
}

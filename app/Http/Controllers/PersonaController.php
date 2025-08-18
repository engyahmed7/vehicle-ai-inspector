<?php

namespace App\Http\Controllers;

use App\Services\PersonaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PersonaController extends Controller
{
    protected $persona;

    public function __construct(PersonaService $persona)
    {
        $this->persona = $persona;
    }

    public function createInquiry(Request $request)
    {
        $request->validate([
            'reference_id' => 'nullable|string',
        ]);

        $response = $this->persona->createInquiry($request->reference_id);

        if (isset($response['data']['id']) && Auth::check()) {
            $user = Auth::user();
            $user->update([
                'persona_inquiry_id' => $response['data']['id'],
                'kyc_status' => 'pending'
            ]);

            Log::info('Persona inquiry created', [
                'user_id' => $user->id,
                'inquiry_id' => $response['data']['id'],
                'status' => 'pending'
            ]);
        }

        return response()->json($response);
    }

    public function checkInquiry($id)
    {
        $response = $this->persona->getInquiryStatus($id);

        Log::info('Checking inquiry status', [
            'inquiry_id' => $id,
            'persona_response' => $response
        ]);

        if (Auth::check() && Auth::user()->persona_inquiry_id === $id) {
            $user = Auth::user();
            $status = $response['data']['attributes']['status'] ?? 'unknown';
            $kycStatus = $this->mapPersonaStatusToKyc($status);

            $updateData = ['kyc_status' => $kycStatus];

            if (in_array($kycStatus, ['approved', 'rejected'])) {
                $updateData['kyc_completed_at'] = now();
            }

            $user->update($updateData);

            Log::info('User KYC status updated', [
                'user_id' => $user->id,
                'persona_status' => $status,
                'kyc_status' => $kycStatus
            ]);
        }

        return response()->json($response);
    }

    public function getCurrentStatus()
    {
        $user = Auth::user();

        return response()->json([
            'user_id' => $user->id,
            'persona_inquiry_id' => $user->persona_inquiry_id,
            'kyc_status' => $user->kyc_status,
            'kyc_completed_at' => $user->kyc_completed_at
        ]);
    }

    public function webhook(Request $request)
    {
        $payload = $request->all();
        Log::info('Persona webhook received', $payload);

        if (!$this->verifyWebhook($request)) {
            return response()->json(['error' => 'Invalid webhook signature'], 400);
        }

        $inquiryId = $payload['data']['id'] ?? null;
        $status = $payload['data']['attributes']['status'] ?? null;

        if ($inquiryId && $status) {
            $user = \App\Models\User::where('persona_inquiry_id', $inquiryId)->first();

            if ($user) {
                $kycStatus = $this->mapPersonaStatusToKyc($status);
                $updateData = ['kyc_status' => $kycStatus];

                if (in_array($kycStatus, ['approved', 'rejected'])) {
                    $updateData['kyc_completed_at'] = now();
                }

                $user->update($updateData);
            }
        }

        return response()->json(['success' => true]);
    }

    public function getVerificationUrl()
    {
        $user = Auth::user();

        if (!$user->persona_inquiry_id) {
            return response()->json(['error' => 'No inquiry found'], 404);
        }

        $sessionUrl = $this->persona->getInquirySessionUrl($user->persona_inquiry_id);

        if (!$sessionUrl) {
            return response()->json(['error' => 'Unable to get verification URL'], 500);
        }

        return response()->json(['url' => $sessionUrl]);
    }

    private function mapPersonaStatusToKyc($personaStatus)
    {
        return match ($personaStatus) {
            'approved' => 'approved',
            'declined' => 'rejected',
            'completed' => 'approved',
            'failed' => 'rejected',
            'expired' => 'expired',
            'needs-review' => 'pending_review',
            default => 'pending'
        };
    }

    private function verifyWebhook(Request $request)
    {
        $webhookSecret = config('services.persona.webhook_secret');

        if (!$webhookSecret) {
            return true;
        }

        $signature = $request->header('Persona-Signature');
        if (!$signature) {
            return false;
        }

        $payload = $request->getContent();
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }
}

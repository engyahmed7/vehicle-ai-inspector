<?php

namespace App\Http\Controllers;

use App\Services\CanopyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InsuranceController extends Controller
{
    protected $canopy;

    public function __construct(CanopyService $canopy)
    {
        $this->canopy = $canopy;
    }


    public function createWidget()
    {
        $widget = $this->canopy->createWidget();
        Log::info('Widget from createWidget: ', $widget);
        return response()->json($widget);
    }

    public function consentAndConnect(Request $request)
    {
        $validated = $request->validate([
            'insurerName'     => 'required|string',
            'insurerUsername' => 'required|string|min:5|max:200',
            'insurerPassword' => 'required|string|min:5|max:200',
        ]);

        $deviceIdentifier = $request->ip();
        Log::info('Device Identifier: ' . $deviceIdentifier);
        $consentLanguage  = "I consent to share my insurance information with RNTL via Canopy.";

        $widget = $this->canopy->createWidget();

        Log::info('Widget created: ', $widget);

        $publicAlias = null;
        if (isset($widget['widgets'][0]['public_alias'])) {
            $publicAlias = $widget['widgets'][0]['public_alias'];
            Log::info('Extracted public_alias: ' . $publicAlias);
        }

        $response = $this->canopy->consentAndConnect(
            $deviceIdentifier,
            $widget['terms_version'] ?? 1,
            $consentLanguage,
            $publicAlias,
            $validated
        );

        Log::info('Response from consentAndConnect: ', [
            'response' => $response
        ]);

        return redirect('/insurance-form')->with('pull_id', $response['pull_id'] ?? null);
    }

    public function getInsuranceDetails($pullId)
    {
        $data = $this->canopy->getPull($pullId);

        $autoPolicy = null;
        if (!empty($data['pull']['policies'])) {
            foreach ($data['pull']['policies'] as $policy) {
                if (isset($policy['policy_type']) && $policy['policy_type'] === 'AUTO') {
                    $autoPolicy = $policy;
                    break;
                }
            }
        }

        return response()->json([
            'provider_name' => $autoPolicy['carrier_friendly_name'] ?? $data['pull']['insurance_provider_friendly_name'] ?? null,
            'policy_number' => $autoPolicy['carrier_policy_number'] ?? null,
            'expiry_date'   => $autoPolicy['expiry_date'] ?? null,
        ]);
    }

    public function handle(Request $request)
    {
        Log::info('Received Canopy webhook: ', $request->all());


        return response()->json(['status' => 'success']);
    }
}

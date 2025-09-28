<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CanopyService
{
    protected $baseUrl;
    protected $secretKey;
    protected $clientKey;
    protected $teamId;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.canopy.base_url'), '/'); 
        $this->secretKey = config('services.canopy.secret_key');
        $this->clientKey = config('services.canopy.client_key');
        $this->teamId  = config('services.canopy.team_id');
    }

    private function request($method, $endpoint, $data = [], $teamScoped = true)
    {
        $url = $this->baseUrl;

        if ($teamScoped) {
            $url .= "/teams/{$this->teamId}{$endpoint}";
        } else {
            $url .= $endpoint;
        }

        return Http::withHeaders([
            'x-canopy-client-id'     => $this->clientKey,
            'x-canopy-client-secret' => $this->secretKey,
            'Accept'                 => 'application/json',
            'Content-Type'           => 'application/json',
        ])->$method($url, $data);
    }


    /**
     * Create a widget
     */
    public function createWidget()
    {
        return $this->request('get', '/widgets', [
            'widget_type' => 'consent_and_connect',
            'servicing_actions' => [
                ['type' => 'insurance']
            ],
        ], true)->json();
    }

    /**
     * Call consentAndConnect
     */    public function consentAndConnect($deviceIdentifier, $termsVersion, $consentLanguage, $publicAlias, $metaData)
    {
        $response = $this->request('post', '/widget/pull/consentAndConnect', [
            'device_identifier' => $deviceIdentifier,
            'terms_version'     => $termsVersion,
            'consent_language'  => $consentLanguage,
            'public_alias'      => $publicAlias,
            'insurerName'       => $metaData['insurerName'] ?? '',
            'insurerUsername'   => $metaData['insurerUsername'] ?? '',
            'insurerPassword'   => $metaData['insurerPassword'] ?? '',
        ], false)->json();

        Log::info('Response from consentAndConnect: ', ['response' => $response]);

        return $response;
    }




    /**
     * Get pull details (insurance data)
     */
    public function getPull($pullId)
    {
        return $this->request('get', "/pulls/{$pullId}")->json();
    }
}




<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PersonaService
{
    protected $baseUrl;
    protected $apiKey;
    protected $templateId;
    protected $templateVersionId;

    public function __construct()
    {
        $this->baseUrl = config('services.persona.url', 'https://api.withpersona.com/api/v1');
        $this->apiKey = config('services.persona.key');
        $this->templateId = config('services.persona.template_id');
        $this->templateVersionId = config('services.persona.template_version_id');
    }

    protected function request($method, $endpoint, $data = [])
    {
        return Http::withToken($this->apiKey)
            ->acceptJson()
            ->$method("{$this->baseUrl}{$endpoint}", $data)
            ->json();
    }

    /** Create a new inquiry (KYC verification request) */
    public function createInquiry($referenceId = null)
    {
        return $this->request('post', '/inquiries', [
            'data' => [
                'attributes' => [
                    'inquiry-template-id' => $this->templateId,
                    'inquiry-template-version-id' => $this->templateVersionId,
                    'reference-id' => $referenceId,
                ],
            ],
        ]);
    }

    /** Get inquiry status and details */
    public function getInquiryStatus($inquiryId)
    {
        return $this->request('get', "/inquiries/{$inquiryId}");
    }

    /** Get inquiry session URL for the user to complete verification */
    public function getInquirySessionUrl($inquiryId)
    {
        $inquiry = $this->getInquiryStatus($inquiryId);

        if (isset($inquiry['data']['attributes']['inquiry-url'])) {
            return $inquiry['data']['attributes']['inquiry-url'];
        }

        return null;
    }
}

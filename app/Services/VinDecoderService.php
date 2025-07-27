<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class VinDecoderService
{
    public function decode(string $vin): array
    {
        $response = Http::get("https://vpic.nhtsa.dot.gov/api/vehicles/decodevin/{$vin}?format=json");
        $results = collect($response['Results']);

        return [
            'make' => $results->firstWhere('Variable', 'Make')['Value'] ?? null,
            'model' => $results->firstWhere('Variable', 'Model')['Value'] ?? null,
            'year' => $results->firstWhere('Variable', 'Model Year')['Value'] ?? null,
        ];
    }
}

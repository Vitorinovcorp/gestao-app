<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class VIESService
{
    public function validateVAT($nif, $countryCode = 'PT')
    {
        $cacheKey = "vies_{$countryCode}_{$nif}";
        
        return Cache::remember($cacheKey, 3600, function () use ($nif, $countryCode) {
            try {
                $response = Http::post('https://ec.europa.eu/taxation_customs/vies/rest-api/check-vat', [
                    'countryCode' => $countryCode,
                    'vatNumber' => $nif
                ]);
                
                if ($response->successful() && $response->json('valid')) {
                    return [
                        'valid' => true,
                        'name' => $response->json('name'),
                        'address' => $response->json('address')
                    ];
                }
                
                return ['valid' => false];
            } catch (\Exception $e) {
                return ['valid' => false, 'error' => $e->getMessage()];
            }
        });
    }
}
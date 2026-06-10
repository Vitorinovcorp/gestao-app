<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GrokService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.grok.api_key');
        $this->baseUrl = config('services.grok.base_url', 'https://api.x.ai/v1');
    }

    public function chat($messages, $model = 'grok-2-1212')
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/chat/completions', [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 500,
        ]);

        if ($response->failed()) {
            throw new \Exception('Erro na API da Grok: ' . $response->body());
        }

        return $response->json();
    }
}
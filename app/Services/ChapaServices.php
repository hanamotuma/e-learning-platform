<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ChapaService
{
    protected $secretKey;
    protected $baseUrl = 'https://api.chapa.co/v1';

    public function __construct()
    {
        $this->secretKey = config('services.chapa.secret');
    }

    public function initializePayment(array $data)
    {
        return Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/transaction/initialize", $data)
            ->json();
    }
}
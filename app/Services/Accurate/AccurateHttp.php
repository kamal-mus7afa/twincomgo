<?php

namespace App\Services\Accurate;

use Illuminate\Support\Facades\Http;

class AccurateHttp
{
    public static function get(string $url, array $query = [])
    {
        $acc = AccurateContext::token();

        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $acc['access_token'],
            'X-Session-ID'  => $acc['session_id'],
        ])
        ->timeout(120)
        ->retry(3, 2000)
        ->get($url, $query);
    }
}
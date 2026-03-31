<?php

namespace App\Services\Accurate;

use Illuminate\Support\Facades\Cache;

class PriceService
{
    public function get(int $itemId, string $mode = 'USER'): float
    {
        $label = AccurateContext::label();
        $cacheKey = "price:{$label}:{$itemId}:{$mode}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($itemId, $mode) {

            $resp = AccurateHttp::get(
                'https://odin.accurate.id/accurate/api/item/get-selling-price.do',
                [
                    'id' => $itemId,
                    'priceCategoryName' => $mode,
                ]
            );

            if (!$resp->successful()) {
                return 0;
            }

            $d = $resp->json()['d'] ?? [];

            return $d['unitPrice']
                ?? ($d['unitPriceRule'][0]['price'] ?? 0);
        });
    }
}
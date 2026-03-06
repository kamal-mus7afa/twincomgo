<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\AccurateGlobal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CustomerController extends Controller
{
    private function fetchItemsForList(Request $request)
    {
        $acc     = AccurateGlobal::token();
        $token   = $acc['access_token'];
        $session = $acc['session_id'];
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');

        $perPage = $request->query('per_page', 24);
        $pageWeb = max(1, (int) $request->query('page', 1));
        $offset  = ($pageWeb - 1) * $perPage;

        $search     = trim($request->query('search', ''));
        $categoryId = $request->query('category_id');
        $stokAda    = $request->query('stok_ada', '1');
        $priceMode  = $request->query('price_mode', 'default');

        $minPrice = $request->filled('min_price')
            ? floatval(str_replace(['.', ','], ['', '.'], $request->input('min_price')))
            : null;

        $maxPrice = $request->filled('max_price')
            ? floatval(str_replace(['.', ','], ['', '.'], $request->input('max_price')))
            : null;

        $usePriceFilter = ($minPrice !== null || $maxPrice !== null);
        $priceCategory  = $priceMode === 'reseller' ? 'RESELLER' : 'USER';

        // ----------------------------------------------------
        //       HYBRID SMART SCAN CONFIG (AMAN)
        // ----------------------------------------------------
        $targetBase = $offset + $perPage + 1;
        $targetDeep = $perPage;
        $deepStep   = 50;
        $maxLimit   = 1000;

        $buffer      = collect();
        $rawScanned  = 0;
        $pageAcc     = 1;

        $rowsNeeded = $targetBase;

        // ----------------------------------------------------
        //     HELPER FILTER ( harga + stok + push buffer )
        // ----------------------------------------------------
        $processRow = function (&$buffer, $row) use (
            $stokAda, $usePriceFilter, $minPrice, $maxPrice,
            $token, $session, $priceCategory
        ) {

            if ($stokAda === '1' && ($row['availableToSell'] ?? 0) <= 0) {
                return false;
            }

            if ($usePriceFilter) {
                $price = $this->getPriceGlobal($row['id'], $token, $session, $priceCategory);

                if ($minPrice !== null && $price < $minPrice) return false;
                if ($maxPrice !== null && $price > $maxPrice) return false;

                $row['price'] = $price;
            }

            $buffer->push($row);
            return true;
        };

        // ----------------------------------------------------
        //                     BASE SCAN
        // ----------------------------------------------------
        while ($buffer->count() < $rowsNeeded && $rawScanned < $maxLimit) {

            $query = [
                'sp.page'         => $pageAcc,
                'sp.pageSize'     => 100,
                'fields'          => 'id,name,no,availableToSell,itemCategory.name,availableToSellInAllUnit,detailItemImage',
                'filter.suspended'=> false,
            ];

            // SEARCH (CONTAIN)
            if ($search !== '') {
                $query['filter.keywords.op']      = 'CONTAIN';
                $query['filter.keywords.val[0]']  = $search;
            }

            // CATEGORY FILTER
            if (!empty($categoryId)) {
                $query['filter.itemCategoryId.op'] = 'EQUAL';
                foreach ($categoryId as $i => $id) {
                    $query["filter.itemCategoryId.val[$i]"] = $id;
                }
            }

            $resp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID'  => $session,
            ])->timeout(60)->retry(3, 2000)->get("$baseUrl/item/list.do", $query);

            if (!$resp->successful()) break;

            $json  = $resp->json();
            $rows  = collect($json['d'] ?? []);
            $sp = $json['sp'] ?? [];
            $rawScanned += $rows->count();

            if ($rows->isEmpty()) break;

            foreach ($rows as $row) {
                if (!empty($row['detailItemImage'][0]['fileName'])) {
                    $row['image'] = $row['detailItemImage'][0]['fileName'];
                } else {
                    $row['image'] = null;
                }

                $processRow($buffer, $row);
                if ($buffer->count() >= $rowsNeeded) break;
            }

            $pageCount = $json['sp']['pageCount'] ?? null;
            if ($pageCount && $pageAcc >= $pageCount) break;

            $pageAcc++;
        }

        // ----------------------------------------------------
        //            DEEP SCAN (Jika stok jarang)
        // ----------------------------------------------------
        if ($buffer->count() < $offset + $perPage) {

            $extraPages = 1;

            while (
                $buffer->count() < ($offset + $perPage + $targetDeep)
                && $rawScanned < $maxLimit
            ) {

                $startPage = $pageAcc + $extraPages;

                for ($p = $startPage; $p < $startPage + 2; $p++) {

                    if ($rawScanned >= $maxLimit) break 2;

                    $query = [
                        'sp.page'         => $p,
                        'sp.pageSize'     => 100,
                        'fields'          => 'id,name,no,availableToSell,itemCategory.name,availableToSellInAllUnit,detailItemImage',
                        'filter.suspended'=> false,
                    ];

                    if ($search !== '') {
                        $query['filter.keywords.op']      = 'CONTAIN';
                        $query['filter.keywords.val[0]']  = $search;
                    }

                    if (!empty($categoryId)) {
                        $query['filter.itemCategoryId.op'] = 'EQUAL';
                        $query['filter.itemCategoryId.op'] = 'EQUAL';
                        foreach ($categoryId as $i => $id) {
                            $query["filter.itemCategoryId.val[$i]"] = $id;
                        }
                    }

                    $resp = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                        'X-Session-ID'  => $session,
                    ])->timeout(60)->retry(3, 2000)->get("$baseUrl/item/list.do", $query);

                    if (!$resp->successful()) break 2;

                    $json = $resp->json();
                    $rows = collect($json['d'] ?? []);
                    if ($rows->isEmpty()) break 2;

                    $rawScanned += $rows->count();

                    foreach ($rows as $row) {
                        if (!empty($row['detailItemImage'][0]['fileName'])) {
                            $row['image'] = $row['detailItemImage'][0]['fileName'];
                        } else {
                            $row['image'] = null;
                        }
                        $processRow($buffer, $row);
                        if ($buffer->count() >= ($offset + $perPage + $targetDeep)) break 3;
                    }
                }

                $extraPages += 2;
            }
        }

        // ----------------------------------------------------
        //             PAGINATION RESULT
        // ----------------------------------------------------
        $totalFiltered = $buffer->count();
        $items         = $buffer->slice($offset, $perPage)->values();
        $hasMore       = $totalFiltered > ($offset + $items->count());

        return [
            'rows'       => $sp,
            'items'      => $items,
            'page'       => $pageWeb,
            'pageCount'  => $hasMore ? $pageWeb + 1 : $pageWeb,
            'totalItems' => $totalFiltered,
            'filters'    => compact('search', 'categoryId', 'stokAda', 'minPrice', 'maxPrice', 'priceMode'),
        ];
    }


    // ===================================================
    //                        INDEX
    // ===================================================
    public function index(Request $request)
    {
        $data = $this->fetchItemsForList($request);

        // CACHE CATEGORY (tidak bikin 503)
        $categories = Cache::remember("accurate:categories:global", 86400, function () {
            $acc = AccurateGlobal::token();
            $token   = $acc['access_token'];
            $session = $acc['session_id'];
            $baseUrl = rtrim(config('services.accurate.base_api'), '/');

            $cats = collect();
            $page = 1;

            do {
                $resp = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'X-Session-ID'  => $session,
                ])->timeout(60)->retry(3, 2000)->get("$baseUrl/item-category/list.do", [
                    'sp.page'     => $page,
                    'sp.pageSize' => 100,
                    'fields'      => 'id,name,parent',
                ]);

                if (!$resp->successful()) break;

                $json = $resp->json();
                $cats = $cats->merge($json['d'] ?? []);

                $page++;
                $pageCount = $json['sp']['pageCount'] ?? 1;

            } while ($page <= $pageCount);

            return $cats->values();
        });

        $acc = AccurateGlobal::token();

        return view('users.index', [
            'items'      => $data['items'],
            'page'       => $data['page'],
            'pageCount'  => $data['pageCount'],
            'totalItems' => $data['totalItems'],
            'categories' => $categories,
            'filters'    => $data['filters'],
            'session'    => $acc['session_id'],
        ]);
    }

    // ===================================================
    //                 PRICE API (AMAN)
    // ===================================================
    private function getPriceGlobal($itemId, $token, $session, $priceCategory = 'USER')
    {
        $cacheKey = "price:{$itemId}:{$priceCategory}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use (
            $itemId, $token, $session, $priceCategory
        ) {
            $resp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID'  => $session,
            ])
            ->timeout(60)
            ->retry(3, 2000)
            ->get("https://public.accurate.id/accurate/api/item/get-selling-price.do", [
                'id' => $itemId,
                'priceCategoryName' => $priceCategory,
            ]);

            if (!$resp->successful()) return 0;

            $d = $resp->json()['d'] ?? [];

            return $d['unitPrice']
                ?? ($d['unitPriceRule'][0]['price'] ?? 0);
        });
    }

    // ===================================================
    //                  AJAX PRICE
    // ===================================================
    public function ajaxPrice(Request $request)
    {
        $id = $request->query('id');
        $mode = $request->query('mode', 'USER');

        if (!$id) {
            return response()->json(['price' => 0]);
        }

        // 🟩 KEY cache unik per item + mode harga
        $cacheKey = "price:{$id}:{$mode}";

        $price = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($id, $mode) {

            $acc = AccurateGlobal::token();

            $resp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $acc['access_token'],
                'X-Session-ID'  => $acc['session_id'],
            ])
            ->timeout(30)
            ->retry(3, 2000)
            ->get("https://public.accurate.id/accurate/api/item/get-selling-price.do", [
                'id' => $id,
                'priceCategoryName' => $mode,
            ]);

            if (!$resp->successful()) return 0;

            $d = $resp->json()['d'] ?? [];

            return $d['unitPrice']
                ?? ($d['unitPriceRule'][0]['price'] ?? 0);
        });

        return response()->json([
            'price' => $price,
            'cache' => true,
        ]);
    }
    
    private function fetchItemsByCategory($categoryId, $limit=6) 
    {
        $acc     = AccurateGlobal::token();
        $token   = $acc['access_token'];
        $session = $acc['session_id'];
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');

        $resp = Http::withHeaders([
            'Authorization' => 'Bearer' . $token,
            'X-Session-ID' => $session,
        ])->timeout(60)->retry(2, 2000)->get("$baseUrl/item/list.do", [
            'sp.page' => 1,
            'sp.pageSize' => $limit,
            'fields' => 'id,name,no,availableToSell,itemCategory.name,detailItemImage',
            'filter.suspended' => false,
            'filter.itemCategoryId.op' => "EQUAL",
            'filter.itemCategoryId.val[0]' => $categoryId,
        ]);

        if(!$resp->successful()) return collect();

        return collect($resp->json()['d'] ?? [])->filter(fn ($row) => ($row['availableToSell'] ?? 0) > 0)->map(function ($row) {
            $row['image'] = $row['detailItemImage'][0]['fileName'] ?? null;
            return $row;
        })->take($limit)->values();
    }
    
    public function homePage() {
        // CATEGORY dari cache yang SUDAH kamu punya
        $categories = Cache::get('accurate:categories:global', collect())
            ->take(4); // tampilkan 3–5 kategori saja

        $promoByCategory = [];

        foreach ($categories as $cat) {
            $items = $this->fetchItemsByCategory($cat['id'], 6);

            if ($items->isNotEmpty()) {
                $promoByCategory[$cat['name']] = $items;
            }
        }

        $acc = AccurateGlobal::token();

        return view('users.beranda', [
            'promoByCategory' => $promoByCategory,
            'session' => $acc['session_id'],
        ]);
    }

}

<?php

namespace App\Http\Controllers;

use App\Helpers\AccurateGlobal;
use App\Services\Accurate\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Vinkla\Hashids\Facades\Hashids;

class ResellerController extends Controller
{
    private $unitMap = [
        '1' => 52850, 'BATANG' => 53550, 'BOX' => 53950, 'BTL' => 53200, 'CAM' => 53450,
        'DUS' => 53300, 'HPP' => 52950, 'IKAT' => 53400, 'KALENG' => 53600, 'KARUNG' => 53700,
        'KG' => 53900, 'KLG' => 53350, 'METER' => 52701, 'MTR' => 52750, 'PACK' => 53000,
        'PAJAK' => 53750, 'PAKET' => 53100, 'PCH' => 53151, 'PCS' => 50, 'POTONG' => 53500,
        'RIT' => 53650, 'ROLL' => 52900, 'SAK' => 53150, 'SET' => 53800, 'UNIT' => 53050, 'RIM' => 53850,
    ];

    /**
     * ===================================================
     * HELPER: OTORISASI AKUN & KATEGORI (SINGLE SOURCE)
     * ===================================================
     * Pakai ini agar logika token dan kategori harga tidak belang-belang!
     */
    private function getAccurateAuth()
    {
        $statusAkun = strtoupper(trim(Auth::user()->status ?? ''));
        $kategoriHarga = strtoupper(trim(Auth::user()->kategori_penjualan ?? ''));

        // Token ditentukan dari status akun
        $tokenLabel = in_array($statusAkun, ['RESELLER', 'TWINCOM PATNER']) ? 'RESELLER' : 'GLOBAL';
        $acc = AccurateGlobal::token($tokenLabel);

        return [
            'token'         => $acc['access_token'],
            'session'       => $acc['session_id'],
            'priceCategory' => $kategoriHarga !== '' ? $kategoriHarga : 'GLOBAL', // Harga berdasar kategori_penjualan
        ];
    }

    // ===================================================
    //  INDEX & LIST (HYBRID SCAN)
    // ===================================================
    public function index2(Request $request, CategoryService $categories)
    {
        $data = $this->fetchItemsForList($request);
        return view('reseller.index', [
            'items'      => $data['items'],
            'page'       => $data['page'],
            'pageCount'  => $data['pageCount'],
            'totalItems' => $data['totalItems'],
            'categories' => $categories->all(),
            'filters'    => $data['filters'],
        ]);
    }

    private function fetchItemsForList(Request $request)
    {
        $auth = $this->getAccurateAuth();
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');

        $perPage = (int) $request->query('per_page', 10);
        $pageWeb = max(1, (int) $request->query('page', 1));
        $offset  = ($pageWeb - 1) * $perPage;

        $search     = trim($request->query('search', ''));
        $categoryId = $request->query('category_id');
        $stokAda    = $request->query('stok_ada', '1');
        $priceMode  = $request->query('price_mode', 'default');

        $minPrice = $request->filled('min_price') ? floatval(str_replace(['.', ','], ['', '.'], $request->input('min_price'))) : null;
        $maxPrice = $request->filled('max_price') ? floatval(str_replace(['.', ','], ['', '.'], $request->input('max_price'))) : null;
        $usePriceFilter = ($minPrice !== null || $maxPrice !== null);

        $targetBase = $offset + $perPage + 1;
        $maxLimit   = 1000;
        $buffer     = collect();
        $rawScanned = 0;
        $pageAcc    = 1;

        $allowedCategoryIds = app(CategoryService::class)->all()->pluck('id')->flip();

        while ($buffer->count() < $targetBase && $rawScanned < $maxLimit) {
            $query = [
                'sp.page'          => $pageAcc,
                'sp.pageSize'      => 100,
                'fields'           => 'id,name,no,availableToSell,itemCategory.name,availableToSellInAllUnit,detailItemImage,itemCategory',
                'filter.suspended' => false,
            ];

            if ($search !== '') {
                $query['filter.keywords.op'] = 'CONTAIN';
                $query['filter.keywords.val[0]'] = $search;
            }

            if (!empty($categoryId)) {
                $query['filter.itemCategoryId.op'] = 'EQUAL';
                foreach ($categoryId as $i => $id) {
                    $query["filter.itemCategoryId.val[$i]"] = $id;
                }
            }

            $resp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $auth['token'],
                'X-Session-ID'  => $auth['session'],
            ])->timeout(60)->retry(3, 2000)->get("{$baseUrl}/item/list.do", $query);

            if (!$resp->successful()) break;

            $json = $resp->json();
            $rows = collect($json['d'] ?? []);
            if ($rows->isEmpty()) break;
            
            $rawScanned += $rows->count();

            foreach ($rows as $row) {
                $catId = $row['itemCategory']['id'] ?? null;
                if (!$catId || !isset($allowedCategoryIds[$catId])) continue;
                if ($stokAda === '1' && ($row['availableToSell'] ?? 0) <= 0) continue;

                // PERINGATAN: Filter rentang harga ini akan sangat memperlambat aplikasi karena menembak API per-item.
                if ($usePriceFilter) {
                    $price = $this->getPriceGlobal($row['id'], $auth['token'], $auth['session'], $auth['priceCategory']);
                    if ($minPrice !== null && $price < $minPrice) continue;
                    if ($maxPrice !== null && $price > $maxPrice) continue;
                    $row['price'] = $price;
                }

                $buffer->push($row);
                if ($buffer->count() >= $targetBase) break;
            }

            if (($json['sp']['pageCount'] ?? 0) <= $pageAcc) break;
            $pageAcc++;
        }

        $items = $buffer->slice($offset, $perPage)->values()->map(function ($item) {
            $item['category_id'] = $item['itemCategory']['id'] ?? null;
            $item['fileName'] = collect($item['detailItemImage'] ?? [])->pluck('fileName')->filter()->values()->toArray();
            $item['encryptedId'] = Hashids::encode($item['id']);
            return $item;
        });

        return [
            'items'      => $items,
            'page'       => $pageWeb,
            'pageCount'  => ($buffer->count() > ($offset + $items->count())) ? $pageWeb + 1 : $pageWeb,
            'totalItems' => $buffer->count(),
            'filters'    => compact('search', 'categoryId', 'stokAda', 'minPrice', 'maxPrice', 'priceMode'),
        ];
    }

    // ===================================================
    //  DETAIL PAGE
    // ===================================================
    public function show($encrypted, Request $request)
    {
        $id = Hashids::decode($encrypted)[0] ?? null;
        if (!$id) abort(404, 'ID item tidak valid');

        $auth = $this->getAccurateAuth();
        $branchName = $request->input('branchName');
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');

        $resp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $auth['token'],
            'X-Session-ID'  => $auth['session'],
        ])->timeout(60)->retry(3, 2000)->get("{$baseUrl}/item/detail.do", ['id' => $id]);

        $item = $resp->json()['d'] ?? null;
        if (!$item) return back()->with('error', 'Gagal mengambil data item dari Accurate.');

        $unitId = $this->getBaseUnitId($item['detailWarehouseData'][0] ?? null);
        $prices = $this->getCompiledPrices($id, $branchName, $auth, $unitId);
        $warehouses = $this->processWarehouses($item['detailWarehouseData'] ?? []);

        return view('reseller.detail', [
            'item'               => $item,
            'images'             => collect($item['detailItemImage'] ?? [])->pluck('fileName')->filter()->values()->toArray(),
            'session'            => $auth['session'],
            'branchName'         => $branchName,
            'prices'             => $prices['basePrices'],
            'unitPrices'         => $prices['unitPrices'],
            'hasMultiUnitPrices' => $prices['hasMultiUnitPrices'],
            'warehousesStore'    => $warehouses['store'],
            'warehousesUser'     => $warehouses['user'],
            'catId'              => $item['itemCategoryId'],
        ]);
    }

    // ===================================================
    //  AJAX PRICE (CACHED)
    // ===================================================
    public function ajaxPriceReseller(Request $request)
    {
        $id = $request->query('id');
        if (!$id) return response()->json(['price' => 0]);

        $auth = $this->getAccurateAuth();
        $mode = $auth['priceCategory']; // Menggunakan kategori_penjualan
        $cacheKey = "price:{$id}:{$mode}";

        if (Cache::has($cacheKey)) {
            return response()->json(['price' => Cache::get($cacheKey), 'cache' => true]);
        }

        $price = $this->getPriceGlobal($id, $auth['token'], $auth['session'], $mode);
        Cache::put($cacheKey, $price, now()->addMinutes(1));

        return response()->json(['price' => $price, 'cache' => false]);
    }

    // CATATAN: Fungsi `getPrice` lama dihapus karena tugasnya sudah digantikan dan dioverlap oleh `ajaxPriceReseller`
    // Jika masih dibutuhkan oleh endpoint lain, bisa dibalikan, tapi pastikan memakai getAccurateAuth().

    // ===================================================
    //  PRIVATE UTILITIES (CLEAN CODE METHODS)
    // ===================================================
    private function getPriceGlobal($itemId, $token, $session, $priceCategory)
    {
        try {
            $baseUrl = rtrim(config('services.accurate.base_api'), '/');
            $resp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID'  => $session,
            ])->timeout(30)->retry(2, 1000)->get("{$baseUrl}/item/get-selling-price.do", [
                'id'                => $itemId,
                'priceCategoryName' => $priceCategory,
            ]);

            if (!$resp->successful()) return 0;
            $data = $resp->json()['d'] ?? [];
            return $data['unitPrice'] ?? ($data['unitPriceRule'][0]['price'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function getBaseUnitId($firstWH)
    {
        if (!$firstWH) return 50; // default PCS
        $rawUnit = explode(' ', $firstWH['balanceUnit'] ?? '');
        $unitName = strtoupper($rawUnit[1] ?? 'PCS');
        return $this->unitMap[$unitName] ?? 50;
    }

    private function getCompiledPrices($id, $branchName, $auth, $unitId)
    {
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');
        $headers = ['Authorization' => 'Bearer ' . $auth['token'], 'X-Session-ID'  => $auth['session']];

        // USER (Global) Price
        $userData = Http::withHeaders($headers)->get("{$baseUrl}/item/get-selling-price.do", [
            'id' => $id, 'branchName' => $branchName,
        ])['d'] ?? [];

        // RESELLER (Spesifik user logged in) Price
        $resellerData = Http::withHeaders($headers)->get("{$baseUrl}/item/get-selling-price.do", [
            'id' => $id, 'branchName' => $branchName,
            'priceCategoryName' => $auth['priceCategory'], 
            'discountCategoryName' => $auth['priceCategory'],
        ])['d'] ?? [];

        $userPrice = $this->applyDiscount($this->getPriceByUnitId($userData, $unitId), $userData);
        $resellerPrice = $this->applyDiscount($this->getPriceByUnitId($resellerData, $unitId), $resellerData);

        $unitPrices = [];
        foreach (['user' => $userData, 'reseller' => $resellerData] as $type => $data) {
            if (!isset($data['unitPriceRule'])) continue;
            foreach ($data['unitPriceRule'] as $r) {
                $uid = $r['unitId'];
                $unitName = array_search($uid, $this->unitMap, true) ?? $uid;
                $unitPrices[$unitName][$type] = $this->applyDiscount($r['price'], $data);
            }
        }

        return [
            'basePrices'         => ['user' => $userPrice, 'reseller' => $resellerPrice],
            'unitPrices'         => $unitPrices,
            'hasMultiUnitPrices' => count($unitPrices) > 1,
        ];
    }

    private function getPriceByUnitId($data, $unitId)
    {
        if (!isset($data['unitPriceRule'])) return $data['unitPrice'] ?? 0;
        foreach ($data['unitPriceRule'] as $rule) {
            if ((int)$rule['unitId'] === (int)$unitId) return $rule['price'];
        }
        return $data['unitPrice'] ?? ($data['unitPriceRule'][0]['price'] ?? 0);
    }

    private function applyDiscount($price, $data)
    {
        if (isset($data['discountRule'][0]['discount'])) {
            $disc = floatval($data['discountRule'][0]['discount']);
            return $price - ($price * $disc / 100);
        }
        return $price;
    }

    private function processWarehouses($rawWarehouses)
    {
        $processed = collect($rawWarehouses)->map(function ($wh) {
            $raw = trim($wh['balanceUnit'] ?? '');
            preg_match('/^([\d.,]+)/', $raw, $m);
            $first = isset($m[1]) ? (float) str_replace(',', '.', str_replace('.', '', $m[1])) : null;
            $balance = $wh['balance'] ?? $first;
            
            preg_match_all('/\b([A-Za-z]+)\b/', $raw, $units);
            
            if (count($units[1]) > 1 || ($first !== null && abs($first - $balance) > 0.0001)) {
                $wh['unit_display'] = $raw;
            } else {
                $wh['unit_display'] = strtoupper(preg_replace('/^[\d.,]+\s+/', '', $raw));
            }
            return $wh;
        })->filter(fn($w) => ($w['balance'] ?? 0) > 0)->values();

        $storeNames = ['TSTORE KAYUTANGI','TSTORE BANJARBARU A. YANI','TSTORE BANJARBARU P. BATUR','TSTORE BELITUNG','TSTORE MARTAPURA','TDC','STORE PALANGKARAYA','LANDASAN ULIN','TDC-2', 'STORE PELAIHARI'];
        $userWarehouse = strtoupper(auth()->user()->name ?? '');

        return [
            'store' => $processed->filter(fn($w) => in_array(strtoupper($w['name'] ?? ''), $storeNames))->values(),
            'user'  => $processed->filter(fn($w) => strtoupper($w['name'] ?? '') === $userWarehouse)->values(),
        ];
    }
}
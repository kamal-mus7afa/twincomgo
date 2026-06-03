<?php

namespace App\Http\Controllers;

use App\Helpers\AccurateGlobal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Http\Client\Pool;

class KaryawanController extends Controller
{
    private array $unitMap = [
        '1'       => 52850, 'BATANG'  => 53550, 'BOX'     => 53950,
        'BTL'     => 53200, 'CAM'     => 53450, 'DUS'     => 53300,
        'HPP'     => 52950, 'IKAT'    => 53400, 'KALENG'  => 53600,
        'KARUNG'  => 53700, 'KG'      => 53900, 'KLG'     => 53350,
        'METER'   => 52701, 'MTR'     => 52750, 'PACK'    => 53000,
        'PAJAK'   => 53750, 'PAKET'   => 53100, 'PCH'     => 53151,
        'PCS'     => 50,    'POTONG'  => 53500, 'RIT'     => 53650,
        'ROLL'    => 52900, 'SAK'     => 53150, 'SET'     => 53800,
        'UNIT'    => 53050, 'RIM'     => 53850,
    ];

    /**
     * ==========================================
     * 1. HALAMAN DETAIL WEB
     * ==========================================
     */
    public function show($encrypted, Request $request)
    {
        $id = $this->decodeId($encrypted);
        $auth = $this->getAccurateAuth();
        $branchName = $request->input('branchName');

        // Fetch Detail Item
        $item = $this->fetchItemDetail($id, $auth);
        if (!$item) return back()->with('error', 'Gagal mengambil detail item.');

        $unitId = $this->getBaseUnitId($item);
        $prices = $this->getCompiledPrices($id, $branchName, $auth, $unitId);
        $warehouses = $this->processAndGroupWarehouses($item['detailWarehouseData'] ?? []);
        
        // Prepare images (Fast method)
        $images = $this->prepareImagesForWeb($item['detailItemImage'] ?? [], $auth);

        return view('items.karyawan.detail', [
            'item'                 => $item,
            'images'               => $images,
            'session'              => $auth['session_id'],
            'branchName'           => $branchName,
            'note'                 => $item['notes'] ?? '',
            'prices'               => $prices['basePrices'],
            'partnerPrice'         => $prices['partnerPrice'],
            'unitPrices'           => $prices['unitPrices'],
            'hasMultiUnitPrices'   => $prices['hasMultiUnitPrices'],
            
            // Unpack gudang untuk Blade
            'warehousesStore'      => $warehouses['store'] ?? [],
            'warehousesTsc'        => $warehouses['tsc'] ?? [],
            'warehousesReseller'   => $warehouses['reseller'] ?? [],
            'warehousesKonsinyasi' => $warehouses['konsinyasi'] ?? [],
            'warehousesPanda'      => $warehouses['panda'] ?? [],
            'warehousesTransit'    => $warehouses['transit'] ?? [],
        ]);
    }

    /**
     * ==========================================
     * 2. EXPORT PDF
     * ==========================================
     */
    public function exportPdf($encrypted, Request $request)
    {
        $id = $this->decodeId($encrypted);
        $auth = $this->getAccurateAuth();
        
        $branchName = $request->input('branchName');
        $priceType  = $request->input('priceType', 'all');
        $warehouseFilter = (array) $request->input('warehouses', []);

        $item = $this->fetchItemDetail($id, $auth);
        if (!$item) abort(404, 'Gagal mengambil detail item.');

        $unitId = $this->getBaseUnitId($item);
        $prices = $this->getCompiledPrices($id, $branchName, $auth, $unitId);
        $warehouses = $this->processAndGroupWarehouses($item['detailWarehouseData'] ?? []);
        $imagesBase64 = $this->prepareImagesForPdf($item['detailItemImage'] ?? [], $auth);

        // Terapkan filter pilihan gudang user
        if (!empty($warehouseFilter)) {
            foreach ($warehouses as $key => $group) {
                if (!in_array($key, $warehouseFilter)) {
                    unset($warehouses[$key]);
                }
            }
        }

        $pdf = Pdf::loadView('items.karyawan.pdf', [
            'item'               => $item,
            'images'             => $imagesBase64,
            'priceType'          => $priceType,
            'branchName'         => $branchName,
            'session'            => $auth['session_id'],
            'warehouses'         => $warehouses, // Kirim array key-value ke view
            'prices'             => $prices['basePrices'],
            'partnerPrice'       => $prices['partnerPrice'],
            'unitPrices'         => $prices['unitPrices'],
            'hasMultiUnitPrices' => $prices['hasMultiUnitPrices'],
        ])->setPaper('a4', 'portrait');

        $cleanName = preg_replace('/[\/\\\\:*?"<>|]+/', '-', $item['name']);
        return $pdf->stream("Detail_{$cleanName}.pdf");
    }

    /**
     * ==========================================
     * 3. AJAX: AMBIL HARGA (REALTIME)
     * ==========================================
     */
    public function getPrice(Request $request, $id)
    {
        $auth = $this->getAccurateAuth();
        $branchName = $request->input('branchName');

        $item = $this->fetchItemDetail($id, $auth);
        if (!$item) return response()->json(['error' => 'Gagal mengambil detail item.'], 404);

        $unitId = $this->getBaseUnitId($item);
        $prices = $this->getCompiledPrices($id, $branchName, $auth, $unitId);

        return response()->json([
            'user'         => $prices['basePrices']['user'],
            'reseller'     => $prices['basePrices']['reseller'],
            'partnerPrice' => $prices['partnerPrice'],
            'unitPrices'   => $prices['unitPrices'],
            'hasMultiUnit' => $prices['hasMultiUnitPrices'],
            'unitIdUsed'   => $unitId,
        ]);
    }

    /**
     * ==========================================
     * 4. AJAX: REALTIME STOCK & OTHERS
     * ==========================================
     */
    public function getWarehouseStock(Request $request)
    {
        $itemId    = $request->id;
        $warehouse = $request->warehouse;
        $branch    = $request->branchName;

        $cacheKey = "stock_{$itemId}_{$warehouse}_{$branch}";

        return response()->json(Cache::remember($cacheKey, 30, function () use ($itemId, $warehouse, $branch) {
            $auth = $this->getAccurateAuth();
            $baseUrl = rtrim(config('services.accurate.base_api'), '/');

            $resp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $auth['access_token'],
                'X-Session-ID'  => $auth['session_id'],
            ])->timeout(30)->get("$baseUrl/item/get-on-sales.do", [
                'id' => $itemId,
                'warehouseName' => $warehouse,
                'branchName' => $branch,
            ]);

            if ($resp->status() == 429) return ['error' => true, 'message' => 'Limit API tercapai'];
            if (!$resp->successful()) return ['error' => true, 'message' => 'API gagal'];

            return ['stock' => $resp->json()['d']['availableStock'] ?? 0];
        }));
    }

    public function getBranches(Request $request)
    {
        $page = (int) $request->query('page', 1);
        
        return response()->json(Cache::remember("accurate_branches_page_{$page}", 3600, function () use ($page) {
            $auth = $this->getAccurateAuth();
            $baseUrl = rtrim(config('services.accurate.base_api'), '/');

            $resp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $auth['access_token'],
                'X-Session-ID'  => $auth['session_id'],
            ])->timeout(30)->retry(2, 2000)->get("{$baseUrl}/branch/list.do", [
                'sp.page'     => $page,
                'sp.pageSize' => 50,
            ]);

            $json = $resp->json();
            return [
                'data' => collect($json['d'] ?? [])->map(fn($b) => [
                    'id'   => $b['id'] ?? null,
                    'name' => $b['name'] ?? 'Tanpa Nama',
                ])->values(),
                'totalPage' => $json['sp']['pageCount'] ?? 1,
            ];
        }));
    }

    public function proxyImage(Request $request)
    {
        $file = $request->query('file');
        $auth = $this->getAccurateAuth();

        $imageUrl = 'https://odin.accurate.id' . $file . '?session=' . $auth['session_id'];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $auth['access_token'],
        ])->get($imageUrl);

        if (!$response->successful()) {
            return response()->file(public_path('images/noimage.jpg'));
        }

        return response($response->body(), 200)
            ->header('Content-Type', $response->header('Content-Type'))
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function getItemImage(Request $request)
    {
        $file = $request->query('file');
        $session = $request->query('session');

        if (!$file || !$session) return response("", 200);

        try {
            $file = strpos($file, '/') !== 0 ? '/' . $file : $file;
            $url = "https://odin.accurate.id{$file}?session={$session}";

            $resp = Http::timeout(30)->retry(2, 2000)->get($url);
            if (!$resp->successful()) return response("", 200);

            return base64_encode($resp->body());
        } catch (\Throwable $e) {
            return response("", 200);
        }
    }

    /**
     * ==========================================
     * 5. HELPER METHODS (INTERNAL LOGIC)
     * ==========================================
     */
    private function decodeId($encrypted)
    {
        $decoded = Hashids::decode($encrypted);
        $id = $decoded[0] ?? null;
        if (!$id) abort(404, 'ID item tidak valid.');
        return $id;
    }

    private function getAccurateAuth(): array
    {
        $status = strtoupper(trim(Auth::user()->status ?? ''));
        $label = in_array($status, ['GLOBAL', 'RESELLER']) ? $status : 'GLOBAL';
        return AccurateGlobal::token($label);
    }

    private function fetchItemDetail($id, $auth)
    {
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');
        $resp = Http::withHeaders([
            'Authorization' => "Bearer {$auth['access_token']}",
            'X-Session-ID'  => $auth['session_id'],
        ])->timeout(30)->retry(2, 2000)->get("$baseUrl/item/detail.do", ['id' => $id]);

        return $resp->json()['d'] ?? null;
    }

    private function getBaseUnitId($item)
    {
        $firstWH = $item['detailWarehouseData'][0] ?? null;
        if (!$firstWH) return 50; // Default PCS

        $rawUnit = explode(' ', $firstWH['balanceUnit'] ?? '');
        $unitName = strtoupper($rawUnit[1] ?? 'PCS');

        return $this->unitMap[$unitName] ?? 50;
    }

    /**
     * Optimized: Mengambil 3 harga sekaligus secara paralel menggunakan Http::pool
     */
    private function getCompiledPrices($id, $branchName, $auth, $unitId): array
    {
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');
        $headers = [
            'Authorization' => 'Bearer ' . $auth['access_token'],
            'X-Session-ID'  => $auth['session_id'],
        ];

        // Fetch semua API Harga secara PARALEL untuk menghemat waktu loading
        $responses = Http::pool(fn (Pool $pool) => [
            $pool->as('user')->withHeaders($headers)->get("$baseUrl/item/get-selling-price.do", [
                'id' => $id, 'branchName' => $branchName,
            ]),
            $pool->as('reseller')->withHeaders($headers)->get("$baseUrl/item/get-selling-price.do", [
                'id' => $id, 'branchName' => $branchName,
                'priceCategoryName' => 'RESELLER', 'discountCategoryName' => 'RESELLER',
            ]),
            $pool->as('partner')->withHeaders($headers)->get("$baseUrl/item/get-selling-price.do", [
                'id' => $id, 'branchName' => $branchName,
                'priceCategoryName' => 'TWINCOM PATNER', 'discountCategoryName' => 'TWINCOM PATNER',
            ]),
        ]);

        $userData     = $responses['user']->ok() ? ($responses['user']->json()['d'] ?? []) : [];
        $resellerData = $responses['reseller']->ok() ? ($responses['reseller']->json()['d'] ?? []) : [];
        $partnerData  = $responses['partner']->ok() ? ($responses['partner']->json()['d'] ?? []) : [];

        // Hitung Base Price
        $userPrice     = $this->applyDiscount($this->getPriceByUnitId($userData, $unitId), $userData);
        $resellerPrice = $this->applyDiscount($this->getPriceByUnitId($resellerData, $unitId), $resellerData);
        $partnerPrice  = $this->applyDiscount($this->getPriceByUnitId($partnerData, $unitId), $partnerData);

        // Hitung Multi-Unit Prices
        $unitPrices = [];
        $priceCategories = [
            'user'     => $userData, 
            'reseller' => $resellerData, 
            'partner'  => $partnerData
        ];

        foreach ($priceCategories as $type => $data) {
            if (!isset($data['unitPriceRule'])) continue;
            
            foreach ($data['unitPriceRule'] as $r) {
                $uid = $r['unitId'];
                $unitName = array_search($uid, $this->unitMap, true) ?? $uid;
                
                $priceAfterDisc = $this->applyDiscount($r['price'], $data);
                $unitPrices[$unitName][$type] = $priceAfterDisc;
            }
        }

        return [
            'basePrices'         => ['user' => $userPrice, 'reseller' => $resellerPrice],
            'partnerPrice'       => $partnerPrice,
            'unitPrices'         => $unitPrices,
            'hasMultiUnitPrices' => count($unitPrices) > 1,
        ];
    }

    private function applyDiscount($price, $data)
    {
        if (isset($data['discountRule'][0]['discount'])) {
            $disc = floatval($data['discountRule'][0]['discount']);
            return $price - ($price * $disc / 100);
        }
        return $price;
    }

    private function getPriceByUnitId($data, $unitId)
    {
        if (!isset($data['unitPriceRule'])) return $data['unitPrice'] ?? 0;
        foreach ($data['unitPriceRule'] as $rule) {
            if ((int)$rule['unitId'] === (int)$unitId) return $rule['price'];
        }
        return $data['unitPrice'] ?? ($data['unitPriceRule'][0]['price'] ?? 0);
    }

    private function processAndGroupWarehouses($rawWarehouses): array
    {
        $processed = collect($rawWarehouses)
            ->map(function ($wh) {
                $raw = trim($wh['balanceUnit'] ?? '');
                preg_match('/^([\d.,]+)/', $raw, $m);
                
                $first = isset($m[1]) ? (float) str_replace(',', '.', str_replace('.', '', $m[1])) : null;
                $balance = $wh['balance'] ?? $first;
                
                preg_match_all('/\b([A-Za-z]+)\b/', $raw, $units);
                
                if (count($units[1]) > 1 || ($first !== null && abs($first - $balance) > 0.001)) {
                    $wh['unit_display'] = $raw;
                } else {
                    $wh['unit_display'] = strtoupper(preg_replace('/^[\d.,]+\s+/', '', $raw));
                }
                return $wh;
            })
            ->filter(fn($w) => ($w['balance'] ?? 0) > 0)
            ->values();

        $groups = [
            'store'    => ['TSTORE KAYUTANGI','TSTORE BANJARBARU A. YANI','TSTORE BANJARBARU P. BATUR','TSTORE BELITUNG','TSTORE MARTAPURA','TDC','STORE PALANGKARAYA','LANDASAN ULIN', 'TDC-2'],
            'tsc'      => ['TSC BANJARBARU A. YANI','TSC BANJARBARU P. BATUR','TSC BELITUNG','TSC KAYUTANGI','TSC LANDASAN ULIN','TSC MARTAPURA','TSC PALANGKARAYA'],
            'panda'    => ['PANDA STORE BANJARBARU','PANDA SC BANJARBARU', 'PANDA STORE LANDASAN ULIN'],
            'reseller' => ['RESELLER ZAKI','RESELLER MARDANI'],
            'transit'  => ['TRANSIT (AOL SYSTEM)'],
        ];

        $result = [];
        foreach ($groups as $key => $names) {
            $result[$key] = $processed->filter(fn($w) => in_array(strtoupper($w['name'] ?? ''), $names))->values();
        }
        
        $result['konsinyasi'] = $processed->filter(fn($w) => isset($w['description']) && Str::contains(strtolower($w['description']), 'konsinyasi'))->values();

        return $result;
    }

    /**
     * Optimized: Menghilangkan HTTP Request & getimagesizefromstring agar loading web instan
     */
    private function prepareImagesForWeb($imagesData, $auth): array
    {
        $images = [];
        $files = collect($imagesData)->pluck('fileName')->filter()->values();

        foreach ($files as $file) {
            // Buat nama cache unik berdasarkan nama file
            $cacheKey = 'img_dim_' . md5($file);

            // Ingat dimensi gambar selama 30 hari (86400 detik * 30)
            $dimensions = Cache::remember($cacheKey, 86400 * 30, function () use ($file, $auth) {
                $imageUrl = "https://odin.accurate.id{$file}?session={$auth['session_id']}";
                $response = Http::withHeaders(['Authorization' => "Bearer {$auth['access_token']}"])->get($imageUrl);

                if ($response->successful()) {
                    [$width, $height] = getimagesizefromstring($response->body());
                    return ['width' => $width, 'height' => $height];
                }
                
                // Fallback default jika API gagal agar tidak error
                return ['width' => 800, 'height' => 800]; 
            });

            $images[] = [
                'file'   => $file,
                'url'    => route('proxy.image', ['file' => $file]),
                'width'  => $dimensions['width'],
                'height' => $dimensions['height'],
            ];
        }
        
        return $images;
    }

    private function prepareImagesForPdf($imagesData, $auth): array
    {
        $base64 = [];
        $files = collect($imagesData)->pluck('fileName')->filter()->values();

        foreach ($files as $file) {
            try {
                $resp = Http::withHeaders([
                    'Authorization' => "Bearer {$auth['access_token']}",
                    'X-Session-ID'  => $auth['session_id'],
                ])->timeout(30)->retry(2, 2000)->get("https://odin.accurate.id{$file}?session={$auth['session_id']}");

                if ($resp->successful()) {
                    $base64[] = 'data:image/jpeg;base64,' . base64_encode($resp->body());
                }
            } catch (\Throwable $e) {}
        }
        return $base64;
    }
}
<?php

namespace App\Http\Controllers;

use App\Helpers\AccurateGlobal;
use App\Models\AccurateAccount;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\Accurate\CategoryService;
use App\Services\Accurate\ItemService;
use App\Services\Accurate\PriceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Vinkla\Hashids\Facades\Hashids;

class AdminController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();

        // 1. Total Pengguna & Pertumbuhannya
        $totalUsers = User::count();
        $usersLastMonth = User::whereMonth('created_at', $lastMonth->month)->count();
        $usersThisMonth = User::whereMonth('created_at', $now->month)->count();
        $userGrowth = $this->calculateGrowth($usersLastMonth, $usersThisMonth);

        // 2. Aktivitas Hari Ini & Perbandingan dengan Hari Kemarin
        $logToday = ActivityLog::whereDate('created_at', Carbon::today())->count();
        $logYesterday = ActivityLog::whereDate('created_at', Carbon::yesterday())->count();
        $logGrowth = $this->calculateGrowth($logYesterday, $logToday);

        // 3. Akun Accurate & Pertumbuhannya
        $totalAccurate = AccurateAccount::count();
        $accurateLastMonth = AccurateAccount::whereMonth('created_at', $lastMonth->month)->count();
        $accurateThisMonth = AccurateAccount::whereMonth('created_at', $now->month)->count();
        $accurateGrowth = $this->calculateGrowth($accurateLastMonth, $accurateThisMonth);

        // 4. Reseller Aktif & Pertumbuhannya
        $totalReseller = User::where('status', 'reseller')->count();
        $resellerLastMonth = User::where('status', 'reseller')->whereMonth('created_at', $lastMonth->month)->count();
        $resellerThisMonth = User::where('status', 'reseller')->whereMonth('created_at', $now->month)->count();
        $resellerGrowth = $this->calculateGrowth($resellerLastMonth, $resellerThisMonth);

        // Log Aktivitas Terbaru (Maksimal 5)
        $recentLogs = ActivityLog::with('causer')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalUsers', 'userGrowth', 
            'logToday', 'logGrowth',
            'totalAccurate', 'accurateGrowth',
            'totalReseller', 'resellerGrowth',
            'recentLogs'
        ));
    }

    /**
     * Fungsi helper untuk menghitung persentase pertumbuhan.
     */
    private function calculateGrowth($oldValue, $newValue)
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0; // Jika sebelumnya 0 dan sekarang ada, naik 100%
        }
        return round((($newValue - $oldValue) / $oldValue) * 100, 1);
    }

    public function viewUser(Request $request) {

        $query = User::query()->orderBy('name', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status); // pastikan ada field 'status' di tabel user
        }

        $users = $query->get();
        $totalReseller = User::where('status', 'reseller')->count();
        $totalKaryawan = User::where('status', 'karyawan')->count();
        $totalAdmin    = User::where('status', 'admin')->count();
        $totalTwincomPatner    = User::where('status', 'twincom patner')->count();
        $totalUsers = User::count();

        return view('admin.users-index', compact('users', 'totalReseller', 'totalKaryawan', 'totalAdmin', 'totalUsers', 'totalTwincomPatner'));
    }

    public function logActivity(Request $request)
    {
        $loginQuery = ActivityLog::query()
            ->where('description', 'like', '%sedang melakukan login%');

        if ($request->filled('user')) {
            $loginQuery->where('log_name', $request->user);
        }

        if ($request->filled('status')) {
            $loginQuery->whereHas('causer', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        if ($request->filled('start_date')) {
            $loginQuery->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $loginQuery->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $loginQuery->where(function ($q) use ($search) {
                $q->where('log_name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $loginActivities = $loginQuery->latest()->paginate(50);

        return view('admin.log-activity', ['activities' => $loginActivities]);
    }


    public function searchUser(Request $request)
    {
        $search = $request->input('q');

        $results = Activity::select('log_name')
            ->where('log_name', 'like', "%{$search}%")
            ->distinct()
            ->limit(10)
            ->pluck('log_name');

        return response()->json($results);
    }

    public function autoLogout(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Cari aktivitas login terakhir user yang belum memiliki logout_time
        $loginActivity = Activity::where('log_name', $user->name)
            ->where('description', 'like', '%sedang melakukan login%')
            ->whereNull('logout_time')
            ->latest('created_at')
            ->first();

        if ($loginActivity) {
            // Catat waktu logout sekarang
            $loginActivity->logout_time = now();
            $loginActivity->save();
        }

        return response()->json(['message' => 'Logout time recorded']);
    }

    public function create () 
    {
        $accounts = AccurateAccount::orderBy('label')->get(['id','label']);
        $user = new User();
        return view('admin.users.create', compact('user','accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|unique:users,email',
            'password' => 'required|min:6',
            'status' => 'required|string',
            'accurate_account_id' => ['nullable','exists:accurate_accounts,id'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'],
            'accurate_account_id' => $data['accurate_account_id'] ?? null,
        ]);

        return redirect()->route('users.create')->with('succes', 'Data berhasil ditambahkan');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users2.detail', compact('user'));
    }

    // ===============================
    //              INDEX
    // ===============================
    public function indexItems(
        Request $request,
        ItemService $items,
        CategoryService $categories
    ) {
        $data = $items->fetchItemsForList($request);
        // dd($data);
        return view('admin.items', [
            'items'      => $data['items'],
            'page'       => $data['page'],
            'pageCount'  => $data['pageCount'],
            'totalItems' => $data['totalItems'],
            'filters'    => $data['filters'],
            'categories' => $categories->all(),
        ]);
    }

    // ===============================
    //          API LIST JSON
    // ===============================
    public function apiList(
        Request $request,
        ItemService $items
    ) {
        return response()->json(
            $items->fetchItemsForList($request)
        );
    }

    // ===============================
    //           AJAX PRICE
    // ===============================
    public function ajaxPrice(
        Request $request,
        PriceService $prices
    ) {
        $id   = (int) $request->query('id');
        $mode = $request->query('mode', 'USER');

        if (!$id) {
            return response()->json(['price' => 0]);
        }

        return response()->json([
            'price' => $prices->get($id, $mode),
            'cache' => true,
        ]);
    }

    // ===============================
    //            EXPORT PDF
    // ===============================
    public function exportPdf1(
        Request $request,
        ItemService $items,
        PriceService $prices
    ) {
        $data = $items->fetchItemsForList($request);

        $priceCategory = $data['filters']['priceMode'] === 'reseller'
            ? 'RESELLER'
            : 'USER';

        $itemsWithPrice = $data['items']->map(function ($item) use ($prices, $priceCategory) {
            $item['price'] = $prices->get($item['id'], $priceCategory);
            return $item;
        });

        $pdf = Pdf::loadView('items.pdf', [
            'items'   => $itemsWithPrice,
            'filters' => $data['filters'],
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Daftar Produk.pdf');
    }
        private $unitMap = [
            '1'       => 52850,
            'BATANG'  => 53550,
            'BOX'     => 53950,
            'BTL'     => 53200,
            'CAM'     => 53450,
            'DUS'     => 53300,
            'HPP'     => 52950,
            'IKAT'    => 53400,
            'KALENG'  => 53600,
            'KARUNG'  => 53700,
            'KG'      => 53900,
            'KLG'     => 53350,
            'METER'   => 52701,
            'MTR'     => 52750,
            'PACK'    => 53000,
            'PAJAK'   => 53750,
            'PAKET'   => 53100,
            'PCH'     => 53151,
            'PCS'     => 50,
            'POTONG'  => 53500,
            'RIT'     => 53650,
            'ROLL'    => 52900,
            'SAK'     => 53150,
            'SET'     => 53800,
            'UNIT'    => 53050,
            'RIM'     => 53850,
        ];

        private function getPriceByUnitId($data, $unitId)
        {
            if (!isset($data['unitPriceRule'])) {
                return $data['unitPrice'] ?? 0;
            }

            foreach ($data['unitPriceRule'] as $rule) {
                if ((int)$rule['unitId'] === (int)$unitId) {
                    return $rule['price'];
                }
            }

            // fallback
            return $data['unitPrice'] ?? ($data['unitPriceRule'][0]['price'] ?? 0);
        }
        /**
         * ============================
         *  HALAMAN DETAIL (FINAL OPTIMAL)
         * ============================
         */
        public function showItems($encrypted, Request $request)
        {
            $decoded = Hashids::decode($encrypted);
            $id = $decoded[0] ?? null;
            if (!$id) abort(404, 'ID item tidak valid.');

            $status = strtoupper(trim(Auth::user()->status ?? ''));

                $label = in_array($status, ['GLOBAL', 'RESELLER'])
                    ? $status
                    : 'GLOBAL';
            $acc     = AccurateGlobal::token($label);
            $token   = $acc['access_token'];
            $session = $acc['session_id'];
            $branchName = $request->input('branchName');

            $baseUrl = rtrim(config('services.accurate.base_api'), '/');

            /** ---------------------------------------------
             * 1. DETAIL ITEM
             * --------------------------------------------- */
            $detailResp = Http::withHeaders([
                'Authorization' => "Bearer $token",
                'X-Session-ID'  => $session,
            ])->timeout(60)->retry(3, 300)->get("$baseUrl/item/detail.do", [
                'id' => $id,
            ]);

            $item = $detailResp->json()['d'] ?? null;
            $fileName = collect($item['detailItemImage'] ?? [])->pluck('fileName')->filter()->values()->toArray();
            if (!$item) return back()->with('error', 'Gagal mengambil detail item.');
            $note = $item['notes'];

            // =============================
            // UNIT ID berdasar gudang pertama
            // =============================
            $firstWH = $item['detailWarehouseData'][0] ?? null;

            $unitId = 50; // default PCS

            if ($firstWH) {
                // balanceUnit: "6 PCS"
                $rawUnit = explode(' ', $firstWH['balanceUnit'] ?? '');
                $unitName = strtoupper($rawUnit[1] ?? 'PCS');

                // cocokkan ke map
                if (isset($this->unitMap[$unitName])) {
                    $unitId = $this->unitMap[$unitName];
                }
            }

            // =============================
            // HARGA USER
            // =============================
            $defaultResp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID'  => $session,
            ])->get("$baseUrl/item/get-selling-price.do", [
                'id'         => $id,
                'branchName' => $branchName,
            ]);

            $userData = $defaultResp['d'] ?? [];
            $userPrice = $this->getPriceByUnitId($userData, $unitId);

            // apply discount
            if (isset($userData['discountRule'][0]['discount'])) {
                $disc = floatval($userData['discountRule'][0]['discount']);
                $userPrice -= ($userPrice * $disc / 100);
            }

            // =============================
            // HARGA RESELLER
            // =============================
            $resellerResp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID'  => $session,
            ])->get("$baseUrl/item/get-selling-price.do", [
                'id'                  => $id,
                'priceCategoryName'   => 'RESELLER',
                'discountCategoryName'=> 'RESELLER',
                'branchName'          => $branchName,
            ]);

            $resellerData = $resellerResp['d'] ?? [];
            $resellerPrice = $this->getPriceByUnitId($resellerData, $unitId);

            // apply discount
            if (isset($resellerData['discountRule'][0]['discount'])) {
                $disc = floatval($resellerData['discountRule'][0]['discount']);
                $resellerPrice -= ($resellerPrice * $disc / 100);
            }

            $prices = [
                'user'     => $userPrice,
                'reseller' => $resellerPrice,
            ];

            // =============================================
            // AMBIL SEMUA HARGA PER UNIT & DISKON PER UNIT
            // =============================================
            $unitPrices = [];

            if (isset($userData['unitPriceRule'])) {
                foreach ($userData['unitPriceRule'] as $r) {
                    $unitId = $r['unitId'];
                    $price  = $r['price'];        
                    $unitName = array_search($unitId, $this->unitMap, true) ?? $unitId;

                    $unitPrices[$unitName]['user'] = $price;
                }
            }

            if (isset($resellerData['unitPriceRule'])) {
                foreach ($resellerData['unitPriceRule'] as $r) {
                    $unitId = $r['unitId'];
                    $price  = $r['price'];
                    $unitName = array_search($unitId, $this->unitMap, true) ?? $unitId;

                    $unitPrices[$unitName]['reseller'] = $price;
                }
            }

            // apply discount per-unit (jika ada)
            foreach ($unitPrices as $unit => &$p) {
                if (isset($userData['discountRule'][0]['discount']) && isset($p['user'])) {
                    $disc = floatval($userData['discountRule'][0]['discount']);
                    $p['user'] -= ($p['user'] * $disc / 100);
                }
                if (isset($resellerData['discountRule'][0]['discount']) && isset($p['reseller'])) {
                    $disc = floatval($resellerData['discountRule'][0]['discount']);
                    $p['reseller'] -= ($p['reseller'] * $disc / 100);
                }
            }

            $hasMultiUnitPrices = count($unitPrices) > 1;

            /** ---------------------------------------------
             * 4. GAMBAR LIST
             * --------------------------------------------- */
            $fileName = collect($item['detailItemImage'] ?? [])
                ->pluck('fileName')   // <-- pakai thumbnail
                ->filter()
                ->values()
                ->toArray();

            /** ---------------------------------------------
             * 5. GUDANG AWAL (belum realtime)
             * --------------------------------------------- */
            $warehouses = collect($item['detailWarehouseData'] ?? [])
                ->map(function ($wh) {
                    $unitParts = explode(' ', $wh['balanceUnit'] ?? '');
                    $wh['unit'] = $unitParts[1] ?? null;
                    return $wh;
                });

            /** ---------------------------------------------
             * 6. GROUP GUDANG
             * --------------------------------------------- */
            $groups = [
                'store' => [
                    'TSTORE KAYUTANGI','TSTORE BANJARBARU A. YANI','TSTORE BANJARBARU P. BATUR',
                    'TSTORE BELITUNG','TSTORE MARTAPURA','TDC','STORE PALANGKARAYA','LANDASAN ULIN',
                ],
                'tsc' => [
                    'TSC BANJARBARU A. YANI','TSC BANJARBARU P. BATUR','TSC BELITUNG','TSC KAYUTANGI',
                    'TSC LANDASAN ULIN','TSC MARTAPURA','TSC PALANGKARAYA',
                ],
                'panda' => [
                    'PANDA STORE BANJARBARU','PANDA SC BANJARBARU', 'PANDA STORE LANDASAN ULIN',
                ],
                'reseller' => [
                    'RESELLER ZAKI','RESELLER MARDANI',
                ],
                'transit' => [
                    'TRANSIT (AOL SYSTEM)',
                ],
            ];

            /** ---------------------------------------------
             * 7. FILTER GUDANG PER KELOMPOK
             * --------------------------------------------- */
            foreach ($groups as $key => $names) {
                ${"warehouses" . ucfirst($key)} = $warehouses->filter(fn($w) =>
                    in_array(strtoupper($w['name'] ?? ''), $names)
                )->values();
            }

            $warehousesKonsinyasi = $warehouses->filter(function($w){
                return isset($w['description']) 
                    && Str::contains(strtolower($w['description']), 'konsinyasi');
            })->values();

            /** ---------------------------------------------
             * 8. PROSES UNIT (balanceUnit parsing)
             * --------------------------------------------- */
            $processUnit = function ($collection) {
                return $collection->map(function ($wh) {

                $raw = trim($wh['balanceUnit'] ?? '');

                if ($raw === '') {
                    $wh['unit_display'] = '';
                    return $wh;
                }

                // Ambil angka depan
                preg_match('/^([\d.,]+)/', $raw, $m);
                    $first = isset($m[1])
                        ? (float) str_replace(',', '.', str_replace('.', '', $m[1]))
                        : null;

                    $balance = isset($wh['balance']) ? (float)$wh['balance'] : $first;

                    // Ambil semua unit yg muncul (PCS, BOX, ROLL, METER, dll)
                    preg_match_all('/\b([A-Za-z]+)\b/', $raw, $units);

                    // Jika ada lebih dari 1 unit → tampil RAW
                    if (count($units[1]) > 1) {
                        $wh['unit_display'] = $raw;
                        return $wh;
                    }

                    // Jika angka depan tidak sama dengan balance → tampil RAW
                    if ($first !== null && abs($first - $balance) > 0.0001) {
                        $wh['unit_display'] = $raw;
                        return $wh;
                    }

                    // Jika cuma 1 unit → tampil unit saja (tanpa angka)
                    $unitOnly = preg_replace('/^[\d.,]+\s+/', '', $raw);
                    $wh['unit_display'] = strtoupper($unitOnly);

                    return $wh;
                });
            };

            // Apply processing
            $warehousesStore      = $processUnit($warehousesStore);
            $warehousesTsc        = $processUnit($warehousesTsc);
            $warehousesReseller   = $processUnit($warehousesReseller);
            $warehousesKonsinyasi = $processUnit($warehousesKonsinyasi);
            $warehousesPanda      = $processUnit($warehousesPanda);
            $warehousesTransit    = $processUnit($warehousesTransit);

            /** ---------------------------------------------
             * 9. HILANGKAN STOK 0
             * --------------------------------------------- */
            $warehousesStore      = $warehousesStore->filter(fn($w) => ($w['balance'] ?? 0) > 0)->values();
            $warehousesTsc        = $warehousesTsc->filter(fn($w) => ($w['balance'] ?? 0) > 0)->values();
            $warehousesReseller   = $warehousesReseller->filter(fn($w) => ($w['balance'] ?? 0) > 0)->values();
            $warehousesKonsinyasi = $warehousesKonsinyasi->filter(fn($w) => ($w['balance'] ?? 0) > 0)->values();
            $warehousesPanda      = $warehousesPanda->filter(fn($w) => ($w['balance'] ?? 0) > 0)->values();
            $warehousesTransit    = $warehousesTransit->filter(fn($w) => ($w['balance'] ?? 0) > 0)->values();

            $userBasePrice = $this->getPriceByUnitId($userData, $unitId);
            $resellerBasePrice = $this->getPriceByUnitId($resellerData, $unitId);

            $partnerPrice = $userBasePrice - (($userBasePrice - $resellerBasePrice) / 2);

            // dd(number_format($partnerPrice, 0, ',', '.'));

            return view('admin.detail', [
                'item'          => $item,
                'images'        => $fileName,
                'session'       => $session,
                'branchName'    => $branchName,
                'partnerPrice'  => $partnerPrice,
                'prices' => [
                    'user'     => $userPrice,
                    'reseller' => $resellerPrice,
                ],
                'note' => $note,
                'unitPrices' => $unitPrices,
                'hasMultiUnitPrices' => $hasMultiUnitPrices,
                // Kirim ke Blade
                'warehousesStore'      => $warehousesStore,
                'warehousesTsc'        => $warehousesTsc,
                'warehousesReseller'   => $warehousesReseller,
                'warehousesKonsinyasi' => $warehousesKonsinyasi,
                'warehousesPanda'      => $warehousesPanda,
                'warehousesTransit'    => $warehousesTransit,
            ]);
        }

        public function proxyImage(Request $request)
        {
            $file = $request->query('file');
            $session = $request->query('session');

            if (!$file || !$session) {
                return response('Missing params', 400);
            }

            // URL asli Accurate (WAJIB)
            $url = "https://odin.accurate.id{$file}?session={$session}";

            // Token & Session
            $status = strtoupper(trim(Auth::user()->status ?? ''));

                $label = in_array($status, ['GLOBAL', 'RESELLER'])
                    ? $status
                    : 'GLOBAL';
            $acc     = AccurateGlobal::token($label);
            $token = $acc['access_token'];
            $accurateSession = $acc['session_id'];

            // Request ke Accurate pakai header WAJIB
            $response = Http::withHeaders([
                'Authorization' => "Bearer $token",
                'X-Session-ID'  => $accurateSession,
            ])->get($url);

            if (!$response->successful()) {
                return response()->file(public_path('images/noimage.jpg'));
            }

            return response($response->body(), 200)
                ->header('Content-Type', $response->header('Content-Type') ?? 'image/jpeg')
                ->header('Cache-Control', 'public, max-age=3600');
        }


        public function getPrice(Request $request, $id)
        {
            $branchName = $request->input('branchName');

            $status = strtoupper(trim(Auth::user()->status ?? ''));

            $label = in_array($status, ['GLOBAL', 'RESELLER'])
                ? $status
                : 'GLOBAL';
            $acc     = AccurateGlobal::token($label);
            $token = $acc['access_token'];
            $session = $acc['session_id'];

            $baseUrl = rtrim(config('services.accurate.base_api'), '/');

            /** ---------------------------------------------
             * 1. DETAIL ITEM
             * --------------------------------------------- */
            $detailResp = Http::withHeaders([
                'Authorization' => "Bearer $token",
                'X-Session-ID'  => $session,
            ])->timeout(30)->retry(2, 2000)->get("$baseUrl/item/detail.do", [
                'id' => $id,
            ]);

            $item = $detailResp->json()['d'] ?? null;
            $fileName = collect($item['detailItemImage'] ?? [])->pluck('fileName')->filter()->values()->toArray();
            if (!$item) return back()->with('error', 'Gagal mengambil detail item.');
            $note = $item['notes'];

            // =============================
            // UNIT ID berdasar gudang pertama
            // =============================
            $firstWH = $item['detailWarehouseData'][0] ?? null;

            $unitId = 50; // default PCS

            if ($firstWH) {
                // contoh "6 PCS"
                $rawUnit = explode(' ', $firstWH['balanceUnit'] ?? '');
                $unitName = strtoupper($rawUnit[1] ?? 'PCS');

                if (isset($this->unitMap[$unitName])) {
                    $unitId = $this->unitMap[$unitName];
                }
            }

            // =============================
            // 2. HARGA USER
            // =============================
            $defaultResp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID'  => $session,
            ])->get("{$baseUrl}/item/get-selling-price.do", [
                'id'         => $id,
                'branchName' => $branchName,
            ]);

            $userData = $defaultResp['d'] ?? [];
            $userPrice = $this->getPriceByUnitId($userData, $unitId);

            if (isset($userData['discountRule'][0]['discount'])) {
                $disc = floatval($userData['discountRule'][0]['discount']);
                $userPrice -= ($userPrice * $disc / 100);
            }

            // =============================
            // 3. HARGA RESELLER
            // =============================
            $resellerResp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID'  => $session,
            ])->get("{$baseUrl}/item/get-selling-price.do", [
                'id'                  => $id,
                'branchName'          => $branchName,
                'priceCategoryName'   => 'RESELLER',
                'discountCategoryName'=> 'RESELLER',
            ]);

            $resellerData = $resellerResp['d'] ?? [];
            $resellerPrice = $this->getPriceByUnitId($resellerData, $unitId);

            if (isset($resellerData['discountRule'][0]['discount'])) {
                $disc = floatval($resellerData['discountRule'][0]['discount']);
                $resellerPrice -= ($resellerPrice * $disc / 100);
            }

            // =============================
            // 4. KUMPULKAN SEMUA HARGA PER UNIT/PACK
            // =============================
            $unitPrices = [];

            if (isset($userData['unitPriceRule'])) {
                foreach ($userData['unitPriceRule'] as $r) {
                    $uid  = $r['unitId'];
                    $price = $r['price'];

                    $unitName = array_search($uid, $this->unitMap, true) ?? $uid;
                    $unitPrices[$unitName]['user'] = $price;
                }
            }

            if (isset($resellerData['unitPriceRule'])) {
                foreach ($resellerData['unitPriceRule'] as $r) {
                    $uid  = $r['unitId'];
                    $price = $r['price'];

                    $unitName = array_search($uid, $this->unitMap, true) ?? $uid;
                    $unitPrices[$unitName]['reseller'] = $price;
                }
            }

            // apply discount per unit
            foreach ($unitPrices as $unit => &$p) {
                if (isset($userData['discountRule'][0]['discount']) && isset($p['user'])) {
                    $disc = floatval($userData['discountRule'][0]['discount']);
                    $p['user'] -= ($p['user'] * $disc / 100);
                }
                if (isset($resellerData['discountRule'][0]['discount']) && isset($p['reseller'])) {
                    $disc = floatval($resellerData['discountRule'][0]['discount']);
                    $p['reseller'] -= ($p['reseller'] * $disc / 100);
                }
            }

            $userBasePrice = $this->getPriceByUnitId($userData, $unitId);
            $resellerBasePrice = $this->getPriceByUnitId($resellerData, $unitId);

            $partnerPrice = $userBasePrice - (($userBasePrice - $resellerBasePrice) / 2);

            return response()->json([
                'user'             => $userPrice,
                'reseller'         => $resellerPrice,
                'partnerPrice'     => $partnerPrice,
                'unitPrices'       => $unitPrices,
                'hasMultiUnit'     => count($unitPrices) > 1,
                'unitIdUsed'       => $unitId,
            ]);
        }


        /**
         * ============================
         *  AJAX – REALTIME STOCK
         * ============================
         */
        public function getWarehouseStock(Request $request)
        {
            $itemId   = $request->id;
            $warehouse = $request->warehouse;
            $branch    = $request->branchName;
            $baseUrl = rtrim(config('services.accurate.base_api'), '/');

            $status = strtoupper(trim(Auth::user()->status ?? ''));

                $label = in_array($status, ['GLOBAL', 'RESELLER'])
                    ? $status
                    : 'GLOBAL';
            $acc     = AccurateGlobal::token($label);
            $token   = $acc['access_token'];
            $session = $acc['session_id'];

            $resp = Http::withHeaders([
                'Authorization' => "Bearer $token",
                'X-Session-ID'  => $session,
            ])->timeout(60)->retry(2, 2000)->get("$baseUrl/item/get-on-sales.do", [
                'id'            => $itemId,
                'warehouseName' => $warehouse,
                'branchName'    => $branch,
            ]);

            if (!$resp->successful()) {
                Log::info("API Accurate gagal");
                return response()->json([
                    'error' => true,
                    'message' => 'API Accurate gagal'
                ], 500);
            }

            $data = $resp->json();

            if (!isset($data['d']['availableStock'])) {
                Log::info("Error");
                return response()->json([
                    'error' => true,
                ], 500);
            }

            return [
                'stock' => $data['d']['availableStock']
            ];
        }

        public function getBranches(Request $request)
        {
            $page = (int) $request->query('page', 1);

            // Cache key dinamis per halaman
            $cacheKey = "accurate_branches_page_{$page}";

            // Cache selama 1 jam
            $cached = Cache::remember($cacheKey, 3600, function () use ($page) {

                $status = strtoupper(trim(Auth::user()->status ?? ''));

                $label = in_array($status, ['GLOBAL', 'RESELLER'])
                    ? $status
                    : 'GLOBAL';
                $acc     = AccurateGlobal::token($label);
                $token = $acc['access_token'];
                $session = $acc['session_id'];

                $baseUrl = rtrim(config('services.accurate.base_api'), '/');

                $resp = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'X-Session-ID'  => $session,
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
            });

            return response()->json($cached);
        }

        /**
         * AJAX: Load Image (base64)
         */
        public function getItemImage(Request $request)
        {
            $file = $request->query('file');
            $session = $request->query('session');

            if (!$file || !$session) {
                return response("", 200);
            }

            try {
                // Pastikan file diawali slash
                if (strpos($file, '/') !== 0) {
                    $file = '/' . $file;
                }

                $url = "https://odin.accurate.id{$file}?session={$session}";

                $resp = Http::timeout(30)->retry(2, 2000)->get($url);

                if (!$resp->successful()) {
                    return response("", 200);
                }

                return base64_encode($resp->body());

            } catch (\Throwable $e) {
                return response("", 200);
            }
        }

         public function exportPdf($encrypted, Request $request)
        {
            // =============================
            // 1. Decode ID Item
            // =============================
            $decoded = Hashids::decode($encrypted);
            $id = $decoded[0] ?? null;
            if (!$id) abort(404, 'ID item tidak valid.');

            // =============================
            // 2. Ambil Token Accurate
            // =============================
            $status = strtoupper(trim(Auth::user()->status ?? ''));

                $label = in_array($status, ['GLOBAL', 'RESELLER'])
                    ? $status
                    : 'GLOBAL';
            $acc     = AccurateGlobal::token($label);
            $token = $acc['access_token'];
            $session = $acc['session_id'];

            // FILTER dari user
            $branchName      = $request->input('branchName');
            $priceType       = $request->input('priceType', 'all');
            $warehouseFilter = (array) $request->input('warehouses', []);

            $baseUrl = rtrim(config('services.accurate.base_api'), '/');

            // =============================
            // 3. Ambil Detail Item
            // =============================
            $detailResp = Http::withHeaders([
                'Authorization' => "Bearer $token",
                'X-Session-ID'  => $session,
            ])->timeout(30)->retry(2, 2000)->get("$baseUrl/item/detail.do", ['id' => $id]);

            $item = $detailResp->json()['d'] ?? null;
            if (!$item) abort(404, 'Gagal mengambil detail item.');

            $note = $item['notes'] ?? '';

            // =============================
            // 4. Ambil Gambar Item (base64)
            // =============================
            $imagesBase64 = [];

            $imageList = collect($item['detailItemImage'] ?? [])
                ->pluck('fileName')
                ->filter()
                ->values()
                ->toArray();

            foreach ($imageList as $file) {
                $url = "https://odin.accurate.id{$file}?session={$session}";

                try {
                    $resp = Http::withHeaders([
                        'Authorization' => "Bearer $token",
                        'X-Session-ID'  => $session,
                    ])->timeout(30)->retry(2, 2000)->get($url);

                    if ($resp->successful()) {
                        $imagesBase64[] = 'data:image/jpeg;base64,' . base64_encode($resp->body());
                    }
                } catch (\Throwable $e) {}
            }

            // =============================
            // UNIT ID berdasar gudang pertama
            // =============================
            $firstWH = $item['detailWarehouseData'][0] ?? null;

            $unitId = 50; // default PCS

            if ($firstWH) {
                // balanceUnit: "6 PCS"
                $rawUnit = explode(' ', $firstWH['balanceUnit'] ?? '');
                $unitName = strtoupper($rawUnit[1] ?? 'PCS');

                // cocokkan ke map
                if (isset($this->unitMap[$unitName])) {
                    $unitId = $this->unitMap[$unitName];
                }
            }

            // =============================
            // HARGA USER
            // =============================
            $defaultResp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID'  => $session,
            ])->get("$baseUrl/item/get-selling-price.do", [
                'id'         => $id,
                'branchName' => $branchName,
            ]);

            $userData = $defaultResp['d'] ?? [];
            $userPrice = $this->getPriceByUnitId($userData, $unitId);

            // apply discount
            if (isset($userData['discountRule'][0]['discount'])) {
                $disc = floatval($userData['discountRule'][0]['discount']);
                $userPrice -= ($userPrice * $disc / 100);
            }

            // =============================
            // HARGA RESELLER
            // =============================
            $resellerResp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID'  => $session,
            ])->get("$baseUrl/item/get-selling-price.do", [
                'id'                  => $id,
                'priceCategoryName'   => 'RESELLER',
                'discountCategoryName'=> 'RESELLER',
                'branchName'          => $branchName,
            ]);

            $resellerData = $resellerResp['d'] ?? [];
            $resellerPrice = $this->getPriceByUnitId($resellerData, $unitId);

            // apply discount
            if (isset($resellerData['discountRule'][0]['discount'])) {
                $disc = floatval($resellerData['discountRule'][0]['discount']);
                $resellerPrice -= ($resellerPrice * $disc / 100);
            }

            $prices = [
                'user'     => $userPrice,
                'reseller' => $resellerPrice,
            ];

            // =============================================
            // AMBIL SEMUA HARGA PER UNIT & DISKON PER UNIT
            // =============================================
            $unitPrices = [];

            if (isset($userData['unitPriceRule'])) {
                foreach ($userData['unitPriceRule'] as $r) {
                    $unitId = $r['unitId'];
                    $price  = $r['price'];        
                    $unitName = array_search($unitId, $this->unitMap, true) ?? $unitId;

                    $unitPrices[$unitName]['user'] = $price;
                }
            }

            if (isset($resellerData['unitPriceRule'])) {
                foreach ($resellerData['unitPriceRule'] as $r) {
                    $unitId = $r['unitId'];
                    $price  = $r['price'];
                    $unitName = array_search($unitId, $this->unitMap, true) ?? $unitId;

                    $unitPrices[$unitName]['reseller'] = $price;
                }
            }

            // apply discount per-unit (jika ada)
            foreach ($unitPrices as $unit => &$p) {
                if (isset($userData['discountRule'][0]['discount']) && isset($p['user'])) {
                    $disc = floatval($userData['discountRule'][0]['discount']);
                    $p['user'] -= ($p['user'] * $disc / 100);
                }
                if (isset($resellerData['discountRule'][0]['discount']) && isset($p['reseller'])) {
                    $disc = floatval($resellerData['discountRule'][0]['discount']);
                    $p['reseller'] -= ($p['reseller'] * $disc / 100);
                }
            }

            $hasMultiUnitPrices = count($unitPrices) > 1;

            // =============================
            // 6. GUDANG – sesuai logic terbaru
            // =============================
            $warehouses = collect($item['detailWarehouseData'] ?? [])
                ->map(function ($wh) {
                    $raw = trim($wh['balanceUnit'] ?? '');

                    preg_match('/^([\d.,]+)/', $raw, $m);
                    $first = isset($m[1])
                        ? (float) str_replace(',', '.', str_replace('.', '', $m[1]))
                        : null;

                    $balance = $wh['balance'] ?? $first;

                    preg_match_all('/\b([A-Za-z]+)\b/', $raw, $units);

                    if (count($units[1]) > 1) {
                        $wh['unit_display'] = $raw;
                    } elseif ($first !== null && abs($first - $balance) > 0.001) {
                        $wh['unit_display'] = $raw;
                    } else {
                        $unitOnly = preg_replace('/^[\d.,]+\s+/', '', $raw);
                        $wh['unit_display'] = strtoupper($unitOnly);
                    }

                    return $wh;
                })
                ->filter(fn($w) => ($w['balance'] ?? 0) > 0)
                ->values();

            // =============================
            // 7. Kelompok Gudang
            // =============================
            $groups = [
                'store' => [
                    'TSTORE KAYUTANGI','TSTORE BANJARBARU A. YANI','TSTORE BANJARBARU P. BATUR',
                    'TSTORE BELITUNG','TSTORE MARTAPURA','TDC','STORE PALANGKARAYA','LANDASAN ULIN',
                ],
                'tsc' => [
                    'TSC BANJARBARU A. YANI','TSC BANJARBARU P. BATUR','TSC BELITUNG','TSC KAYUTANGI',
                    'TSC LANDASAN ULIN','TSC MARTAPURA','TSC PALANGKARAYA',
                ],
                'panda' => [
                    'PANDA STORE BANJARBARU','PANDA SC BANJARBARU', 'PANDA STORE LANDASAN ULIN',
                ],
                'reseller' => [
                    'RESELLER ZAKI','RESELLER MARDANI',
                ],
            ];

            $filteredGroups = [];

            foreach ($groups as $key => $names) {
                $filteredGroups[$key] = $warehouses->filter(
                    fn($w) => in_array(strtoupper($w['name']), $names)
                )->values();
            }

            // KONSINYASI
            $filteredGroups['konsinyasi'] = $warehouses->filter(function ($w) {
                return isset($w['description']) &&
                    str_contains(strtolower($w['description']), 'konsinyasi');
            })->values();

            foreach ($filteredGroups as $key => $group) {
                $filteredGroups[$key] = $group->map(function($w) use ($id, $branchName) {
                    $newStock = $this->getRealtimeStock($id, $w['name'], $branchName);
                    $w['balance'] = $newStock;

                    return $w;
                })->filter(fn($x) => $x['balance'] > 0)->values();
            }

            // =============================
            // 8. Filter Gudang sesuai pilihan user
            // =============================
            if (!empty($warehouseFilter)) {
                foreach ($filteredGroups as $key => $group) {
                    if (!in_array($key, $warehouseFilter)) {
                        unset($filteredGroups[$key]);
                    }
                }
            }

            $userBasePrice = $this->getPriceByUnitId($userData, $unitId);
            $resellerBasePrice = $this->getPriceByUnitId($resellerData, $unitId);

            $partnerPrice = $userBasePrice - (($userBasePrice - $resellerBasePrice) / 2);

            // =============================
            // 9. GENERATE PDF
            // =============================
            $pdf = Pdf::loadView('items.karyawan.pdf', [
                'item'       => $item,
                'images'     => $imagesBase64,
                'partnerPrice' => $partnerPrice,
                'prices' => [
                    'user'     => $userPrice,
                    'reseller' => $resellerPrice,
                ],
                'priceType'  => $priceType,
                'branchName' => $branchName,
                'warehouses' => $filteredGroups,
                'session'    => $session,
                'unitPrices' => $unitPrices,
                'hasMultiUnitPrices' => $hasMultiUnitPrices,
            ])->setPaper('a4', 'portrait');

            $cleanName = preg_replace('/[\/\\\\:*?"<>|]+/', '-', $item['name']);

            return $pdf->stream("Detail_{$cleanName}.pdf");
        }

        private function getRealtimeStock($itemId, $warehouseName, $branchName)
        {
            $status = strtoupper(trim(Auth::user()->status ?? ''));

                $label = in_array($status, ['GLOBAL', 'RESELLER'])
                    ? $status
                    : 'GLOBAL';
            $acc     = AccurateGlobal::token($label);
            $token = $acc['access_token'];
            $session = $acc['session_id'];
            $baseUrl = rtrim(config('services.accurate.base_api'), '/');

            $resp = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'X-Session-ID'  => $session,
            ])->timeout(30)->retry(2, 2000)->get("$baseUrl/item/get-on-sales.do", [
                'id'            => $itemId,
                'warehouseName' => $warehouseName,
                'branchName'    => $branchName,
            ]);

            return $resp->json()['d']['availableStock'] ?? 0;
        }

        public function indexRakitPc ()
        {
            return view("admin.simulasi.rakitpc");
        }

        public function indexRakitCctv ()
        {
            return view("admin.simulasi.rakitcctv");
        }
    }

<?php

namespace App\Http\Controllers;

use App\Helpers\AccurateGlobal;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GaleriController extends Controller
{
    // public function index ()
    // {
    //     $seconds = DB::table('draft_items')->whereIn('status', ['unkeep', 'keep'])
    //         ->get()
    //         ->groupBy('item_no');

    //     return view('admin.galeriSecond.index', compact('seconds'));
    // }

    public function itemReady ()
    {
        $itemReady = DB::table('draft_items')->whereIn('status', ['unkeep'])->get();
        return view('admin.galeriSecond.ready', compact('itemReady'));
    }

    public function create()
    {
        return view("admin.galeriSecond.create");
    }

    public function updateStatus(Request $request, $id)
    {
        $allowed = ['unkeep', 'keep', 'sold'];

        if (!in_array($request->status, $allowed)) {
            return response()->json([
                'success' => false,
                'message' => 'Status tidak valid'
            ]);
        }

        $data = [
            'status' => $request->status,
            'updated_at' => now()
        ];

        // 🔥 kalau KEEP wajib ada SO
        if ($request->status === 'keep') {

            if (!$request->so_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sales Order wajib dipilih'
                ]);
            }

            $data['so_id'] = $request->so_id;
        }

        // 🔥 kalau CANCEL (balik ke unkeep) → hapus SO
        if ($request->status === 'unkeep') {
            $data['so_id'] = null;
        }

        DB::table('draft_items')
            ->where('id', $id)
            ->update($data);

        return response()->json(['success' => true]);
    }

    public function getItems(Request $request)
    {
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');
        $status = strtoupper(trim(Auth::user()->status ?? ''));
        $label = in_array($status, ['GLOBAL', 'RESELLER']) ? $status : 'GLOBAL';

        $acc = AccurateGlobal::token($label);
        $token = $acc['access_token'];
        $session = $acc['session_id'];

        $search     = trim($request->query('search', ''));

        $page = 1;

        $query = [
            'sp.page' => $page,
            'sp.pageSize' => 100,
            'fields' => 'id,name,no,availableToSell,manageSN,itemCategory',
            'filter.suspended' => false,
        ];

        if ($request->search) {
            $query['filter.keywords.op'] = 'CONTAIN';
            $query['filter.keywords.val[0]'] = $search;
        }

        $resp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session,
        ])->get("{$baseUrl}/item/list.do", $query);

        $items = $resp->json()['d'] ?? [];

        $filtered = collect($items)->filter(function ($item) {
            $name = trim($item['name'] ?? '');

            $words = preg_split('/\s+/', $name);
            $lastWord = strtoupper(end($words));

            return $lastWord === '2ND';
        })->values();

        return response()->json($filtered);
    }

    public function usedSN()
    {
        return response()->json(
            DB::table('draft_items')->pluck('sn')
        );
    }

    public function getSN(Request $request)
    {
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');
        $status = strtoupper(trim(Auth::user()->status ?? ''));
        $label = in_array($status, ['GLOBAL', 'RESELLER']) ? $status : 'GLOBAL';

        $acc = AccurateGlobal::token($label);
        $token = $acc['access_token'];
        $session = $acc['session_id'];

        $resp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session,
        ])->get("{$baseUrl}/report/serial-number-per-warehouse.do", [
            'itemNo' => $request->itemNo
        ]);

        $data = $resp->json()['d'] ?? [];

        // cukup filter basic saja (optional)
        $filtered = collect($data)->filter(function ($item) {
            return $item['quantity'] > 0;
        })->values();

        return response()->json($filtered);
    }

    public function getSaleOrder(Request $request)
    {
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');
        $status = strtoupper(trim(Auth::user()->status ?? ''));
        $label = in_array($status, ['GLOBAL', 'RESELLER']) ? $status : 'GLOBAL';

        $acc = AccurateGlobal::token($label);
        $token = $acc['access_token'];
        $session = $acc['session_id'];

        $search     = trim($request->query('search', ''));

        $page = 1;

        $query = [
            'sp.page' => $page,
            'sp.pageSize' => 100,
            'fields' => 'id,number,status,statusName',
        ];

        if ($request->search) {
            $query['filter.keywords.op'] = 'CONTAIN';
            $query['filter.keywords.val[0]'] = $search;
        }

        $resp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session,
        ])->get("{$baseUrl}/sales-order/list.do", $query);

        return response()->json($resp->json()['d'] ?? []);
    }

    public function getPrice(Request $request)
    {
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');
        $status = strtoupper(trim(Auth::user()->status ?? ''));
        $label = in_array($status, ['GLOBAL', 'RESELLER']) ? $status : 'GLOBAL';

        $acc = AccurateGlobal::token($label);
        $token = $acc['access_token'];
        $session = $acc['session_id'];

        $no = $request->query('no');

        $resp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session,
        ])->get("{$baseUrl}/item/get-selling-price.do", [
            'no' => $no,
            'priceCategoryName' => 'RESELLER',
        ]);

        return response()->json($resp->json()['d'] ?? []);
    }

    public function add(Request $request)
    {
        $request->validate([
            'gambar' => 'required|array|max:3',
            'gambar.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $tanggalReal = $request->tanggal_real;
        $tanggalFake = $tanggalReal 
            ? Carbon::parse($tanggalReal)->subDays(3) 
            : null;

        // 🔥 simpan item dulu
        $itemId = DB::table('draft_items')->insertGetId([
            'user_id' => auth()->id(),
            'item_id' => $request->item_id,
            'item_no' => $request->item_no,
            'item_name' => $request->item_name,
            'category' => $request->category,
            'category_id' => $request->category_id,
            'sn' => $request->sn,
            'warehouse' => $request->warehouse,
            'qty' => $request->qty,
            'type_garansi' => $request->type_garansi,
            'tanggal_real' => $tanggalReal,
            'tanggal_fake' => $tanggalFake,
            'status' => 'unkeep',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 🔥 upload banyak gambar
        foreach ($request->file('gambar') as $file) {

            $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
            $storagePath = 'item-'.$request->sn.'/'.$filename;

            $response = Http::withHeaders([
                'apikey' => env('SUPABASE_KEY'),
                'Authorization' => 'Bearer '.env('SUPABASE_KEY'),
            ])
            ->attach('file', fopen($file->getRealPath(), 'r'), $filename)
            ->post(env('SUPABASE_URL').'/storage/v1/object/'.env('SUPABASE_BUCKET').'/'.$storagePath);

            if (!$response->successful()) {
                continue; // skip kalau gagal
            }

            $url = env('SUPABASE_URL').'/storage/v1/object/public/'.env('SUPABASE_BUCKET').'/'.$storagePath;

            // 🔥 simpan ke tabel gambar
            DB::table('item_images')->insert([
                'item_id' => $itemId,
                'url' => $url,
                'created_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }
}

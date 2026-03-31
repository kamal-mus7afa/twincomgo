<?php

namespace App\Http\Controllers;

use App\Helpers\AccurateGlobal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class GaleriController extends Controller
{
    public function index ()
    {
        return view("admin.galeriSecond.index");
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
            'fields' => 'id,name,no,availableToSell,manageSN',
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

        return response()->json($resp->json()['d']);
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
}

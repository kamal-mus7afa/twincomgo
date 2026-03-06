<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\AccurateGlobal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ListController extends Controller
{
    public function search(Request $request) 
    {
        $acc = AccurateGlobal::token();
        $token   = $acc['access_token'];
        $session = $acc['session_id'];
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');

        $q = $request->q;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session,
        ])->get("$baseUrl/item/list.do", [
            'fields' => 'id,name,no',
            'filter.keywords.op' => 'CONTAIN',
            'filter.keywords.val[0]' => $q, 
        ])->json();

        return response()->json($response['d'] ?? []);
    }

    public function add(Request $request)
    {
        $acc = AccurateGlobal::token();
        $token   = $acc['access_token'];
        $session = $acc['session_id'];
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');

        $itemId = $request->item_id;
        $warehouseName = $request->warehouseName;
        $priceCategory = $request->priceCategory;

        $item = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session,
        ])->get("$baseUrl/item/detail.do", [
            'id' => $itemId
        ])->json();

        $stock = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session,
        ])->get("$baseUrl/item/get-stock.do", [
            'id' => $itemId,
            'warehouseName' => $warehouseName,
        ])->json();

        $resp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID'  => $session,
        ])->get("$baseUrl/item/get-selling-price.do", [
            'id' => $itemId,
            'priceCategoryName' => $priceCategory,
        ])->json();

        $data = $item['d'];
        $price = $resp['d'];
        $availableToSell = $stock['d'];
        Log::info($availableToSell);

        $list = session('list', []);

        $list[] = [
            'id' => $data['id'],
            'code' => $data['no'],
            'name' => $data['name'],
            'stock' => $availableToSell['availableStock'],
            'price' => $price['unitPrice']
                ?? ($price['unitPriceRule'][0]['price'] ?? 0),
        ];

        session(['list' => $list]);

        return response()->json($list);
        
    }

    public function index()
    {
        $list = session('list', []);
        return view('items', compact('list'));
    }

    public function clear()
    {
        session()->forget('list');
        return response()->json(['ok' => true]);
    }

    public function remove(Request $request)
    {
        $index = $request->index;

        $list = session('list', []);

        if (isset($list[$index])) {
            unset($list[$index]);
        }

        // rapikan index array
        $list = array_values($list);

        session(['list' => $list]);

        return response()->json($list);
    }

    public function pdf()
    {
        $list = session('list', []);

        $pdf = Pdf::loadView('pdf.list', compact('list'));

        return $pdf->stream('list-barang.pdf');
    }

}

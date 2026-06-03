<?php

namespace App\Http\Controllers;

use App\Exports\ItemsExport;
use App\Services\Accurate\CategoryService;
use App\Services\Accurate\ItemService;
use App\Services\Accurate\PriceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ItemController extends Controller
{
    // ===============================
    //              INDEX
    // ===============================
    public function index(
        Request $request,
        ItemService $items,
        CategoryService $categories
    ) {
        $data = $items->fetchItemsForList($request);
        // dd($data);
        return view('items.index', [
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
    //         AJAX PRICE
    // ===============================
    public function ajaxPrice(
        Request $request,
        PriceService $prices
    ) {
        $id      = (int) $request->query('id');
        $rawMode = $request->query('mode', 'default'); 

        if (!$id) {
            return response()->json(['price' => 0]);
        }

        // Tangkap kedua kemungkinan kata kunci
        $mode = match (strtolower($rawMode)) {
            'reseller'                 => 'RESELLER',
            'patner', 'twincom patner' => 'TWINCOM PATNER', // ✅ Tambahkan 'twincom patner' di sini
            default                    => 'USER',
        };

        return response()->json([
            'price' => $prices->get($id, $mode),
            'cache' => true,
        ]);
    }

    // ===============================
    //          EXPORT PDF
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

        // PERBAIKAN: Bungkus $data['items'] dengan collect()
        $itemsWithPrice = collect($data['items'])->map(function ($item) use ($prices, $priceCategory) {
            $item['price'] = $prices->get($item['id'], $priceCategory);
            return $item;
        });

        $pdf = Pdf::loadView('items.pdf', [
            'items'   => $itemsWithPrice,
            'filters' => $data['filters'],
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Daftar Produk.pdf');
    }

    public function exportExcel1(
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

        return Excel::download(
            new ItemsExport($itemsWithPrice),
            'Daftar Produk.xlsx'
        );
    }
}
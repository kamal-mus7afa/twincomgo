<?php

namespace App\Services\Accurate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ItemService
{
    public function fetchItemsForList(Request $request): array
    {
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');

        $perPage   = (int) $request->query('per_page', 10);
        $pageWeb   = max(1, (int) $request->query('page', 1));
        $offset    = ($pageWeb - 1) * $perPage;

        $search    = trim($request->query('search', ''));
        $categoryId = (array) $request->query('category_id', []); 
        $stokAda   = $request->query('stok_ada', '1');
        $stockType = $request->query('stock_type', 'availableToSell');
        $priceMode = $request->query('price_mode', 'default');

        $minPrice = $request->filled('min_price') ? floatval(str_replace(['.', ','], ['', '.'], $request->input('min_price'))) : null;
        $maxPrice = $request->filled('max_price') ? floatval(str_replace(['.', ','], ['', '.'], $request->input('max_price'))) : null;
        $usePriceFilter = ($minPrice !== null || $maxPrice !== null);
        $priceCategory = match ($priceMode) {
            'reseller'        => 'RESELLER',
            'patner'          => 'TWINCOM PATNER', // Sesuai dengan string penamaan di database/Accurate Anda
            default           => 'USER',
        };

        // Batasi target pencarian memori agar PHP tidak overload
        $rowsNeeded = $offset + $perPage;
        $maxLimit   = 500; // Dikurangi dari 1000 agar mempercepat respon jika stok banyak yang 0

        $buffer     = [];
        $rawScanned = 0;
        $pageAcc    = 1;

        // OPTIMASI 1: Ambil data kategori sekali saja dan simpan di cache pendek (5 menit)
        // Ini memangkas query DB internal berulang kali setiap hit request web
        $allowedCategoryIds = Cache::remember('acc_category_lookup', 300, function () {
            return app(CategoryService::class)->all()->pluck('id')->flip()->toArray();
        });

        // Loop mengambil data langsung dari Accurate secara real-time
        while (count($buffer) < $rowsNeeded && $rawScanned < $maxLimit) {

            $query = [
                'sp.page'          => $pageAcc,
                'sp.pageSize'      => 100, // Maksimalkan ukuran page API agar jaringannya efisien
                'fields'           => 'id,name,no,availableToSell,itemCategory,availableToSellInAllUnit',
                'filter.suspended' => false,
            ];

            if ($search !== '') {
                $query['filter.keywords.op']     = 'CONTAIN';
                $query['filter.keywords.val[0]'] = $search;
            }

            if (!empty($categoryId)) {
                $query['filter.itemCategoryId.op'] = 'EQUAL';
                foreach ($categoryId as $i => $id) {
                    $query["filter.itemCategoryId.val[$i]"] = $id;
                }
            }

            $resp = AccurateHttp::get("$baseUrl/item/list.do", $query);
            if (!$resp->successful()) break;

            $json = $resp->json();
            $rows = $json['d'] ?? [];
            
            if (empty($rows)) break;

            $rawScanned += count($rows);

            foreach ($rows as $row) {
                $itemCategoryId = $row['itemCategory']['id'] ?? null;

                if (!$itemCategoryId || !isset($allowedCategoryIds[$itemCategoryId])) {
                    continue;
                }

                $currentStock = $row[$stockType] ?? 0;
                if ($stokAda === '1' && $currentStock <= 0) {
                    continue;
                }

                $row['selected_stock'] = $currentStock;
                $row['category_id']    = $itemCategoryId;

                // ==========================================
                // 🔥 PERBAIKAN LOGIKA FILTER HARGA DI SINI
                // ==========================================
                if ($usePriceFilter) {
                    // Karena user butuh filter harga, kita TERPAKSA cek harga sekarang
                    // agar kalau harganya tidak sesuai, kita bisa lanjut scan barang berikutnya
                    $price = app(PriceService::class)->get($row['id'], $priceCategory);

                    if ($minPrice !== null && $price < $minPrice) continue;
                    if ($maxPrice !== null && $price > $maxPrice) continue;

                    // Simpan harganya agar di bawah tidak perlu hit service lagi
                    $row['price'] = $price; 
                }

                // Masukkan ke buffer HANYA JIKA lolos semua filter
                $buffer[] = $row;

                if (count($buffer) >= $rowsNeeded) {
                    break;
                }
            }

            if (($json['sp']['pageCount'] ?? 0) <= $pageAcc) break;
            $pageAcc++;
        }

        // OPTIMASI 4: Paginasi dan Filter Harga dilakukan HANYA pada data yang lolos (maksimal 10-20 item saja)
        $slicedItems = array_slice($buffer, $offset, $perPage);
        $finalItems  = [];
        
        // Kita panggil service harga sekali saja di luar loop jika diperlukan
        $priceService = app(PriceService::class);

        foreach ($slicedItems as $item) {
            // Jika user tadi TIDAK pakai filter harga, array 'price' belum ada.
            // Maka kita ambil harganya di sini (super cepat karena maksimal cuma 10 kali)
            if (!isset($item['price'])) {
                $item['price'] = $priceService->get($item['id'], $priceCategory);
            }
            
            $finalItems[] = $item;
        }

        $totalFiltered = count($buffer);
        $hasMore       = $totalFiltered >= $rowsNeeded;

        return [
            'rows'       => $json['sp'] ?? [],
            'items'      => $finalItems,
            'page'       => $pageWeb,
            'pageCount'  => $hasMore ? $pageWeb + 1 : $pageWeb,
            'totalItems' => $totalFiltered,
            'filters'    => compact('search', 'categoryId', 'stokAda', 'stockType', 'minPrice', 'maxPrice', 'priceMode'),
        ];
    }
}
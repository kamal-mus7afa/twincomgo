<?php

namespace App\Services\Accurate;

use Hashids\Hashids;
use Illuminate\Http\Request;

class ItemService
{
    public function fetchItemsForList(Request $request): array
    {
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');

        $perPage = $request->query('per_page', 10);
        $pageWeb = max(1, (int) $request->query('page', 1));
        $offset  = ($pageWeb - 1) * $perPage;

        $search     = trim($request->query('search', ''));
        $categoryId = $request->query('category_id');
        $stokAda    = $request->query('stok_ada', '1');
        $stockType = $request->query('stock_type', 'availableToSell');
        $priceMode  = $request->query('price_mode', 'default');

        $minPrice = $request->filled('min_price')
            ? floatval(str_replace(['.', ','], ['', '.'], $request->input('min_price')))
            : null;

        $maxPrice = $request->filled('max_price')
            ? floatval(str_replace(['.', ','], ['', '.'], $request->input('max_price')))
            : null;

        $usePriceFilter = ($minPrice !== null || $maxPrice !== null);
        $priceCategory  = $priceMode === 'reseller' ? 'RESELLER' : 'USER';

        $targetBase = $offset + $perPage + 1;
        $targetDeep = $perPage;
        $maxLimit   = 1000;

        $buffer     = collect();
        $rawScanned = 0;
        $pageAcc    = 1;
        $rowsNeeded = $targetBase;

        $priceService = app(PriceService::class);
        $allowedCategoryIds = app(CategoryService::class)
        ->all()
        ->pluck('id')
        ->flip(); // buat lookup cepat

        $processRow = function ($row) use (
            &$buffer,
            $stokAda,
            $stockType,
            $usePriceFilter,
            $minPrice,
            $maxPrice,
            $priceCategory,
            $priceService,
            $allowedCategoryIds
        ) {
            $itemCategoryId = $row['itemCategory']['id'] ?? null;

            if (!$itemCategoryId || !isset($allowedCategoryIds[$itemCategoryId])) {
                return false;
            }

            $currentStock = $row[$stockType] ?? 0;

            if ($stokAda === '1' && $currentStock <= 0) {
                return;
            }

            if ($usePriceFilter) {
                $price = $priceService->get($row['id'], $priceCategory);

                if ($minPrice !== null && $price < $minPrice) return;
                if ($maxPrice !== null && $price > $maxPrice) return;

                $row['price'] = $price;
            }

            $row['selected_stock'] = $currentStock;

            $buffer->push($row);
        };

        while ($buffer->count() < $rowsNeeded && $rawScanned < $maxLimit) {

            $query = [
                'sp.page'         => $pageAcc,
                'sp.pageSize'     => 100,
                'fields'          => 'id,name,no,availableToSell,itemCategory.name,availableToSellInAllUnit,itemCategory,allQuantity',
                'filter.suspended'=> false,
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
            $rows = collect($json['d'] ?? []);
            $sp   = $json['sp'] ?? [];

            $rawScanned += $rows->count();

            if ($rows->isEmpty()) break;

            foreach ($rows as $row) {
                $processRow($row);
                if ($buffer->count() >= $rowsNeeded) break;
            }

            if (($json['sp']['pageCount'] ?? 0) <= $pageAcc) break;
            $pageAcc++;
        }

        $totalFiltered = $buffer->count();
        $items         = $buffer->slice($offset, $perPage)->values();
        $hasMore       = $totalFiltered > ($offset + $items->count());

        $items = $items->map(function ($item) {

            $item['category_id'] = $item['itemCategory']['id'] ?? null;

            return $item;
        });

        return [
            'rows'       => $sp ?? [],
            'items'      => $items,
            'page'       => $pageWeb,
            'pageCount'  => $hasMore ? $pageWeb + 1 : $pageWeb,
            'totalItems' => $totalFiltered,
            'filters'    => compact('search', 'categoryId', 'stokAda','stockType', 'minPrice', 'maxPrice', 'priceMode'),
        ];
    }
}
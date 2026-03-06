<?php

namespace App\Services\Accurate;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CategoryService
{
    // 🔴 Parent + semua child DISEMBUNYIKAN
    protected array $hiddenTreeCategoryIds = [
        53900,
        53301,
    ];

    // 🟡 HANYA parent DISEMBUNYIKAN, child TETAP TAMPIL
    protected array $hiddenParentOnlyIds = [
        53104,
        52710,
        53103,
        53750,
        53051,
        56350,
        54400,
        54450,
        55650,
        55600,
        54700,
        54650,
        54350,
        55750,
        53800,
        55350,
        55002,
        55800,
        52704,
        56100,
        54300,
        53303,
        55200,
        54950,
        55050,
        53100,
        53401,
        53501,
        53850,
        52712,
        52750,
        53200,
        55950,
        53050,
        53103,
        53750,
        55250,
        54800,
        56800,
        56550,
        55900,
        55500,
        52718,
        54750,
        55300,
        55100,
        54500,
        51,
        56450,
        55150,
    ];

    protected array $hiddenResellerExtraIds = [
        54850,
        52708,
    ];

    public function all()
    {
        $label = AccurateContext::label();
        $role  = Auth::user()?->status   ?? 'guest';

        return Cache::remember("accurate:categories:{$label}:{$role}", 86400, function () use ($role) {

            $baseUrl = rtrim(config('services.accurate.base_api'), '/');

            $cats = collect();
            $page = 1;

            do {
                $resp = AccurateHttp::get("{$baseUrl}/item-category/list.do", [
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

            $hideTree  = array_map('intval', $this->hiddenTreeCategoryIds);
            $hideOnly  = array_map('intval', $this->hiddenParentOnlyIds);

            if ($role === 'RESELLER') {
                $hideOnly = array_merge(
                    $hideOnly,
                    array_map('intval', $this->hiddenResellerExtraIds)
                );
            }

            return $cats
                ->reject(function ($cat) use ($hideTree, $hideOnly) {

                    $id = (int) $cat['id'];

                    // ❌ hide parent-only (tapi child aman)
                    if (in_array($id, $hideOnly, true)) {
                        return true;
                    }

                    // ❌ hide parent + child
                    if (in_array($id, $hideTree, true)) {
                        return true;
                    }

                    // ❌ hide child jika ancestor ada di hideTree
                    $parent = $cat['parent'] ?? null;

                    while ($parent) {
                        $parentId = (int) $parent['id'];

                        if (in_array($parentId, $hideTree, true)) {
                            return true;
                        }

                        $parent = $parent['parent'] ?? null;
                    }

                    return false;
                })
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values();
        });
    }
}
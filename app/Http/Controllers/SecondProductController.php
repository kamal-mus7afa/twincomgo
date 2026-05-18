<?php

namespace App\Http\Controllers;

use App\Helpers\AccurateGlobal;
use App\Models\Order;
use App\Models\SecondProduct;
use App\Models\SecondProductImage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SecondProductController extends Controller
{
    public function index()
    {
        $seconds = SecondProduct::latest()->get();
        return view('cabang.purchase.index', compact('seconds'));
    }

    public function indexKaryawan()
    {
        $seconds = SecondProduct::latest()->where('user_id', Auth::id())->get();
        return view('cabang.admin.index', compact('seconds'));
    }

    public function daftarProduct() 
    {
        $seconds = SecondProduct::with('images')
            ->where('status', 'ready')
            ->where('is_publish', true)
            ->orderByDesc('selling_price')
            ->get()
            ->unique('sales_order_number');

        $draftOrder = Order::with('items')
        ->where('user_id', auth()->id())
        ->where('status', 'DRAFT')
        ->first();
        return view('admin.galeriSecond.daftar-product', compact('seconds', 'draftOrder'));
    }

    public function store(Request $request)
    {
        $createdProducts = collect();

        /**
         * =========================
         * SAVE LOCAL DATABASE
         * =========================
         */
        DB::beginTransaction();

        try {

            $baseUrl = rtrim(config('services.accurate.base_api'), '/');

            $acc = AccurateGlobal::token();

            $token = $acc['access_token'];
            $session = $acc['session_id'];

            $invoice = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID' => $session,
            ])->get("{$baseUrl}/purchase-invoice/detail.do", [
                'number' => $request->numberInvoice,
            ])->json();

            if (!($invoice['s'] ?? false)) {
                throw new \Exception('Purchase invoice tidak ditemukan');
            }

            $invoiceData = $invoice['d'];

            $tanggalReal = null;
            $tanggalFake = null;

            if ($request->filled('tanggal_real')) {
                $tanggalReal = Carbon::parse($request->tanggal_real);
                $tanggalFake = $tanggalReal->copy()->subDays(3);
            }

            foreach ($request->items as $selected) {

                $detail = collect($invoiceData['detailItem'])
                    ->firstWhere('item.no', $selected['item']['no']);

                if (!$detail) {
                    continue;
                }

                if (!empty($detail['detailSerialNumber'])) {

                    foreach ($detail['detailSerialNumber'] as $sn) {

                        $product = SecondProduct::create([
                            'user_id' => Auth::id(),
                            'purchase_invoice_id' => $invoiceData['id'] ?? null,
                            'purchase_invoice_number' => $invoiceData['number'] ?? null,
                            'item_id' => $detail['item']['id'] ?? null,
                            'item_no' => $detail['item']['no'] ?? null,
                            'item_name' => $detail['detailName'] ?? null,
                            'serial_number' => $sn['serialNumber']['number'] ?? null,
                            'customer_id' => $request->customer_id ?? null,
                            'customer_no' => $request->customer_no ?? null,
                            'customer_name' => $request->customer_name ?? null,
                            'status' => 'diajukan',
                            'description' => $request->description,
                            'type_garansi' => $request->type_garansi,
                            'tanggal_real' => $tanggalReal,
                            'tanggal_fake' => $tanggalFake,
                        ]);

                        $createdProducts->push($product);
                    }

                } else {

                    $product = SecondProduct::create([
                        'user_id' => Auth::id(),
                        'purchase_invoice_id' => $invoiceData['id'] ?? null,
                        'purchase_invoice_number' => $invoiceData['number'] ?? null,
                        'item_id' => $detail['item']['id'] ?? null,
                        'item_no' => $detail['item']['no'] ?? null,
                        'item_name' => $detail['detailName'] ?? null,
                        'customer_id' => $request->customer_id ?? null,
                        'customer_no' => $request->customer_no ?? null,
                        'customer_name' => $request->customer_name ?? null,
                        'status' => 'diajukan',
                        'description' => $request->description,
                        'type_garansi' => $request->type_garansi,
                        'tanggal_real' => $tanggalReal,
                        'tanggal_fake' => $tanggalFake,
                    ]);

                    $createdProducts->push($product);
                }
            }

            DB::commit();

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }

        /**
         * =========================
         * CREATE SO ACCURATE
         * =========================
         */
        try {

            $detailItem = [];

            foreach ($request->items as $selected) {

                $detail = collect($invoiceData['detailItem'])
                    ->firstWhere('item.no', $selected['item']['no']);

                if (!$detail) {
                    continue;
                }

                $detailItem[] = [
                    'itemNo' => $detail['item']['no'],
                    'itemUnitName' => $detail['itemUnit']['name'] ?? 'PCS',
                    'quantity' => $detail['quantity'],
                    'unitPrice' => 0,
                    'warehouseName' => $detail['warehouse']['name'],
                    'useTax1' => false,
                ];
            }

            $payload = [
                'customerNo' => $request->customer_no,
                'transDate' => now()->format('d/m/Y'),
                'branchId' => $invoiceData['branchId'],
                'description' => $request->description,
                'charField1' => 'Tidak',
                'taxable' => false,
                'inclusiveTax' => false,
                'detailItem' => $detailItem
            ];

            $so = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID' => $session,
            ])->post(
                "{$baseUrl}/sales-order/save.do",
                $payload
            )->json();

            if (!($so['s'] ?? false)) {
                throw new \Exception(
                    json_encode($so['d'] ?? [])
                );
            }

            $salesOrderId = $so['r']['id'] ?? null;
            $salesOrderNumber = $so['r']['number'] ?? null;

            $createdProducts->each(function ($product) use (
                $salesOrderId,
                $salesOrderNumber
            ) {

                $product->update([
                    'sales_order_id' => $salesOrderId,
                    'sales_order_number' => $salesOrderNumber,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'SO berhasil dibuat',
                'sales_order_number' => $salesOrderNumber,
            ]);

        } catch (\Exception $e) {

            Log::error($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getWarehouse(Request $request)
    {
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');
        $acc = AccurateGlobal::token();
        $token = $acc['access_token'];
        $session = $acc['session_id'];

        $params = [
            'filter.suspended' => false,
            'sp.page' => 1,
            'sp.pageSize' => 100,
        ];

        if ($request->search) {
            $query['filter.keywords.op'] = 'CONTAIN';
            $query['filter.keywords.val[0]'] = $request->search;
        }

        $res = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session,
        ])->get("{$baseUrl}/warehouse/list.do", $params);

        $warehouses = $res->json();

        return response()->json($warehouses);
    }

    public function update(Request $request, $id)
    {
        $request->validate([

            'description' =>
                'nullable|string',

            'type_garansi' =>
                'nullable|in:resmi,distributor',

            'tanggal_real' =>
                'nullable|date',

            'images.*' =>
                'nullable|image|max:2048',
        ]);

        DB::beginTransaction();

        try {

            $second = SecondProduct::findOrFail($id);

            // tanggal fake
            $tanggalFake = null;

            if ($request->tanggal_real) {

                $tanggalFake =
                    Carbon::parse(
                        $request->tanggal_real
                    )->subDays(3);
            }

            // update local
            $second->update([

                'description' =>
                    $request->description,

                'type_garansi' =>
                    $request->type_garansi,

                'tanggal_real' =>
                    $request->tanggal_real,

                'tanggal_fake' =>
                    $tanggalFake,
            ]);

            // upload image
            if ($request->hasFile('images')) {

                foreach ($request->file('images') as $file) {

                    $filename =
                        Str::uuid() . '.'
                        . $file->getClientOriginalExtension();

                    $storagePath =
                        'second-products/'
                        . $second->id
                        . '/'
                        . $filename;

                    $response = Http::withHeaders([

                        'apikey' =>
                            env('SUPABASE_KEY'),

                        'Authorization' =>
                            'Bearer ' . env('SUPABASE_KEY'),

                    ])
                    ->attach(
                        'file',
                        fopen($file->getRealPath(), 'r'),
                        $filename
                    )
                    ->post(

                        env('SUPABASE_URL')
                        . '/storage/v1/object/'
                        . env('SUPABASE_BUCKET')
                        . '/'
                        . $storagePath
                    );

                    if (!$response->successful()) {

                        Log::error($response->body());

                        continue;
                    }

                    SecondProductImage::create([

                        'second_product_id' =>
                            $second->id,

                        'path' =>
                            $storagePath,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('second.index')
                ->with('success', 'Data berhasil diupdate');

        } catch (\Throwable $th) {

            DB::rollBack();

            Log::error($th);

            return back()->withErrors(
                $th->getMessage()
            );
        }
    }

    public function close(Request $request, $id)
    {
        $request->validate([
            'selling_price' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            $second = SecondProduct::findOrFail($id);

            /*
            |--------------------------------------------------------------------------
            | CEK APAKAH SO SUDAH PERNAH DITUTUP
            |--------------------------------------------------------------------------
            | Jika ada item lain dengan sales_order_number sama
            | dan status sudah ready,
            | berarti SO sudah pernah di-close sebelumnya.
            */

            $alreadyClosed = SecondProduct::where(
                    'sales_order_number',
                    $second->sales_order_number
                )
                ->where('status', 'ready')
                ->where('id', '!=', $second->id)
                ->exists();

            /*
            |--------------------------------------------------------------------------
            | CLOSE SO KE ACCURATE HANYA SEKALI
            |--------------------------------------------------------------------------
            */

            if (!$alreadyClosed) {

                $baseUrl = rtrim(
                    config('services.accurate.base_api'),
                    '/'
                );

                $acc = AccurateGlobal::token();

                $token = $acc['access_token'];

                $session = $acc['session_id'];

                $params = [

                    'number' =>
                        $second->sales_order_number,

                    'orderClosed' =>
                        true,

                    'closeReason' =>
                        'Sudah mendapatkan harga'
                ];

                $closeSO = Http::withHeaders([

                    'Authorization' =>
                        'Bearer ' . $token,

                    'X-Session-ID' =>
                        $session,

                ])->post(

                    "{$baseUrl}/sales-order/manual-close-order.do",

                    $params

                )->json();

                if (!($closeSO['s'] ?? false)) {

                    throw new \Exception(
                        json_encode(
                            $closeSO['d'] ?? []
                        )
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | UPDATE DATA ITEM
            |--------------------------------------------------------------------------
            */

            $second->update([

                'selling_price' =>
                    $request->selling_price,

                'status' =>
                    'ready',

                'is_publish' =>
                    !is_null($request->selling_price),
            ]);

            DB::commit();

            return redirect()
                ->route('second.index')
                ->with(
                    'success',
                    'Barang berhasil dipublish'
                );

        } catch (\Throwable $th) {

            DB::rollBack();

            Log::error($th);

            return back()->withErrors(
                $th->getMessage()
            );
        }
    }

    public function edit($id)
    {
        $second = SecondProduct::with('images')
            ->findOrFail($id);

        return view(
            'cabang.admin.edit',
            compact('second')
        );
    }

    public function editClose($id)
    {
        $second = SecondProduct::with('images')
            ->findOrFail($id);

        return view(
            'cabang.purchase.close',
            compact('second')
        );
    }

    public function deleteImage($id)
    {
        try {

            $image = SecondProductImage::findOrFail($id);

            // hapus dari storage supabase
            Http::withHeaders([

                'apikey' =>
                    env('SUPABASE_KEY'),

                'Authorization' =>
                    'Bearer ' . env('SUPABASE_KEY'),

            ])->delete(

                env('SUPABASE_URL')
                . '/storage/v1/object/'
                . env('SUPABASE_BUCKET')
                . '/'
                . $image->path
            );

            // hapus database
            $image->delete();

            return response()->json([

                'success' => true
            ]);

        } catch (\Throwable $th) {

            Log::error($th);

            return response()->json([

                'success' => false,

                'message' =>
                    $th->getMessage()

            ], 500);
        }
    }

    public function getDetailPurchaseInvoice(Request $request)
    {
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');
        $acc = AccurateGlobal::token();

        $numberInvoice = $request->numberInvoice;

        $resp = Http::withHeaders([
            'Authorization' => 'Bearer ' . $acc['access_token'],
            'X-Session-ID' => $acc['session_id'],
        ])->get("{$baseUrl}/purchase-invoice/detail.do", [
            'number' => $numberInvoice,
        ]);

        $data = $resp->json();

        return response()->json($data);
    }

    public function submission()
    {
        return view('cabang.admin.create');
    }

    public function show($id)
    {
        $second = SecondProduct::with('images')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $second->id,
                'serial_number' => $second->serial_number,
                'item_name' => $second->item_name,
                'item_no' => $second->item_no,
                'item_id' => $second->item_id,
                'status' => $second->status,
                'selling_price' => $second->selling_price,
                'description' => $second->description,
                'purchase_invoice_number' => $second->purchase_invoice_number,
                'sales_order_number' => $second->sales_order_number,
                'customer_name' => $second->customer_name,
                'customer_no' => $second->customer_no,
                'branch_name' => $second->branch_name,
                'warehouse_name' => $second->warehouse_name,
                'type_garansi' => $second->type_garansi,
                'tanggal_real' => $second->tanggal_real ? Carbon::parse($second->tanggal_real)->format('d/m/Y') : null,
                'tanggal_fake' => $second->tanggal_fake ? Carbon::parse($second->tanggal_fake)->format('d/m/Y') : null,
                'created_at' => Carbon::parse($second->created_at)->format('d/m/Y H:i:s'),
                'images' => $second->images->map(function($image) {
                    return [
                        'id' => $image->id,
                        'path' => $image->path,
                        'url' => env('SUPABASE_URL') . '/storage/v1/object/' . env('SUPABASE_BUCKET') . '/' . $image->path
                    ];
                })
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $second = SecondProduct::findOrFail($id);
            
            // Hapus gambar dari Supabase
            if ($second->images) {
                foreach ($second->images as $image) {
                    try {
                        $baseUrl = rtrim(env('SUPABASE_URL'), '/');
                        $bucket = env('SUPABASE_BUCKET');
                        
                        Http::withHeaders([
                            'apikey' => env('SUPABASE_KEY'),
                            'Authorization' => 'Bearer ' . env('SUPABASE_KEY'),
                        ])->delete($baseUrl . '/storage/v1/object/' . $bucket . '/' . $image->path);
                        
                    } catch (\Exception $e) {
                        Log::warning('Gagal menghapus gambar dari Supabase: ' . $e->getMessage());
                    }
                }
            }
            
            // Hapus record gambar dari database
            SecondProductImage::where('second_product_id', $id)->delete();
            
            // Hapus record produk
            $second->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Gagal menghapus data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update status produk
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $second = SecondProduct::findOrFail($id);
            
            $validStatus = ['keep', 'ready', 'terjual', 'draft'];
            
            if (!in_array($request->status, $validStatus)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status tidak valid'
                ], 400);
            }
            
            $second->update([
                'status' => $request->status
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diupdate',
                'data' => [
                    'status' => $second->status
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal update status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal update status'
            ], 500);
        }
    }

    public function keep($id)
    {
        DB::beginTransaction();

        try {

            $product = SecondProduct::lockForUpdate()
                ->findOrFail($id);

            $products = SecondProduct::where(
                'sales_order_number',
                $product->sales_order_number
            )
            ->lockForUpdate()
            ->get();

            // hanya bisa dibook jika READY
            foreach ($products as $item) {

                if ($item->status !== 'ready') {

                    throw new \Exception(
                        'Ada barang dalam paket yang belum ready'
                    );
                }
            }

            // draft order user
            $order = Order::firstOrCreate([
                'user_id' => auth()->id(),
                'status' => 'DRAFT'
            ], [
                'customer_no' => '',
            ]);

            // jangan sampai double input
            $alreadyExists = $order->items()
                ->where('second_product_id', $product->id)
                ->exists();

            if ($alreadyExists) {
                throw new \Exception('Barang sudah ada di order');
            }

            // create order item
            foreach ($products as $item) {
                $alreadyExists = $order->items()
                    ->where('second_product_id', $item->id)
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                $order->items()->create([
                    'second_product_id' =>
                        $item->id,
                    'accurate_item_no' =>
                        $item->item_no,
                    'item_name' =>
                        $item->item_name,
                    'serial_number' =>
                        $item->serial_number,
                    'item_unit_name' =>
                        'PCS',
                    'quantity' =>
                        1,
                    'unit_price' =>
                        $item->selling_price ?? 0,
                ]);

                $item->update([
                    'status' => 'booked'
                ]);
            }

            // ubah status barang
            $product->update([
                'status' => 'BOOKED'
            ]);
            DB::commit();

            return response()->json([
                's' => true,
                'm' => 'Barang berhasil di keep'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                's' => 'error',
                'm' => $e->getMessage()
            ]);
        }
    }
}

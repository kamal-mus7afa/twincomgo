<?php

namespace App\Http\Controllers;

use App\Helpers\AccurateGlobal;
use App\Models\Order;
use App\Models\SecondProduct;
use App\Models\SecondProductImage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SecondProductController extends Controller
{
    public function index()
    {
        $seconds = SecondProduct::latest()->get();
        return view('admin.galeriSecond.index', compact('seconds'));
    }

    public function daftarProduct() 
    {
        $seconds = SecondProduct::with('images')->where('status', 'ready')->get();

        $draftOrder = Order::with('items')
        ->where('user_id', auth()->id())
        ->where('status', 'DRAFT')
        ->first();
        return view('admin.galeriSecond.daftar-product', compact('seconds', 'draftOrder'));
    }

    public function store(Request $request)
    {
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

            $detailItem = [];

            foreach ($invoiceData['detailItem'] as $detail) {

                $detailItem[] = [
                    'itemNo' => $detail['item']['no'],
                    'itemUnitName' => $detail['itemUnit']['name'] ?? 'PCS',
                    'quantity' => $detail['quantity'],
                    'unitPrice' => 15000,
                    'warehouseName' => $request->warehouse_name,
                    'useTax1' => false,
                ];
            }

            $payload = [
                'customerNo' => $request->customer_no,
                'transDate' => now()->format('d/m/Y'),
                'branchName' => $request->branch_name,
                'description' => $request->description,
                'charField1' => "Tidak",
                'taxable' => false,
                'inclusiveTax' => false,
                'detailItem' => $detailItem
            ];
            // dd($payload);
            $so = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID' => $session,
            ])->post("{$baseUrl}/sales-order/save.do", $payload)->json();
            // dd($so);
            if (!($so['s'] ?? false)) {
                Log::error($so);

                throw new \Exception(
                    json_encode($so['d'])
                );
            }
            $tanggalReal =
                Carbon::parse($request->tanggal_real);

            $tanggalFake =
                $tanggalReal->copy()->subDays(3);
            foreach ($invoiceData['detailItem'] as $detail) {

                if (!empty($detail['detailSerialNumber'])) {

                    foreach ($detail['detailSerialNumber'] as $sn) {

                        SecondProduct::create([
                            'purchase_invoice_id' =>
                                $invoiceData['id'] ?? null,

                            'purchase_invoice_number' =>
                                $invoiceData['number'] ?? null,

                            'item_id' =>
                                $detail['item']['id'] ?? null,

                            'item_no' =>
                                $detail['item']['no'] ?? null,

                            'item_name' =>
                                $detail['detailName'] ?? null,

                            'serial_number' =>
                                $sn['serialNumber']['number'] ?? null,

                            'customer_id' =>
                                $request->customer_id ?? null,

                            'customer_no' =>
                                $request->customer_no ?? null,

                            'customer_name' =>
                                $request->customer_name ?? null,

                            'sales_order_id' =>
                                $so['r']['id'] ?? null,

                            'sales_order_number' =>
                                $so['r']['number'] ?? null,

                            'status' => 'keep',
                            'description' => $request->description,

                            'type_garansi' =>
                                $request->type_garansi,

                            'tanggal_real' =>
                                $tanggalReal,

                            'tanggal_fake' =>
                                $tanggalFake,
                        ]);
                    }

                } else {

                    SecondProduct::create([
                        'purchase_invoice_id' =>
                            $invoiceData['id'] ?? null,

                        'purchase_invoice_number' =>
                            $invoiceData['number'] ?? null,

                        'item_id' =>
                            $detail['item']['id'] ?? null,

                        'item_no' =>
                            $detail['item']['no'] ?? null,

                        'item_name' =>
                            $detail['detailName'] ?? null,

                        'customer_id' =>
                            $invoiceData['customer']['id'] ?? null,

                        'customer_no' =>
                            $invoiceData['customer']['no'] ?? null,

                        'customer_name' =>
                            $invoiceData['customer']['name'] ?? null,

                        'sales_order_id' =>
                            $so['d']['id'] ?? null,

                        'sales_order_number' =>
                            $so['d']['number'] ?? null,

                        'status' => 'keep',
                    ]);
                }
            }
        
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'SO berhasil dibuat',
                'sales_order_number' => $so['r']['number'] ?? "",
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getBranch(Request $request) 
    {
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');
        $acc = AccurateGlobal::token();
        $token = $acc['access_token'];
        $session = $acc['session_id'];

        $res = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session,
        ])->get("{$baseUrl}/branch/list.do", [
            'filter.suspended' => false,
            'sp.page' => 1,
            'sp.pageSize' => 100,
        ]);

        $branch = $res->json();

        return response()->json($branch);
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

    public function getCustomer(Request $request)
    {
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');
        $acc = AccurateGlobal::token();
        $token = $acc['access_token'];
        $session = $acc['session_id'];

        $params = [
            'fields' => 'id,name,customerNo',
            'filter.suspended' => false,
            'sp.page' => 1,
            'sp.pageSize' => 100,
        ];

        if ($request->search) {
            $params['filter.keywords.op'] = 'CONTAIN';
            $params['filter.keywords.val[0]'] = $request->search;
        }

        $res = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session,
        ])->get("{$baseUrl}/customer/list.do", $params);

        $customers = $res->json();

        return response()->json($customers);
    }

    public function update(Request $request, $id) 
    {
        DB::beginTransaction();

        try {
            $second = SecondProduct::findOrFail($id);

            $baseUrl = rtrim(config('services.accurate.base_api'), '/');
            $acc = AccurateGlobal::token();
            $token = $acc['access_token'];
            $session = $acc['session_id'];

            $params = [
                'number' => $second->sales_order_number,
                'orderClosed' => true,
                'closeReason' => 'Sudah mendapatkan harga'
            ];

            $closeSO = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID' => $session,
            ])->post(
                "{$baseUrl}/sales-order/manual-close-order.do",
                $params
            )->json();

            $second->update([
                'selling_price' => $request->selling_price,
                'status' => 'ready',
                'is_publish' => 1,
            ]);

            if($request->hasFile('images')) {
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
                        'second_product_id' => $second->id,
                        'path' => $storagePath,
                    ]);
                }
            }
            DB::commit();
            return redirect()->route('second.index');
        } catch (\Exception $e) {
            DB::rollBack();
            //throw $th;

            Log::error($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
        
    }

    public function edit($id)
    {
        $second = SecondProduct::with('images')
            ->findOrFail($id);

        return view(
            'admin.galeriSecond.edit',
            compact('second')
        );
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

    public function invoiceIndex()
    {
        return view('admin.galeriSecond.invoice');
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
        $product = SecondProduct::findOrFail($id);

        // Cari / buat draft order user
        $order = Order::firstOrCreate([
            'user_id' => auth()->id(),
            'status' => 'DRAFT'
        ]);

        // Cek apakah item sudah ada
        $existingItem = $order->items()
            ->where('accurate_item_no', $product->item_no)
            ->first();

        if ($existingItem) {

            // Kalau mau qty bertambah
            $existingItem->increment('quantity');

        } else {

            // Tambah item baru
            $order->items()->create([
                'accurate_item_no' => $product->item_no,
                'item_name' => $product->item_name,
                'item_unit_name' => 'PCS',
                'quantity' => 1,
                'unit_price' => $product->selling_price,
            ]);
        }

        return back()->with('success', 'Barang berhasil di-keep');
    }
}

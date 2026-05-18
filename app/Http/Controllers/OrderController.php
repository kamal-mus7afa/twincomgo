<?php

namespace App\Http\Controllers;

use App\Helpers\AccurateGlobal;
use App\Models\Customer;
use App\Models\Order;
use App\Models\SecondProduct;
use App\Services\Accurate\BranchService;
use App\Services\Accurate\CustomerService;
use App\Services\Accurate\WarehouseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index()
    {
        $order = Order::with([
                'items.secondProduct.images'
            ])
            ->where('user_id', auth()->id())
            ->where('status', 'DRAFT')
            ->first();

        if (!$order || $order->items->isEmpty()) {

        return redirect()
            ->back()
            ->with('error', 'Belum ada barang yang di-keep');
        }
    
        $warehouses = $this->getWarehouses();

        // dd($warehouses);
        return view('admin.galeriSecond.cart', compact('order', 'warehouses'));
    }

    public function checkout(Request $request)
    {
        DB::beginTransaction();

        try {

            $order = Order::with('items.secondProduct')
                ->where('user_id', auth()->id())
                ->where('status', 'DRAFT')
                ->firstOrFail();

            // UPDATE LOCAL DULU
            $order->update([
                'status' => 'PENDING_SYNC',
                'customer_no' => $request->customer_no,
                'branch_name' => $request->branch_name,
                'description' => $request->description,
            ]);

            foreach ($request->items as $reqItem) {

                $orderItem = $order->items
                    ->where('id', $reqItem['order_item_id'])
                    ->first();

                if (!$orderItem) {
                    continue;
                }

                $orderItem->update([
                    'warehouse_name' => $reqItem['warehouse_name']
                ]);
            }

            DB::commit();

        } catch (\Throwable $th) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }

        // =========================
        // ACCURATE PROCESS
        // =========================

        try {

            $baseUrl = rtrim(
                config('services.accurate.base_api'),
                '/'
            );

            $acc = AccurateGlobal::token();
            $token = $acc['access_token'];
            $session = $acc['session_id'];

            $detailItem = [];

            foreach($order->items as $item) {
                $detailItem[] = [
                    'itemNo' => $item->accurate_item_no,
                    'itemUnitName' => $item->item_unit_name ?? 'PCS',
                    'quantity' => $item->quantity,
                    'unitPrice' => $item->unit_price,
                    'warehouseName' => $item->warehouse_name,
                    'useTax1' => false,
                ];
            }

            $payload = [
                'customerNo' => $order->customer_no,
                'transDate' => now()->format('d/m/Y'),
                'branchName' => $order->branch_name,
                'description' => $order->description,
                'charField1' => 'Tidak',
                'taxable' => false,
                'inclusiveTax' => false,
                'detailItem' =>  $detailItem
            ];

            $so = Http::withHeaders([
                'Authorization' =>'Bearer ' . $token,
                'X-Session-ID' => $session,
            ])->post(
                "{$baseUrl}/sales-order/save.do",
                $payload
            )->json();

            if (!($so['s'] ?? false)) {
                $order->update(['status' => 'FAILED_SYNC']);
                throw new \Exception(json_encode($so['d']));
            }

            // SUCCESS
            $order->update([
                'status' => 'CHECKOUT',
                'accurate_so_number' =>
                    $so['r']['number'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'SO berhasil dibuat'
            ]);

        } catch (\Throwable $th) {

            $order->update([
                'status' => 'FAILED_SYNC'
            ]);

            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function customer(Request $request, CustomerService $customer)
    {
        return response()->json($customer->getCustomer($request));
    }

    public function customerManual(Request $request)
    {
        $search = $request->search;

        $customers = Customer::query()

            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_number', 'like', "%{$search}%");
            })

            ->limit(20)

            ->get([
                'id',
                'name',
                'customer_number'
            ]);

        return response()->json([
            'd' => $customers
        ]);
    }

    public function branch(
        Request $request,
        BranchService $branch
    )
    {
        return response()->json(
            $branch->getBranch($request)
        );
    }

    public function warehouse(
        Request $request,
        WarehouseService $warehouse,
    )
    {
        return response()->json(
            $warehouse->getWarehouse($request)
        );
    }

    public function stock(Request $request)
    {
        try {

            $baseUrl = rtrim(
                config('services.accurate.base_api'),
                '/'
            );

            $acc = AccurateGlobal::token();

            $token = $acc['access_token'];

            $session = $acc['session_id'];

            $response = Http::withHeaders([

                'Authorization' =>
                    'Bearer ' . $token,

                'X-Session-ID' =>
                    $session,

            ])->get(
                "{$baseUrl}/item/get-stock.do",
                [
                    'no' =>
                        $request->no,

                    'warehouseName' =>
                        $request->warehouseName
                ]
            )->json();

            return response()->json([
                'success' => true,
                'stock' =>
                    $response['d']['availableStock'] ?? 0
            ]);

        } catch (\Throwable $th) {

            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    private function getWarehouses()
    {
        $baseUrl = rtrim(
            config('services.accurate.base_api'),
            '/'
        );

        $acc = AccurateGlobal::token();

        $token = $acc['access_token'];

        $session = $acc['session_id'];

        $response = Http::withHeaders([

            'Authorization' =>
                'Bearer ' . $token,

            'X-Session-ID' =>
                $session,

        ])->get(
            "{$baseUrl}/warehouse/list.do"
        )->json();

        return $response['d'] ?? [];
    }

    public function removeItem($id)
    {
        DB::beginTransaction();

        try {

            $order = Order::with('items.secondProduct')
                ->where('user_id', auth()->id())
                ->where('status', 'DRAFT')
                ->firstOrFail();

            $item = $order->items()
                ->with('secondProduct')
                ->where('id', $id)
                ->firstOrFail();

            if (!$item->secondProduct) {

                throw new \Exception(
                    'Product tidak ditemukan'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | AMBIL SEMUA PRODUCT DALAM SATU PAKET
            |--------------------------------------------------------------------------
            */

            $salesOrderNumber =
                $item->secondProduct->sales_order_number;

            $packageProductIds = SecondProduct::where(
                    'sales_order_number',
                    $salesOrderNumber
                )
                ->pluck('id');

            /*
            |--------------------------------------------------------------------------
            | AMBIL SEMUA ORDER ITEM DALAM PAKET
            |--------------------------------------------------------------------------
            */

            $orderItems = $order->items()
                ->whereIn(
                    'second_product_id',
                    $packageProductIds
                )
                ->get();

            /*
            |--------------------------------------------------------------------------
            | KEMBALIKAN STATUS PRODUCT
            |--------------------------------------------------------------------------
            */

            SecondProduct::whereIn(
                'id',
                $packageProductIds
            )->update([
                'status' => 'ready'
            ]);

            /*
            |--------------------------------------------------------------------------
            | HAPUS ITEM CART
            |--------------------------------------------------------------------------
            */

            foreach ($orderItems as $orderItem) {

                $orderItem->delete();
            }

            /*
            |--------------------------------------------------------------------------
            | HAPUS ORDER JIKA KOSONG
            |--------------------------------------------------------------------------
            */

            $isEmpty =
                $order->items()->count() <= 0;

            if ($isEmpty) {

                $order->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'empty' => $isEmpty
            ]);

        } catch (\Throwable $th) {

            DB::rollBack();

            return response()->json([

                'success' => false,

                'message' => $th->getMessage()

            ], 500);
        }
    }

    public function orderList()
    {
        $query = Order::with(['items', 'user']);

        // kalau bukan admin
        if (auth()->user()->role !== 'admin') {

            $query->where(
                'user_id',
                auth()->id()
            );
        }

        $orders = $query
            ->latest()
            ->get();

        return view(
            'cabang.order.index',
            compact('orders')
        );
    }

    public function deal($id)
    {
        DB::beginTransaction();

        try {

            $order = Order::with('items.secondProduct')
                ->findOrFail($id);

            // update order
            $order->update([
                'status' => 'DEAL'
            ]);

            // update second product
            foreach ($order->items as $item) {

                if ($item->secondProduct) {

                    $item->secondProduct->update([
                        'status' => 'sold'
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil di-DEAL'
            ]);

        } catch (\Throwable $th) {

            DB::rollBack();

            Log::error($th);

            return response()->json(
                $th->getMessage()
            );
        }
    }

    public function cancel($id)
    {
        DB::beginTransaction();

        try {

            $order = Order::with('items.secondProduct')
                ->findOrFail($id);

            // status order
            $order->update([
                'status' => 'CANCEL'
            ]);

            // balikin barang jadi ready
            foreach ($order->items as $item) {

                if ($item->secondProduct) {

                    $item->secondProduct->update([
                        'status' => 'ready'
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibatalkan'
            ]);

        } catch (\Throwable $th) {

            DB::rollBack();

            Log::error($th);

            return response()->json(
                $th->getMessage()
            );
        }
    }
}

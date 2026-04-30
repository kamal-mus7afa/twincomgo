<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index() 
    {
        return view('admin.galeriSecond.cart');
    }

    public function checkout()
    {
        $order = Order::with([
                'items.product.images'
            ])
            ->where('user_id', auth()->id())
            ->where('status', 'DRAFT')
            ->first();

        if (!$order || $order->items->isEmpty()) {

            return redirect()
                ->back()
                ->with('error', 'Belum ada barang yang di-keep');
        }

        return view('admin.galeriSecond.cart', compact('order'));
    }
}

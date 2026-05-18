<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class CustomerController extends Controller
{
    public function index () 
    {
        $customers = Customer::all();
        return view('admin.customer.index', compact('customers'));
    }

    public function create()
    {
        return view('admin.customer.create');
    }

    public function store (Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'customer_number' => 'required',
            'phone' => 'nullable', 
        ]);

        Customer::create($validated);
        
        Alert::success('Berhasil', 'Data telah disimpan');
        return back();
    }
}

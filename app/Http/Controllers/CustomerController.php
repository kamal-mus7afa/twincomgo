<?php

namespace App\Http\Controllers;

use App\Helpers\AccurateGlobal;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

    public function customer()
    {
        $acc = AccurateGlobal::token();

        $token = $acc['access_token'];
        $session = $acc['session_id'];
        $pageSize = 10;
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');

        $targetRole = [
            2650 => 'RESELLER',
            2651 => 'PARTNER',
        ];

        foreach ($targetRole as $categoryId => $targetStatus) {
            dd($categoryId, $targetStatus);
        };
        
        $params = [
            'sp.page' => 1,
            'sp.pageSize' => $pageSize,
            'fields' => 'id,name,email,suspended,customerBranchName,customerNo,priceCategory',
            'filter.customerCategoryId' => 2650,
            'filter.suspended' => false,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Session-ID' => $session
        ])->get("{$baseUrl}/customer/list.do", $params);

        $data = $response->json()['d'];

        foreach( $data as $d) {
            $name = $d['name'];
            $accurateId = $d['id'] ?? null;
            $email = $d['email'] ?? null;
            $priceCategory = $d['priceCategory']['name'];            
        }
        dd($name, $accurateId, $email, $priceCategory);
    }
}

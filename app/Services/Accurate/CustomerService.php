<?php

namespace App\Services\Accurate;

use App\Helpers\AccurateGlobal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CustomerService 
{
    protected string $baseUrl;
    protected string $token;
    protected string $session;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.accurate.base_api'), '/');

        $acc = AccurateGlobal::token();

        $this->token = $acc['access_token'];
        $this->session = $acc['session_id'];
    }

    public function client()
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'X-Session-ID' => $this->session,
        ]);
    }

    public function getCustomer(Request $request) 
    {
        $params = [
            'filter.suspended' => false,
            'sp.page' => 1,
            'sp.pageSize' => 100,
            'fields' => 'id,name,customerNo,suspended,mobilePhone',
        ];

        if($request->search) {
            $params['filter.keywords.op'] = 'CONTAIN';
            $params['filter.keywords.val[0]'] = $request->search;
        }

        $res = $this->client()->get(
            "{$this->baseUrl}/customer/list.do", $params
        );

        return $res->json();
    }
}
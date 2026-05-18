<?php

namespace App\Services\Accurate;

use App\Helpers\AccurateGlobal;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class BranchService
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

    public function getBranch(Request $request)
    {
        $params = [
            'filter.suspended' => false,
            'sp.page' => 1,
            'sp.pageSize' => 100,
            'fields' => 'id,name',
        ];

        if($request->search){
            $params['filter.keywords.op'] = 'CONTAIN';
            $params['filter.keywords.val[0]'] = $request->search;
        }

        $res = $this->client()->get(
            "{$this->baseUrl}/branch/list.do", 
            $params
        );

        return $res->json();
    }
}
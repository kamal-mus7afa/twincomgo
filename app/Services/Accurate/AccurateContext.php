<?php

namespace App\Services\Accurate;

use App\Helpers\AccurateGlobal;
use Illuminate\Support\Facades\Auth;

class AccurateContext 
{
    public static function label(): string
    {
        $status = strtoupper(trim(Auth::user()->status ?? ''));

        return in_array($status, ['GLOBAL', 'RESELLER'])
            ? $status
            : 'GLOBAL';  
    }

    public static function token(): array
    {
        return AccurateGlobal::token(self::label());
    }
}
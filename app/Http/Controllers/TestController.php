<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TestController extends Controller
{
    public function logActivityReseller (Request $request) 
    {
        $loginQuery = ActivityLog::query()->
            with('causer')->whereHas('causer', function ($q) {
                $q->where('status', 'reseller');
            });

        if ($request->filled('user')) {
            $loginQuery->where('log_name', $request->user);
        }

        if ($request->filled('start_date')) {
            $loginQuery->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $loginQuery->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $loginQuery->where(function ($q) use ($search) {
                $q->where('log_name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $loginActivities = $loginQuery->latest()->paginate(50);

        return view('karyawan.logactivity.index', ['activities' => $loginActivities]);
    }
}

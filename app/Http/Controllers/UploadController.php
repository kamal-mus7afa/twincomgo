<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'gambar' => 'required|image'
        ]);

        $file = $request->file('gambar');

        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = 'test/'.$filename;

        $response = Http::withHeaders([
            'apikey' => env('SUPABASE_KEY'),
            'Authorization' => 'Bearer ' .env('SUPABASE_KEY'),
        ])
        ->attach(
            'file',
            fopen($file->getRealPath(), 'r'),
            $filename
        )
        ->post(env('SUPABASE_URL'). '/storage/v1/object/'.env('SUPABASE_BUCKET').'/'.$path);

        if(!$response->successful()) {
            return response()->json([
                'error' => $response->body(),
            ], 500);
        }

        $url = env('SUPABASE_URL').'/storage/v1/object/public/'.env('SUPABASE_BUCKET').'/'.$path;

        return response()->json([
            'url' => $url,
        ]);
    }
}

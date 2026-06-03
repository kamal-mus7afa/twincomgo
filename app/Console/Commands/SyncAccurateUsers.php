<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Helpers\AccurateGlobal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncAccurateUsers extends Command
{
    protected $signature = 'sync:accurate-users';
    protected $description = 'Sync users from Accurate API to local database';

    public function handle()
    {
        Log::info('=============================================');
        Log::info('🚀 SYNC ACCURATE USERS STARTED');
        $this->info('⏳ Memulai sinkronisasi data dari Accurate...');

        $acc = AccurateGlobal::token();
        $token = $acc['access_token'] ?? null;
        $session = $acc['session_id'] ?? null;
        
        if (!$token || !$session) {
            Log::error('❌ Gagal mendapatkan token atau session ID dari AccurateGlobal.');
            $this->error('Gagal mendapatkan akses token Accurate!');
            return;
        }

        $pageSize = 100;
        $totalUsers = 0;
        $newUsers = 0;
        $updatedUsers = 0;
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');

        // ================================
        // 1. CUSTOMER RESELLER & PARTNER
        // ================================
        $this->info("👥 Sinkronisasi Customer Reseller & Partner...");
        Log::info('🔄 Memulai sinkronisasi kategori Customer Reseller & Partner...');

        $targetRoles = [
            2650  => 'RESELLER',
            54050 => 'TWINCOM PATNER',
        ];

        foreach ($targetRoles as $categoryId => $targetStatus) {
            $page = 1;

            do {
                Log::info("📥 [Customer: {$targetStatus}] Mengambil data Page {$page}...");
                
                $params = [
                    'sp.page' => $page,
                    'sp.pageSize' => $pageSize,
                    'fields' => 'id,name,email,suspended,customerBranchName,customerNo,priceCategory',
                    'filter.customerCategoryId' => $categoryId,
                ];

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'X-Session-ID' => $session
                ])->timeout(30)->retry(3, 1000)->get("{$baseUrl}/customer/list.do", $params);

                if ($response->failed()) {
                    Log::error("❌ [Customer: {$targetStatus}] API Request gagal pada Page {$page}!", [
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                    break;
                }

                $json = $response->json();
                $customers = $json['d'] ?? [];
                
                if (empty($customers)) {
                    Log::info("✅ [Customer: {$targetStatus}] Tidak ada data lagi di Page {$page}. Pindah ke kategori selanjutnya.");
                    break;
                }

                foreach ($customers as $cust) {
                    $accurateId = $cust['id'] ?? null;
                    $email = $cust['email'] ?? null;

                    if (!$accurateId || !$email) {
                        Log::warning("⚠️ [Customer] Data tidak lengkap (ID/Email kosong), diabaikan.", ['data' => $cust]);
                        continue;
                    }

                    $user = User::where('accurate_id', $accurateId)->orWhere('email', $email)->first();

                    // PROTEKSI: Jangan timpa akun Admin atau Karyawan yang ada di DB Lokal
                    if ($user && in_array($user->status, ['admin', 'KARYAWAN'])) {
                        Log::info("⏭️ [Customer] Skip update: {$email} (Proteksi akun {$user->status} lokal).");
                        continue; 
                    }

                    // Jika customer disuspend di Accurate, set active = false
                    if (!empty($cust['suspended'])) {
                        if ($user) {
                            $user->active = false;
                            $user->save();
                            Log::warning("🛑 [Customer] Akun disuspend di Accurate: {$email} dinonaktifkan di lokal.");
                        }
                        continue;
                    }

                    if (!$user) {
                        $user = new User();
                        $user->password = bcrypt('twincom@' . str_replace(' ', '', strtolower($targetStatus)) . '123'); 
                        $newUsers++;
                        Log::info("✨ [Customer] Membuat user baru: {$email} ({$targetStatus})");
                    } else {
                        $updatedUsers++;
                        // Opsional: Jika log terlalu penuh, baris di bawah bisa diubah jadi Log::debug
                        Log::info("🔄 [Customer] Memperbarui user: {$email}"); 
                    }

                    $duplicate = User::where('accurate_id', $accurateId)
                        ->where('id', '!=', $user->id ?? 0)
                        ->exists();

                    $user->accurate_id = $duplicate ? null : $accurateId;
                    $user->name = $cust['name'];
                    $user->email = $email;
                    $user->province = null; 
                    $user->kategori_penjualan = $cust['priceCategory']['name'] ?? null;
                    $user->status = $targetStatus; 
                    $user->customer_branch = $cust['customerBranchName'] ?? null;
                    
                    // Pastikan di-set aktif karena lolos dari pengecekan suspended di atas
                    $user->active = true; 
                    $user->save();

                    $totalUsers++;
                }
                
                $pageCount = $json['sp']['pageCount'] ?? 0;
                if ($page >= $pageCount) break;
                
                $page++;

            } while (true);
        }

        // ================================
        // 2. KARYAWAN
        // ================================
        $this->info("👨‍💼 Sinkronisasi Karyawan...");
        Log::info('🔄 Memulai sinkronisasi data Karyawan...');
        $page = 1;

        do {
            Log::info("📥 [Karyawan] Mengambil data Page {$page}...");
            
            $params = [
                'sp.page' => $page,
                'sp.pageSize' => $pageSize,
                'fields' => 'id,name,email,suspended',
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID' => $session
            ])->timeout(30)->retry(3, 1000)->get("{$baseUrl}/employee/list.do", $params);

            if ($response->failed()) {
                Log::error("❌ [Karyawan] API Request gagal pada Page {$page}!", [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                break;
            }

            $json = $response->json();
            $employees = $json['d'] ?? [];
            if (empty($employees)) {
                Log::info("✅ [Karyawan] Tidak ada data lagi di Page {$page}.");
                break;
            }

            foreach ($employees as $employee) {
                $accurateId = $employee['id'] ?? null;
                $email = $employee['email'] ?? null;

                if (!$accurateId || !$email) {
                    Log::warning("⚠️ [Karyawan] Data tidak lengkap (ID/Email kosong), diabaikan.", ['data' => $employee]);
                    continue;
                }

                $user = User::where('accurate_id', $accurateId)->orWhere('email', $email)->first();

                // PROTEKSI ADMIN
                if ($user && $user->status === 'admin') {
                    Log::info("⏭️ [Karyawan] Skip update: {$email} (Proteksi akun admin lokal).");
                    continue;
                }

                // Jika Karyawan disuspend di Accurate, set active = false
                if (!empty($employee['suspended'])) {
                    if ($user && $user->status === 'KARYAWAN') {
                        $user->active = false;
                        $user->save();
                        Log::warning("🛑 [Karyawan] Akun disuspend di Accurate: {$email} dinonaktifkan di lokal.");
                    }
                    continue;
                }

                if (!$user) {
                    $user = new User();
                    $user->password = bcrypt('twincom@karyawan123');
                    $newUsers++;
                    Log::info("✨ [Karyawan] Membuat user baru: {$email}");
                } else {
                    $updatedUsers++;
                    Log::info("🔄 [Karyawan] Memperbarui user: {$email}");
                }

                $duplicate = User::where('accurate_id', $accurateId)
                    ->where('id', '!=', $user->id ?? 0)
                    ->exists();

                $user->accurate_id = $duplicate ? null : $accurateId;
                $user->name = $employee['name'];
                $user->email = $email;
                $user->status = 'KARYAWAN';
                
                // Pastikan di-set aktif karena lolos pengecekan suspended
                $user->active = true; 
                $user->save();

                $totalUsers++;
            }

            $pageCount = $json['sp']['pageCount'] ?? 0;
            if ($page >= $pageCount) break;

            $page++;

        } while (true);

        // ================================
        // 3. ADMIN DEFAULT
        // ================================
        if (!User::where('email', 'admin@gmail.com')->exists()) {
            User::create([
                'name'     => 'Administrator',
                'email'    => 'admin@gmail.com',
                'password' => bcrypt('twincom@123'),
                'status'   => 'admin',
                'active'   => true // Set admin bawaan selalu aktif
            ]);
            $newUsers++;
            $totalUsers++;
            Log::info("🛡️ [Admin] Akun admin default (admin@gmail.com) berhasil dibuat.");
        }

        Log::info('🏁 SYNC ACCURATE USERS FINISHED', [
            'total_users'   => $totalUsers,
            'new_users'     => $newUsers,
            'updated_users' => $updatedUsers,
            'time'          => now()->toDateTimeString()
        ]);
        Log::info('=============================================');

        $this->info("✅ Sinkronisasi selesai");
        $this->info("👤 Total user diproses: $totalUsers");
        $this->info("🆕 User baru: $newUsers");
        $this->info("♻️ User update: $updatedUsers");
    }
}
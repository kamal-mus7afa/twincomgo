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
        Log::info('SYNC ACCURATE USERS STARTED', [
            'time' => now()
        ]);
        $this->info('⏳ Memulai sinkronisasi data dari Accurate...');

        $acc = AccurateGlobal::token();

        $token = $acc['access_token'];
        $session = $acc['session_id'];
        $pageSize = 100;

        $totalUsers = 0;
        $newUsers = 0;
        $updatedUsers = 0;
        $baseUrl = rtrim(config('services.accurate.base_api'), '/');
        // ================================
        // CUSTOMER RESELLER
        // ================================
        $this->info("👥 Sinkronisasi Customer Reseller...");

        $page = 1;

        do {
            $params = [
                'sp.page' => $page,
                'sp.pageSize' => $pageSize,
                'fields' => 'id,name,email,suspended,customerBranchName,customerNo',
                'filter.customerCategoryId' => 2650,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID' => $session
            ])->get("{$baseUrl}/customer/list.do", $params);

            if ($response->failed()) break;

            $customers = $response->json()['d'] ?? [];
            if (empty($customers)) break;

            $suspendedEmails = [];

            foreach ($customers as $cust) {
                $accurateId = $cust['id'] ?? null;
                $email = $cust['email'] ?? null;

                if (!$accurateId || !$email) continue;

                if (!empty($cust['suspended'])) {
                    $suspendedEmails[] = $email;
                    continue;
                }

                $province = $withProvince[$accurateId] ?? null;

                $user = User::where('accurate_id', $accurateId)
                    ->orWhere('email', $email)
                    ->first();

                if ($user && $user->status !== 'RESELLER') {
                    $user = User::where('email', $email)->first();
                }

                if (!$user) {
                    $user = new User();
                    $user->password = bcrypt('twincom@reseller123');
                    $newUsers++;
                } else {
                    $updatedUsers++;
                }

                $duplicate = User::where('accurate_id', $accurateId)
                    ->where('id', '!=', $user->id ?? 0)
                    ->exists();

                $user->accurate_id = $duplicate ? null : $accurateId;
                $user->name = $cust['name'];
                $user->email = $email;
                $user->province = $province;
                $user->status = 'RESELLER';
                $user->customer_branch = $cust['customerBranchName'] ?? null;
                $user->save();

                $totalUsers++;
            }
            $page++;

        } while (true);

        // ================================
        // KARYAWAN
        // ================================
        $this->info("👨‍💼 Sinkronisasi Karyawan...");

        $page = 1;

        do {
            $params = [
                'sp.page' => $page,
                'sp.pageSize' => $pageSize,
                'fields' => 'id,name,email,suspended',
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID' => $session
            ])->get("{$baseUrl}/employee/list.do", $params);

            if ($response->failed()) break;

            $employees = $response->json()['d'] ?? [];
            if (empty($employees)) break;

            foreach ($employees as $employee) {
                $accurateId = $employee['id'] ?? null;
                $email = $employee['email'] ?? null;

                if (!$accurateId || !$email) continue;

                if (!empty($employee['suspended'])) {
                    User::where(function ($q) use ($accurateId, $email) {
                            $q->where('accurate_id', $accurateId)
                            ->orWhere('email', $email);
                        })
                        ->where('status', 'KARYAWAN') // ⬅️ ini kuncinya
                        ->delete();

                    continue;
                }

                $user = User::where('accurate_id', $accurateId)
                    ->orWhere('email', $email)
                    ->first();

                if (!$user) {
                    $user = new User();
                    $user->password = bcrypt('twincom@karyawan123');
                    $newUsers++;
                } else {
                    $updatedUsers++;
                }

                $duplicate = User::where('accurate_id', $accurateId)
                    ->where('id', '!=', $user->id ?? 0)
                    ->exists();

                $user->accurate_id = $duplicate ? null : $accurateId;
                $user->name = $employee['name'];
                $user->email = $email;
                $user->status = 'KARYAWAN';
                $user->save();

                $totalUsers++;
            }

            $page++;

        } while (true);

        // ================================
        // ADMIN DEFAULT
        // ================================
        if (!User::where('email', 'admin@gmail.com')->exists()) {
            User::create([
                'name' => 'Administrator',
                'email' => 'admin@gmail.com',
                'password' => bcrypt('twincom@123'),
                'status' => 'ADMIN'
            ]);

            $newUsers++;
            $totalUsers++;
        }

        Log::info('SYNC ACCURATE USERS FINISHED', [
            'total_users' => $totalUsers,
            'new_users' => $newUsers,
            'updated_users' => $updatedUsers,
            'time' => now()
        ]);

        // ================================
        // RINGKASAN
        // ================================
        $this->info("✅ Sinkronisasi selesai");
        $this->info("👤 Total user diproses: $totalUsers");
        $this->info("🆕 User baru: $newUsers");
        $this->info("♻️ User update: $updatedUsers");
    }
}
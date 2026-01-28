<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Helpers\AccurateGlobal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncAccurateUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:accurate-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users from Accurate API to local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);

        Log::info('[SyncAccurateUsers] Command started');
        $this->info('⏳ Memulai sinkronisasi data dari Accurate...');

        try {
            $acc = AccurateGlobal::token();
        } catch (\Throwable $e) {
            Log::error('[SyncAccurateUsers] Failed to get token', [
                'error' => $e->getMessage()
            ]);
            return Command::FAILURE;
        }

        $token   = $acc['access_token'] ?? null;
        $session = $acc['session_id'] ?? null;
        $pageSize = 100;

        if (!$token || !$session) {
            Log::error('[SyncAccurateUsers] Token or session missing', $acc);
            return Command::FAILURE;
        }

        Log::info('[SyncAccurateUsers] Token & session acquired');

        $totalUsers   = 0;
        $newUsers     = 0;
        $updatedUsers = 0;

        /**
         * ==================================================
         * 1️⃣ Sinkronisasi Customer Reseller
         * ==================================================
         */
        Log::info('[SyncAccurateUsers] Start syncing customer reseller');
        $this->info("👥 Sinkronisasi data Customer Reseller...");

        $page = 1;
        do {
            Log::info('[SyncAccurateUsers] Fetch customer page', [
                'page' => $page
            ]);

            $params = [
                'sp.page'     => $page,
                'sp.pageSize' => $pageSize,
                'fields'      => 'id,name,email,suspended,customerBranchName,customerNo',
                'filter.customerCategoryId' => 2650,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID'  => $session
            ])->get('https://public.accurate.id/accurate/api/customer/list.do', $params);

            if ($response->failed()) {
                Log::error('[SyncAccurateUsers] Failed fetch customer list', [
                    'page' => $page,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                break;
            }

            $customers = $response->json()['d'] ?? [];
            if (empty($customers)) {
                Log::info('[SyncAccurateUsers] No more customers');
                break;
            }

            $ids = collect($customers)->pluck('id')->filter();
            $batches = $ids->chunk(50);
            $withProvince = [];

            foreach ($batches as $batch) {
                $detailResponses = Http::pool(fn ($pool) =>
                    $batch->map(fn ($id) =>
                        $pool->withHeaders([
                            'Authorization' => 'Bearer ' . $token,
                            'X-Session-ID'  => $session
                        ])->get("https://public.accurate.id/accurate/api/customer/detail.do?id={$id}")
                    )->all()
                );

                foreach ($detailResponses as $resp) {
                    if ($resp->successful()) {
                        $d = $resp->json()['d'] ?? [];
                        if (isset($d['id'])) {
                            $withProvince[$d['id']] = $d['shipProvince'] ?? null;
                        }
                    }
                }

                usleep(200_000); // throttle
            }

            foreach ($customers as $cust) {
                $accurateId = $cust['id'] ?? null;
                $email      = $cust['email'] ?? null;
                $province   = $withProvince[$accurateId] ?? null;

                if (!$accurateId || !$email) {
                    continue;
                }

                if (!empty($cust['suspended']) && $cust['suspended'] === true) {
                    User::where('accurate_id', $accurateId)
                        ->orWhere(fn ($q) => $q->whereNull('accurate_id')->where('email', $email))
                        ->delete();

                    Log::info('[SyncAccurateUsers] User deleted (suspended)', [
                        'accurate_id' => $accurateId,
                        'email' => $email
                    ]);
                    continue;
                }

                $user = User::where('accurate_id', $accurateId)
                    ->orWhere(fn ($q) => $q->whereNull('accurate_id')->where('email', $email))
                    ->first();

                if (!$user) {
                    $user = new User();
                    $user->password = bcrypt('twincom@reseller123');
                    $newUsers++;
                } else {
                    $updatedUsers++;
                }

                $user->accurate_id     = $accurateId;
                $user->name            = $cust['name'] ?? null;
                $user->email           = $email;
                $user->province        = $province;
                $user->status          = 'RESELLER';
                $user->customer_branch = $cust['customerBranchName'] ?? null;
                $user->save();

                $totalUsers++;
            }

            Log::info('[SyncAccurateUsers] Customer page processed', [
                'page' => $page,
                'totalUsers' => $totalUsers,
                'newUsers' => $newUsers,
                'updatedUsers' => $updatedUsers
            ]);

            $page++;
        } while (true);

        /**
         * ==================================================
         * 2️⃣ Sinkronisasi Karyawan
         * ==================================================
         */
        Log::info('[SyncAccurateUsers] Start syncing employees');
        $this->info("👨‍💼 Sinkronisasi data Karyawan...");

        $page = 1;
        do {
            Log::info('[SyncAccurateUsers] Fetch employee page', [
                'page' => $page
            ]);

            $params = [
                'sp.page'     => $page,
                'sp.pageSize' => $pageSize,
                'fields'      => 'id,name,email,suspended',
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Session-ID'  => $session
            ])->get('https://public.accurate.id/accurate/api/employee/list.do', $params);

            if ($response->failed()) {
                Log::error('[SyncAccurateUsers] Failed fetch employee list', [
                    'page' => $page,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                break;
            }

            $employees = $response->json()['d'] ?? [];
            if (empty($employees)) {
                Log::info('[SyncAccurateUsers] No more employees');
                break;
            }

            foreach ($employees as $employee) {
                $accurateId = $employee['id'] ?? null;
                $email      = $employee['email'] ?? null;

                if (!$accurateId || !$email) continue;

                if (!empty($employee['suspended']) && $employee['suspended'] === true) {
                    User::where('accurate_id', $accurateId)
                        ->orWhere(fn ($q) => $q->whereNull('accurate_id')->where('email', $email))
                        ->delete();

                    Log::info('[SyncAccurateUsers] Employee deleted (suspended)', [
                        'accurate_id' => $accurateId,
                        'email' => $email
                    ]);
                    continue;
                }

                $user = User::where('accurate_id', $accurateId)
                    ->orWhere(fn ($q) => $q->whereNull('accurate_id')->where('email', $email))
                    ->first();

                if (!$user) {
                    $user = new User();
                    $user->password = bcrypt('twincom@karyawan123');
                    $newUsers++;
                } else {
                    $updatedUsers++;
                }

                $user->accurate_id = $accurateId;
                $user->name        = $employee['name'] ?? null;
                $user->email       = $email;
                $user->status      = 'KARYAWAN';
                $user->save();

                $totalUsers++;
            }

            $page++;
        } while (true);

        /**
         * ==================================================
         * 3️⃣ Admin default
         * ==================================================
         */
        if (!User::where('email', 'admin@gmail.com')->exists()) {
            User::create([
                'name' => 'Administrator',
                'email' => 'admin@gmail.com',
                'password' => bcrypt('twincom@123'),
                'status' => 'admin',
            ]);

            Log::info('[SyncAccurateUsers] Default admin created');
            $newUsers++;
            $totalUsers++;
        }

        /**
         * ==================================================
         * ✅ DONE
         * ==================================================
         */
        $duration = round(microtime(true) - $startTime, 2);

        Log::info('[SyncAccurateUsers] Command finished', [
            'totalUsers' => $totalUsers,
            'newUsers' => $newUsers,
            'updatedUsers' => $updatedUsers,
            'execution_time_sec' => $duration
        ]);

        $this->info("✅ Sinkronisasi selesai.");
        $this->info("⏱️ Waktu eksekusi: {$duration} detik");

        return Command::SUCCESS;
    }
}

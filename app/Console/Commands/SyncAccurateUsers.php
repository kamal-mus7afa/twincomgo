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
        $startTime = microtime(true);

        Log::info('===== SYNC ACCURATE USERS START =====');
        $this->info('⏳ Memulai sinkronisasi data dari Accurate...');

        try {
            $acc = AccurateGlobal::token();
        } catch (\Throwable $e) {
            Log::error('Token error: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $headers = [
            'Authorization' => 'Bearer ' . $acc['access_token'],
            'X-Session-ID'  => $acc['session_id']
        ];

        $pageSize = 100;

        $totalUsers   = 0;
        $newUsers     = 0;
        $updatedUsers = 0;
        $deletedUsers = 0;
        $skippedUsers = 0;

        /*
        |--------------------------------------------------------------------------
        | CUSTOMER RESELLER
        |--------------------------------------------------------------------------
        */
        $this->info("👥 Sinkronisasi data Customer Reseller...");
        $page = 1;

        do {
            $response = Http::withHeaders($headers)->get(
                'https://public.accurate.id/accurate/api/customer/list.do',
                [
                    'sp.page'     => $page,
                    'sp.pageSize' => $pageSize,
                    'fields'      => 'id,name,email,suspended,customerBranchName,mobilePhone',
                    'filter.customerCategoryId' => 2650,
                    'filter.suspended' => false,
                ]
            );

            if ($response->failed()) {
                Log::error("Customer fetch gagal page {$page}");
                break;
            }

            $customers = $response->json()['d'] ?? [];
            if (empty($customers)) break;

            foreach ($customers as $cust) {

                    $accurateId = $cust['id'] ?? null;
                    $email      = strtolower(trim($cust['email'] ?? ''));
                    $phone      = trim($cust['mobilePhone'] ?? '');

                    if (!$accurateId) continue;

                    /*
                    |--------------------------------------------------------------------------
                    | Kalau suspended → delete by phone OR email
                    |--------------------------------------------------------------------------
                    */
                    if ($cust['suspended'] === true) {

                        $query = User::where('status', 'RESELLER');

                        if ($phone) {
                            $query->where('mobile_phone', $phone);
                        } elseif ($email) {
                            $query->where('email', $email);
                        }

                        $deleted = $query->delete();

                        if ($deleted) {
                            Log::info('RESELLER DELETED (suspended)', [
                                'accurate_id' => $accurateId,
                                'phone' => $phone,
                                'email' => $email
                            ]);
                        }

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Tentukan identity (phone prioritas, fallback email)
                    |--------------------------------------------------------------------------
                    */
                    if ($phone) {
                        $user = User::where('mobile_phone', $phone)
                            ->where('status', 'RESELLER')
                            ->first();
                    } elseif ($email) {
                        $user = User::where('email', $email)
                            ->where('status', 'RESELLER')
                            ->first();
                    } else {
                        Log::warning('RESELLER SKIPPED (no phone & no email)', [
                            'accurate_id' => $accurateId
                        ]);
                        continue;
                    }

                    $isNew = false;

                    if (!$user) {
                        $user = new User();
                        $user->password = bcrypt('twincom@reseller123');
                        $isNew = true;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Update Data
                    |--------------------------------------------------------------------------
                    */
                    $user->accurate_id     = $accurateId;
                    $user->name            = $cust['name'] ?? null;
                    $user->email           = $email ?: null;
                    $user->mobile_phone    = $phone ?: null;
                    $user->status          = 'RESELLER';
                    $user->customer_branch = $cust['customerBranchName'] ?? null;
                    $user->save();

                    Log::info($isNew ? 'RESELLER CREATED' : 'RESELLER UPDATED', [
                        'accurate_id' => $accurateId,
                        'phone' => $phone,
                        'email' => $email
                    ]);

                    $totalUsers++;
                }

            $page++;

        } while (true);

        /*
        |--------------------------------------------------------------------------
        | EMPLOYEE
        |--------------------------------------------------------------------------
        */
        $this->info("👨‍💼 Sinkronisasi data Karyawan...");
        $page = 1;

        do {
            $response = Http::withHeaders($headers)->get(
                'https://public.accurate.id/accurate/api/employee/list.do',
                [
                    'sp.page'     => $page,
                    'sp.pageSize' => $pageSize,
                    'fields'      => 'id,name,email,suspended',
                ]
            );

            if ($response->failed()) {
                Log::error("Employee fetch gagal page {$page}");
                break;
            }

            $employees = $response->json()['d'] ?? [];
            if (empty($employees)) break;

            foreach ($employees as $emp) {

                $accurateId = $emp['id'] ?? null;
                $email      = strtolower(trim($emp['email'] ?? ''));

                if (!$accurateId || !$email) {
                    $skippedUsers++;
                    continue;
                }

                if (($emp['suspended'] ?? false) === true) {

                    $deleted = User::where('email', $email)
                        ->where('status', 'KARYAWAN')
                        ->delete();

                    if ($deleted) {
                        Log::info('DELETED EMPLOYEE (suspended)', [
                            'accurate_id' => $accurateId,
                            'email' => $email
                        ]);
                    }

                    continue;
                }

                $user = User::where('email', $email)
                        ->where('status', 'KARYAWAN')
                        ->first();

                $isNew = false;

                if (!$user) {
                    $user = new User();
                    $user->password = bcrypt('twincom@karyawan123');
                    $isNew = true;
                    $newUsers++;
                } else {
                    $updatedUsers++;
                }

                $user->accurate_id = $accurateId;
                $user->name        = $emp['name'] ?? null;
                $user->email       = $email;
                $user->status      = 'KARYAWAN';
                $user->save();

                if ($isNew) {
                    Log::info('CREATED EMPLOYEE', [
                        'accurate_id' => $accurateId,
                        'email' => $email
                    ]);
                } else {
                    Log::info('UPDATED EMPLOYEE', [
                        'accurate_id' => $accurateId,
                        'email' => $email
                    ]);
                }

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

        Log::info('===== SYNC FINISHED =====', [
            'total_processed' => $totalUsers,
            'created' => $newUsers,
            'updated' => $updatedUsers,
            'deleted' => $deletedUsers,
            'skipped' => $skippedUsers,
            'duration_sec' => round(microtime(true) - $startTime, 2)
        ]);

        $this->info("✅ Sinkronisasi selesai.");

        return Command::SUCCESS;
    }
}
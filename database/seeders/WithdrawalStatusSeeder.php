<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WithdrawalStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('withdrawal_statuses')->insert([
            [
                'id' => 1,
                'name' => 'Pending',
                'description' => 'Failed auto-processing, needs manual CSV export',
                'color' => 'warning',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Processing',
                'description' => 'Auto-processing in progress',
                'color' => 'info',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Completed',
                'description' => 'Successfully processed',
                'color' => 'success',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Failed',
                'description' => 'Permanently failed, needs investigation',
                'color' => 'danger',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('skills')
            ->where('name', 'PM Software')
            ->update(['name' => 'PMS Software']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('skills')
            ->where('name', 'PMS Software')
            ->update(['name' => 'PM Software']);
    }
};

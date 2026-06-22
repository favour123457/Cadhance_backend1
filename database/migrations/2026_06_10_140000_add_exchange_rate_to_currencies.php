<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->decimal('exchange_rate', 12, 4)->default(1.0000)->after('symbol2');
            $table->boolean('is_base_currency')->default(false)->after('exchange_rate');
        });

        // Set default exchange rates (1 USD = X currency)
        // You can update these values based on current rates
        DB::table('currencies')->where('symbol', 'USD')->update([
            'exchange_rate' => 1.0000,
            'is_base_currency' => true
        ]);
        
        DB::table('currencies')->where('symbol', 'NGN')->update(['exchange_rate' => 1500.00]); // 1 USD = 1500 NGN
        DB::table('currencies')->where('symbol', 'GHS')->update(['exchange_rate' => 12.50]);   // 1 USD = 12.5 GHS
        DB::table('currencies')->where('symbol', 'KES')->update(['exchange_rate' => 130.00]);  // 1 USD = 130 KES
        DB::table('currencies')->where('symbol', 'UGX')->update(['exchange_rate' => 3700.00]); // 1 USD = 3700 UGX
        DB::table('currencies')->where('symbol', 'TZS')->update(['exchange_rate' => 2500.00]); // 1 USD = 2500 TZS
        DB::table('currencies')->where('symbol', 'ZAR')->update(['exchange_rate' => 18.50]);   // 1 USD = 18.5 ZAR
        DB::table('currencies')->where('symbol', 'ZMW')->update(['exchange_rate' => 25.00]);   // 1 USD = 25 ZMW
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn(['exchange_rate', 'is_base_currency']);
        });
    }
};

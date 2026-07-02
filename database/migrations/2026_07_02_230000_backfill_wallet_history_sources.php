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
        // 1. Infer source from tx_ref prefix for legacy records.
        DB::table('wallet_histories')
            ->whereNull('source')
            ->where('tx_ref', 'like', 'topup_%')
            ->update(['source' => 'topup']);

        DB::table('wallet_histories')
            ->whereNull('source')
            ->where('tx_ref', 'like', 'asset_%')
            ->update(['source' => 'asset_sale']);

        DB::table('wallet_histories')
            ->whereNull('source')
            ->where('tx_ref', 'like', 'template_%')
            ->update(['source' => 'template_sale']);

        DB::table('wallet_histories')
            ->whereNull('source')
            ->where('tx_ref', 'like', 'group_%')
            ->update(['source' => 'group_subscription']);

        // 2. Fix legacy subscription debits that were wrongly hardcoded as status 3
        // (Failed) instead of SUCCESS. We only touch rows that were created at the
        // same time as a user subscription for the same wallet owner.
        DB::table('wallet_histories as wh')
            ->join('wallets as w', 'w.id', '=', 'wh.wallet_id')
            ->where('wh.wallet_history_type_id', 2) // Debit
            ->where('wh.wallet_history_status_id', 3) // Was wrongly recorded as Failed
            ->whereNull('wh.source')
            ->whereNull('wh.tx_ref')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('user_subscriptions as us')
                    ->whereColumn('us.user_id', 'w.user_id')
                    ->whereRaw('ABS(TIMESTAMPDIFF(SECOND, us.created_at, wh.created_at)) <= 120');
            })
            ->update([
                'wh.wallet_history_status_id' => 2, // Success
                'wh.source' => 'subscription',
            ]);

        // 3. Legacy affiliate commission credits have no tx_ref and no source.
        DB::table('wallet_histories')
            ->where('wallet_history_type_id', 1) // Credit
            ->where('wallet_history_status_id', 2) // Success
            ->whereNull('source')
            ->whereNull('tx_ref')
            ->update(['source' => 'affiliate_commission']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('wallet_histories')
            ->whereIn('source', ['topup', 'asset_sale', 'template_sale', 'group_subscription', 'subscription', 'affiliate_commission', 'withdrawal'])
            ->update(['source' => null]);
    }
};

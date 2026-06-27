<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Group;
use App\Models\GroupSubscription;
use App\Models\Template;
use App\Models\User;
use App\Models\UserPurchase;
use App\Models\WalletHistory;
use App\Models\WalletHistoryStatus;
use App\Models\WalletHistoryType;
use Illuminate\Support\Facades\Log;

class PaymentFulfillmentService
{
    /**
     * Fulfill an asset purchase atomically.
     * Returns true if fulfilled by this call, false if already completed.
     */
    public static function fulfillAssetPurchase(UserPurchase $purchase): bool
    {
        if ($purchase->status === 'completed') {
            return false;
        }

        $affected = UserPurchase::where('id', $purchase->id)
            ->where('status', 'pending')
            ->update(['status' => 'completed']);

        if ($affected === 0) {
            return false;
        }

        $asset = Asset::find($purchase->purchasable_id);

        if ($asset) {
            $asset->increment('purchase_count');
            $asset->increment('bought_count');

            $seller = User::find($asset->user_id);
            $sellerAmount = $asset->price; // service charge goes to platform

            if ($seller && $seller->wallet) {
                $seller->wallet->increment('balance', $sellerAmount);
                WalletHistory::create([
                    'wallet_id'                => $seller->wallet->id,
                    'amount'                   => $sellerAmount,
                    'wallet_history_type_id'   => WalletHistoryType::CREDIT,
                    'wallet_history_status_id' => WalletHistoryStatus::SUCCESS,
                    'tx_ref'                   => $purchase->tx_ref,
                ]);
            }
        }

        return true;
    }

    /**
     * Fulfill a template purchase atomically.
     */
    public static function fulfillTemplatePurchase(UserPurchase $purchase): bool
    {
        if ($purchase->status === 'completed') {
            return false;
        }

        $affected = UserPurchase::where('id', $purchase->id)
            ->where('status', 'pending')
            ->update(['status' => 'completed']);

        if ($affected === 0) {
            return false;
        }

        $template = Template::find($purchase->purchasable_id);

        if ($template) {
            $template->increment('download_count');
            $seller = User::find($template->user_id);

            if ($seller && $seller->wallet) {
                $seller->wallet->increment('balance', $template->price);
                WalletHistory::create([
                    'wallet_id'                => $seller->wallet->id,
                    'amount'                   => $template->price,
                    'wallet_history_type_id'   => WalletHistoryType::CREDIT,
                    'wallet_history_status_id' => WalletHistoryStatus::SUCCESS,
                    'tx_ref'                   => $purchase->tx_ref,
                ]);
            }
        }

        return true;
    }

    /**
     * Fulfill a group subscription atomically.
     */
    public static function fulfillGroupSubscription(GroupSubscription $subscription): bool
    {
        if ($subscription->status === 'completed') {
            return false;
        }

        $affected = GroupSubscription::where('id', $subscription->id)
            ->where('status', 'pending')
            ->update(['status' => 'completed']);

        if ($affected === 0) {
            return false;
        }

        $group = Group::find($subscription->group_id);

        if ($group) {
            $group->increment('subscribers_count');

            $owner = User::find($group->user_id);
            if ($owner && $owner->wallet) {
                $owner->wallet->increment('balance', $group->price);
                WalletHistory::create([
                    'wallet_id'                => $owner->wallet->id,
                    'amount'                   => $group->price,
                    'wallet_history_type_id'   => WalletHistoryType::CREDIT,
                    'wallet_history_status_id' => WalletHistoryStatus::SUCCESS,
                    'tx_ref'                   => $subscription->tx_ref,
                ]);
            }
        }

        return true;
    }

    /**
     * Attempt to fulfill a purchase/subscription from a charge.completed webhook.
     * The tx_ref prefix determines the type.
     */
    public static function fulfillFromTxRef(string $tx_ref): bool
    {
        $prefix = explode('_', $tx_ref)[0] ?? '';

        switch ($prefix) {
            case 'asset':
                $purchase = UserPurchase::where('tx_ref', $tx_ref)
                    ->where('purchasable_type', 'asset')
                    ->first();
                return $purchase ? self::fulfillAssetPurchase($purchase) : false;

            case 'template':
                $purchase = UserPurchase::where('tx_ref', $tx_ref)
                    ->where('purchasable_type', 'template')
                    ->first();
                return $purchase ? self::fulfillTemplatePurchase($purchase) : false;

            case 'group':
                $subscription = GroupSubscription::where('tx_ref', $tx_ref)->first();
                return $subscription ? self::fulfillGroupSubscription($subscription) : false;

            default:
                Log::warning('Unhandled charge tx_ref prefix in webhook', ['tx_ref' => $tx_ref]);
                return false;
        }
    }
}

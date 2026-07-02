<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionPlanResource;
use App\Http\Resources\UserSubscriptionResource;
use App\Models\AdminData;
use App\Models\AffiliateCommission;
use App\Models\Notification;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Models\Wallet;
use App\Models\WalletHistory;
use App\Models\WalletHistoryStatus;
use App\Models\WalletHistoryType;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class SubscriptionController extends Controller
{
    public function plans()
    {
        $plans = SubscriptionPlan::where('active', true)->get();

        return response()->json(SubscriptionPlanResource::collection($plans));
    }

    public function mySubscription()
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $subscription = UserSubscription::where('user_id', $user->id)
            ->where('active', true)
            ->where('expire_at', '>=', now()->toDateString())
            ->first();

        $accessInfo = app(SubscriptionService::class)->getAccessInfo($user);

        if (!$subscription) {
            return response()->json([
                'status'      => 'free',
                'message'     => 'No active subscription.',
                'access_info' => $accessInfo,
            ]);
        }

        return response()->json([
            'subscription' => new UserSubscriptionResource($subscription),
            'access_info'  => $accessInfo,
        ]);
    }

    public function subscribe(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $plan_id      = $request->subscription_plan_id;
        $billingCycle = in_array($request->billing_cycle, ['monthly', 'annual'])
            ? $request->billing_cycle
            : 'monthly';

        $plan = SubscriptionPlan::find($plan_id);

        if (!$plan || !$plan->active) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Invalid subscription plan!',
            ], 400);
        }

        $planAmount = $billingCycle === 'annual'
            ? (float) ($plan->annual_price ?? 0)
            : (float) ($plan->monthly_price ?? 0);

        if ($planAmount <= 0) {
            $planAmount = (float) ($plan->price ?? 0);
        }

        // Check wallet balance
        $wallet = Wallet::where('user_id', $user->id)->first();
        if (!$wallet || (float) $wallet->balance < $planAmount) {
            $balance = $wallet ? number_format((float) $wallet->balance, 2) : '0.00';
            return response()->json([
                'status'  => 'failed',
                'message' => "Insufficient wallet balance. Your balance is \${$balance}. Please top up your wallet first.",
            ], 422);
        }

        $subscription = DB::transaction(function () use ($user, $plan, $plan_id, $billingCycle, $wallet, $planAmount) {
            // Deduct subscription fee from wallet
            $wallet->decrement('balance', $planAmount);

            WalletHistory::create([
                'wallet_id'                => $wallet->id,
                'amount'                   => $planAmount,
                'wallet_history_type_id'   => WalletHistoryType::DEBIT,
                'wallet_history_status_id' => WalletHistoryStatus::SUCCESS,
                'source'                   => 'subscription',
            ]);

            // Deactivate any previous subscriptions
            UserSubscription::where('user_id', $user->id)
                ->where('active', true)
                ->update(['active' => false]);

            $expireAt = $billingCycle === 'annual'
                ? now()->addYear()->toDateString()
                : now()->addMonth()->toDateString();

            $subscription = UserSubscription::create([
                'user_id'              => $user->id,
                'subscription_plan_id' => $plan_id,
                'billing_cycle'        => $billingCycle,
                'expire_at'            => $expireAt,
                'active'               => true,
            ]);

            $this->creditAffiliateCommission($subscription, $planAmount);

            return $subscription;
        });

        Notification::create([
            'user_id' => $user->id,
            'title'   => 'Subscription Activated',
            'message' => 'Your ' . $plan->name . ' (' . $billingCycle . ') subscription has been activated successfully!',
        ]);

        return response()->json([
            'status'       => 'success',
            'message'      => 'Subscription activated successfully!',
            'subscription' => new UserSubscriptionResource($subscription),
            'access_info'  => app(SubscriptionService::class)->getAccessInfo($user),
        ]);
    }

    private function creditAffiliateCommission(UserSubscription $subscription, float $planAmount): void
    {
        $subscriber = $subscription->user;

        if (!$subscriber || !$subscriber->referred_by_user_id || $planAmount <= 0) {
            return;
        }

        if ($subscriber->referred_by_user_id === $subscriber->id) {
            return;
        }

        $existing = AffiliateCommission::where('user_subscription_id', $subscription->id)->first();
        if ($existing) {
            return;
        }

        $adminData = AdminData::find(6);
        $rate = (float) ($adminData->value ?? 0);

        if ($rate <= 0) {
            return;
        }

        $commissionAmount = round(($planAmount * $rate) / 100, 2);

        if ($commissionAmount <= 0) {
            return;
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $subscriber->referred_by_user_id],
            ['balance' => 0]
        );

        $wallet->increment('balance', $commissionAmount);

        WalletHistory::create([
            'wallet_id'                => $wallet->id,
            'amount'                   => $commissionAmount,
            'wallet_history_type_id'   => WalletHistoryType::CREDIT,
            'wallet_history_status_id' => WalletHistoryStatus::SUCCESS,
            'source'                   => 'affiliate_commission',
        ]);

        AffiliateCommission::create([
            'referrer_user_id'    => $subscriber->referred_by_user_id,
            'referred_user_id'    => $subscriber->id,
            'user_subscription_id' => $subscription->id,
            'subscription_plan_id' => $subscription->subscription_plan_id,
            'billing_cycle'       => $subscription->billing_cycle,
            'plan_amount'         => $planAmount,
            'commission_rate'     => $rate,
            'commission_amount'   => $commissionAmount,
            'admin_data_id'       => $adminData->id,
            'status'              => 'approved',
            'paid_at'             => now(),
        ]);
    }

    public function cancel()
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $subscription = UserSubscription::where('user_id', $user->id)
            ->where('active', true)
            ->first();

        if (!$subscription) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'No active subscription to cancel!',
            ], 400);
        }

        $subscription->update(['active' => false]);

        Notification::create([
            'user_id' => $user->id,
            'title'   => 'Subscription Cancelled',
            'message' => 'Your subscription has been cancelled successfully!',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Subscription cancelled successfully!',
        ]);
    }

    /**
     * Returns a full breakdown of the authenticated user's current access levels.
     * Useful for the mobile/web app to gate UI features.
     */
    public function accessInfo()
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        return response()->json(app(SubscriptionService::class)->getAccessInfo($user));
    }
}

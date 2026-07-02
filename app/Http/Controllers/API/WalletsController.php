<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalletHistoriesResource;
use App\Models\Escrow;
use App\Models\WalletHistory;
use App\Models\WalletHistoryStatus;
use App\Models\WalletHistoryType;
use App\Services\FlutterwaveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class WalletsController extends Controller
{
    public function histories(){
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $wallet_histories = $user->wallet->wallet_histories;

        return response()->json(WalletHistoriesResource::collection($wallet_histories));
    }

    /**
     * GET /wallet/summary
     * Returns the user's wallet balance, total earned, total withdrawn, and escrow.
     * Totals are calculated in USD using amount_usd when available.
     */
    public function summary()
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $wallet = $user->wallet;

        // Credits are split into user deposits (top-ups) and platform earnings
        // (sales, group subscriptions, affiliate commissions). The `source`
        // column is preferred; for legacy rows we infer from the tx_ref prefix.
        $totalDeposited = WalletHistory::where('wallet_id', $wallet->id)
            ->where('wallet_history_type_id', WalletHistoryType::CREDIT)
            ->where('wallet_history_status_id', WalletHistoryStatus::SUCCESS)
            ->where(function ($query) {
                $query->where('source', 'topup')
                    ->orWhere(function ($q) {
                        $q->whereNull('source')->where('tx_ref', 'like', 'topup_%');
                    });
            })
            ->sum(DB::raw('COALESCE(amount_usd, amount)'));

        $totalEarned = WalletHistory::where('wallet_id', $wallet->id)
            ->where('wallet_history_type_id', WalletHistoryType::CREDIT)
            ->where('wallet_history_status_id', WalletHistoryStatus::SUCCESS)
            ->where(function ($query) {
                $query->whereIn('source', ['asset_sale', 'template_sale', 'group_subscription', 'affiliate_commission'])
                    ->orWhere(function ($q) {
                        $q->whereNull('source')
                            ->where(function ($inner) {
                                $inner->where('tx_ref', 'like', 'asset_%')
                                    ->orWhere('tx_ref', 'like', 'template_%')
                                    ->orWhere('tx_ref', 'like', 'group_%');
                            });
                    })
                    ->orWhere(function ($q) {
                        // Legacy affiliate commission credits have no source and no tx_ref.
                        $q->whereNull('source')->whereNull('tx_ref');
                    });
            })
            ->sum(DB::raw('COALESCE(amount_usd, amount)'));

        $totalWithdrawn = WalletHistory::where('wallet_id', $wallet->id)
            ->where('wallet_history_type_id', WalletHistoryType::DEBIT)
            ->where('wallet_history_status_id', WalletHistoryStatus::SUCCESS)
            ->sum(DB::raw('COALESCE(amount_usd, amount)'));

        $inEscrow = Escrow::where('user_id', $user->id)->sum('amount');

        return response()->json([
            'status' => 'success',
            'data' => [
                'current_balance' => (float) $wallet->balance,
                'total_earned'    => (float) $totalEarned,
                'total_deposited' => (float) $totalDeposited,
                'total_withdrawn' => (float) $totalWithdrawn,
                'in_escrow'       => (float) $inEscrow,
            ],
        ]);
    }

    /**
     * POST /wallet/topup/initiate
     * Body: { "amount": 5, "currency": "USD" (optional, defaults to USD) }
     * Returns a Flutterwave hosted payment link.
     */
    public function initiateTopup(Request $request)
    {
        $request->validate([
            'amount'   => 'required|numeric|min:1',
            'currency' => 'sometimes|string|size:3',
        ]);

        $token    = JWTAuth::parseToken();
        $user     = $token->authenticate();
        $currency = strtoupper($request->input('currency', 'USD'));
        $amount   = (float) $request->input('amount');
        $tx_ref   = 'topup_' . $user->id . '_' . Str::random(12);

        // The wallet balance is kept in USD. Store the USD equivalent so the wallet
        // is credited correctly regardless of which currency Flutterwave charged.
        $amountUsd = $currency === 'USD'
            ? $amount
            : convertCurrency($amount, $currency, 'USD');

        // Create a pending wallet history record
        $history = WalletHistory::create([
            'wallet_id'               => $user->wallet->id,
            'amount'                  => $amount,
            'amount_usd'              => $amountUsd,
            'wallet_history_type_id'  => WalletHistoryType::CREDIT,
            'wallet_history_status_id'=> WalletHistoryStatus::PENDING,
            'tx_ref'                  => $tx_ref,
            'currency'                => $currency,
            'source'                  => 'topup',
        ]);

        $flutterwave  = new FlutterwaveService();
        $redirect_url = url('/api/wallet/topup/callback');

        $response = $flutterwave->initiatePayment(
            amount:       $amount,
            currency:     $currency,
            tx_ref:       $tx_ref,
            redirect_url: $redirect_url,
            customer: [
                'email'       => $user->email,
                'name'        => $user->name,
                'phonenumber' => $user->phone ?? '',
            ]
        );

        if (($response['status'] ?? '') !== 'success') {
            // Roll back the pending record
            WalletHistory::where('tx_ref', $tx_ref)->delete();

            return response()->json([
                'status'  => 'failed',
                'message' => $response['message'] ?? 'Could not generate payment link.',
            ], 502);
        }

        // Store the Flutterwave transaction ID so callbacks/webhooks can ensure
        // they are fulfilling the exact transaction we created.
        $history->update(['transaction_id' => $response['data']['id'] ?? null]);

        return response()->json([
            'status'       => 'success',
            'payment_link' => $response['data']['link'],
            'tx_ref'       => $tx_ref,
        ]);
    }

    /**
     * GET /wallet/topup/callback
     * Flutterwave redirects here after the user completes (or cancels) payment.
     * Query params: status, tx_ref, transaction_id
     */
    public function topupCallback(Request $request)
    {
        $status         = $request->query('status');
        $tx_ref         = $request->query('tx_ref');
        $transaction_id = (int) $request->query('transaction_id');

        \Log::info('Wallet topup callback received', [
            'status' => $status,
            'tx_ref' => $tx_ref,
            'transaction_id' => $transaction_id,
        ]);

        if (!in_array($status, ['successful', 'completed'], true) || !$tx_ref || !$transaction_id) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Payment was not completed.',
            ], 400);
        }

        $history = WalletHistory::where('tx_ref', $tx_ref)->first();

        if (!$history) {
            return response()->json(['status' => 'failed', 'message' => 'Transaction not found.'], 404);
        }

        // Already processed — idempotency guard
        if ($history->wallet_history_status_id === WalletHistoryStatus::SUCCESS) {
            return response()->json(['status' => 'success', 'message' => 'Already processed.']);
        }

        // Verify with Flutterwave, including amount/currency and transaction_id checks.
        $verification = verifyFlutterwavePayment(
            transaction_id: $transaction_id,
            tx_ref: $tx_ref,
            expected_amount: (float) $history->amount,
            expected_currency: $history->currency,
            expected_transaction_id: $history->transaction_id,
        );

        if (!$verification['valid']) {
            $history->update(['wallet_history_status_id' => WalletHistoryStatus::FAILED]);
            return response()->json([
                'status'  => 'failed',
                'message' => 'Payment verification failed: ' . $verification['error'],
            ], 400);
        }

        // Atomically fulfill the top-up (prevents double-credit from callback + webhook)
        $fulfilled = fulfillWalletTopup($history);

        return response()->json([
            'status'  => 'success',
            'message' => $fulfilled ? 'Wallet topped up successfully.' : 'Already processed.',
            'amount'  => $history->amount,
            'balance' => $history->wallet->fresh()->balance,
        ]);
    }
}

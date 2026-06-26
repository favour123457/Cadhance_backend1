<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalletHistoriesResource;
use App\Models\WalletHistory;
use App\Models\WalletHistoryStatus;
use App\Models\WalletHistoryType;
use App\Services\FlutterwaveService;
use Illuminate\Http\Request;
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

        // Create a pending wallet history record
        WalletHistory::create([
            'wallet_id'               => $user->wallet->id,
            'amount'                  => $amount,
            'wallet_history_type_id'  => WalletHistoryType::CREDIT,
            'wallet_history_status_id'=> WalletHistoryStatus::PENDING,
            'tx_ref'                  => $tx_ref,
            'currency'                => $currency,
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

        if ($status !== 'successful' || !$tx_ref || !$transaction_id) {
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

        // Verify with Flutterwave
        $flutterwave = new FlutterwaveService();
        $verification = $flutterwave->verifyTransaction($transaction_id);

        $data = $verification['data'] ?? null;

        if (
            ($verification['status'] ?? '') !== 'success' ||
            ($data['status'] ?? '') !== 'successful' ||
            ($data['tx_ref'] ?? '') !== $tx_ref
        ) {
            $history->update(['wallet_history_status_id' => WalletHistoryStatus::FAILED]);
            return response()->json([
                'status'  => 'failed',
                'message' => 'Payment verification failed.',
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

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WalletHistory;
use App\Models\WalletHistoryStatus;
use App\Models\WalletHistoryType;
use App\Models\Withdrawal;
use App\Services\FlutterwaveService;
use App\Services\PaymentFulfillmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FlutterwaveWebhookController extends Controller
{
    /**
     * Handle Flutterwave webhooks for transfers and payments.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        $event = $payload['event'] ?? '';
        $data = $payload['data'] ?? [];

        Log::info('Flutterwave webhook received', [
            'event' => $event,
            'reference' => $data['reference'] ?? ($data['tx_ref'] ?? null),
        ]);

        if (!$this->verifySignature($request)) {
            Log::warning('Flutterwave webhook signature mismatch');
            return response()->json(['status' => 'failed', 'message' => 'Unauthorized'], 401);
        }

        // Transfer lifecycle events
        if (str_starts_with($event, 'transfer.')) {
            return $this->handleTransferWebhook($data);
        }

        // Payment/charge events (top-ups, purchases)
        if ($event === 'charge.completed') {
            return $this->handleChargeWebhook($data);
        }

        return response()->json(['status' => 'success', 'message' => 'Event ignored']);
    }

    /**
     * Verify the Flutterwave webhook signature.
     */
    protected function verifySignature(Request $request): bool
    {
        $secret = config('flutterwave.webhook_secret');

        // If no secret is configured, accept webhooks in local/dev environments only
        if (empty($secret)) {
            return app()->environment('local', 'development', 'testing');
        }

        $signature = $request->header('verif-hash');

        return hash_equals($secret, (string) $signature);
    }

    /**
     * Process transfer status updates.
     */
    protected function handleTransferWebhook(array $data)
    {
        $reference = $data['reference'] ?? null;

        if (!$reference) {
            return response()->json(['status' => 'failed', 'message' => 'Missing reference'], 400);
        }

        $withdrawal = Withdrawal::where('flutterwave_reference', $reference)->first();

        if (!$withdrawal) {
            Log::warning('Withdrawal not found for Flutterwave transfer webhook', ['reference' => $reference]);
            return response()->json(['status' => 'failed', 'message' => 'Withdrawal not found'], 404);
        }

        // Idempotency guard
        if ($withdrawal->withdrawal_status_id == Withdrawal::COMPLETED) {
            return response()->json(['status' => 'success', 'message' => 'Already completed']);
        }

        $status = strtoupper($data['status'] ?? '');
        $completeMessage = $data['complete_message'] ?? null;

        switch ($status) {
            case 'SUCCESSFUL':
            case 'SUCCESS':
                $withdrawal->update([
                    'withdrawal_status_id' => Withdrawal::COMPLETED,
                    'processed_at' => now(),
                    'failure_reason' => null,
                ]);
                $this->updateWalletHistoryStatus($withdrawal, WalletHistoryStatus::SUCCESS);
                break;

            case 'FAILED':
            case 'FAIL':
                // Refund if not already refunded
                if ($withdrawal->withdrawal_status_id != Withdrawal::FAILED && $withdrawal->user->wallet) {
                    $withdrawal->user->wallet->increment('balance', $withdrawal->amount);
                }

                $withdrawal->update([
                    'withdrawal_status_id' => Withdrawal::FAILED,
                    'failure_reason' => $completeMessage ?? $data['status'] ?? 'Transfer failed',
                ]);
                $this->updateWalletHistoryStatus($withdrawal, WalletHistoryStatus::FAILED);
                break;

            case 'PENDING':
            case 'PROCESSING':
            case 'QUEUED':
                $withdrawal->update([
                    'withdrawal_status_id' => Withdrawal::PROCESSING,
                    'failure_reason' => null,
                ]);
                $this->updateWalletHistoryStatus($withdrawal, WalletHistoryStatus::PENDING);
                break;

            default:
                Log::info('Unhandled transfer webhook status', ['status' => $status, 'reference' => $reference]);
        }

        return response()->json(['status' => 'success', 'message' => 'Transfer status updated']);
    }

    /**
     * Process charge.completed events for wallet top-ups and purchases.
     */
    protected function handleChargeWebhook(array $data)
    {
        $tx_ref = $data['tx_ref'] ?? null;
        $status = $data['status'] ?? '';
        $transaction_id = (int) ($data['id'] ?? 0);

        if (!$tx_ref) {
            return response()->json(['status' => 'failed', 'message' => 'Missing tx_ref'], 400);
        }

        if (!$transaction_id) {
            return response()->json(['status' => 'failed', 'message' => 'Missing transaction_id'], 400);
        }

        // Wallet top-ups
        if (str_starts_with($tx_ref, 'topup_')) {
            return $this->handleTopupWebhook($tx_ref, $status, $transaction_id);
        }

        // Purchases and subscriptions
        if (strtolower($status) !== 'successful') {
            return response()->json(['status' => 'success', 'message' => 'Charge status ignored']);
        }

        // Look up the pending record to enforce amount/currency/transaction_id checks.
        $expected = $this->getExpectedPaymentDetails($tx_ref);

        if (!$expected) {
            Log::warning('Purchase/subscription not found for Flutterwave charge webhook', ['tx_ref' => $tx_ref]);
            return response()->json(['status' => 'failed', 'message' => 'Transaction not found'], 404);
        }

        // Server-side verification with amount/currency/transaction_id checks.
        $verification = verifyFlutterwavePayment(
            transaction_id: $transaction_id,
            tx_ref: $tx_ref,
            expected_amount: $expected['amount'],
            expected_currency: $expected['currency'],
            expected_transaction_id: $expected['transaction_id'],
        );

        if (!$verification['valid']) {
            Log::warning('Charge webhook verification failed', [
                'tx_ref' => $tx_ref,
                'transaction_id' => $transaction_id,
                'error' => $verification['error'],
            ]);
            return response()->json(['status' => 'failed', 'message' => 'Verification failed'], 400);
        }

        $fulfilled = PaymentFulfillmentService::fulfillFromTxRef($tx_ref);

        return response()->json([
            'status'  => 'success',
            'message' => $fulfilled ? 'Purchase fulfilled' : 'Already processed or unrecognized',
        ]);
    }

    /**
     * Process charge.completed events for wallet top-ups.
     */
    protected function handleTopupWebhook(string $tx_ref, string $status, $transaction_id)
    {
        $transaction_id = (int) $transaction_id;

        if (!$transaction_id) {
            return response()->json(['status' => 'failed', 'message' => 'Missing transaction_id'], 400);
        }

        $history = WalletHistory::where('tx_ref', $tx_ref)->first();

        if (!$history) {
            Log::warning('Wallet history not found for Flutterwave charge webhook', ['tx_ref' => $tx_ref]);
            return response()->json(['status' => 'failed', 'message' => 'Transaction not found'], 404);
        }

        if (strtolower($status) !== 'successful') {
            $history->update(['wallet_history_status_id' => WalletHistoryStatus::FAILED]);
            return response()->json(['status' => 'success', 'message' => 'Charge status recorded']);
        }

        // Server-side verification with amount/currency/transaction_id checks.
        $verification = verifyFlutterwavePayment(
            transaction_id: $transaction_id,
            tx_ref: $tx_ref,
            expected_amount: (float) $history->amount,
            expected_currency: $history->currency,
            expected_transaction_id: $history->transaction_id,
        );

        if (!$verification['valid']) {
            Log::warning('Topup webhook verification failed', [
                'tx_ref' => $tx_ref,
                'transaction_id' => $transaction_id,
                'error' => $verification['error'],
            ]);
            $history->update(['wallet_history_status_id' => WalletHistoryStatus::FAILED]);
            return response()->json(['status' => 'failed', 'message' => 'Verification failed'], 400);
        }

        fulfillWalletTopup($history);

        return response()->json(['status' => 'success', 'message' => 'Wallet credited']);
    }

    /**
     * Get expected payment details for a tx_ref so webhooks can verify
     * amount, currency, and transaction_id before fulfilling.
     */
    protected function getExpectedPaymentDetails(string $tx_ref): ?array
    {
        $prefix = explode('_', $tx_ref)[0] ?? '';

        switch ($prefix) {
            case 'asset':
                $purchase = \App\Models\UserPurchase::where('tx_ref', $tx_ref)
                    ->where('purchasable_type', 'asset')
                    ->first();
                return $purchase ? [
                    'amount' => (float) $purchase->amount_paid,
                    'currency' => $purchase->currency,
                    'transaction_id' => $purchase->transaction_id,
                ] : null;

            case 'template':
                $purchase = \App\Models\UserPurchase::where('tx_ref', $tx_ref)
                    ->where('purchasable_type', 'template')
                    ->first();
                return $purchase ? [
                    'amount' => (float) $purchase->amount_paid,
                    'currency' => $purchase->currency,
                    'transaction_id' => $purchase->transaction_id,
                ] : null;

            case 'group':
                $subscription = \App\Models\GroupSubscription::where('tx_ref', $tx_ref)->first();
                return $subscription ? [
                    'amount' => (float) $subscription->amount_paid,
                    'currency' => $subscription->currency,
                    'transaction_id' => $subscription->transaction_id,
                ] : null;
        }

        return null;
    }

    /**
     * Update the linked wallet history status for a withdrawal.
     */
    protected function updateWalletHistoryStatus(Withdrawal $withdrawal, int $statusId): void
    {
        WalletHistory::where('wallet_id', $withdrawal->user->wallet->id ?? null)
            ->where('amount', $withdrawal->amount)
            ->where('wallet_history_type_id', WalletHistoryType::DEBIT)
            ->where('created_at', '>=', $withdrawal->created_at->subMinutes(5))
            ->latest()
            ->first()
            ?->update(['wallet_history_status_id' => $statusId]);
    }
}

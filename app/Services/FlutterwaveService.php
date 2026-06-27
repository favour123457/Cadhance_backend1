<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FlutterwaveService
{
    protected string $base;
    protected string $secret;

    public function __construct()
    {
        $this->base   = config('flutterwave.base_url', 'https://api.flutterwave.com/v3');
        $this->secret = config('flutterwave.secret_key');
    }

    protected function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->secret,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }

    /**
     * Resolve / verify a bank account.
     * Returns Flutterwave response array.
     */
    public function resolveAccount(string $account_number, string $bank_code): array
    {
        $resp = Http::withHeaders($this->headers())
            ->post($this->base . '/accounts/resolve', [
                'account_number' => $account_number,
                'account_bank'   => $bank_code,
            ]);

        return $this->handleResponse($resp);
    }

    /**
     * Initiate a bank transfer (payout).
     * Supports multiple currencies.
     */
    public function initiateTransfer(
        string $account_number,
        string $bank_code,
        string $account_name,
        float  $amount,
        string $currency = 'NGN',
        string $narration = '',
        string $reference = null,
        ?string $destination_branch_code = null,
        array  $meta = []
    ): array {
        $reference = $reference ?? 'fw_' . Str::random(16);

        $payload = [
            'account_bank'   => $bank_code,
            'account_number' => $account_number,
            'amount'         => $amount,
            'narration'      => $narration,
            'currency'       => $currency,
            'reference'      => $reference,
            'debit_currency' => $currency,
        ];

        // Add optional fields for specific countries
        if ($destination_branch_code) {
            $payload['destination_branch_code'] = $destination_branch_code;
        }

        // Beneficiary metadata required by some countries/regulators
        if (!empty($meta)) {
            $payload['meta'] = $meta;
        }

        $resp = Http::withHeaders($this->headers())
            ->post($this->base . '/transfers', $payload);

        return $this->handleResponse($resp);
    }

    /**
     * Initiate mobile money transfer (payout).
     * For mobile money withdrawals in supported countries.
     */
    public function initiateMobileMoneyTransfer(
        string $phone_number,
        string $network,
        float  $amount,
        string $currency,
        string $narration = '',
        string $reference = null,
        array  $meta = []
    ): array {
        $reference = $reference ?? 'fw_mm_' . Str::random(16);

        $payload = [
            'account_bank'   => $network, // e.g., MTN, VDF (Vodafone), AIRTEL, etc.
            'account_number' => $phone_number,
            'amount'         => $amount,
            'narration'      => $narration,
            'currency'       => $currency,
            'reference'      => $reference,
            'debit_currency' => $currency,
        ];

        if (!empty($meta)) {
            $payload['meta'] = $meta;
        }

        $resp = Http::withHeaders($this->headers())
            ->post($this->base . '/transfers', $payload);

        return $this->handleResponse($resp);
    }

    /**
     * Fetch a transfer by its unique reference.
     */
    public function verifyTransfer(string $reference): array
    {
        $resp = Http::withHeaders($this->headers())
            ->get($this->base . '/transfers', ['reference' => $reference]);

        return $this->handleResponse($resp);
    }

    /**
     * Generate a Flutterwave hosted payment link.
     *
     * @param  float   $amount       Amount in the given currency (e.g. USD).
     * @param  string  $currency     ISO currency code, default 'USD'.
     * @param  string  $tx_ref       Unique transaction reference.
     * @param  string  $redirect_url Where Flutterwave redirects after payment.
     * @param  array   $customer     ['email', 'name', 'phonenumber'].
     * @param  string  $title        Payment page title.
     * @return array
     */
    public function initiatePayment(
        float  $amount,
        string $currency,
        string $tx_ref,
        string $redirect_url,
        array  $customer,
        string $title = 'Wallet Top-up'
    ): array {
        $payload = [
            'tx_ref'       => $tx_ref,
            'amount'       => $amount,
            'currency'     => $currency,
            'redirect_url' => $redirect_url,
            'payment_options' => 'card,banktransfer,ussd',
            'customer'     => $customer,
            'customizations' => [
                'title'       => $title,
                'description' => 'Fund your wallet',
            ],
        ];

        $resp = Http::withHeaders($this->headers())
            ->post($this->base . '/payments', $payload);

        return $this->handleResponse($resp);
    }

    /**
     * Verify a transaction by Flutterwave transaction ID.
     */
    public function verifyTransaction(int $transaction_id): array
    {
        $resp = Http::withHeaders($this->headers())
            ->get($this->base . '/transactions/' . $transaction_id . '/verify');

        return $this->handleResponse($resp);
    }

    protected function handleResponse($resp): array
    {
        if ($resp->successful()) {
            return $resp->json();
        }

        return [
            'status'  => 'error',
            'message' => 'Flutterwave request failed',
            'data'    => $resp->json() ?? [],
        ];
    }
}

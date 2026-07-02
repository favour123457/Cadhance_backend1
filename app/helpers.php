<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use App\Models\GeneralSetting;
use App\Models\Permission;
// use Kreait\Firebase\Messaging\CloudMessage;
// use Kreait\Firebase\Messaging\Notification;


function showImage($path)
{
    return $path && file_exists('storage/' . $path) ? asset('storage/' . $path) : asset('img/product/noimage.jpg');
}

function showMoney($amount)
{
    return '$' . number_format($amount, 2);
}

function sanitizeMoney($amount)
{
    return  preg_replace('/[^0-9.]+/', '', $amount);
}

function checkPermission($table)
{
    $user = auth()->user();
    $role = $user->role;

    $permission = Permission::where('description', $table)->first();
    if (!$permission) {
        return false;
    }

    $isGranted = $role->permissions->contains($permission);
    if (!$isGranted) {
        return false;
    }

    return true;
}

function checkButtonPermission($table, $type)
{
    $user = auth()->user();
    $role = $user->role;

    $permission = Permission::where('description', $table)->where('name', $type . '_' . $table)->first();
    if (!$permission) {
        return false;
    }

    $isGranted = $role->permissions->contains($permission);
    if (!$isGranted) {
        return false;
    }

    return true;
}

function allSettings()
{
    $settings  = GeneralSetting::where('active', 1)->orderBy('name')->get();

    return $settings;
}

// Function to validate email addresses and check domain existence
function validateEmails(array $emails): array
{
    $validEmails = [];
    $validTLDs = [
        'com',
        'net',
        'org',
        'edu',
        'gov',
        'mil',
        'int',
        'io',
        'co',
        'biz',
        'info',
        'xyz',
        'ai',
        'tech',
        'me',
        'us',
        'uk',
        'ng',
        'ca',
        'au',
        'de',
        'fr',
        'es',
        'it',
        'in'
    ]; // Extend this list as needed

    foreach ($emails as $email) {
        // Validate email format
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Extract domain from email
            $domain = substr(strrchr($email, "@"), 1);

            // Extract TLD
            $domainParts = explode('.', $domain);
            $tld = end($domainParts);

            // Check if the TLD is valid and domain has MX or A records
            if (in_array($tld, $validTLDs) && (checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A'))) {
                $validEmails[] = $email;
            }
        }
    }

    return $validEmails;
}

function sendSMS($phoneNumber, $message)
{

    try {

        // Get API credentials from config
        $apiKey = config('app.messagecentral.api_key');
        $clientId = config('app.messagecentral.client_id');


        if (!$apiKey || !$clientId) {
            return response()->json([
                'status' => 'error',
                'message' => 'API credentials not configured'
            ], 500);
        }

        // Build API URL
        $apiUrl = 'https://user.messagecentral.com/api/v2/SendSMS';
        $trimPhoneNumber = '234' . ltrim($phoneNumber, '0');

        Log::error('Phone number: ' . $trimPhoneNumber);

        // Make API call
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->get($apiUrl, [
            'ApiKey' => $apiKey,
            'ClientId' => $clientId,
            'SenderId' => "Supercash",
            'Message' => $message,
            'MobileNumbers' => $trimPhoneNumber,
            //                'enddate' => $validated['enddate'],
        ]);

        // Check if request was successful
        if ($response->successful()) {
            return response()->json([
                'status' => 'success',
                'data' => $response->json(),
            ]);
        }

        // Handle API errors
        return response()->json([
            'status' => 'error',
            'message' => 'API request failed',
            'error' => $response->json() ?? $response->body(),
        ], $response->status());
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        // Log the error
        Log::error('SMS API error: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'An error occurred while processing the request',
        ], 500);
    }
}

// function triggerFirebase($path, $user_id)
// {
//     $firebase = (new Factory)
//         ->withServiceAccount(__DIR__ . '/supercash-32e0c-firebase-adminsdk-fbsvc-119685a43e.json')
//         ->withDatabaseUri('https://supercash-32e0c-default-rtdb.firebaseio.com');
//     $database = $firebase->createDatabase();

//     $reference = $database->getReference($path . '/' . $user_id);

//     $now = date('Y-m-d H:i:s');

//     $creategroup = $reference->set([
//         "lastUpdated" => $now,
//     ]);
// }

// function sendPushNotification($firebaseId, $title, $body)
// {
//     try {

//         //send to all users if firebaseId is null
//         if($firebaseId == null || $firebaseId == '') {
//             $userTokens = \App\Models\User::whereNotNull('firebase_id')->pluck('firebase_id')->toArray();
//             $firebaseId = implode(',', $userTokens);
//         }

//         // Initialize Firebase SDK once
//         $serviceAccountPath = base_path('app/showmei-firebase-adminsdk-fbsvc-dfc173a0dd.json');
//         $factory = (new Factory)->withServiceAccount($serviceAccountPath);
//         $messaging = $factory->createMessaging();

//         // Parse tokens (supports both single and multiple comma-separated IDs)
//         $tokens = array_filter(array_map('trim', explode(',', $firebaseId)));

//         if (empty($tokens)) {
//             return response()->json([
//                 'status' => 'Error',
//                 'message' => 'No valid Firebase tokens provided'
//             ], 400);
//         }

//         // Create notification
//         $notification = Notification::create($title, $body);

//         // Build messages for all tokens
//         $messages = array_map(function ($token) use ($notification) {
//             return CloudMessage::withTarget('token', $token)
//                 ->withNotification($notification)
//                 ->withData([
//                     'key1' => 'value1',
//                     'key2' => 'value2',
//                 ])
//                 ->withAndroidConfig([
//                     'priority' => 'high',
//                     'ttl' => '3600s',
//                 ]);
//         }, $tokens);

//         // Send notifications
//         $response = $messaging->sendAll($messages);

//         // Return detailed response
//         return response()->json([
//             'status' => 'Success',
//             'successCount' => $response->successes()->count(),
//             'failureCount' => $response->failures()->count(),
//             'response' => $response
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => 'Error',
//             'message' => $e->getMessage()
//         ], 500);
//     }
// }

function getForexPrice($base, $quote)
{
    $base = strtoupper($base);
    $quote = strtoupper($quote);

    // Prefer free fiat API (Open Exchange Rates API - no key required)
    $url = 'https://open.er-api.com/v6/latest/' . urlencode($quote);

    $context = stream_context_create([
        'http' => ['timeout' => 15],
    ]);

    $response = @file_get_contents($url, false, $context);
    if (!$response) {
        throw new \Exception('Unable to fetch exchange rate from API');
    }

    $data = json_decode($response, true);
    if (empty($data['rates'][$base])) {
        throw new \Exception("Rate for {$base} not found in API response");
    }

    return round((float) $data['rates'][$base], 6);
}

/**
 * Convert amount from one currency to another using stored USD-based exchange rates.
 * Falls back to a fiat API if stored rates are unavailable.
 *
 * @param float $amount - Amount in source currency
 * @param string $fromCurrency - Source currency symbol (e.g., 'USD')
 * @param string $toCurrency - Target currency symbol (e.g., 'NGN')
 * @return float - Converted amount
 * @throws \RuntimeException When a conversion rate cannot be determined.
 */
function convertCurrency($amount, $fromCurrency, $toCurrency)
{
    $from = strtoupper($fromCurrency);
    $to = strtoupper($toCurrency);

    // If same currency, no conversion needed
    if ($from === $to) {
        return $amount;
    }

    $rate = getStoredExchangeRate($from, $to);

    if ($rate === null) {
        // Fallback to live API for direct rate
        $rate = getForexPrice($to, $from);
    }

    if ($rate === null || $rate <= 0) {
        \Log::error('Currency conversion failed', [
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
        ]);
        throw new \RuntimeException('Could not determine exchange rate from ' . $from . ' to ' . $to);
    }

    return round($amount * $rate, 2);
}

/**
 * Retrieve cross exchange rate from stored USD-based currency rates.
 * Returns null if either currency is not found.
 *
 * @param string $from
 * @param string $to
 * @return float|null
 */
function getStoredExchangeRate($from, $to)
{
    $from = strtoupper($from);
    $to = strtoupper($to);

    if ($from === $to) {
        return 1.0;
    }

    $fromCurrency = \App\Models\Currency::where('symbol', $from)->orWhere('symbol2', $from)->first();
    $toCurrency = \App\Models\Currency::where('symbol', $to)->orWhere('symbol2', $to)->first();

    if (!$fromCurrency || !$toCurrency) {
        return null;
    }

    $fromRate = (float) ($fromCurrency->is_base_currency ? 1.0 : $fromCurrency->exchange_rate);
    $toRate = (float) ($toCurrency->is_base_currency ? 1.0 : $toCurrency->exchange_rate);

    if ($fromRate <= 0 || $toRate <= 0) {
        return null;
    }

    return round($toRate / $fromRate, 6);
}

if (!function_exists('fulfillWalletTopup')) {
    /**
     * Atomically fulfill a wallet top-up.
     * Uses a status-guarded update so the wallet is credited at most once,
     * even if the callback and webhook run concurrently.
     *
     * @param \App\Models\WalletHistory $history
     * @return bool True if the top-up was fulfilled by this call.
     */
    function fulfillWalletTopup(\App\Models\WalletHistory $history): bool
    {
        $pending = \App\Models\WalletHistoryStatus::PENDING;
        $success = \App\Models\WalletHistoryStatus::SUCCESS;

        // Only act on pending histories
        if ($history->wallet_history_status_id != $pending) {
            return $history->wallet_history_status_id == $success;
        }

        $affected = \App\Models\WalletHistory::where('id', $history->id)
            ->where('wallet_history_status_id', $pending)
            ->update(['wallet_history_status_id' => $success]);

        if ($affected > 0) {
            $creditAmount = $history->amount_usd ?? $history->amount;
            $history->wallet->increment('balance', $creditAmount);
            return true;
        }

        // Another process already fulfilled it
        return false;
    }
}

if (!function_exists('verifyFlutterwavePayment')) {
    /**
     * Verify a Flutterwave transaction server-side.
     *
     * Performs the standard checks (API status, transaction status, tx_ref match)
     * plus amount/currency verification and an optional transaction_id guard.
     * Logs the raw Flutterwave response so unexpected behaviour can be audited.
     *
     * @param int $transaction_id Flutterwave transaction ID from the callback/webhook.
     * @param string $tx_ref The transaction reference we generated.
     * @param float $expected_amount The amount we expect to have been charged.
     * @param string $expected_currency The currency we expect (e.g. USD, NGN).
     * @param string|null $expected_transaction_id If the initiate response stored a Flutterwave transaction ID, it must match.
     * @return array ['valid' => bool, 'data' => array|null, 'error' => string]
     */
    function verifyFlutterwavePayment(
        int $transaction_id,
        string $tx_ref,
        float $expected_amount,
        string $expected_currency,
        ?string $expected_transaction_id = null
    ): array {
        $flutterwave = new \App\Services\FlutterwaveService();
        $verification = $flutterwave->verifyTransaction($transaction_id);
        $data = $verification['data'] ?? null;

        Log::info('Flutterwave transaction verification', [
            'transaction_id' => $transaction_id,
            'tx_ref' => $tx_ref,
            'expected_amount' => $expected_amount,
            'expected_currency' => $expected_currency,
            'expected_transaction_id' => $expected_transaction_id,
            'response_status' => $verification['status'] ?? null,
            'data_status' => $data['status'] ?? null,
            'data_tx_ref' => $data['tx_ref'] ?? null,
            'data_amount' => $data['amount'] ?? null,
            'data_charged_amount' => $data['charged_amount'] ?? null,
            'data_currency' => $data['currency'] ?? null,
        ]);

        if (($verification['status'] ?? '') !== 'success') {
            return ['valid' => false, 'data' => $data, 'error' => 'Flutterwave verification API call failed.'];
        }

        if (!$data) {
            return ['valid' => false, 'data' => null, 'error' => 'No transaction data returned by Flutterwave.'];
        }

        $dataStatus = strtolower($data['status'] ?? '');
        if (!in_array($dataStatus, ['successful', 'completed'], true)) {
            return ['valid' => false, 'data' => $data, 'error' => 'Flutterwave transaction status is not successful.'];
        }

        if (($data['tx_ref'] ?? '') !== $tx_ref) {
            return ['valid' => false, 'data' => $data, 'error' => 'Transaction reference mismatch.'];
        }

        // If we stored a transaction ID from the initiate response, it must match the one in the callback.
        if ($expected_transaction_id !== null && (string) $transaction_id !== (string) $expected_transaction_id) {
            return ['valid' => false, 'data' => $data, 'error' => 'Transaction ID mismatch.'];
        }

        $chargedAmount = (float) ($data['charged_amount'] ?? $data['amount'] ?? 0);
        $currency = strtoupper($data['currency'] ?? '');

        if ($currency !== strtoupper($expected_currency)) {
            return ['valid' => false, 'data' => $data, 'error' => "Transaction currency mismatch. Expected {$expected_currency}, got {$currency}."];
        }

        // Allow tiny rounding differences (up to 1 unit of the smallest currency unit).
        if (abs($chargedAmount - $expected_amount) > 0.01) {
            return ['valid' => false, 'data' => $data, 'error' => "Transaction amount mismatch. Expected {$expected_amount} {$expected_currency}, Flutterwave charged {$chargedAmount} {$currency}."];
        }

        return ['valid' => true, 'data' => $data, 'error' => ''];
    }
}

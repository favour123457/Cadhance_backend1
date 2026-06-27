<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\WithdrawalHistoryResource;
use App\Mail\GeneralMail;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\Currency;
use App\Models\MobileMoneyAccount;
use App\Models\WalletHistory;
use App\Models\WalletHistoryStatus;
use App\Models\WalletHistoryType;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\FlutterwaveService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WithdrawalsController extends Controller
{
    protected FlutterwaveService $flutterwave;

    public function __construct(FlutterwaveService $flutterwave)
    {
        $this->flutterwave = $flutterwave;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount'                 => 'required|numeric|min:1',
            'payment_method'         => 'sometimes|string|in:bank_transfer,mobile_money',
            'reason'                 => 'sometimes|string|max:255',
            'bank_id'                => 'required_if:payment_method,bank_transfer|integer',
            'mobile_money_account_id'=> 'required_if:payment_method,mobile_money|integer',
        ]);

        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $amount         = (float) $validated['amount'];
        $payment_method = $validated['payment_method'] ?? 'bank_transfer'; // 'bank_transfer' or 'mobile_money'
        
        $wallet = $user->wallet;
        $reason = $validated['reason'] ?? 'Withdrawal';

        // Check if balance is enough
        if ($wallet->balance < $amount) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Insufficient balance!',
            ], 403);
        }

        $beneficiary_account = null;
        $currency = null;
        $withdrawal_status_id = Withdrawal::PROCESSING; // Start as "processing" for auto withdrawal

        DB::beginTransaction();

        try {
            if ($payment_method === 'mobile_money') {
                // Mobile Money withdrawal
                $mobile_money_account_id = $request->mobile_money_account_id;
                $mobile_money = MobileMoneyAccount::where('user_id', $user->id)
                    ->where('id', $mobile_money_account_id)
                    ->first();

                if (!$mobile_money) {
                    return response()->json([
                        'status'  => 'failed',
                        'message' => 'Invalid mobile money account selected!',
                    ], 403);
                }

                $networkCode = $mobile_money->network_code ?? $mobile_money->provider;
                if (empty($networkCode)) {
                    return response()->json([
                        'status'  => 'failed',
                        'message' => 'Mobile money network code is missing. Please update your account.',
                    ], 422);
                }

                $beneficiary_account = $mobile_money;
                $currency = $mobile_money->currency_id ? Currency::find($mobile_money->currency_id) : null;

                // Deduct wallet balance (in USD)
                $wallet->decrement('balance', $amount);

                // Convert amount from USD to target currency
                $targetCurrency = $currency ? $currency->symbol : 'NGN';
                $convertedAmount = convertCurrency($amount, 'USD', $targetCurrency);

                // Attempt automatic Flutterwave transfer for mobile money
                $reference = 'WD_MM_' . $user->id . '_' . time() . '_' . rand(1000, 9999);

                $transferMeta = $this->buildMobileMoneyTransferMeta($mobile_money, $user);

                $transferResp = $this->flutterwave->initiateMobileMoneyTransfer(
                    $mobile_money->account_number, // phone number
                    $networkCode, // network code
                    (float) $convertedAmount,
                    $targetCurrency,
                    $reason,
                    $reference,
                    $transferMeta
                );

                // Determine status from Flutterwave response
                $withdrawal_status_id = Withdrawal::PENDING; // Pending (manual processing fallback)
                $failure_reason = null;
                if (isset($transferResp['status']) && $transferResp['status'] === 'success') {
                    $dataStatus = $transferResp['data']['status'] ?? null;
                    if ($dataStatus === 'FAILED') {
                        $withdrawal_status_id = Withdrawal::FAILED;
                        $failure_reason = $transferResp['data']['complete_message'] ?? 'Transfer failed';
                    } elseif ($dataStatus === 'SUCCESS') {
                        $withdrawal_status_id = Withdrawal::COMPLETED;
                    } else {
                        $withdrawal_status_id = Withdrawal::PROCESSING;
                    }
                } else {
                    $failure_reason = $transferResp['message'] ?? 'Flutterwave API error';
                    Log::error('Mobile Money withdrawal failed', [
                        'user_id' => $user->id,
                        'reference' => $reference,
                        'response' => $transferResp
                    ]);
                }

                // Add conversion details to response
                $transferResp['conversion'] = [
                    'original_amount' => $amount,
                    'original_currency' => 'USD',
                    'converted_amount' => $convertedAmount,
                    'converted_currency' => $targetCurrency,
                ];

                // Refund wallet on hard Flutterwave failure
                if ($withdrawal_status_id == Withdrawal::FAILED) {
                    $wallet->increment('balance', $amount);
                }

                // Create withdrawal record
                $withdrawal = Withdrawal::create([
                    'user_id'                  => $user->id,
                    'mobile_money_account_id'  => $beneficiary_account->id,
                    'payment_method'           => $payment_method,
                    'reason'                   => $reason,
                    'amount'                   => $amount,
                    'currency_id'              => $currency?->id,
                    'withdrawal_status_id'     => $withdrawal_status_id,
                    'flutterwave_reference'    => $reference,
                    'flutterwave_response'     => json_encode($transferResp),
                    'failure_reason'           => $failure_reason,
                    'auto_processed'           => true,
                    'processed_at'             => $withdrawal_status_id == Withdrawal::COMPLETED ? now() : null,
                ]);

            } else {
                // Bank Transfer withdrawal
                $bank_id = $request->bank_id;
                
                $bank_account = BankAccount::where('user_id', $user->id)
                    ->where('id', $bank_id)
                    ->where('is_deleted', 0)
                    ->first();

                if (!$bank_account) {
                    return response()->json([
                        'status'  => 'failed',
                        'message' => 'Invalid bank account selected!',
                    ], 403);
                }

                $beneficiary_account = $bank_account;
                $currency = $bank_account->currency_id ? Currency::find($bank_account->currency_id) : null;

                // Get bank code - try from Bank model first, fallback to stored bank_code or bank_name
                $bank_code = null;
                if ($bank_account->bank_id) {
                    $bank = Bank::find($bank_account->bank_id);
                    $bank_code = $bank?->code;
                }
                if (!$bank_code) {
                    $bank_code = $bank_account->bank_code ?? $bank_account->bank_name;
                }

                // Deduct wallet balance (in USD)
                $wallet->decrement('balance', $amount);

                // Convert amount from USD to target currency
                $targetCurrency = $currency ? $currency->symbol : 'NGN';
                $convertedAmount = convertCurrency($amount, 'USD', $targetCurrency);

                // Attempt automatic Flutterwave transfer for bank transfer
                $reference = 'WD_BT_' . $user->id . '_' . time() . '_' . rand(1000, 9999);

                $transferMeta = $this->buildBankTransferMeta($bank_account, $user);

                $transferResp = $this->flutterwave->initiateTransfer(
                    $bank_account->account_number,
                    $bank_code,
                    $bank_account->account_name,
                    (float) $convertedAmount,
                    $targetCurrency,
                    $reason,
                    $reference,
                    $bank_account->destination_branch_code, // For Ghana, Kenya
                    $transferMeta
                );

                // Determine status from Flutterwave response
                $withdrawal_status_id = Withdrawal::PENDING; // Pending (manual processing fallback)
                $failure_reason = null;
                if (isset($transferResp['status']) && $transferResp['status'] === 'success') {
                    $dataStatus = $transferResp['data']['status'] ?? null;
                    if ($dataStatus === 'FAILED') {
                        $withdrawal_status_id = Withdrawal::FAILED;
                        $failure_reason = $transferResp['data']['complete_message'] ?? 'Transfer failed';
                    } elseif ($dataStatus === 'SUCCESS') {
                        $withdrawal_status_id = Withdrawal::COMPLETED;
                    } else {
                        $withdrawal_status_id = Withdrawal::PROCESSING;
                    }
                } else {
                    $failure_reason = $transferResp['message'] ?? 'Flutterwave API error';
                    Log::error('Bank transfer withdrawal failed', [
                        'user_id' => $user->id,
                        'reference' => $reference,
                        'response' => $transferResp
                    ]);
                }

                // Add conversion details to response
                $transferResp['conversion'] = [
                    'original_amount' => $amount,
                    'original_currency' => 'USD',
                    'converted_amount' => $convertedAmount,
                    'converted_currency' => $targetCurrency,
                ];

                // Refund wallet on hard Flutterwave failure
                if ($withdrawal_status_id == Withdrawal::FAILED) {
                    $wallet->increment('balance', $amount);
                }

                // Create withdrawal record
                $withdrawal = Withdrawal::create([
                    'user_id'                  => $user->id,
                    'bank_account_id'          => $beneficiary_account->id,
                    'payment_method'           => $payment_method,
                    'reason'                   => $reason,
                    'amount'                   => $amount,
                    'currency_id'              => $currency?->id,
                    'withdrawal_status_id'     => $withdrawal_status_id,
                    'flutterwave_reference'    => $reference,
                    'flutterwave_response'     => json_encode($transferResp),
                    'failure_reason'           => $failure_reason,
                    'auto_processed'           => true,
                    'processed_at'             => $withdrawal_status_id == Withdrawal::COMPLETED ? now() : null,
                ]);
            }

            // Create wallet history
            $walletHistoryStatusId = match ($withdrawal_status_id) {
                Withdrawal::COMPLETED => WalletHistoryStatus::SUCCESS,
                Withdrawal::FAILED    => WalletHistoryStatus::FAILED,
                default               => WalletHistoryStatus::PENDING,
            };

            WalletHistory::create([
                'wallet_id'                => $wallet->id,
                'amount'                   => $amount,
                'wallet_history_type_id'   => WalletHistoryType::DEBIT,
                'wallet_history_status_id' => $walletHistoryStatusId,
            ]);

            DB::commit();

            // Send email notification
            $statusText = match ($withdrawal_status_id) {
                Withdrawal::COMPLETED  => 'Completed',
                Withdrawal::FAILED     => 'Failed',
                Withdrawal::PROCESSING => 'Processing',
                default                => 'Pending',
            };
            $accountInfo = $payment_method === 'mobile_money' 
                ? '<li><strong>Provider:</strong> ' . $beneficiary_account->provider . '</li>
                   <li><strong>Phone:</strong> ' . $beneficiary_account->account_number . '</li>'
                : '<li><strong>Bank:</strong> ' . $beneficiary_account->bank_name . '</li>
                   <li><strong>Account:</strong> ' . $beneficiary_account->account_number . '</li>';

            $msg = '
        <p>We have received your withdrawal request of <strong>' . showMoney($amount) . '</strong>.</p>
        <p><strong>Transaction Details:</strong></p>
        <ul>
            <li><strong>Amount:</strong> ' . showMoney($amount) . '</li>
            <li><strong>Payment Method:</strong> ' . ucwords(str_replace('_', ' ', $payment_method)) . '</li>
            ' . $accountInfo . '
            <li><strong>Date:</strong> ' . now()->format('d M, Y h:i A') . '</li>
            <li><strong>Status:</strong> ' . $statusText . '</li>
            <li><strong>Reference:</strong> ' . $reference . '</li>
        </ul>
        ' . ($withdrawal_status_id == Withdrawal::COMPLETED 
            ? '<p>The funds should reflect in your account shortly, depending on processing time.</p>' 
            : '<p>Your withdrawal is being processed. You will receive notification once completed.</p>') . '
        <p>If you did not initiate this withdrawal, contact our support team immediately.</p>
        <p>Thank you for using ' . config('app.name') . '.</p>
        <p>Best regards,<br>The ' . config('app.name') . ' Team</p>
    ';

            // Only send email if user has valid email address
            if ($user->email && filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                Mail::send(new GeneralMail($user->name, $user->email, 'Withdrawal Request Received', $msg));
            }

            // If pending (manual processing fallback), notify admin via email
            if ($withdrawal_status_id == Withdrawal::PENDING && config('app.admin_email') && filter_var(config('app.admin_email'), FILTER_VALIDATE_EMAIL)) {
                $adminMsg = '
            <p>This is to notify you that an <strong>automatic withdrawal</strong> failed and requires manual processing.</p>
            <p><strong>Failure Details:</strong></p>
            <ul>
                <li><strong>User:</strong> ' . ($user->first_name ?? '') . ' ' . ($user->last_name ?? '') . ' (' . $user->email . ')</li>
                <li><strong>Amount:</strong> ' . showMoney($amount) . '</li>
                <li><strong>Payment Method:</strong> ' . ucwords(str_replace('_', ' ', $payment_method)) . '</li>
                <li><strong>Reference:</strong> ' . $reference . '</li>
                <li><strong>Reason:</strong> ' . ($failure_reason ?? 'No specific reason provided') . '</li>
                <li><strong>Date:</strong> ' . now()->format('d M, Y h:i A') . '</li>
            </ul>
            <p>Please process this withdrawal manually via the admin panel.</p>
            <p>Regards,<br><strong>' . config('app.name') . ' System</strong></p>
        ';
                Mail::send(new GeneralMail('Admin', config('app.admin_email'), 'Manual Withdrawal Required', $adminMsg));
            }

            return response()->json([
                'status'     => 'success',
                'message'    => $withdrawal_status_id == Withdrawal::COMPLETED 
                    ? 'Withdrawal processed successfully!' 
                    : 'Withdrawal request submitted and is being processed!',
                'withdrawal' => new WithdrawalHistoryResource($withdrawal),
            ]);

        } catch (\RuntimeException $e) {
            DB::rollBack();
            Log::error('Withdrawal currency conversion failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status'  => 'failed',
                'message' => 'Currency conversion failed. Please try again later or contact support.',
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Withdrawal error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status'  => 'failed',
                'message' => 'An error occurred while processing your withdrawal. Please try again later.',
            ], 500);
        }
    }

    public function histories()
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $withdrawals = $user->withdrawals;

        return response()->json(WithdrawalHistoryResource::collection($withdrawals));
    }

    public function verifyReceiver(Request $request)
    {
        $account_number = $request->account_number;
        $bank_id        = $request->bank_id;

        $bank = Bank::find($bank_id);

        if (!$bank) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Invalid bank',
            ], 403);
        }

        $data = $this->flutterwave->resolveAccount($account_number, $bank->code);

        if (!isset($data['status']) || $data['status'] !== 'success') {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Invalid account number or bank!',
            ], 403);
        }

        return response()->json([
            'status'       => 'success',
            'account_name' => $data['data']['account_name'],
        ]);
    }

    /**
     * Build Flutterwave transfer meta payload for bank withdrawals.
     * Different countries/regulators require different beneficiary details.
     */
    private function buildBankTransferMeta(BankAccount $bankAccount, $user): array
    {
        $country = $user->country?->name;
        $meta = [];

        // Common fields
        if ($bankAccount->recipient_email) {
            $meta['recipient_email'] = $bankAccount->recipient_email;
        }
        if ($bankAccount->recipient_address) {
            $meta['recipient_address'] = $bankAccount->recipient_address;
        }
        if ($bankAccount->recipient_phone) {
            $meta['recipient_phone'] = $bankAccount->recipient_phone;
        }
        if ($bankAccount->bank_branch) {
            $meta['bank_branch'] = $bankAccount->bank_branch;
        }

        switch ($country) {
            case 'United States':
            case 'USA':
                if ($bankAccount->account_type) {
                    $meta['account_type'] = $bankAccount->account_type;
                }
                if ($bankAccount->routing_number) {
                    $meta['routing_number'] = $bankAccount->routing_number;
                }
                if ($bankAccount->swift_code) {
                    $meta['swift_code'] = $bankAccount->swift_code;
                }
                if ($bankAccount->postal_code) {
                    $meta['postal_code'] = $bankAccount->postal_code;
                }
                if ($bankAccount->recipient_city) {
                    $meta['recipient_city'] = $bankAccount->recipient_city;
                }
                if ($bankAccount->recipient_country) {
                    $meta['recipient_country'] = $bankAccount->recipient_country;
                }
                break;

            case 'India':
                if ($bankAccount->recipient_address) {
                    $meta['recipient_address'] = $bankAccount->recipient_address;
                }
                if ($bankAccount->recipient_phone) {
                    $meta['mobile_number'] = $bankAccount->recipient_phone;
                }
                if ($bankAccount->sender_id_type) {
                    $meta['sender_id_type'] = $bankAccount->sender_id_type;
                }
                if ($bankAccount->sender_id_number) {
                    $meta['sender_id_number'] = $bankAccount->sender_id_number;
                }
                if ($bankAccount->transfer_purpose_code) {
                    $meta['transfer_purpose_code'] = $bankAccount->transfer_purpose_code;
                }
                break;

            case 'South Africa':
                if ($bankAccount->recipient_email) {
                    $meta['recipient_email'] = $bankAccount->recipient_email;
                }
                if ($bankAccount->recipient_phone) {
                    $meta['mobile_number'] = $bankAccount->recipient_phone;
                }
                if ($bankAccount->recipient_address) {
                    $meta['recipient_address'] = $bankAccount->recipient_address;
                }
                break;

            case 'Cameroun':
            case 'Cameroon':
            case 'Cote D\'Ivoire':
            case 'Côte d\'Ivoire':
                if ($bankAccount->beneficiary_country) {
                    $meta['beneficiary_country'] = $bankAccount->beneficiary_country;
                }
                break;
        }

        return $meta;
    }

    /**
     * Build Flutterwave transfer meta payload for mobile money withdrawals.
     */
    private function buildMobileMoneyTransferMeta(MobileMoneyAccount $mobileMoney, $user): array
    {
        $country = $user->country?->name;
        $meta = [];

        $requiresAddress = in_array($country, ["Cote D'Ivoire", "Côte d'Ivoire", 'Senegal'], true);

        if ($requiresAddress) {
            if ($mobileMoney->recipient_address) {
                $meta['recipient_address'] = $mobileMoney->recipient_address;
            }
            if ($mobileMoney->recipient_email) {
                $meta['recipient_email'] = $mobileMoney->recipient_email;
            }
            if ($mobileMoney->recipient_country) {
                $meta['recipient_country'] = $mobileMoney->recipient_country;
            }
        }

        return $meta;
    }
}

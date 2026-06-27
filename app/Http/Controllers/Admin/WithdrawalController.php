<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $status_filter = $request->get('status', 'pending'); // pending, processing, completed, failed, all
        
        // Get pending withdrawals (status_id = 1)
        $pendingQuery = Withdrawal::with(['user.country', 'bankAccount', 'mobileMoneyAccount', 'currency'])
            ->where('withdrawal_status_id', 1)
            ->orderBy('created_at', 'desc');
        
        $bankWithdrawals = (clone $pendingQuery)->where('payment_method', 'bank_transfer')->get();
        $mobileMoneyWithdrawals = (clone $pendingQuery)->where('payment_method', 'mobile_money')->get();

        // Get processing withdrawals (status_id = 2)
        $processingWithdrawals = Withdrawal::with(['user.country', 'bankAccount', 'mobileMoneyAccount', 'currency'])
            ->where('withdrawal_status_id', 2)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get completed withdrawals (status_id = 3)
        $completedWithdrawals = Withdrawal::with(['user', 'bankAccount', 'mobileMoneyAccount', 'currency'])
            ->where('withdrawal_status_id', 3)
            ->orderBy('processed_at', 'desc')
            ->limit(100)
            ->get();

        // Get failed withdrawals (status_id = 4)
        $failedWithdrawals = Withdrawal::with(['user', 'bankAccount', 'mobileMoneyAccount', 'currency'])
            ->where('withdrawal_status_id', 4)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        // Get unique countries for filtering
        $bankCountries = $bankWithdrawals->pluck('user.country.name')->unique()->filter()->sort()->values();
        $mobileMoneyCountries = $mobileMoneyWithdrawals->pluck('user.country.name')->unique()->filter()->sort()->values();

        return view('admin.withdrawals.index', compact(
            'bankWithdrawals',
            'mobileMoneyWithdrawals',
            'processingWithdrawals',
            'completedWithdrawals',
            'failedWithdrawals',
            'bankCountries',
            'mobileMoneyCountries',
            'status_filter'
        ));
    }

    public function exportBankCSV(Request $request)
    {
        $country = $request->get('country', 'Nigeria');
        $withdrawals = Withdrawal::with(['user.country', 'bankAccount'])
            ->where('payment_method', 'bank_transfer')
            ->whereNull('processed_at')
            ->whereHas('user.country', function($q) use ($country) {
                $q->where('name', $country);
            })
            ->get();

        if ($withdrawals->isEmpty()) {
            return back()->with('error', "No pending bank withdrawals found for {$country}");
        }

        $csvData = $this->generateBankCSV($country, $withdrawals);
        $filename = 'bank_withdrawals_' . strtolower(str_replace(' ', '_', $country)) . '_' . date('Y-m-d_His') . '.csv';

        return Response::make($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    public function exportMobileMoneyCSV(Request $request)
    {
        $country = $request->get('country', 'Nigeria');
        $withdrawals = Withdrawal::with(['user.country', 'mobileMoneyAccount'])
            ->where('payment_method', 'mobile_money')
            ->whereNull('processed_at')
            ->whereHas('user.country', function($q) use ($country) {
                $q->where('name', $country);
            })
            ->get();

        if ($withdrawals->isEmpty()) {
            return back()->with('error', "No pending mobile money withdrawals found for {$country}");
        }

        $csvData = $this->generateMobileMoneyCSV($country, $withdrawals);
        $filename = 'mobilemoney_withdrawals_' . strtolower(str_replace(' ', '_', $country)) . '_' . date('Y-m-d_His') . '.csv';

        return Response::make($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    public function markProcessed(Request $request)
    {
        $ids = $request->get('ids', []);
        $status = $request->get('status', 3); // 3 = completed, 4 = failed
        
        if (empty($ids)) {
            return back()->with('error', 'No withdrawals selected');
        }

        $updated = Withdrawal::whereIn('id', $ids)
            ->update([
                'processed_at' => now(),
                'processed_by' => auth()->id(),
                'withdrawal_status_id' => $status,
            ]);

        $message = $status == 3 ? "{$updated} withdrawals marked as completed" : "{$updated} withdrawals marked as failed";
        return back()->with('success', $message);
    }

    /**
     * Return a list of required beneficiary fields that are empty for a withdrawal.
     */
    public static function missingRequiredFields(Withdrawal $withdrawal): array
    {
        $country = $withdrawal->user->country?->name;
        $missing = [];

        if ($withdrawal->payment_method === 'bank_transfer') {
            $account = $withdrawal->bankAccount;
            if (!$account) {
                return ['Bank account missing'];
            }

            $required = match (true) {
                in_array($country, ['United States', 'USA'], true) => [
                    'account_type' => 'Account type',
                    'routing_number' => 'Routing number',
                    'recipient_email' => 'Recipient email',
                    'recipient_address' => 'Recipient address',
                    'recipient_country' => 'Recipient country',
                    'recipient_city' => 'Recipient city',
                    'postal_code' => 'Postal code',
                ],
                $country === 'India' => [
                    'bank_branch' => 'Bank branch',
                    'recipient_address' => 'Recipient address',
                    'recipient_phone' => 'Mobile number',
                    'recipient_email' => 'Email address',
                    'sender_id_type' => 'Sender identification type',
                    'sender_id_number' => 'Sender identification number',
                    'transfer_purpose_code' => 'Transfer purpose code',
                ],
                $country === 'South Africa' => [
                    'recipient_email' => 'Recipient email',
                    'recipient_phone' => 'Mobile number',
                    'recipient_address' => 'Recipient address',
                ],
                in_array($country, ['Cameroun', 'Cameroon', "Cote D'Ivoire", "Côte d'Ivoire"], true) => [
                    'beneficiary_country' => 'Beneficiary country',
                    'bank_branch' => 'Bank branch',
                ],
                in_array($country, ['Ghana', 'Rwanda', 'Tanzania', 'Uganda'], true) => [
                    'bank_branch' => 'Bank branch',
                ],
                default => [],
            };

            foreach ($required as $field => $label) {
                if (empty($account->{$field})) {
                    $missing[] = $label;
                }
            }
        } else {
            $account = $withdrawal->mobileMoneyAccount;
            if (!$account) {
                return ['Mobile money account missing'];
            }

            if (in_array($country, ["Cote D'Ivoire", "Côte d'Ivoire", 'Senegal'], true)) {
                foreach ([
                    'recipient_address' => 'Recipient address',
                    'recipient_email' => 'Recipient email',
                    'recipient_country' => 'Recipient country',
                ] as $field => $label) {
                    if (empty($account->{$field})) {
                        $missing[] = $label;
                    }
                }
            }
        }

        return $missing;
    }

    private function recipientName($withdrawal): string
    {
        $account = $withdrawal->bankAccount ?? $withdrawal->mobileMoneyAccount;
        if ($account && !empty($account->account_name)) {
            return $account->account_name;
        }
        return trim(($withdrawal->user->first_name ?? '') . ' ' . ($withdrawal->user->last_name ?? ''));
    }

    private function generateBankCSV(string $country, $withdrawals): string
    {
        $handle = fopen('php://temp', 'r+');

        switch ($country) {
            case 'Nigeria':
                fputcsv($handle, ['Account number', 'Bank', 'Amount', 'Description']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->bankAccount->account_number ?? '',
                        $w->bankAccount->bank_code ?? $w->bankAccount->bank_name ?? '',
                        $w->amount,
                        $w->reason ?: 'Withdrawal',
                    ]);
                }
                break;

            case 'Cameroun':
            case 'Cameroon':
            case 'Cote D\'Ivoire':
            case 'Côte d\'Ivoire':
                fputcsv($handle, ['Account number', 'Bank', 'Amount', 'Debit Currency Amount', 'Narration', 'Recipient name', 'Beneficiary country', 'Bank branch']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->bankAccount->account_number ?? '',
                        $w->bankAccount->bank_name ?? '',
                        $w->amount,
                        $w->amount,
                        $w->reason ?: 'Withdrawal',
                        $this->recipientName($w),
                        $w->bankAccount->beneficiary_country ?? $w->user->country->name ?? '',
                        $w->bankAccount->bank_branch ?? '',
                    ]);
                }
                break;

            case 'Ghana':
                fputcsv($handle, ['Account number', 'Bank', 'Amount', 'Debit Currency Amount', 'Description', 'Recipient name', 'Bank branch']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->bankAccount->account_number ?? '',
                        $w->bankAccount->bank_name ?? '',
                        $w->amount,
                        $w->amount,
                        $w->reason ?: 'Withdrawal',
                        $this->recipientName($w),
                        $w->bankAccount->bank_branch ?? '',
                    ]);
                }
                break;

            case 'India':
                fputcsv($handle, ['Account number', 'Bank', 'Bank branch', 'Amount', 'Debit Currency Amount', 'Narration', 'Recipient name', 'Recipient address', 'Mobile Number', 'Email address', 'Sender identification type', 'Sender identification number', 'Transfer Purpose code']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->bankAccount->account_number ?? '',
                        $w->bankAccount->bank_name ?? '',
                        $w->bankAccount->bank_branch ?? '',
                        $w->amount,
                        $w->amount,
                        $w->reason ?: 'Withdrawal',
                        $this->recipientName($w),
                        $w->bankAccount->recipient_address ?? '',
                        $w->bankAccount->recipient_phone ?? $w->user->phone ?? '',
                        $w->bankAccount->recipient_email ?? $w->user->email ?? '',
                        $w->bankAccount->sender_id_type ?? '',
                        $w->bankAccount->sender_id_number ?? '',
                        $w->bankAccount->transfer_purpose_code ?? '',
                    ]);
                }
                break;

            case 'Kenya':
                fputcsv($handle, ['Account number', 'Bank', 'Amount', 'Debit Currency Amount', 'Description', 'Recipient name']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->bankAccount->account_number ?? '',
                        $w->bankAccount->bank_name ?? '',
                        $w->amount,
                        $w->amount,
                        $w->reason ?: 'Withdrawal',
                        $this->recipientName($w),
                    ]);
                }
                break;

            case 'Rwanda':
                fputcsv($handle, ['Account number', 'Bank', 'Amount', 'Debit Currency Amount', 'Description', 'Recipient name', 'Bank branch']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->bankAccount->account_number ?? '',
                        $w->bankAccount->bank_name ?? '',
                        $w->amount,
                        $w->amount,
                        $w->reason ?: 'Withdrawal',
                        $this->recipientName($w),
                        $w->bankAccount->bank_branch ?? '',
                    ]);
                }
                break;

            case 'South Africa':
                fputcsv($handle, ['Account number', 'Bank', 'Amount', 'Debit Currency Amount', 'Description', 'Recipient name', 'Recipient email', 'Mobile number', 'Recipient address']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->bankAccount->account_number ?? '',
                        $w->bankAccount->bank_name ?? '',
                        $w->amount,
                        $w->amount,
                        $w->reason ?: 'Withdrawal',
                        $this->recipientName($w),
                        $w->bankAccount->recipient_email ?? $w->user->email ?? '',
                        $w->bankAccount->recipient_phone ?? $w->user->phone ?? '',
                        $w->bankAccount->recipient_address ?? '',
                    ]);
                }
                break;

            case 'Tanzania':
            case 'Uganda':
                fputcsv($handle, ['Account number', 'Bank', 'Amount', 'Debit Currency Amount', 'Description', 'Recipient name', 'Bank branch']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->bankAccount->account_number ?? '',
                        $w->bankAccount->bank_name ?? '',
                        $w->amount,
                        $w->amount,
                        $w->reason ?: 'Withdrawal',
                        $this->recipientName($w),
                        $w->bankAccount->bank_branch ?? '',
                    ]);
                }
                break;

            case 'United States':
            case 'USA':
                fputcsv($handle, ['Account number', 'Bank', 'Amount', 'Debit Currency Amount', 'Narration', 'Recipient name', 'Recipient email address', 'Recipient address', 'Recipient country', 'Recipient city', 'Account type', 'Routing number', 'Swift code', 'Postal code']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->bankAccount->account_number ?? '',
                        $w->bankAccount->bank_name ?? '',
                        $w->amount,
                        $w->amount,
                        $w->reason ?: 'Withdrawal',
                        $this->recipientName($w),
                        $w->bankAccount->recipient_email ?? $w->user->email ?? '',
                        $w->bankAccount->recipient_address ?? '',
                        $w->bankAccount->recipient_country ?? $w->user->country->name ?? '',
                        $w->bankAccount->recipient_city ?? '',
                        $w->bankAccount->account_type ?? '',
                        $w->bankAccount->routing_number ?? '',
                        $w->bankAccount->swift_code ?? '',
                        $w->bankAccount->postal_code ?? '',
                    ]);
                }
                break;

            case 'Zambia':
                fputcsv($handle, ['Account number', 'Bank', 'Amount', 'Debit Currency Amount', 'Recipient name', 'Description']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->bankAccount->account_number ?? '',
                        $w->bankAccount->bank_name ?? '',
                        $w->amount,
                        $w->amount,
                        $this->recipientName($w),
                        $w->reason ?: 'Withdrawal',
                    ]);
                }
                break;

            default:
                fputcsv($handle, ['Account number', 'Bank', 'Amount', 'Recipient name', 'Description']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->bankAccount->account_number ?? '',
                        $w->bankAccount->bank_name ?? '',
                        $w->amount,
                        $this->recipientName($w),
                        $w->reason ?: 'Withdrawal',
                    ]);
                }
                break;
        }

        rewind($handle);
        $csvData = stream_get_contents($handle);
        fclose($handle);

        return $csvData;
    }

    private function generateMobileMoneyCSV(string $country, $withdrawals): string
    {
        $handle = fopen('php://temp', 'r+');

        switch ($country) {
            case 'Cote D\'Ivoire':
            case 'Côte d\'Ivoire':
            case 'Senegal':
                fputcsv($handle, ['Phone Number', 'Network', 'Amount', 'Debit Currency Amount', 'Narration', 'Recipient name', 'Recipient Address', 'Recipient Email Address', 'Recipient Country']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->mobileMoneyAccount->account_number ?? '',
                        $w->mobileMoneyAccount->network_code ?? $w->mobileMoneyAccount->provider ?? '',
                        $w->amount,
                        $w->amount,
                        $w->reason ?: 'Withdrawal',
                        $this->recipientName($w),
                        $w->mobileMoneyAccount->recipient_address ?? '',
                        $w->mobileMoneyAccount->recipient_email ?? $w->user->email ?? '',
                        $w->mobileMoneyAccount->recipient_country ?? $w->user->country->name ?? '',
                    ]);
                }
                break;

            case 'Cameroun':
            case 'Cameroon':
            case 'Chad':
            case 'Gabon':
            case 'Ghana':
            case 'Kenya':
            case 'Rwanda':
            case 'Tanzania':
            case 'Uganda':
            case 'Zambia':
            case 'Republic of Congo':
            case 'Congo':
                fputcsv($handle, ['Phone Number', 'Network', 'Amount', 'Debit Currency Amount', 'Narration', 'Recipient name']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->mobileMoneyAccount->account_number ?? '',
                        $w->mobileMoneyAccount->network_code ?? $w->mobileMoneyAccount->provider ?? '',
                        $w->amount,
                        $w->amount,
                        $w->reason ?: 'Withdrawal',
                        $this->recipientName($w),
                    ]);
                }
                break;

            default:
                fputcsv($handle, ['Phone Number', 'Network', 'Amount', 'Recipient name', 'Narration']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->mobileMoneyAccount->account_number ?? '',
                        $w->mobileMoneyAccount->network_code ?? $w->mobileMoneyAccount->provider ?? '',
                        $w->amount,
                        $this->recipientName($w),
                        $w->reason ?: 'Withdrawal',
                    ]);
                }
                break;
        }

        rewind($handle);
        $csvData = stream_get_contents($handle);
        fclose($handle);

        return $csvData;
    }
}

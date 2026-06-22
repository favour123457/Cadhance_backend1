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

    private function generateBankCSV(string $country, $withdrawals): string
    {
        $handle = fopen('php://temp', 'r+');

        switch ($country) {
            case 'Nigeria':
                // Header: Account number, Bank, Amount, Description
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

            case 'Ghana':
                // Header: Account number, Bank, Amount, Debit Currency Amount, Recipient name, Bank branch, Description
                fputcsv($handle, ['Account number', 'Bank', 'Amount', 'Debit Currency Amount', 'Recipient name', 'Bank branch', 'Description']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->bankAccount->account_number ?? '',
                        $w->bankAccount->bank_name ?? '',
                        $w->amount,
                        $w->amount, // Same as amount
                        $w->bankAccount->account_name ?? ($w->user->first_name . ' ' . $w->user->last_name),
                        '', // Bank branch (leave empty if not available)
                        $w->reason ?: 'Withdrawal',
                    ]);
                }
                break;

            case 'Kenya':
                // Header: Account number, Bank, Amount, Debit Currency Amount, Recipient name, Bank branch, Description
                fputcsv($handle, ['Account number', 'Bank', 'Amount', 'Debit Currency Amount', 'Recipient name', 'Bank branch', 'Description']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->bankAccount->account_number ?? '',
                        $w->bankAccount->bank_name ?? '',
                        $w->amount,
                        $w->amount,
                        $w->bankAccount->account_name ?? ($w->user->first_name . ' ' . $w->user->last_name),
                        '',
                        $w->reason ?: 'Withdrawal',
                    ]);
                }
                break;

            case 'Rwanda':
                // Header: Account number, Bank, Amount, Debit Currency Amount, Recipient name, Description
                fputcsv($handle, ['Account number', 'Bank', 'Amount', 'Debit Currency Amount', 'Recipient name', 'Description']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->bankAccount->account_number ?? '',
                        $w->bankAccount->bank_name ?? '',
                        $w->amount,
                        $w->amount,
                        $w->bankAccount->account_name ?? ($w->user->first_name . ' ' . $w->user->last_name),
                        $w->reason ?: 'Withdrawal',
                    ]);
                }
                break;

            case 'South Africa':
                // Header: Account number, Bank code, Amount, Debit Currency Amount, Recipient name, Description
                fputcsv($handle, ['Account number', 'Bank code', 'Amount', 'Debit Currency Amount', 'Recipient name', 'Description']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->bankAccount->account_number ?? '',
                        $w->bankAccount->bank_name ?? '',
                        $w->amount,
                        $w->amount,
                        $w->bankAccount->account_name ?? ($w->user->first_name . ' ' . $w->user->last_name),
                        $w->reason ?: 'Withdrawal',
                    ]);
                }
                break;

            case 'Tanzania':
                // Header: Account number, Bank, Amount, Debit Currency Amount, Recipient name, Description
                fputcsv($handle, ['Account number', 'Bank', 'Amount', 'Debit Currency Amount', 'Recipient name', 'Description']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->bankAccount->account_number ?? '',
                        $w->bankAccount->bank_name ?? '',
                        $w->amount,
                        $w->amount,
                        $w->bankAccount->account_name ?? ($w->user->first_name . ' ' . $w->user->last_name),
                        $w->reason ?: 'Withdrawal',
                    ]);
                }
                break;

            case 'Uganda':
                // Header: Account number, Bank, Amount, Debit Currency Amount, Recipient name, Description
                fputcsv($handle, ['Account number', 'Bank', 'Amount', 'Debit Currency Amount', 'Recipient name', 'Description']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->bankAccount->account_number ?? '',
                        $w->bankAccount->bank_name ?? '',
                        $w->amount,
                        $w->amount,
                        $w->bankAccount->account_name ?? ($w->user->first_name . ' ' . $w->user->last_name),
                        $w->reason ?: 'Withdrawal',
                    ]);
                }
                break;

            case 'United States':
            case 'USA':
                // Header: Account number, Routing number (ACH code), Amount, Description, Recipient name
                fputcsv($handle, ['Account number', 'Routing number (ACH code)', 'Amount', 'Description', 'Recipient name']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->bankAccount->account_number ?? '',
                        $w->bankAccount->bank_name ?? '', // ACH routing number
                        $w->amount,
                        $w->reason ?: 'Withdrawal',
                        $w->bankAccount->account_name ?? ($w->user->first_name . ' ' . $w->user->last_name),
                    ]);
                }
                break;

            case 'Cameroun':
            case 'Cameroon':
                // Header: Account number, Bank, Amount, Debit Currency Amount, Narration, Recipient name
                fputcsv($handle, ['Account number', 'Bank', 'Amount', 'Debit Currency Amount', 'Narration', 'Recipient name']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->bankAccount->account_number ?? '',
                        $w->bankAccount->bank_name ?? '',
                        $w->amount,
                        $w->amount,
                        $w->reason ?: 'Withdrawal',
                        $w->bankAccount->account_name ?? ($w->user->first_name . ' ' . $w->user->last_name),
                    ]);
                }
                break;

            case 'Zambia':
                // Header: Account number, Bank, Amount, Debit Currency Amount, Recipient name, Description
                fputcsv($handle, ['Account number', 'Bank', 'Amount', 'Debit Currency Amount', 'Recipient name', 'Description']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->bankAccount->account_number ?? '',
                        $w->bankAccount->bank_name ?? '',
                        $w->amount,
                        $w->amount,
                        $w->bankAccount->account_name ?? ($w->user->first_name . ' ' . $w->user->last_name),
                        $w->reason ?: 'Withdrawal',
                    ]);
                }
                break;

            default:
                // Generic format
                fputcsv($handle, ['Account number', 'Bank', 'Amount', 'Recipient name', 'Description']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->bankAccount->account_number ?? '',
                        $w->bankAccount->bank_name ?? '',
                        $w->amount,
                        $w->bankAccount->account_name ?? ($w->user->first_name . ' ' . $w->user->last_name),
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
            case 'Cameroun':
            case 'Cameroon':
            case 'Kenya':
            case 'Ghana':
            case 'Rwanda':
            case 'Tanzania':
            case 'Uganda':
            case 'Zambia':
            case 'Cote D\'Ivoire':
            case 'Côte d\'Ivoire':
            case 'Ivory Coast':
            case 'Chad':
            case 'Gabon':
            case 'Senegal':
            case 'Republic of Congo':
            case 'Congo':
                // Header: Phone Number, Network, Amount, Debit Currency Amount, Narration, Recipient name
                fputcsv($handle, ['Phone Number', 'Network', 'Amount', 'Debit Currency Amount', 'Narration', 'Recipient name']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->mobileMoneyAccount->account_number ?? '',
                        $w->mobileMoneyAccount->network_code ?? $w->mobileMoneyAccount->provider ?? '',
                        $w->amount,
                        $w->amount,
                        $w->reason ?: 'Withdrawal',
                        $w->mobileMoneyAccount->account_name ?? ($w->user->first_name . ' ' . $w->user->last_name),
                    ]);
                }
                break;

            default:
                // Generic format
                fputcsv($handle, ['Phone Number', 'Network', 'Amount', 'Recipient name', 'Narration']);
                foreach ($withdrawals as $w) {
                    fputcsv($handle, [
                        $w->mobileMoneyAccount->account_number ?? '',
                        $w->mobileMoneyAccount->network_code ?? $w->mobileMoneyAccount->provider ?? '',
                        $w->amount,
                        $w->mobileMoneyAccount->account_name ?? ($w->user->first_name . ' ' . $w->user->last_name),
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

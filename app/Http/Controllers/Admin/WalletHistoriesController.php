<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletHistory;
use Illuminate\Http\Request;

class WalletHistoriesController extends Controller
{
    public function index()
    {
        $walletHistories = WalletHistory::with(['wallet.user', 'wallet_history_type', 'wallet_history_status'])->latest()->get();
        return view('admin.wallet-histories.index', compact('walletHistories'));
    }

    public function show(WalletHistory $walletHistory)
    {
        $walletHistory->load(['wallet.user', 'wallet_history_type', 'wallet_history_status']);
        return view('admin.wallet-histories.show', compact('walletHistory'));
    }

    public function destroy(WalletHistory $walletHistory)
    {
        $walletHistory->delete();
        flash()->success('Wallet history deleted successfully.');
        return redirect()->route('wallet-histories.index');
    }
}

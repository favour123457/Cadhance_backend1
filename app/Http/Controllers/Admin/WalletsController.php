<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletsController extends Controller
{
    public function index()
    {
        $wallets = Wallet::with('user')->latest()->get();
        return view('admin.wallets.index', compact('wallets'));
    }

    public function show(Wallet $wallet)
    {
        $wallet->load(['user', 'wallet_histories.wallet_history_type', 'wallet_histories.wallet_history_status']);
        return view('admin.wallets.show', compact('wallet'));
    }

    public function destroy(Wallet $wallet)
    {
        $wallet->delete();
        flash()->success('Wallet deleted successfully.');
        return redirect()->route('wallets.index');
    }
}

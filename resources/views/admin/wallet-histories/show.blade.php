@extends('layouts.dashboard')
@section('title', 'Wallet History Details')
@section('main-content')
<div class="middle-content container-xxl p-0">
    <div class="row layout-top-spacing">
        <div class="col-xl-8 col-lg-8 col-md-10 col-sm-12 col-12 layout-spacing mx-auto">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12 d-flex justify-content-between align-items-center">
                            <h4>Wallet History #{{ $walletHistory->id }}</h4>
                            <a href="{{ route('wallet-histories.index') }}" class="btn btn-secondary btn-sm">Back</a>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    <table class="table table-bordered">
                        <tr><th>User</th><td>{{ $walletHistory->wallet->user->name ?? 'N/A' }}</td></tr>
                        <tr><th>Wallet</th><td><a href="{{ route('wallets.show', $walletHistory->wallet_id) }}">#{{ $walletHistory->wallet_id }}</a></td></tr>
                        <tr><th>Type</th><td>{{ $walletHistory->wallet_history_type->name ?? 'N/A' }}</td></tr>
                        <tr><th>Amount</th><td>{{ showMoney($walletHistory->amount ?? 0) }}</td></tr>
                        <tr><th>Status</th><td><span class="badge badge-light-info">{{ $walletHistory->wallet_history_status->name ?? 'N/A' }}</span></td></tr>
                        <tr><th>Reference</th><td>{{ $walletHistory->reference ?? '-' }}</td></tr>
                        <tr><th>Note</th><td>{{ $walletHistory->note ?? '-' }}</td></tr>
                        <tr><th>Date</th><td>{{ $walletHistory->created_at->format('M d, Y H:i') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

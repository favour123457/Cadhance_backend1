@extends('layouts.dashboard')
@section('title', 'Wallet Details')
@section('main-content')
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">
            <div class="col-xl-8 col-lg-8 col-md-10 col-sm-12 col-12 layout-spacing mx-auto">
                <div class="statbox widget box box-shadow">
                    <div class="widget-header">
                        <div class="row">
                            <div
                                class="col-xl-12 col-md-12 col-sm-12 col-12 d-flex justify-content-between align-items-center">
                                <h4>Wallet #{{ $wallet->id }}</h4>
                                <a href="{{ route('wallets.index') }}" class="btn btn-secondary btn-sm">Back</a>
                            </div>
                        </div>
                    </div>
                    <div class="widget-content widget-content-area">
                        <table class="table table-bordered">
                            <tr>
                                <th>User</th>
                                <td>{{ $wallet->user->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Balance</th>
                                <td>{{ showMoney($wallet->balance ?? 0) }}</td>
                            </tr>
                            <tr>
                                <th>Currency</th>
                                <td>{{ $wallet->currency ?? 'USD' }}</td>
                            </tr>
                            <tr>
                                <th>Total Histories</th>
                                <td>{{ $wallet->wallet_histories->count() }}</td>
                            </tr>
                            <tr>
                                <th>Created</th>
                                <td>{{ $wallet->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                        </table>

                        @if ($wallet->wallet_histories->count())
                            <h5 class="mt-4">Recent Transactions</h5>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($wallet->wallet_histories->take(10) as $history)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $history->wallet_history_type->name ?? 'N/A' }}</td>
                                            <td>{{ showMoney($history->amount ?? 0) }}</td>
                                            <td><span class="badge badge-light-info">{{ $history->wallet_history_status->name ?? 'N/A' }}</span></td>
                                            <td>{{ $history->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.dashboard')
@section('title', 'Escrow Details')
@section('main-content')
<div class="middle-content container-xxl p-0">
    <div class="row layout-top-spacing">
        <div class="col-xl-8 col-lg-8 col-md-10 col-sm-12 col-12 layout-spacing mx-auto">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12 d-flex justify-content-between align-items-center">
                            <h4>Escrow #{{ $escrow->id }}</h4>
                            <a href="{{ route('escrows.index') }}" class="btn btn-secondary btn-sm">Back</a>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    <table class="table table-bordered">
                        <tr><th>Client</th><td>{{ $escrow->customization_request->user->first_name ?? 'N/A' }} {{ $escrow->customization_request->user->last_name ?? '' }}</td></tr>
                        <tr><th>Customization Request</th><td><a href="{{ route('customization-requests.show', $escrow->customization_request_id) }}">#{{ $escrow->customization_request_id }}</a></td></tr>
                        <tr><th>Amount</th><td>{{ showMoney($escrow->amount ?? 0) }}</td></tr>
                        <tr><th>Status</th><td>{{ $escrow->status ?? 'N/A' }}</td></tr>
                        <tr><th>Escrow Histories</th><td>{{ $escrow->escrow_histories->count() }}</td></tr>
                        <tr><th>Created</th><td>{{ $escrow->created_at->format('M d, Y H:i') }}</td></tr>
                    </table>

                    @if($escrow->escrow_histories->count())
                    <h5 class="mt-4">Escrow History</h5>
                    <table class="table table-hover">
                        <thead><tr><th>#</th><th>Type</th><th>Amount</th><th>Note</th><th>Date</th></tr></thead>
                        <tbody>
                            @foreach($escrow->escrow_histories as $history)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $history->type ?? 'N/A' }}</td>
                                <td>{{ showMoney($history->amount ?? 0) }}</td>
                                <td>{{ $history->note ?? '-' }}</td>
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

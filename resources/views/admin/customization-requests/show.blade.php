@extends('layouts.dashboard')
@section('title', 'Customization Request Details')
@section('main-content')
<div class="middle-content container-xxl p-0">
    <div class="row layout-top-spacing">
        <div class="col-xl-8 col-lg-8 col-md-10 col-sm-12 col-12 layout-spacing mx-auto">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12 d-flex justify-content-between align-items-center">
                            <h4>Customization Request #{{ $customizationRequest->id }}</h4>
                            <div>
                                <a href="{{ route('customization-requests.edit', $customizationRequest->id) }}" class="btn btn-warning btn-sm">Edit Status</a>
                                <a href="{{ route('customization-requests.index') }}" class="btn btn-secondary btn-sm ms-1">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    <table class="table table-bordered">
                        <tr><th>Client</th><td>{{ $customizationRequest->user->name ?? 'N/A' }}</td></tr>
                        <tr><th>Status</th><td><span class="badge badge-light-warning">{{ $customizationRequest->customization_status->name ?? 'N/A' }}</span></td></tr>
                        <tr><th>Price</th><td>{{ showMoney($customizationRequest->price ?? 0) }}</td></tr>
                        <tr><th>Description</th><td>{{ $customizationRequest->description ?? 'N/A' }}</td></tr>
                        <tr><th>Milestones</th><td>{{ $customizationRequest->milestones->count() }}</td></tr>
                        <tr><th>Price Adjustments</th><td>{{ $customizationRequest->price_adjustments->count() }}</td></tr>
                        <tr><th>Escrow</th><td>{{ $customizationRequest->escrow ? showMoney($customizationRequest->escrow->amount) : 'None' }}</td></tr>
                        <tr><th>Created</th><td>{{ $customizationRequest->created_at->format('M d, Y H:i') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

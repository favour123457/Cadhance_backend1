@extends('layouts.dashboard')

@section('title', 'User Details')

@section('main-content')
<div class="middle-content container-xxl p-0">

    <div class="row layout-top-spacing">
        <div class="col-xl-8 col-lg-8 col-md-10 col-sm-12 col-12 layout-spacing mx-auto">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12 d-flex justify-content-between align-items-center">
                            <h4>User Details</h4>
                            <div>
                                @if(checkButtonPermission('users', 'edit'))
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                @endif
                                <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm ms-1">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">

                    @if($user->disabled)
                    <div class="alert alert-danger mb-3">
                        <strong>Account Disabled</strong>
                        @if($user->disabled_reason)
                            &mdash; {{ $user->disabled_reason }}
                        @endif
                    </div>
                    @endif

                    <div class="d-flex align-items-center mb-4">
                        @if($user->profile_picture)
                        <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="Profile"
                             class="rounded-circle me-3" style="width:70px;height:70px;object-fit:cover;"
                             onerror="this.src='{{ asset('src/assets/img/profile-16.jpeg') }}'">
                        @endif
                        <div>
                            <h5 class="mb-0">{{ $user->name }}</h5>
                            <small class="text-muted">{{ $user->email }}</small>
                        </div>
                    </div>

                    <table class="table table-bordered">
                        <tr><th width="35%">Name</th><td>{{ $user->name }}</td></tr>
                        <tr><th>Email</th><td>{{ $user->email }}</td></tr>
                        <tr><th>Phone</th><td>{{ $user->phone ?? 'N/A' }}</td></tr>
                        <tr><th>Role</th><td>{{ $user->role->name ?? 'N/A' }}</td></tr>
                        <tr><th>Account Type</th><td>{{ $user->account_type->name ?? 'N/A' }}</td></tr>
                        <tr><th>Offer Type</th><td>{{ $user->offer_type->name ?? 'N/A' }}</td></tr>
                        <tr><th>User Type</th><td>{{ $user->user_type->name ?? 'N/A' }}</td></tr>
                        <tr><th>Country</th><td>{{ $user->country->name ?? 'N/A' }}</td></tr>
                        <tr><th>State</th><td>{{ $user->state->name ?? 'N/A' }}</td></tr>
                        <tr><th>Service Charge</th><td>{{ $user->service_charge ?? 0 }}%</td></tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($user->disabled)
                                    <span class="badge badge-danger">Disabled</span>
                                @else
                                    <span class="badge badge-success">Active</span>
                                @endif
                            </td>
                        </tr>
                        @if($user->disabled && $user->disabled_reason)
                        <tr><th>Disable Reason</th><td>{{ $user->disabled_reason }}</td></tr>
                        @endif
                        <tr><th>Email Verified</th><td>{{ $user->email_verified_at ? $user->email_verified_at->format('M d, Y H:i') : 'Not verified' }}</td></tr>
                        <tr>
                            <th>Wallet Balance</th>
                            <td>{{ $user->wallet ? showMoney($user->wallet->balance) : 'N/A' }}</td>
                        </tr>
                        <tr><th>Joined</th><td>{{ $user->created_at->format('M d, Y H:i') }}</td></tr>
                        <tr><th>Last Updated</th><td>{{ $user->updated_at->format('M d, Y H:i') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

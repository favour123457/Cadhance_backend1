@extends('layouts.dashboard')
@section('title', 'Deleted User Details')
@section('main-content')
<div class="middle-content container-xxl p-0">
    <div class="row layout-top-spacing">
        <div class="col-xl-8 col-lg-8 col-md-10 col-sm-12 col-12 layout-spacing mx-auto">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12 d-flex justify-content-between align-items-center">
                            <h4>Deleted User Details</h4>
                            <a href="{{ route('deleted-users.index') }}" class="btn btn-secondary btn-sm">Back</a>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    <table class="table table-bordered">
                        <tr><th>Name</th><td>{{ $deletedUser->first_name ?? 'N/A' }} {{ $deletedUser->last_name ?? '' }}</td></tr>
                        <tr><th>Email</th><td>{{ $deletedUser->email ?? 'N/A' }}</td></tr>
                        <tr><th>Phone</th><td>{{ $deletedUser->phone ?? '-' }}</td></tr>
                        <tr><th>Deletion Reason</th><td>{{ $deletedUser->deletion_reason ?? '-' }}</td></tr>
                        <tr><th>Registered</th><td>{{ $deletedUser->created_at->format('M d, Y') }}</td></tr>
                        <tr><th>Deleted At</th><td>{{ $deletedUser->deleted_at ? \Carbon\Carbon::parse($deletedUser->deleted_at)->format('M d, Y H:i') : 'N/A' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

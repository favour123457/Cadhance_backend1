@extends('layouts.dashboard')
@section('title', 'Notification Details')
@section('main-content')
<div class="middle-content container-xxl p-0">
    <div class="row layout-top-spacing">
        <div class="col-xl-8 col-lg-8 col-md-10 col-sm-12 col-12 layout-spacing mx-auto">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12 d-flex justify-content-between align-items-center">
                            <h4>Notification Details</h4>
                            <a href="{{ route('notifications.index') }}" class="btn btn-secondary btn-sm">Back</a>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    <table class="table table-bordered">
                        <tr><th>User</th><td>{{ $notification->user->name ?? 'N/A' }}</td></tr>
                        <tr><th>Title</th><td>{{ $notification->title ?? 'N/A' }}</td></tr>
                        <tr><th>Message</th><td>{{ $notification->message ?? 'N/A' }}</td></tr>
                        <tr><th>Read</th><td><span class="badge badge-light-{{ $notification->is_read ? 'success' : 'warning' }}">{{ $notification->is_read ? 'Read' : 'Unread' }}</span></td></tr>
                        <tr><th>Date</th><td>{{ $notification->created_at->format('M d, Y H:i') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

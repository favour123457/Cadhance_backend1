@extends('layouts.dashboard')
@section('title', 'Group Details')
@section('main-content')
<div class="middle-content container-xxl p-0">
    <div class="row layout-top-spacing">
        <div class="col-xl-8 col-lg-8 col-md-10 col-sm-12 col-12 layout-spacing mx-auto">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12 d-flex justify-content-between align-items-center">
                            <h4>{{ $group->title ?? 'Group' }}</h4>
                            <div>
                                <a href="{{ route('groups.edit', $group->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                <a href="{{ route('groups.index') }}" class="btn btn-secondary btn-sm ms-1">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    <table class="table table-bordered">
                        <tr><th>Title</th><td>{{ $group->title ?? 'N/A' }}</td></tr>
                        <tr><th>Owner</th><td>{{ $group->user->name ?? 'N/A' }}</td></tr>
                        <tr><th>Platform</th><td>{{ $group->platform->name ?? 'N/A' }}</td></tr>
                        <tr><th>Status</th><td><span class="badge badge-light-info">{{ $group->group_status->name ?? 'N/A' }}</span></td></tr>
                        <tr><th>Members</th><td>{{ $group->group_subscriptions->count() }}</td></tr>
                        <tr><th>Price</th><td>{{ $group->price ?? '-' }}</td></tr>
                        <tr><th>Link</th><td>{{ $group->link ?? '-' }}</td></tr>
                        <tr><th>Description</th><td>{{ $group->description ?? '-' }}</td></tr>
                        <tr><th>Created</th><td>{{ $group->created_at->format('M d, Y H:i') }}</td></tr>
                    </table>

                    @if($group->group_subscriptions->count())
                    <h5 class="mt-4">Subscribers</h5>
                    <table class="table table-hover">
                        <thead><tr><th>#</th><th>User</th><th>Joined</th></tr></thead>
                        <tbody>
                            @foreach($group->group_subscriptions as $subscription)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $subscription->user->name ?? 'N/A' }}</td>
                                <td>{{ $subscription->created_at->format('M d, Y') }}</td>
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

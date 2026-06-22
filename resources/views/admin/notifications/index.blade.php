@extends('layouts.dashboard')
@section('title', 'Notifications')
@section('head')
<link href="{{ asset('') }}src/plugins/src/table/datatable/datatables.css" rel="stylesheet" type="text/css">
<link href="{{ asset('') }}src/plugins/src/table/datatable/dt-global_style.css" rel="stylesheet" type="text/css">
@endsection
@section('main-content')
<div class="middle-content container-xxl p-0">
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12 d-flex justify-content-between align-items-center">
                            <h4>Notifications</h4>
                            @if(checkButtonPermission('notifications', 'add'))
                            <a href="{{ route('notifications.create') }}" class="btn btn-primary btn-sm">Add Notification</a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    @include('inc.messages')
                    <table id="html5-extension" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Title</th>
                                <th>Read</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notifications as $notification)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $notification->user->name ?? 'N/A' }}</td>
                                <td>{{ Str::limit($notification->title ?? '', 60) }}</td>
                                <td><span class="badge badge-light-{{ $notification->is_read ? 'success' : 'warning' }}">{{ $notification->is_read ? 'Read' : 'Unread' }}</span></td>
                                <td>{{ $notification->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('notifications.show', $notification->id) }}" class="btn btn-info btn-sm">View</a>
                                    @if(checkButtonPermission('notifications', 'delete'))
                                    <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this notification?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('') }}src/plugins/src/table/datatable/datatables.js"></script>
<script src="{{ asset('') }}src/plugins/src/table/datatable/button-ext/dataTables.buttons.min.js"></script>
<script src="{{ asset('') }}src/plugins/src/table/datatable/button-ext/jszip.min.js"></script>
<script src="{{ asset('') }}src/plugins/src/table/datatable/button-ext/buttons.html5.min.js"></script>
<script src="{{ asset('') }}src/plugins/src/table/datatable/button-ext/buttons.print.min.js"></script>
<script src="{{ asset('') }}src/plugins/src/table/datatable/custom_miscellaneous.js"></script>
@endsection

@extends('layouts.dashboard')
@section('title', 'Deleted Users')
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
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                            <h4>Deleted Users</h4>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    @include('inc.messages')
                    <table id="html5-extension" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Reason</th>
                                <th>Deleted At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deletedUsers as $user)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $user->first_name ?? 'N/A' }} {{ $user->last_name ?? '' }}</td>
                                <td>{{ $user->email ?? 'N/A' }}</td>
                                <td>{{ Str::limit($user->deletion_reason ?? '-', 50) }}</td>
                                <td>{{ $user->deleted_at ? \Carbon\Carbon::parse($user->deleted_at)->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('deleted-users.show', $user->id) }}" class="btn btn-info btn-sm">View</a>
                                    @if(checkButtonPermission('deleted-users', 'delete'))
                                    <form action="{{ route('deleted-users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete this user record?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm">Purge</button>
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

@extends('layouts.dashboard')
@section('title', 'All General Settings')
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
                            <h4>All General Settings</h4>
                            @if(checkButtonPermission('all-general-settings', 'add'))
                            <a href="{{ route('all-general-settings.create') }}" class="btn btn-primary btn-sm">Add Setting</a>
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
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Model</th>
                                <th>DB Name</th>
                                <th>Description</th>
                                <th>Active</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($settings as $setting)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $setting->name ?? 'N/A' }}</td>
                                <td><code>{{ $setting->slug ?? 'N/A' }}</code></td>
                                <td>{{ $setting->model ?? 'N/A' }}</td>
                                <td>{{ $setting->dbname ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge badge-light-{{ $setting->is_description ? 'info' : 'secondary' }}">
                                        {{ $setting->is_description ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-light-{{ $setting->active ? 'success' : 'danger' }}">
                                        {{ $setting->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    @if(checkButtonPermission('all-general-settings', 'edit'))
                                    <a href="{{ route('all-general-settings.edit', $setting->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    @endif
                                    @if(checkButtonPermission('all-general-settings', 'delete'))
                                    <form action="{{ route('all-general-settings.destroy', $setting->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this setting?')">
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

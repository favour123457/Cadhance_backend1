@extends('layouts.dashboard')
@section('title', $gsetting->name)
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
                            <h4>{{ $gsetting->name }}</h4>
                            @if(checkButtonPermission('settings', 'add'))
                            <a href="{{ route('settings.create', $gsetting->slug) }}" class="btn btn-primary btn-sm">Add {{ $gsetting->name }}</a>
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
                                @if($gsetting->is_description)
                                <th>Description</th>
                                @endif
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($settings as $setting)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $setting->name ?? 'N/A' }}</td>
                                @if($gsetting->is_description)
                                <td>{{ Str::limit($setting->description ?? '-', 80) }}</td>
                                @endif
                                <td>
                                    @if(checkButtonPermission('settings', 'edit'))
                                    <a href="{{ route('settings.edit', [$gsetting->slug, $setting->id]) }}" class="btn btn-warning btn-sm">Edit</a>
                                    @endif
                                    @if(checkButtonPermission('settings', 'delete'))
                                    <form action="{{ route('settings.destroy', [$gsetting->slug, $setting->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this record?')">
                                        @csrf
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

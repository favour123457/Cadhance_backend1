@extends('layouts.dashboard')
@section('title', 'Countries')
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
                            <h4>Countries</h4>
                            @if(checkButtonPermission('countries', 'add'))
                            <a href="{{ route('countries.create') }}" class="btn btn-primary btn-sm">Add Country</a>
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
                                <th>Code</th>
                                <th>Currency</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($countries as $country)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $country->name ?? 'N/A' }}</td>
                                <td>{{ $country->code ?? 'N/A' }}</td>
                                <td>{{ $country->currency ?? '-' }}</td>
                                <td><span class="badge badge-light-{{ $country->status == 'active' ? 'success' : 'danger' }}">{{ $country->status ?? 'N/A' }}</span></td>
                                <td>
                                    @if(checkButtonPermission('countries', 'edit'))
                                    <a href="{{ route('countries.edit', $country->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    @endif
                                    @if(checkButtonPermission('countries', 'delete'))
                                    <form action="{{ route('countries.destroy', $country->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this country?')">
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

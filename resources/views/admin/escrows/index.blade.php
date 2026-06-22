@extends('layouts.dashboard')
@section('title', 'Escrows')
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
                            <h4>Escrows</h4>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    @include('inc.messages')
                    <table id="html5-extension" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Client</th>
                                <th>Customization Request</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($escrows as $escrow)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $escrow->customization_request->user->name ?? 'N/A' }} {{ $escrow->customization_request->user->last_name ?? '' }}</td>
                                <td>#{{ $escrow->customization_request_id }}</td>
                                <td>{{ showMoney($escrow->amount ?? 0) }}</td>
                                <td>{{ $escrow->status ?? 'N/A' }}</td>
                                <td>{{ $escrow->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('escrows.show', $escrow->id) }}" class="btn btn-info btn-sm">View</a>
                                    @if(checkButtonPermission('escrows', 'delete'))
                                    <form action="{{ route('escrows.destroy', $escrow->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this escrow?')">
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

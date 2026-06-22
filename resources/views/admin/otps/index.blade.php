@extends('layouts.dashboard')
@section('title', 'OTPs')
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
                            <h4>OTP Records</h4>
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
                                <th>Code</th>
                                <th>Type</th>
                                <th>Used</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($otps as $otp)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $otp->user }}</td>
                                <td>{{ $otp->code ?? 'N/A' }}</td>
                                <td>{{ $otp->otp_type->name ?? 'N/A' }}</td>
                                <td><span class="badge badge-light-{{ $otp->is_used ? 'success' : 'warning' }}">{{ $otp->is_used ? 'Used' : 'Unused' }}</span></td>
                                <td>{{ $otp->created_at->format('M d, Y') }}</td>
                                <td>
                                    @if(checkButtonPermission('otps', 'delete'))
                                    <form action="{{ route('otps.destroy', $otp->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this OTP?')">
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

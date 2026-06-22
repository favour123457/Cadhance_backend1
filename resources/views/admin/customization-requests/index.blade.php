@extends('layouts.dashboard')
@section('title', 'Customization Requests')
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
                                <h4>Customization Requests</h4>
                            </div>
                        </div>
                    </div>
                    <div class="widget-content widget-content-area">
                        @include('inc.messages')
                        <div class="table-responsive">
                            <table id="html5-extension" class="table table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Client</th>
                                        <th>Status</th>
                                        <th>Budget</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($requests as $i => $req)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $req->user->name ?? 'N/A' }}</td>
                                            <td><span
                                                    class="badge badge-light-warning">{{ $req->customization_status->name ?? 'N/A' }}</span>
                                            </td>
                                            <td>{{ showMoney($req->price ?? 0) }}</td>
                                            <td>{{ $req->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('customization-requests.show', $req->id) }}"
                                                    class="btn btn-info btn-sm"><svg xmlns="http://www.w3.org/2000/svg"
                                                        width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" class="feather feather-eye">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg></a>
                                                <a href="{{ route('customization-requests.edit', $req->id) }}"
                                                    class="btn btn-warning btn-sm"><svg xmlns="http://www.w3.org/2000/svg"
                                                        width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" class="feather feather-edit-2">
                                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z">
                                                        </path>
                                                    </svg></a>
                                                <form action="{{ route('customization-requests.destroy', $req->id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure?');"> @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm"><svg
                                                            xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="feather feather-trash-2">
                                                            <polyline points="3 6 5 6 21 6"></polyline>
                                                            <path
                                                                d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2">
                                                            </path>
                                                        </svg></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No requests found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
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

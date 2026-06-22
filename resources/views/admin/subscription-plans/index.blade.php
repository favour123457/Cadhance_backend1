@extends('layouts.dashboard')
@section('title', 'Subscription Plans')
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
                            <h4>Subscription Plans</h4>
                            @if(checkButtonPermission('subscription-plans', 'add'))
                            <a href="{{ route('subscription-plans.create') }}" class="btn btn-primary btn-sm">Add Plan</a>
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
                                <th>Price</th>
                                {{-- <th>Duration</th> --}}
                                <th>Features</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subscriptionPlans as $plan)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $plan->name ?? 'N/A' }}</td>
                                <td>{{ showMoney($plan->price ?? 0) }}</td>
                                <td>{{ $plan->description ?? '-' }}</td>
                                <td><span class="badge badge-light-{{ $plan->active == 1 ? 'success' : 'danger' }}">{{ $plan->active == 1 ? 'Active' : 'Inactive' }}</span></td>
                                <td>
                                    @if(checkButtonPermission('subscription-plans', 'edit'))
                                    <a href="{{ route('subscription-plans.edit', $plan->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    @endif
                                    @if(checkButtonPermission('subscription-plans', 'delete'))
                                    <form action="{{ route('subscription-plans.destroy', $plan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this plan?')">
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

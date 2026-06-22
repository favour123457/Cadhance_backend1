@extends('layouts.dashboard')

@section('title', 'Template Details')

@section('main-content')
<div class="middle-content container-xxl p-0">
    <div class="row layout-top-spacing">
        <div class="col-xl-8 col-lg-8 col-md-10 col-sm-12 col-12 layout-spacing mx-auto">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12 d-flex justify-content-between align-items-center">
                            <h4>Template: {{ $template->name }}</h4>
                            <div>
                                @if(checkButtonPermission('templates', 'edit'))
                                <a href="{{ route('templates.edit', $template->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                @endif
                                <a href="{{ route('templates.index') }}" class="btn btn-secondary btn-sm ms-1">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    <table class="table table-bordered">
                        <tr><th>Name</th><td>{{ $template->name }}</td></tr>
                        <tr><th>Owner</th><td>{{ $template->user->first_name ?? 'N/A' }} {{ $template->user->last_name ?? '' }}</td></tr>
                        <tr><th>Status</th><td><span class="badge badge-light-info">{{ $template->template_status->name ?? 'N/A' }}</span></td></tr>
                        <tr><th>Price</th><td>{{ showMoney($template->price ?? 0) }}</td></tr>
                        <tr><th>Description</th><td>{{ $template->description ?? 'N/A' }}</td></tr>
                        <tr><th>Created</th><td>{{ $template->created_at->format('M d, Y H:i') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

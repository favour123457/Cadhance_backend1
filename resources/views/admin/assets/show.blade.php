@extends('layouts.dashboard')

@section('title', 'Asset Details')

@section('main-content')
<div class="middle-content container-xxl p-0">


    <div class="row layout-top-spacing">
        <div class="col-xl-8 col-lg-8 col-md-10 col-sm-12 col-12 layout-spacing mx-auto">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12 d-flex justify-content-between align-items-center">
                            <h4>Asset: {{ $asset->name }}</h4>
                            <div>
                                @if(checkButtonPermission('assets', 'edit'))
                                <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                @endif
                                <a href="{{ route('assets.index') }}" class="btn btn-secondary btn-sm ms-1">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    <table class="table table-bordered">
                        <tr><th>Name</th><td>{{ $asset->name }}</td></tr>
                        <tr><th>Owner</th><td>{{ $asset->user->first_name ?? 'N/A' }} {{ $asset->user->last_name ?? '' }}</td></tr>
                        <tr><th>Category</th><td>{{ $asset->design_category->name ?? 'N/A' }}</td></tr>
                        <tr><th>License Type</th><td>{{ $asset->license_type->name ?? 'N/A' }}</td></tr>
                        <tr><th>Status</th><td><span class="badge badge-light-info">{{ $asset->asset_status->name ?? 'N/A' }}</span></td></tr>
                        <tr><th>Price</th><td>{{ showMoney($asset->price ?? 0) }}</td></tr>
                        <tr><th>Description</th><td>{{ $asset->description ?? 'N/A' }}</td></tr>
                        <tr><th>Files</th><td>{{ $asset->asset_files->count() }} file(s)</td></tr>
                        <tr><th>Created</th><td>{{ $asset->created_at->format('M d, Y H:i') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

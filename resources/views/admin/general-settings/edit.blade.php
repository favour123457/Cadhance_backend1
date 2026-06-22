@extends('layouts.dashboard')
@section('title', 'Edit ' . $gsetting->name)
@section('main-content')
<div class="middle-content container-xxl p-0">
    <div class="row layout-top-spacing">
        <div class="col-xl-8 col-lg-8 col-md-10 col-sm-12 col-12 layout-spacing mx-auto">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                            <h4>Edit {{ $gsetting->name }}: {{ $setting->name }}</h4>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    @include('inc.messages')
                    <form action="{{ route('settings.update', [$gsetting->slug, $setting->id]) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $setting->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        @if($gsetting->is_description)
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $setting->description ?? '') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        @endif
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('settings.index', $gsetting->slug) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

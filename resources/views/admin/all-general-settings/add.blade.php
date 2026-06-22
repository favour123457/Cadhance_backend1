@extends('layouts.dashboard')
@section('title', 'Add General Setting')
@section('main-content')
<div class="middle-content container-xxl p-0">
    <div class="row layout-top-spacing">
        <div class="col-xl-8 col-lg-8 col-md-12 col-sm-12 col-12 layout-spacing mx-auto">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                            <h4>Add General Setting</h4>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    @include('inc.messages')
                    <form action="{{ route('all-general-settings.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. App Settings" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Model <span class="text-danger">*</span></label>
                            <input type="text" name="model" class="form-control @error('model') is-invalid @enderror" value="{{ old('model') }}" placeholder="e.g. App\Models\Setting" required>
                            @error('model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">DB Name <span class="text-danger">*</span></label>
                            <input type="text" name="dbname" class="form-control @error('dbname') is-invalid @enderror" value="{{ old('dbname') }}" placeholder="e.g. settings" required>
                            @error('dbname')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Has Description? <span class="text-danger">*</span></label>
                            <select name="is_description" class="form-control @error('is_description') is-invalid @enderror" required>
                                <option value="">-- Select --</option>
                                <option value="1" {{ old('is_description') == '1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('is_description') === '0' ? 'selected' : '' }}>No</option>
                            </select>
                            @error('is_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="active" class="form-control @error('active') is-invalid @enderror" required>
                                <option value="">-- Select --</option>
                                <option value="1" {{ old('active') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('active') === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('active')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('all-general-settings.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Setting</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

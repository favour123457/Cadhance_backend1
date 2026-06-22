@extends('layouts.dashboard')
@section('title', 'Add Subscription Plan')
@section('main-content')
<div class="middle-content container-xxl p-0">
    <div class="row layout-top-spacing">
        <div class="col-xl-8 col-lg-8 col-md-10 col-sm-12 col-12 layout-spacing mx-auto">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                            <h4>Add Subscription Plan</h4>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    @include('inc.messages')
                    <form action="{{ route('subscription-plans.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Monthly Price ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="monthly_price" class="form-control @error('monthly_price') is-invalid @enderror" value="{{ old('monthly_price') }}" required>
                            @error('monthly_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Annual Price ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="annual_price" class="form-control @error('annual_price') is-invalid @enderror" value="{{ old('annual_price') }}" required>
                            @error('annual_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                                    {{-- <div class="mb-3">
                                        <label class="form-label">Duration (days)</label>
                                        <input type="number" name="duration" class="form-control @error('duration') is-invalid @enderror" value="{{ old('duration') }}">
                                        @error('duration')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div> --}}
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="active" class="form-select @error('active') is-invalid @enderror">
                                <option value="1" {{ old('active') == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('active') == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('active')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('subscription-plans.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Plan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

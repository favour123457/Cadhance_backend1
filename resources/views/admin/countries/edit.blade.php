@extends('layouts.dashboard')
@section('title', 'Edit Country')
@section('main-content')
<div class="middle-content container-xxl p-0">
    <div class="row layout-top-spacing">
        <div class="col-xl-8 col-lg-8 col-md-10 col-sm-12 col-12 layout-spacing mx-auto">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                            <h4>Edit Country</h4>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    @include('inc.messages')
                    <form action="{{ route('countries.update', $country->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $country->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dial Code</label>
                            <input type="number" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $country->code) }}">
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Flag (emoji or URL)</label>

                            <input type="text" name="flag" class="form-control @error('flag') is-invalid @enderror" value="{{ old('flag', $country->flag) }}">
                            @error('flag')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Continent</label>
                            <input type="text" name="continent" class="form-control @error('continent') is-invalid @enderror" value="{{ old('continent', $country->continent) }}">
                            @error('continent')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Currency</label>
                            <input type="text" name="currency" class="form-control @error('currency') is-invalid @enderror" value="{{ old('currency', $country->currency) }}">
                            @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sanctioned</label>
                            <select name="sanctioned" class="form-select @error('sanctioned') is-invalid @enderror">
                                <option value="0" {{ old('sanctioned', $country->sanctioned ? '1' : '0') == '0' ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('sanctioned', $country->sanctioned ? '1' : '0') == '1' ? 'selected' : '' }}>Yes</option>
                            </select>
                            @error('sanctioned')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('countries.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Country</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

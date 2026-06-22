@extends('layouts.dashboard')

@section('title', 'Edit Currency')
@section('main-content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">Edit Currency</h2>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('currencies.update', $currency) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Currency Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $currency->name) }}" 
                               placeholder="e.g., Nigerian Naira" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="symbol" class="form-label">Symbol <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('symbol') is-invalid @enderror" 
                               id="symbol" name="symbol" value="{{ old('symbol', $currency->symbol) }}" 
                               placeholder="e.g., NGN" required>
                        @error('symbol')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="symbol2" class="form-label">Symbol 2 (Optional)</label>
                        <input type="text" class="form-control @error('symbol2') is-invalid @enderror" 
                               id="symbol2" name="symbol2" value="{{ old('symbol2', $currency->symbol2) }}" 
                               placeholder="e.g., ₦">
                        @error('symbol2')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="flag" class="form-label">Flag Emoji <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('flag') is-invalid @enderror" 
                               id="flag" name="flag" value="{{ old('flag', $currency->flag) }}" 
                               placeholder="🇳🇬" required>
                        @error('flag')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="country_id" class="form-label">Country <span class="text-danger">*</span></label>
                        <select class="form-select @error('country_id') is-invalid @enderror" 
                                id="country_id" name="country_id" required>
                            <option value="">Select Country</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}" 
                                    {{ old('country_id', $currency->country_id) == $country->id ? 'selected' : '' }}>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('country_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="exchange_rate" class="form-label">Exchange Rate (1 USD = X) <span class="text-danger">*</span></label>
                        <input type="number" step="0.0001" class="form-control @error('exchange_rate') is-invalid @enderror" 
                               id="exchange_rate" name="exchange_rate" 
                               value="{{ old('exchange_rate', $currency->exchange_rate) }}" 
                               placeholder="e.g., 1500.0000" required>
                        @error('exchange_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">1 USD equals how many of this currency?</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_base_currency" 
                                   name="is_base_currency" value="1" 
                                   {{ old('is_base_currency', $currency->is_base_currency) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_base_currency">
                                Is Base Currency (USD)
                            </label>
                        </div>
                        <small class="form-text text-muted">Check this if this is USD (base currency for conversions)</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="active" 
                                   name="active" value="1" 
                                   {{ old('active', $currency->active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="active">
                                Active
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Update Currency
                    </button>
                    <a href="{{ route('currencies.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .card {
        border: none;
        box-shadow: 0 0 20px rgba(0,0,0,0.08);
        border-radius: 10px;
    }
</style>
@endsection

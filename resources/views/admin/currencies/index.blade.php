@extends('layouts.dashboard')

@section('title', 'Manage Currencies')
@section('main-content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Manage Currencies</h2>
                <div>
                    <a href="{{ route('currencies.update-rates') }}" class="btn btn-warning" 
                       onclick="return confirm('Update exchange rates for all active currencies?')">
                        <i class="bi bi-arrow-repeat"></i> Update Exchange Rates
                    </a>
                    <a href="{{ route('currencies.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add Currency
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Flag</th>
                            <th>Name</th>
                            <th>Symbol</th>
                            <th>Country</th>
                            <th>Exchange Rate (to USD)</th>
                            <th>Base Currency</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($currencies as $currency)
                            <tr>
                                <td>{{ $currency->id }}</td>
                                <td><span style="font-size: 24px;">{{ $currency->flag }}</span></td>
                                <td>{{ $currency->name }}</td>
                                <td><strong>{{ $currency->symbol }}</strong></td>
                                <td>{{ $currency->country->name ?? 'N/A' }}</td>
                                <td>
                                    @if($currency->is_base_currency)
                                        <span class="badge bg-primary">1.0000 (Base)</span>
                                    @else
                                        {{ number_format($currency->exchange_rate, 4) }}
                                    @endif
                                </td>
                                <td>
                                    @if($currency->is_base_currency)
                                        <span class="badge bg-primary">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td>
                                    @if($currency->active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('currencies.edit', $currency) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form action="{{ route('currencies.destroy', $currency) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Delete this currency?');"
                                              style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <p class="text-muted mb-0">No currencies found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $currencies->links() }}
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border: none;
        box-shadow: 0 0 20px rgba(0,0,0,0.08);
        border-radius: 10px;
    }
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }
</style>
@endsection

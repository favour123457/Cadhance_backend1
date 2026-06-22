@extends('layouts.dashboard')

@section('title', 'Withdrawal Management')

@section('head')
<style>
    .nav-tabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #888;
        font-weight: 600;
        padding: 12px 24px;
    }
    .nav-tabs .nav-link.active {
        color: #4361ee;
        border-bottom-color: #4361ee;
        background: transparent;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    .badge-status {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .badge-pending {
        background: #fff3cd;
        color: #856404;
    }
    .badge-completed {
        background: #d4edda;
        color: #155724;
    }
    .filter-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .export-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        transition: transform 0.2s;
    }
    .export-btn:hover {
        transform: translateY(-2px);
        color: white;
    }
    .mark-btn {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
    }
</style>
@endsection

@section('main-content')
<div class="middle-content container-xxl p-0">

    <!-- Breadcrumb -->
    <div class="page-meta">
        <nav class="breadcrumb-style-one" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Withdrawals</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row layout-top-spacing">
        <div class="col-12">
            <div class="widget widget-table-two">
                <div class="widget-heading">
                    <h5 class="mb-0">Withdrawal Management</h5>
                </div>

                <div class="widget-content">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs mb-4" id="withdrawalTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank" type="button" role="tab">
                                Bank Transfer ({{ $bankWithdrawals->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="mobilemoney-tab" data-bs-toggle="tab" data-bs-target="#mobilemoney" type="button" role="tab">
                                Mobile Money ({{ $mobileMoneyWithdrawals->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="processing-tab" data-bs-toggle="tab" data-bs-target="#processing" type="button" role="tab">
                                Processing ({{ $processingWithdrawals->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                                Completed ({{ $completedWithdrawals->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="failed-tab" data-bs-toggle="tab" data-bs-target="#failed" type="button" role="tab">
                                Failed ({{ $failedWithdrawals->count() }})
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="withdrawalTabContent">
                        <!-- Bank Transfer Tab -->
                        <div class="tab-pane fade show active" id="bank" role="tabpanel">
                            <div class="filter-card">
                                <form method="GET" action="{{ route('withdrawals.export-bank-csv') }}" class="row align-items-end g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Filter by Country</label>
                                        <select name="country" class="form-select" id="bankCountrySelect">
                                            @foreach($bankCountries as $country)
                                                <option value="{{ $country }}">{{ $country }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="export-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download me-2" viewBox="0 0 16 16">
                                                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                                            </svg>
                                            Export CSV for Flutterwave
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <form method="POST" action="{{ route('withdrawals.mark-processed') }}" id="bankForm">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="selectAllBank" /></th>
                                                <th>User</th>
                                                <th>Country</th>
                                                <th>Bank</th>
                                                <th>Account Number</th>
                                                <th>Account Name</th>
                                                <th>Amount</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($bankWithdrawals as $withdrawal)
                                                <tr>
                                                    <td><input type="checkbox" name="ids[]" value="{{ $withdrawal->id }}" class="bank-checkbox" /></td>
                                                    <td>
                                                        <strong>{{ $withdrawal->user->first_name }} {{ $withdrawal->user->last_name }}</strong><br>
                                                        <small class="text-muted">{{ $withdrawal->user->email }}</small>
                                                    </td>
                                                    <td>{{ $withdrawal->user->country->name ?? 'N/A' }}</td>
                                                    <td>{{ $withdrawal->bankAccount->bank_name ?? 'N/A' }}</td>
                                                    <td>{{ $withdrawal->bankAccount->account_number ?? 'N/A' }}</td>
                                                    <td>{{ $withdrawal->bankAccount->account_name ?? 'N/A' }}</td>
                                                    <td><strong>{{ showMoney($withdrawal->amount) }}</strong></td>
                                                    <td>{{ $withdrawal->created_at->format('M d, Y') }}</td>
                                                    <td><span class="badge-status badge-pending">Pending</span></td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center py-4">
                                                        <p class="text-muted">No pending bank withdrawals found</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                @if($bankWithdrawals->count() > 0)
                                    <div class="mt-3">
                                        <button type="submit" class="mark-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle me-2" viewBox="0 0 16 16">
                                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                                <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                                            </svg>
                                            Mark Selected as Processed
                                        </button>
                                    </div>
                                @endif
                            </form>
                        </div>

                        <!-- Mobile Money Tab -->
                        <div class="tab-pane fade" id="mobilemoney" role="tabpanel">
                            <div class="filter-card">
                                <form method="GET" action="{{ route('withdrawals.export-mobilemoney-csv') }}" class="row align-items-end g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Filter by Country</label>
                                        <select name="country" class="form-select" id="mobileMoneyCountrySelect">
                                            @foreach($mobileMoneyCountries as $country)
                                                <option value="{{ $country }}">{{ $country }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="export-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download me-2" viewBox="0 0 16 16">
                                                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                                            </svg>
                                            Export CSV for Flutterwave
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <form method="POST" action="{{ route('withdrawals.mark-processed') }}" id="mobileMoneyForm">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="selectAllMobileMoney" /></th>
                                                <th>User</th>
                                                <th>Country</th>
                                                <th>Provider</th>
                                                <th>Phone Number</th>
                                                <th>Account Name</th>
                                                <th>Amount</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($mobileMoneyWithdrawals as $withdrawal)
                                                <tr>
                                                    <td><input type="checkbox" name="ids[]" value="{{ $withdrawal->id }}" class="mobilemoney-checkbox" /></td>
                                                    <td>
                                                        <strong>{{ $withdrawal->user->first_name }} {{ $withdrawal->user->last_name }}</strong><br>
                                                        <small class="text-muted">{{ $withdrawal->user->email }}</small>
                                                    </td>
                                                    <td>{{ $withdrawal->user->country->name ?? 'N/A' }}</td>
                                                    <td>{{ $withdrawal->mobileMoneyAccount->provider ?? 'N/A' }}</td>
                                                    <td>{{ $withdrawal->mobileMoneyAccount->account_number ?? 'N/A' }}</td>
                                                    <td>{{ $withdrawal->mobileMoneyAccount->account_name ?? 'N/A' }}</td>
                                                    <td><strong>{{ showMoney($withdrawal->amount) }}</strong></td>
                                                    <td>{{ $withdrawal->created_at->format('M d, Y') }}</td>
                                                    <td><span class="badge-status badge-pending">Pending</span></td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center py-4">
                                                        <p class="text-muted">No pending mobile money withdrawals found</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                @if($mobileMoneyWithdrawals->count() > 0)
                                    <div class="mt-3">
                                        <button type="submit" class="mark-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle me-2" viewBox="0 0 16 16">
                                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                                <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                                            </svg>
                                            Mark Selected as Processed
                                        </button>
                                    </div>
                                @endif
                            </form>
                        </div>

                        <!-- Processing Tab -->
                        <div class="tab-pane fade" id="processing" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Type</th>
                                            <th>Details</th>
                                            <th>Amount</th>
                                            <th>Currency</th>
                                            <th>Reference</th>
                                            <th>Requested</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($processingWithdrawals as $withdrawal)
                                            <tr>
                                                <td>
                                                    <strong>{{ $withdrawal->user->first_name }} {{ $withdrawal->user->last_name }}</strong><br>
                                                    <small class="text-muted">{{ $withdrawal->user->email }}</small>
                                                </td>
                                                <td>{{ $withdrawal->payment_method === 'bank_transfer' ? 'Bank Transfer' : 'Mobile Money' }}</td>
                                                <td>
                                                    @if($withdrawal->payment_method === 'bank_transfer')
                                                        <strong>{{ $withdrawal->bankAccount->bank_name ?? 'N/A' }}</strong><br>
                                                        <small>{{ $withdrawal->bankAccount->account_number ?? 'N/A' }}</small>
                                                    @else
                                                        <strong>{{ $withdrawal->mobileMoneyAccount->provider ?? 'N/A' }}</strong><br>
                                                        <small>{{ $withdrawal->mobileMoneyAccount->account_number ?? 'N/A' }}</small>
                                                    @endif
                                                </td>
                                                <td><strong>{{ showMoney($withdrawal->amount) }}</strong></td>
                                                <td>{{ $withdrawal->currency->symbol ?? 'USD' }}</td>
                                                <td><small>{{ $withdrawal->flutterwave_reference ?? 'N/A' }}</small></td>
                                                <td>{{ $withdrawal->created_at->format('M d, Y H:i') }}</td>
                                                <td><span class="badge-status badge-processing">Processing</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-4">
                                                    <p class="text-muted">No processing withdrawals found</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Completed Tab -->
                        <div class="tab-pane fade" id="completed" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Type</th>
                                            <th>Details</th>
                                            <th>Amount</th>
                                            <th>Currency</th>
                                            <th>Reference</th>
                                            <th>Requested</th>
                                            <th>Processed</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($completedWithdrawals as $withdrawal)
                                            <tr>
                                                <td>
                                                    <strong>{{ $withdrawal->user->first_name }} {{ $withdrawal->user->last_name }}</strong><br>
                                                    <small class="text-muted">{{ $withdrawal->user->email }}</small>
                                                </td>
                                                <td>{{ $withdrawal->payment_method === 'bank_transfer' ? 'Bank Transfer' : 'Mobile Money' }}</td>
                                                <td>
                                                    @if($withdrawal->payment_method === 'bank_transfer')
                                                        <strong>{{ $withdrawal->bankAccount->bank_name ?? 'N/A' }}</strong><br>
                                                        <small>{{ $withdrawal->bankAccount->account_number ?? 'N/A' }}</small>
                                                    @else
                                                        <strong>{{ $withdrawal->mobileMoneyAccount->provider ?? 'N/A' }}</strong><br>
                                                        <small>{{ $withdrawal->mobileMoneyAccount->account_number ?? 'N/A' }}</small>
                                                    @endif
                                                </td>
                                                <td><strong>{{ showMoney($withdrawal->amount) }}</strong></td>
                                                <td>{{ $withdrawal->currency->symbol ?? 'USD' }}</td>
                                                <td><small>{{ $withdrawal->flutterwave_reference ?? 'N/A' }}</small></td>
                                                <td>{{ $withdrawal->created_at->format('M d, Y') }}</td>
                                                <td>{{ $withdrawal->processed_at ? $withdrawal->processed_at->format('M d, Y') : 'N/A' }}</td>
                                                <td><span class="badge-status badge-completed">Completed</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center py-4">
                                                    <p class="text-muted">No completed withdrawals found</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Failed Tab -->
                        <div class="tab-pane fade" id="failed" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Type</th>
                                            <th>Details</th>
                                            <th>Amount</th>
                                            <th>Currency</th>
                                            <th>Reference</th>
                                            <th>Failure Reason</th>
                                            <th>Requested</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($failedWithdrawals as $withdrawal)
                                            <tr>
                                                <td>
                                                    <strong>{{ $withdrawal->user->first_name }} {{ $withdrawal->user->last_name }}</strong><br>
                                                    <small class="text-muted">{{ $withdrawal->user->email }}</small>
                                                </td>
                                                <td>{{ $withdrawal->payment_method === 'bank_transfer' ? 'Bank Transfer' : 'Mobile Money' }}</td>
                                                <td>
                                                    @if($withdrawal->payment_method === 'bank_transfer')
                                                        <strong>{{ $withdrawal->bankAccount->bank_name ?? 'N/A' }}</strong><br>
                                                        <small>{{ $withdrawal->bankAccount->account_number ?? 'N/A' }}</small>
                                                    @else
                                                        <strong>{{ $withdrawal->mobileMoneyAccount->provider ?? 'N/A' }}</strong><br>
                                                        <small>{{ $withdrawal->mobileMoneyAccount->account_number ?? 'N/A' }}</small>
                                                    @endif
                                                </td>
                                                <td><strong>{{ showMoney($withdrawal->amount) }}</strong></td>
                                                <td>{{ $withdrawal->currency->symbol ?? 'USD' }}</td>
                                                <td><small>{{ $withdrawal->flutterwave_reference ?? 'N/A' }}</small></td>
                                                <td>
                                                    <small class="text-danger">{{ $withdrawal->failure_reason ?? 'Unknown error' }}</small>
                                                </td>
                                                <td>{{ $withdrawal->created_at->format('M d, Y H:i') }}</td>
                                                <td><span class="badge-status badge-failed">Failed</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center py-4">
                                                    <p class="text-muted">No failed withdrawals found</p>
                                                </td>
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
    </div>

</div>

<style>
    .badge-processing {
        background-color: #17a2b8;
        color: white;
    }
    .badge-failed {
        background-color: #dc3545;
        color: white;
    }
</style>
@endsection

@section('scripts')
<script>
    // Select All Bank Checkboxes
    document.getElementById('selectAllBank')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.bank-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    // Select All Mobile Money Checkboxes
    document.getElementById('selectAllMobileMoney')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.mobilemoney-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    // Confirm before marking as processed
    document.querySelectorAll('form[id*="Form"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const checked = this.querySelectorAll('input[name="ids[]"]:checked').length;
            if (checked === 0) {
                e.preventDefault();
                alert('Please select at least one withdrawal to mark as processed');
                return;
            }
            if (!confirm(`Are you sure you want to mark ${checked} withdrawal(s) as processed?`)) {
                e.preventDefault();
            }
        });
    });
</script>
@endsection

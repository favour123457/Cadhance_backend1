@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('head')
<link href="{{ asset('') }}src/plugins/src/apex/apexcharts.css" rel="stylesheet" type="text/css">
@endsection

@section('main-content')
<div class="middle-content container-xxl p-0">

    <!-- Breadcrumb -->
    <div class="page-meta">
        <nav class="breadcrumb-style-one" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </nav>
    </div>

    <!-- Stats Cards Row -->
    <div class="row layout-top-spacing">

        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 layout-spacing">
            <a href="{{ route('users.index') }}" class="text-decoration-none">
                <div class="widget" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; overflow: hidden; border: none;">
                    <div class="widget-content p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="mb-1" style="color: rgba(255,255,255,0.9); font-size: 14px; font-weight: 500;">Total Users</p>
                                <h2 class="mb-0" style="color: #fff; font-size: 32px; font-weight: 700;">{{ number_format($totalUsers) }}</h2>
                            </div>
                            <div style="width: 56px; height: 56px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 layout-spacing">
            <a href="{{ route('assets.index') }}" class="text-decoration-none">
                <div class="widget" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 16px; overflow: hidden; border: none;">
                    <div class="widget-content p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="mb-1" style="color: rgba(255,255,255,0.9); font-size: 14px; font-weight: 500;">Total Assets</p>
                                <h2 class="mb-0" style="color: #fff; font-size: 32px; font-weight: 700;">{{ number_format($totalAssets) }}</h2>
                            </div>
                            <div style="width: 56px; height: 56px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 layout-spacing">
            <a href="{{ route('templates.index') }}" class="text-decoration-none">
                <div class="widget" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 16px; overflow: hidden; border: none;">
                    <div class="widget-content p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="mb-1" style="color: rgba(255,255,255,0.9); font-size: 14px; font-weight: 500;">Total Templates</p>
                                <h2 class="mb-0" style="color: #fff; font-size: 32px; font-weight: 700;">{{ number_format($totalTemplates) }}</h2>
                            </div>
                            <div style="width: 56px; height: 56px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 layout-spacing">
            <a href="{{ route('customization-requests.index') }}" class="text-decoration-none">
                <div class="widget" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); border-radius: 16px; overflow: hidden; border: none;">
                    <div class="widget-content p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="mb-1" style="color: rgba(255,255,255,0.9); font-size: 14px; font-weight: 500;">Customization Requests</p>
                                <h2 class="mb-0" style="color: #fff; font-size: 32px; font-weight: 700;">{{ number_format($totalCustomizationReqs) }}</h2>
                            </div>
                            <div style="width: 56px; height: 56px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 layout-spacing">
            <div class="widget" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); border-radius: 16px; overflow: hidden; border: none;">
                <div class="widget-content p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1" style="color: rgba(50,50,50,0.8); font-size: 14px; font-weight: 500;">Total Escrow Value</p>
                            <h2 class="mb-0" style="color: #333; font-size: 32px; font-weight: 700;">{{ showMoney($totalEscrowValue) }}</h2>
                        </div>
                        <div style="width: 56px; height: 56px; background: rgba(255,255,255,0.6); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 layout-spacing">
            <div class="widget" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); border-radius: 16px; overflow: hidden; border: none;">
                <div class="widget-content p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1" style="color: rgba(50,50,50,0.8); font-size: 14px; font-weight: 500;">Total Wallet Balance</p>
                            <h2 class="mb-0" style="color: #333; font-size: 32px; font-weight: 700;">{{ showMoney($totalWalletBalance) }}</h2>
                        </div>
                        <div style="width: 56px; height: 56px; background: rgba(255,255,255,0.6); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 layout-spacing">
            <div class="widget" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); border-radius: 16px; overflow: hidden; border: none;">
                <div class="widget-content p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1" style="color: rgba(50,50,50,0.8); font-size: 14px; font-weight: 500;">Platform Revenue</p>
                            <h2 class="mb-0" style="color: #333; font-size: 32px; font-weight: 700;">${{ number_format($totalPlatformRevenue ?? 0, 2) }}</h2>
                        </div>
                        <div style="width: 56px; height: 56px; background: rgba(255,255,255,0.6); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 layout-spacing">
            <div class="widget widget-card-one">
                <div class="widget-content">
                    <div class="account-box">
                        <div class="info-box">
                            <div class="icon" style="background:#0acf97;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-grid"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="info">
                                    <h5><a href="{{ route('groups.index') }}">{{ number_format($totalGroups) }}</a></h5>
                                    <p class="meta">Total Groups</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Charts Row -->
    <div class="row layout-spacing">

        <!-- Monthly Registrations Chart -->
        <div class="col-xl-8 col-lg-8 col-md-12 col-sm-12 col-12 layout-spacing">
            <div class="widget">
                <div class="widget-heading">
                    <h6>Monthly Activity (Last 12 Months)</h6>
                </div>
                <div class="widget-content">
                    <div id="chart-monthly-activity"></div>
                </div>
            </div>
        </div>

        <!-- Platform Summary Pie -->
        <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-12 layout-spacing">
            <div class="widget">
                <div class="widget-heading">
                    <h6>Platform Summary</h6>
                </div>
                <div class="widget-content">
                    <div id="chart-platform-summary"></div>
                </div>
            </div>
        </div>

    </div>

    <!-- Recent Tables -->
    <div class="row layout-spacing">

        <!-- Recent Users -->
        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12 layout-spacing">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                            <h4>Recent Users</h4>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-4">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentUsers as $i => $user)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td><span class="badge badge-light-primary">{{ $user->role->name ?? 'N/A' }}</span></td>
                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center">No users found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Customization Requests -->
        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12 layout-spacing">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                            <h4>Recent Customization Requests</h4>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-4">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Client</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentRequests as $i => $req)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $req->user->name ?? 'N/A' }}</td>
                                    <td><span class="badge badge-light-info">{{ $req->customization_status->name ?? 'N/A' }}</span></td>
                                    <td>{{ $req->created_at->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center">No requests found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Wallet Histories -->
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                            <h4>Recent Wallet Transactions</h4>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-4">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentWalletHistories as $i => $tx)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $tx->wallet->user->name ?? 'N/A' }}</td>
                                    <td>{{ $tx->wallet_history_type->name ?? 'N/A' }}</td>
                                    <td>{{ showMoney($tx->amount ?? 0) }}</td>
                                    <td><span class="badge badge-light-success">{{ $tx->wallet_history_status->name ?? 'N/A' }}</span></td>
                                    <td>{{ $tx->created_at->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center">No transactions found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
    // Monthly Activity Line Chart
    var activityOptions = {
        series: [
            { name: 'Users', data: {!! json_encode($monthlyUsers) !!} },
            { name: 'Assets', data: {!! json_encode($monthlyAssets) !!} },
            { name: 'Wallet Txs', data: {!! json_encode($monthlyWalletTx) !!} },
        ],
        chart: { height: 300, type: 'line', toolbar: { show: false } },
        colors: ['#4361ee', '#0acf97', '#e7515a'],
        stroke: { curve: 'smooth', width: 2 },
        xaxis: {
            categories: (function () {
                var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                var now = new Date();
                var result = [];
                for (var i = 11; i >= 0; i--) {
                    var d = new Date(now.getFullYear(), now.getMonth() - i, 1);
                    result.push(months[d.getMonth()] + ' ' + d.getFullYear());
                }
                return result;
            })()
        },
        grid: { borderColor: '#e0e6ed' },
        legend: { position: 'top' },
        tooltip: { shared: true, intersect: false },
    };
    new ApexCharts(document.querySelector('#chart-monthly-activity'), activityOptions).render();

    // Platform Summary Donut Chart
    var summaryOptions = {
        series: [
            {{ $totalUsers }},
            {{ $totalAssets }},
            {{ $totalTemplates }},
            {{ $totalSiteJobs }},
            {{ $totalGroups }},
        ],
        labels: ['Users', 'Assets', 'Templates', 'Jobs', 'Groups'],
        chart: { height: 300, type: 'donut' },
        colors: ['#4361ee', '#0acf97', '#e2a03f', '#e7515a', '#2196f3'],
        legend: { position: 'bottom' },
        responsive: [{ breakpoint: 480, options: { chart: { width: 200 } } }],
    };
    new ApexCharts(document.querySelector('#chart-platform-summary'), summaryOptions).render();
</script>
@endsection

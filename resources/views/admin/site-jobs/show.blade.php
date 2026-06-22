@extends('layouts.dashboard')
@section('title', 'Site Job Details')
@section('main-content')
<div class="middle-content container-xxl p-0">
    <div class="row layout-top-spacing">
        <div class="col-xl-8 col-lg-8 col-md-10 col-sm-12 col-12 layout-spacing mx-auto">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12 d-flex justify-content-between align-items-center">
                            <h4>{{ $siteJob->title ?? 'Site Job' }}</h4>
                            <div>
                                <a href="{{ route('site-jobs.edit', $siteJob->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                <a href="{{ route('site-jobs.index') }}" class="btn btn-secondary btn-sm ms-1">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    <table class="table table-bordered">
                        <tr><th>Title</th><td>{{ $siteJob->title ?? 'N/A' }}</td></tr>
                        <tr><th>Location</th><td>{{ $siteJob->location ?? 'N/A' }}</td></tr>
                        <tr><th>Status</th><td><span class="badge badge-light-info">{{ $siteJob->site_job_status->name ?? 'N/A' }}</span></td></tr>
                        <tr><th>Salary Range</th><td>{{ showMoney($siteJob->min_salary ?? 0) }} – {{ showMoney($siteJob->max_salary ?? 0) }} ({{ $siteJob->salary_type ?? 'N/A' }})</td></tr>
                        <tr><th>Deadline</th><td>{{ $siteJob->deadline ?? 'N/A' }}</td></tr>
                        <tr><th>Link</th><td>{{ $siteJob->link ?? '-' }}</td></tr>
                        <tr><th>Contact Email</th><td>{{ $siteJob->contact_email ?? '-' }}</td></tr>
                        <tr><th>Description</th><td>{{ $siteJob->description ?? 'N/A' }}</td></tr>
                        <tr><th>Posted By</th><td>{{ $siteJob->user->name ?? 'N/A' }}</td></tr>
                        <tr><th>Applications</th><td>{{ $siteJob->site_job_applications->count() }}</td></tr>
                        <tr><th>Created</th><td>{{ $siteJob->created_at->format('M d, Y H:i') }}</td></tr>
                    </table>

                    @if($siteJob->site_job_applications->count())
                    <h5 class="mt-4">Applications</h5>
                    <table class="table table-hover">
                        <thead><tr><th>#</th><th>Applicant</th><th>Applied On</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($siteJob->site_job_applications as $app)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $app->user->name ?? 'N/A' }}</td>
                                <td>{{ $app->application_date ?? $app->created_at->format('M d, Y') }}</td>
                                <td>{{ $app->status ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

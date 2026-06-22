@extends('layouts.dashboard')
@section('title', 'Site Jobs')
@section('head')
<link href="{{ asset('') }}src/plugins/src/table/datatable/datatables.css" rel="stylesheet" type="text/css">
<link href="{{ asset('') }}src/plugins/src/table/datatable/dt-global_style.css" rel="stylesheet" type="text/css">
@endsection
@section('main-content')
<div class="middle-content container-xxl p-0">
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                            <h4>Site Jobs</h4>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    @include('inc.messages')
                    <table id="html5-extension" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Location</th>
                                <th>Salary Range</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Applications</th>
                                <th>Posted By</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($siteJobs as $job)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $job->title ?? 'N/A' }}</td>
                                <td>{{ $job->location ?? 'N/A' }}</td>
                                <td>{{ showMoney($job->min_salary ?? 0) }} – {{ showMoney($job->max_salary ?? 0) }}</td>
                                <td>{{ $job->deadline ?? 'N/A' }}</td>
                                <td><span class="badge badge-light-info">{{ $job->site_job_status->name ?? 'N/A' }}</span></td>
                                <td>{{ $job->site_job_applications_count }}</td>
                                <td>{{ $job->user->name ?? 'N/A' }}</td>
                                <td>{{ $job->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('site-jobs.show', $job->id) }}" class="btn btn-info btn-sm">View</a>
                                    @if(checkButtonPermission('site-jobs', 'edit'))
                                    <a href="{{ route('site-jobs.edit', $job->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    @endif
                                    @if(checkButtonPermission('site-jobs', 'delete'))
                                    <form action="{{ route('site-jobs.destroy', $job->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this job?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('') }}src/plugins/src/table/datatable/datatables.js"></script>
<script src="{{ asset('') }}src/plugins/src/table/datatable/button-ext/dataTables.buttons.min.js"></script>
<script src="{{ asset('') }}src/plugins/src/table/datatable/button-ext/jszip.min.js"></script>
<script src="{{ asset('') }}src/plugins/src/table/datatable/button-ext/buttons.html5.min.js"></script>
<script src="{{ asset('') }}src/plugins/src/table/datatable/button-ext/buttons.print.min.js"></script>
<script src="{{ asset('') }}src/plugins/src/table/datatable/custom_miscellaneous.js"></script>
@endsection

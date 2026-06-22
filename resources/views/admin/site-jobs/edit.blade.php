@extends('layouts.dashboard')
@section('title', 'Edit Site Job')
@section('main-content')
<div class="middle-content container-xxl p-0">
    <div class="row layout-top-spacing">
        <div class="col-xl-8 col-lg-8 col-md-10 col-sm-12 col-12 layout-spacing mx-auto">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                            <h4>Edit Site Job</h4>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    @include('inc.messages')
                    <form action="{{ route('site-jobs.update', $siteJob->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $siteJob->title) }}">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="site_job_status_id" class="form-select @error('site_job_status_id') is-invalid @enderror">
                                @foreach($statuses as $status)
                                <option value="{{ $status->id }}" {{ $siteJob->site_job_status_id == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                @endforeach
                            </select>
                            @error('site_job_status_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label class="form-label">Min Salary</label>
                                <input type="number" step="0.01" name="min_salary" class="form-control @error('min_salary') is-invalid @enderror" value="{{ old('min_salary', $siteJob->min_salary) }}">
                                @error('min_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-5 mb-3">
                                <label class="form-label">Max Salary</label>
                                <input type="number" step="0.01" name="max_salary" class="form-control @error('max_salary') is-invalid @enderror" value="{{ old('max_salary', $siteJob->max_salary) }}">
                                @error('max_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Salary Type</label>
                                <select name="salary_type" class="form-select @error('salary_type') is-invalid @enderror">
                                    @foreach(['hourly','daily','weekly','monthly','annual'] as $t)
                                    <option value="{{ $t }}" {{ $siteJob->salary_type == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                                    @endforeach
                                </select>
                                @error('salary_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $siteJob->location) }}">
                            @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deadline</label>
                            <input type="text" name="deadline" class="form-control @error('deadline') is-invalid @enderror" value="{{ old('deadline', $siteJob->deadline) }}">
                            @error('deadline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Application Link</label>
                            <input type="text" name="link" class="form-control @error('link') is-invalid @enderror" value="{{ old('link', $siteJob->link) }}">
                            @error('link')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contact Email</label>
                            <input type="email" name="contact_email" class="form-control @error('contact_email') is-invalid @enderror" value="{{ old('contact_email', $siteJob->contact_email) }}">
                            @error('contact_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $siteJob->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('site-jobs.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

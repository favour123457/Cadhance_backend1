@extends('layouts.dashboard')

@section('title', 'Edit Template')

@section('main-content')
<div class="middle-content container-xxl p-0">
    <div class="row layout-top-spacing">
        <div class="col-xl-8 col-lg-8 col-md-10 col-sm-12 col-12 layout-spacing mx-auto">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                            <h4>Edit Template: {{ $template->title }}</h4>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    @include('inc.messages')
                    <form action="{{ route('templates.update', $template->id) }}" method="POST">
                        @csrf @method('PATCH')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="template_status_id" class="form-select" required>
                                    <option value="">Select Status</option>
                                    @foreach($templateStatuses as $status)
                                    <option value="{{ $status->id }}" {{ old('template_status_id', $template->template_status_id) == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price</label>
                                <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price', $template->price) }}">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="{{ old('title', $template->title) }}" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4">{{ old('description', $template->description) }}</textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Includes <small class="text-muted">(what's included in this template)</small></label>
                                <textarea name="includes" class="form-control" rows="3">{{ old('includes', $template->includes) }}</textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('templates.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Template</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

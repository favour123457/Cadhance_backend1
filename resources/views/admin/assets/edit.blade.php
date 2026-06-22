@extends('layouts.dashboard')

@section('title', 'Edit Asset')

@section('main-content')
<div class="middle-content container-xxl p-0">
    <div class="row layout-top-spacing">
        <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 layout-spacing mx-auto">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                            <h4>Edit Asset: {{ $asset->title }}</h4>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    @include('inc.messages')
                    <form action="{{ route('assets.update', $asset->id) }}" method="POST">
                        @csrf @method('PATCH')

                        <h6 class="text-muted mb-3 mt-1">Basic Info</h6>
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="{{ old('title', $asset->title) }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Unique Code</label>
                                <input type="text" name="unique_code" class="form-control" value="{{ old('unique_code', $asset->unique_code) }}">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4">{{ old('description', $asset->description) }}</textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Detail View</label>
                                <textarea name="detail_view" class="form-control" rows="3">{{ old('detail_view', $asset->detail_view) }}</textarea>
                            </div>
                        </div>

                        <h6 class="text-muted mb-3 mt-2">Classification</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="asset_status_id" class="form-select" required>
                                    <option value="">Select Status</option>
                                    @foreach($assetStatuses as $status)
                                    <option value="{{ $status->id }}" {{ old('asset_status_id', $asset->asset_status_id) == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Design Category</label>
                                <select name="design_category_id" class="form-select">
                                    <option value="">Select Category</option>
                                    @foreach($designCategories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('design_category_id', $asset->design_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">License Type</label>
                                <select name="license_type_id" class="form-select">
                                    <option value="">Select License Type</option>
                                    @foreach($licenseTypes as $lt)
                                    <option value="{{ $lt->id }}" {{ old('license_type_id', $asset->license_type_id) == $lt->id ? 'selected' : '' }}>{{ $lt->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tools Used</label>
                                <input type="text" name="tools_used" class="form-control" value="{{ old('tools_used', $asset->tools_used) }}" placeholder="e.g. Figma, Adobe XD">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Available File Formats</label>
                                <input type="text" name="available_file_formats" class="form-control" value="{{ old('available_file_formats', $asset->available_file_formats) }}" placeholder="e.g. PDF, SVG, AI">
                            </div>
                        </div>

                        <h6 class="text-muted mb-3 mt-2">Pricing</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Price</label>
                                <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price', $asset->price) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Service Charge</label>
                                <input type="number" step="0.01" min="0" name="service_charge" class="form-control" value="{{ old('service_charge', $asset->service_charge) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Rating</label>
                                <input type="number" step="0.1" min="0" max="5" name="rating" class="form-control" value="{{ old('rating', $asset->rating) }}">
                            </div>
                        </div>

                        <h6 class="text-muted mb-3 mt-2">Visibility & Flags</h6>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label d-block">Visible to Public</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="visibility" id="visibility" value="1" {{ old('visibility', $asset->visibility) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="visibility">Enabled</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label d-block">Advanced Upload</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_advanced_upload" id="is_advanced_upload" value="1" {{ old('is_advanced_upload', $asset->is_advanced_upload) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_advanced_upload">Yes</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label d-block">Has Video</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="has_video" id="has_video" value="1" {{ old('has_video', $asset->has_video) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="has_video">Yes</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label d-block">Has Sample</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="has_sample" id="has_sample" value="1" {{ old('has_sample', $asset->has_sample) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="has_sample">Yes</label>
                                </div>
                            </div>
                        </div>

                        <h6 class="text-muted mb-3 mt-2">Affiliate Settings</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label d-block">Affiliate Enabled</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="affiliate_settings" id="affiliate_settings" value="1" {{ old('affiliate_settings', $asset->affiliate_settings) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="affiliate_settings">Enabled</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Affiliate Commission Rate (%)</label>
                                <input type="number" step="0.01" min="0" max="100" name="affiliate_commission_rate" class="form-control" value="{{ old('affiliate_commission_rate', $asset->affiliate_commission_rate) }}">
                            </div>
                        </div>

                        <h6 class="text-muted mb-3 mt-2">Customization</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label d-block">Customization Available</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="customization_available" id="customization_available" value="1" {{ old('customization_available', $asset->customization_available) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="customization_available">Yes</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Customization Price</label>
                                <input type="number" step="0.01" min="0" name="customization_price" class="form-control" value="{{ old('customization_price', $asset->customization_price) }}">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-3">
                            <a href="{{ route('assets.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Asset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

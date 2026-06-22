@extends('layouts.dashboard')
@section('title', 'Add Note')
@section('main-content')
    <div class="middle-content container-xxl p-0">
        <div class="row layout-top-spacing">
            <div class="col-xl-8 col-lg-8 col-md-10 col-sm-12 col-12 layout-spacing mx-auto">
                <div class="statbox widget box box-shadow">
                    <div class="widget-header">
                        <div class="row">
                            <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                <h4>Add Note</h4>
                            </div>
                        </div>
                    </div>
                    <div class="widget-content widget-content-area">
                        @include('inc.messages')
                        <form action="{{ route('notes.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="note_type_id" class="form-select @error('note_type_id') is-invalid @enderror"
                                    required>
                                    <option value="">-- Select Type --</option>
                                    @foreach ($noteTypes as $type)
                                        <option value="{{ $type->id }}"
                                            {{ old('note_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('note_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Note <span class="text-danger">*</span></label>
                                <textarea name="note" rows="5" class="form-control @error('note') is-invalid @enderror" id="myeditorinstance" required>{{ old('note') }}</textarea>
                                @error('note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('notes.index') }}" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save Note</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
<x-head.tinymce-config />
@endsection
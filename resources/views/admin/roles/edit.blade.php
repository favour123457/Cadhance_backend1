@extends('layouts.dashboard')
@section('title', 'Edit Role')
@section('main-content')
<div class="middle-content container-xxl p-0">
    <div class="row layout-top-spacing">
        <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 layout-spacing mx-auto">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                            <h4>Edit Role: {{ $role->name }}</h4>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    @include('inc.messages')
                    <form action="{{ route('roles.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label">Role Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        @if($permissions->count())
                        <div class="mb-3">
                            <label class="form-label">Permissions</label>
                            <div class="mb-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="checkAll()">Check All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="uncheckAll()">Uncheck All</button>
                            </div>
                            <div class="row">
                                @foreach($permissions as $permission)
                                <div class="col-md-3 col-sm-4 col-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input perm-check" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="perm_{{ $permission->id }}" {{ in_array($permission->id, $rolePermissionIds) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_{{ $permission->id }}">{{ $permission->name }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Role</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
function checkAll() {
    document.querySelectorAll('.perm-check').forEach(cb => cb.checked = true);
}
function uncheckAll() {
    document.querySelectorAll('.perm-check').forEach(cb => cb.checked = false);
}
</script>
@endsection

@extends('admin.layouts.index')
@section('title', 'Permissions Edit')
@section('content')
    @include('components.alert')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0">Edit Role</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                    <li class="breadcrumb-item active">Role</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header justify-content-between">
                        <div class="d-flex gap-2">
                            <h4 class="card-title">Edit Role</h4>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-flex gap-1">
                                <a href="{{ route('permission.index') }}" class="btn btn-primary ms-1">Back</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('permission.update', $role->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="col-form-label">Role Name <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-lg-9">
                                    <div class="mb-3">
                                        <input type="text" class="form-control" readonly value="{{ $role->name }}">
                                    </div>
                                </div>
                            </div>
                            @foreach ($permissionMap as $module => $data)
                                @php
                                    $actions = collect($data['actions'])->unique()->values();

                                    $allChecked = $actions->every(
                                        fn($action) => $role->hasPermissionTo("{$module}.{$action}"),
                                    );
                                @endphp

                                <div class="row mb-3">
                                    <div class="col-lg-3">
                                        <label class="col-form-label">{{ $data['label'] }}</label>
                                    </div>

                                    <div class="col-lg-9 perm-row">
                                        <div class="d-flex flex-wrap gap-4">

                                            <div class="form-check form-switch form-check-secondary fs-xxl mb-2">
                                                <input type="checkbox" class="form-check-input mt-1 all-btn"
                                                    id="all_{{ Str::slug($module) }}" {{ $allChecked ? 'checked' : '' }}>

                                                <label class="form-check-label fs-base" for="all_{{ Str::slug($module) }}">
                                                    All
                                                </label>
                                            </div>

                                            @foreach ($actions as $action)
                                                @php
                                                    $perm = "{$module}.{$action}";
                                                    $checked = $role->hasPermissionTo($perm);
                                                    $id = Str::slug($module . '_' . $action);
                                                @endphp

                                                <div class="form-check form-switch form-check-secondary fs-xxl mb-2">
                                                    <input type="hidden" name="id" value="{{ $role->id }}">
                                                    <input type="checkbox" class="form-check-input mt-1 permission-toggle"
                                                        value="{{ $perm }}" name="permissions[]"
                                                        id="{{ $id }}" {{ $checked ? 'checked' : '' }}>

                                                    <label class="form-check-label fs-base" for="{{ $id }}">
                                                        {{ ucfirst($action) }}
                                                    </label>
                                                </div>
                                            @endforeach

                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="col-12 mt-2 mb-4 d-flex gap-2 justify-content-end">
                                <button class="btn btn-primary" type="submit">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.all-btn').forEach(function(allBtn) {
                allBtn.addEventListener('change', function() {
                    const permRow = allBtn.closest('.perm-row');
                    const isChecked = allBtn.checked;
                    permRow.querySelectorAll('.permission-toggle').forEach(function(permCheckbox) {
                        permCheckbox.checked = isChecked;
                    });
                });
            });
        });
    </script>
@endsection

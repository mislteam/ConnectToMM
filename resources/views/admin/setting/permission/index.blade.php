@extends('admin.layouts.index')
@section('title', 'Permissions')
@section('content')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">All Roles</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>

                    <li class="breadcrumb-item active text-black">All Roles</li>
                </ol>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="d-flex gap-1">
                    <x-create-action menu-text="Create" permission="permission.create" :url="route('permission.create')" />
                </div>

            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div data-table data-table-rows-per-page="8" class="card">
                    <div class="card-header">All Roles</div>
                    <div class="table-responsive">
                        <table class="table table-custom table-centered table-select table-hover w-100 mb-0">
                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                <tr class="text-uppercase fs-xxs">
                                    <th class="ps-3" style="width: 1%;">
                                        <input data-table-select-all
                                            class="form-check-input form-check-input-light fs-14 mt-0" type="checkbox"
                                            id="select-all-files" value="option">
                                    </th>
                                    <th data-table-sort>No</th>
                                    <th data-table-sort>Name</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($roles as $role)
                                    <tr>
                                        <td class="ps-3"><input
                                                class="form-check-input form-check-input-light fs-14 file-item-check mt-0"
                                                type="checkbox" value="option"></td>
                                        <td>
                                            <h5 class="m-0"><a href="#"
                                                    class="link-reset">{{ $loop->iteration }}</a>
                                            </h5>
                                        </td>
                                        <td>
                                            {{ $role->name }}
                                        </td>

                                        <td>
                                            @if ($role->name !== 'administrator' && auth()->user()->role !== $role->name)
                                                <div class="d-flex justify-content-center gap-1">
                                                    <x-action-button :url="route('permission.edit', $role->id)" permission="permission.edit"
                                                        icon="ti-edit" />
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div data-table-pagination-info="name"></div>
                            <div data-table-pagination></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

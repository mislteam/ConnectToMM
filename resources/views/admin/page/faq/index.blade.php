@extends('admin.layouts.index')
@section('title', 'FAQs')
@section('content')
    @include('components.alert')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">FAQs</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                    <li class="breadcrumb-item active text-black">FAQs</li>
                </ol>
            </div>
            <div class="d-flex gap-2 justify-content-end">
                <x-create-action menu-text="Create" permission="page.create" :url="route('page.faq.create')" icon="ti-plus" />
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div data-table data-table-rows-per-page="8" class="card">
                    <div class="card-header fw-semibold">FAQs</div>
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
                                    <th data-table-sort>Title</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($faqs as $data)
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
                                            {{ $data->title }}
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <x-action-button :url="route('page.faq.edit', $data->id)" permission="page.edit" icon="ti-edit" />

                                                <x-action-button :data-id="$data->id" permission="page.delete" icon="ti-trash"
                                                    target-name="delete-faq" class="delete-btn"
                                                    data-url="{{ '/page/faq/delete/' . $data->id }}" />

                                            </div>
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
                    <!-- faq delete -->
                    <x-delete-modal-box id="delete-faq" message="Are you sure you want to delete this FAQ?" />

                </div>
            </div>
        </div>
    </div>
@endsection

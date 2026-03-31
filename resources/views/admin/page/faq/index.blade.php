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
                <a href="{{ route('page.faq.create') }}" class="btn btn-primary ms-1">
                    <i class="ti ti-plus fs-sm me-2"></i> Create
                </a>
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
                                                <a href="{{ route('page.faq.edit', $data->id) }}"
                                                    class="btn btn-light btn-icon btn-sm rounded-circle"><i
                                                        class="ti ti-edit fs-lg"></i></a>
                                                <a href="#" data-id="{{ $data->id }}" data-bs-toggle="modal"
                                                    data-bs-target="#delete-faq"
                                                    class="btn btn-light btn-icon btn-sm rounded-circle delete-faq-btn">
                                                    <i class="ti ti-trash fs-lg"></i>
                                                </a>
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
                    <div class="modal fade" id="delete-faq" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Confirm Delete</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <p>Are you sure you want to delete this FAQ?</p>
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let selectedId = null;
            document.querySelectorAll(".delete-faq-btn").forEach((btn) => {
                btn.addEventListener("click", function() {
                    selectedId = this.getAttribute("data-id");
                });
            });

            document
                .getElementById("confirmDeleteBtn")
                .addEventListener("click", function() {
                    if (!selectedId) return;

                    fetch(`/page/faq/delete/${selectedId}`, {
                            method: "DELETE",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector(
                                    'meta[name="csrf-token"]'
                                ).content,
                            },
                        })
                        .then((res) => {
                            if (!res.ok) throw new Error("Failed to delete faq.");
                            return res.json();
                        })
                        .then(() => {
                            const modal = bootstrap.Modal.getInstance(
                                document.getElementById("delete-faq")
                            );
                            modal.hide();

                            document
                                .querySelector(`[data-id="${selectedId}"]`)
                                ?.closest("tr")
                                ?.remove();
                            window.location.reload();
                        })
                        .catch((err) => {
                            console.error(err);
                        });
                });
        });
    </script>
@endsection

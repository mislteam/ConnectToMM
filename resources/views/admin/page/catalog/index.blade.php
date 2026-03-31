@extends('admin.layouts.index')
@section('title', 'Sections & Items')
@section('content')
    @include('components.alert')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Sections & Items</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                    <li class="breadcrumb-item active text-black">Sections & Items</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header fw-semibold">Sections</div>
                    <div class="table-responsive">
                        <table class="table table-custom table-centered table-select table-hover w-100 mb-0">
                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                <tr class="text-uppercase fs-xxs">
                                    <th class="ps-3" style="width: 1%;">
                                    </th>
                                    <th data-table-sort>No</th>
                                    <th data-table-sort>Section Name</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sections as $section)
                                    <tr>
                                        <td class="ps-3"></td>
                                        <td>
                                            <h5 class="m-0"><a href="#"
                                                    class="link-reset">{{ $loop->iteration }}</a>
                                            </h5>
                                        </td>
                                        <td>
                                            {{ $section->eyebrow_text }}
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('page.section.edit', [$section->section_key, $section->id]) }}"
                                                    class="btn btn-light btn-icon btn-sm rounded-circle"><i
                                                        class="ti ti-edit fs-lg"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <p class="text-center">Nothing Found.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div data-table data-table-rows-per-page="8" class="card">
                    <div class="card-header fw-semibold">Items</div>
                    <div class="table-responsive">
                        <table class="table table-custom table-centered table-select table-hover w-100 mb-0">
                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                <tr class="text-uppercase fs-xxs">
                                    <th class="ps-3" style="width: 1%;">
                                    </th>
                                    <th data-table-sort>No</th>
                                    <th data-table-sort>Title</th>
                                    <th data-table-sort>Section Name</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($items as $item)
                                    <tr>
                                        <td class="ps-3"></td>
                                        <td>
                                            <h5 class="m-0"><a href="#"
                                                    class="link-reset">{{ $loop->iteration }}</a>
                                            </h5>
                                        </td>
                                        <td>
                                            {{ $item->title }}
                                        </td>
                                        <td>
                                            {{ $item->section->eyebrow_text }}
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('page.section.edit', $item->id) }}"
                                                    class="btn btn-light btn-icon btn-sm rounded-circle"><i
                                                        class="ti ti-edit fs-lg"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <p class="text-center">Nothing Found.</p>
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div data-table-pagination-info="items" id="pagination-info"></div>
                            <div data-table-pagination id="pagination"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- container -->
@endsection

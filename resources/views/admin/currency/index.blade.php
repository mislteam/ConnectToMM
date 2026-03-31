@extends('admin.layouts.index')
@section('title', 'Currency & Profit')
@section('content')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Currency & Profit</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                    <li class="breadcrumb-item active text-black">Currency & Profit</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header fw-semibold">Currency</div>
                    <div class="table-responsive">
                        <table class="table table-custom table-centered table-select table-hover w-100 mb-0">
                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                <tr class="text-uppercase fs-xxs">
                                    <th class="ps-3" style="width: 1%;">
                                    </th>
                                    <th data-table-sort>No</th>
                                    <th data-table-sort>Currency Name</th>
                                    <th data-table-sort>Exchange Rate</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($currencies as $data)
                                    <tr>
                                        <td class="ps-3"></td>
                                        <td>
                                            <h5 class="m-0"><a href="#"
                                                    class="link-reset">{{ $loop->iteration }}</a>
                                            </h5>
                                        </td>
                                        <td>
                                            {{ $data->name }}
                                        </td>
                                        <td>
                                            {{ $data->value }}
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('currency.edit', $data->id) }}"
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

                <div class="card">
                    <div class="card-header fw-semibold">Profit</div>
                    <!-- profit table -->
                    <div class="table-responsive">
                        <table class="table table-custom table-centered table-select table-hover w-100 mb-0">
                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                <tr class="text-uppercase fs-xxs">
                                    <th class="ps-3" style="width: 1%;">
                                    </th>
                                    <th data-table-sort>No</th>
                                    <th data-table-sort>Amount</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-3"></td>
                                    <td>
                                        <h5 class="m-0"><a href="#" class="link-reset">1</a>
                                        </h5>
                                    </td>
                                    <td>
                                        {{ $profit_amount ?? 0 }}
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ $profit_id ? route('currency.edit', $profit_id) : '' }}"
                                                class="btn btn-light btn-icon btn-sm rounded-circle"><i
                                                    class="ti ti-edit fs-lg"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- container -->
@endsection

@extends('admin.layouts.index')
@section('title', 'Footer Page')
@section('content')
    @include('components.alert')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Footer</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                    <li class="breadcrumb-item active text-black">Footer</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div data-table data-table-rows-per-page="8" class="card">
                    <div class="card-header border-light justify-content-between">
                        <h4>Important Links</h4>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover w-100 mb-0">
                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                <tr class="text-uppercase fs-xxs">
                                    <th data-table-sort>No</th>
                                    <th data-table-sort>Text</th>
                                    <th data-table-sort>Link</th>
                                    <th class="text-center" style="width: 1%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody style="font-weight: 400 !important">
                                @foreach ($importantLinks as $im)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            {{ $im->text }}
                                        </td>
                                        <td>
                                            {{ $im->link }}
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('footer.important.edit', $im->id) }}"
                                                class="btn btn-light btn-icon btn-sm rounded-circle"><i
                                                    class="ti ti-edit fs-lg"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

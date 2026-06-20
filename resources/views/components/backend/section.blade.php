@props(['logo', 'title', 'activeTitle', 'sectionTitle', 'columnName', 'isCreateBtn' => false, 'route' => ''])
@extends('admin.layouts.index')
@section('title', 'Home Page')
@section('content')
    <style>
        .backend-section-page-title,
        .backend-section-breadcrumb-current {
            color: #111827;
        }

        .backend-section-breadcrumb-link {
            color: #4b5563;
        }

        .backend-section-breadcrumb-link:hover {
            color: #1f2937;
        }

        html[data-bs-theme="dark"] .backend-section-page-title,
        html[data-bs-theme="dark"] .backend-section-breadcrumb-current {
            color: #e5edf9;
        }

        html[data-bs-theme="dark"] .backend-section-breadcrumb-link {
            color: #9fb1cc;
        }

        html[data-bs-theme="dark"] .backend-section-breadcrumb-link:hover {
            color: #dbe7ff;
        }
    </style>
    @include('components.alert')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 backend-section-page-title">{{ $sectionTitle }}</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);" class="backend-section-breadcrumb-link">Home</a></li>
                    <li class="breadcrumb-item active backend-section-breadcrumb-current">{{ $activeTitle }}</li>
                </ol>
            </div>
            @if ($isCreateBtn)
                <div class="">
                    <a href="{{ route($route) }}" class="btn btn-primary ms-1">
                        <i class="ti ti-plus fs-sm me-1"></i> Create
                    </a>
                </div>
            @endif
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header fw-semibold">{{ $sectionTitle }}</div>
                    <div class="table-responsive">
                        <table class="table table-custom table-centered table-select table-hover w-100 mb-0">
                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                <tr class="text-uppercase fs-xxs">
                                    <th class="ps-3" style="width: 1%;">
                                    </th>
                                    <th data-table-sort>No</th>
                                    <th data-table-sort>{{ $columnName }}</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{ $slot }}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

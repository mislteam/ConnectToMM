@extends('admin.layouts.index')
@section('title', 'All Admin')
@section('content')
    @include('components.alert')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Refunds Policy</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                    <li class="breadcrumb-item active text-black">Refunds Policy</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card card-h-100 rounded-0 rounded-start">
                    <div class="card-header align-items-start">
                        <h3 class="mb-1 d-flex fs-xl align-items-center fw-semibold text-black">Refunds Policy</h3>
                    </div>
                    <form method="POST" action="{{ route('page.refunds.update', $policy->id) }}">
                        @csrf
                        @method('patch')
                        <div class="card-body px-4">
                            <div class="mb-3">
                                <label for="productName" class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" placeholder="Enter Title..."
                                    value="{{ $policy->title }}">
                                @error('title')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="productName" class="form-label">Description</label>
                                <textarea name="description" class="summernote">
                                    {!! old('description', $policy->description) !!}
                                </textarea>
                                @error('description')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            @can('roam.api-credentials.edit')
                                <div class="mt-2 mb-4 d-flex gap-2 justify-content-end">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            @endcan
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

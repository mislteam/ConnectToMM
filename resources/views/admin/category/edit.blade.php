@extends('admin.layouts.index')
@section('title', 'Joytel eSim')
@section('content')
    @include('components.joytel-alert')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Category</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>

                    <li class="breadcrumb-item active text-black">Category Edit</li>
                </ol>
            </div>
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('blog.category.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>

        <form action="{{ route('blog.category.update', $category->id) }}" method="POST">
            @csrf
            @method('patch')
            <div class="row">
                <div class="col-xxl-12">
                    <div data-table data-table-rows-per-page="8" class="card">
                        <div class="card-header border-light justify-content-between">
                            <h5 class="fw-semibold text-black">{{ $category->cat_name . ' Edit' }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <x-form-input label="Category Name" name="cat_name" placeholder="Enter Category Name"
                                    :isrequired="true" :value="$category->cat_name" />
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex gap-2 justify-content-end">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

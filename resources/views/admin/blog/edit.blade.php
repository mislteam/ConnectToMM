@extends('admin.layouts.index')
@section('title', 'Joytel eSim')
@section('content')
    @include('components.alert')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Blog</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>

                    <li class="breadcrumb-item active text-black">Blog Edit</li>
                </ol>
            </div>
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('blog.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>

        <form action="{{ route('blog.update', $blog->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('patch')
            <div class="row">
                <div class="col-xxl-12">
                    <div data-table data-table-rows-per-page="8" class="card">
                        <div class="card-header border-light justify-content-between">
                            <h5 class="fw-semibold text-black">{{ $blog->title . ' Edit' }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <x-form-input label="Blog Title" name="title" placeholder="Enter Blog Title"
                                    :isrequired="true" :value="$blog->title" />

                                <x-form-text-area :isrequired="true" label="Description" name="desc"
                                    placeholder="Enter Item Description" :value="$blog->desc" />

                                <div class="form-group row mb-3">
                                    <label for="category_id" class="col-sm-2 col-form-label">
                                        <strong>Category </strong><span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-10">
                                        <select class="form-control" name="category_id" id="category_id">
                                            <option value="">Select Category</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ $category->id === $blog->category_id ? 'selected' : '' }}>
                                                    {{ $category->cat_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <x-form.filepond-input name="image" label="Image" :isrequired="true">
                                    @if ($blog->image)
                                        <a target="_blank" href="{{ asset('blog/' . $blog->image) }}"
                                            alt="blog-image">{{ asset('blog/' . $blog->image) }}</a>
                                    @endif
                                </x-form.filepond-input>
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

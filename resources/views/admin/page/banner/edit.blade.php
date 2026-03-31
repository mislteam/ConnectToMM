@extends('admin.layouts.index')
@section('title', 'Banner Page')
@section('content')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Banners</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                    <li class="breadcrumb-item active text-black">Edit Banner</li>
                </ol>
            </div>
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('page.banner.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <form action="{{ route('page.banner.update', $banner->id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('patch')
                    <div class="card">
                        <div class="card-header d-block p-3">
                            <h4 class="card-title mb-1">Edit {{ $banner->page }}</h4>
                        </div>
                        <div class="card-body">
                            <x-form-input label="Page" name="page" placeholder="Enter Page Name" :value="$banner->page"
                                required />

                            <x-form-input label="Main Title" name="title" placeholder="Enter Title" :value="$banner->title"
                                required />

                            <x-form-input label="Subtitle" name="subtitle" placeholder="Enter Subtitle" :value="$banner->subtitle"
                                required />

                            <input type="hidden" name="banner_type" value="{{ $banner->banner_type }}">

                            <x-form.filepond-input name="image" label="Banner Image">
                                <div class="mt-3">
                                    @if ($banner->image)
                                        <a target="_blank" href="{{ asset('banner/' . $banner->image) }}"
                                            alt="banner-image">{{ asset('banner/' . $banner->image) }}</a>
                                    @endif
                                </div>
                            </x-form.filepond-input>
                        </div>

                        <div class="card-footer">
                            <div class="d-flex gap-2 justify-content-end">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

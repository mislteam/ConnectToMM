@extends('admin.layouts.index')
@section('title', 'Section Edit')
@section('content')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Section</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                    <li class="breadcrumb-item active text-black">Edit Section</li>
                </ol>
            </div>
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('page.catalog.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <form action="{{ route('page.section.update', $section->id) }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    @method('patch')
                    <div class="card">
                        <div class="card-header d-block p-3">
                            <h4 class="card-title mb-1">Edit Section</h4>
                        </div>
                        <div class="card-body">
                            <input type="hidden" name="section_key" value="{{ $section->section_key }}">

                            <input type="hidden" name="eyebrow_text" value="{{ $section->eyebrow_text }}">

                            <x-form-input label="Heading" name="title" placeholder="Enter Heading" :value="$section->title"
                                required />

                            <x-form-input label="Description" name="description" placeholder="Enter Description"
                                :value="$section->description" required />

                            @php
                                $hasImage = config('sections.' . $section->section_key . '.hasImg');
                            @endphp
                            @if ($hasImage)
                                <x-form.filepond-input name="image" label="Image">
                                    <div class="mt-3">
                                        @if ($section->image)
                                            <a target="_blank" href="{{ asset('section/' . $section->image) }}"
                                                alt="item-image">{{ asset('section/' . $section->image) }}</a>
                                        @endif
                                    </div>
                                </x-form.filepond-input>
                            @endif
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

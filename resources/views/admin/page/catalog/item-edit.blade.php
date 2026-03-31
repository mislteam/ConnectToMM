@extends('admin.layouts.index')
@section('title', 'Item Edit')
@section('content')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Item</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                    <li class="breadcrumb-item active text-black">Edit Item</li>
                </ol>
            </div>
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('page.catalog.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <form action="{{ route('page.item.update', $item->id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('patch')
                    <div class="card">
                        <div class="card-header d-block p-3">
                            <h4 class="card-title mb-1">Create Items</h4>
                        </div>
                        <div class="card-body">
                            <x-form-input :value="$item->title" label="Heading" name="title" placeholder="Enter Heading"
                                required />

                            <x-form-input :value="$item->description" label="Description" name="description"
                                placeholder="Enter Description" required />

                            <x-form-input :value="$item->button_text" label="Button Text" name="button_text"
                                placeholder="Enter Button Text" required />

                            <x-form-input :value="$item->button_url" label="Button Url" name="button_url" placeholder="Enter Url"
                                required />

                            <input type="hidden" name="section_id" value="{{ $section->id }}">

                            <x-form.filepond-input name="image" label="Image" :isrequired="true">
                                <div class="mt-3">
                                    @if ($item->image)
                                        <a target="_blank" href="{{ asset('section/' . $item->image) }}"
                                            alt="item-image">{{ asset('section/' . $item->image) }}</a>
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

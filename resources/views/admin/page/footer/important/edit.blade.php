@extends('admin.layouts.index')
@section('title', 'Footer Page')
@section('content')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Footer</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                    <li class="breadcrumb-item active text-black">Footer</li>
                </ol>
            </div>
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('footer.important.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <form action="{{ route('footer.important.update', $link->id) }}" method="post">
                    @csrf
                    @method('patch')
                    <div class="card">
                        <div class="card-header d-block p-3">
                            <h4 class="card-title mb-1">Edit Important Link</h4>
                        </div>
                        <div class="card-body">
                            <x-form-input label="Text" name="text" :value="$link->text" placeholder="Enter Text"
                                required />

                            <x-form-input label="Link" name="link" :value="$link->link" placeholder="Enter Link"
                                required />

                            <input type="hidden" name="type" value="{{ $link->type }}">
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

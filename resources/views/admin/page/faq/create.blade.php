@extends('admin.layouts.index')
@section('title', 'FAQ Create')
@section('content')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">FAQs</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                    <li class="breadcrumb-item active text-black">Create FAQ</li>
                </ol>
            </div>
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('page.faq.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <form action="{{ route('page.faq.store') }}" method="post">
                    @csrf
                    <div class="card">
                        <div class="card-header d-block p-3">
                            <h4 class="card-title mb-1">Create FAQ</h4>
                        </div>
                        <div class="card-body">
                            <x-form-input label="Title" name="title" placeholder="Enter Title" required />

                            <x-form-text-area label="Description" name="description" placeholder="Enter Description" />
                        </div>
                        <div class="card-footer">
                            <div class="d-flex gap-2 justify-content-end">
                                <button type="submit" class="btn btn-primary">Create</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

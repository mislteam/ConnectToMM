@extends('admin.layouts.index')
@section('title', 'Currency & Profit')
@section('content')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Edit Currency</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                    <li class="breadcrumb-item active text-black">{{ $currency->name }}</li>
                </ol>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="d-flex gap-1">
                    <a href="{{ route('currency.index') }}" class="btn btn-dark ms-1">Back</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-block">
                        <h4 class="card-title text-black">Edit {{ Str::upper($currency->name) }}</h4>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('currency.update',$currency->id) }}" method="POST">
                            @csrf
                            @method('patch')
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="col-form-label">{{ Str::upper($currency->name) }}</label>
                                    </div>
                                </div>
                                <div class="col-lg-9">
                                    <div class="mb-3">
                                        <input type="text" name="value" class="form-control"
                                            required="" value="{{ $currency->value }}">
                                        <div class="my-1">
                                            @error('value')
                                                <small class="text-danger"></small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2 mb-4 d-flex gap-2 justify-content-end">
                                <button type="submit" class="btn btn-dark">Update</button>
                            </div>
                        </form>
                    </div> <!-- end card-body-->
                </div> <!-- end card-->
            </div><!-- end col -->
        </div><!-- end row -->
    </div>
@endsection

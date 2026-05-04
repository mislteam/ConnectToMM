@extends('admin.layouts.index')
@section('title', 'General Setting')
@section('content')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                @php
                    $name = null;
                    if ($type === 'title' || $type === 'logo') {
                        $name = $data[$type]->name;
                    } elseif ($type === 'joytel') {
                        $name = 'JOYTEL';
                    } elseif ($type === 'roam') {
                        $name = 'ROAM';
                    }
                @endphp
                <h4 class="fs-sm fw-bold m-0 text-black">Edit {{ $name }}</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                    <li class="breadcrumb-item active text-black">{{ $name }}</li>
                </ol>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="d-flex gap-1">
                    <a href="{{ route('generalIndex') }}" class="btn btn-dark ms-1">Back</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-block">
                        <h4 class="card-title text-black">General Setting</h4>
                    </div> <!-- end card-header -->

                    <div class="card-body">
                        <form action="{{ route('generalUpdate', ['type' => $type]) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('patch')
                            <div class="row">

                                @if ($type == 'logo')
                                    <fieldset class="border rounded-2 px-3 py-2 mb-4">
                                        <legend class="float-none w-auto px-2 col-form-label text-black fw-semibold"
                                            style="min-width: 80px; max-width: 100%; width: fit-content;">Upload logo image
                                        </legend>
                                        <div class="row">
                                            <div class="col-lg-3">
                                                <div class="mb-3">
                                                    <label class="col-form-label">Logo<span
                                                            class="text-danger">*</span></label>
                                                </div>
                                            </div>
                                            <div class="col-lg-9">
                                                <div class="mb-3">
                                                    <div class="filepond-uploader">
                                                        <input type="file" class="filepond filepond-input-multiple"
                                                            multiple name="file" data-allow-reorder="true"
                                                            data-max-file-size="3MB" data-max-files="5"
                                                            accept="image/png,image/jpg,image/jpeg">
                                                    </div>
                                                    <div class="mt-3">
                                                        @if ($settings['logo'])
                                                            <a target="_blank"
                                                                href="{{ asset('general/logo/' . $settings['logo']->value) }}"
                                                                alt="sim_img">{{ asset('general/logo/' . $settings['logo']->value) }}</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                @elseif($type == 'title')
                                    <div class="col-lg-3">
                                        <div class="mb-3">
                                            <label class="col-form-label">Title</label>
                                        </div>
                                    </div>
                                    <div class="col-lg-9">
                                        <div class="mb-3">
                                            <input type="text" name="title" class="form-control"
                                                placeholder="Enter Title" required="" value="{{ $data[$type]->value }}">
                                            <div class="my-1">
                                                @error('title')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                @elseif (in_array($type, ['joytel', 'roam']))
                                    <x-general-sim-edit :value="$data[$type . '_title']->value" :type="$type" :image="$data[$type . '_logo']" />
                                @endif
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

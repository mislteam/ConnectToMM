@extends('admin.layouts.index')
@section('title', 'All Admin')
@section('content')
    <div class="container-fluid">
        @include('components.alert')
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Create Admin</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                    <li class="breadcrumb-item active text-black">Create Admin</li>
                </ol>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="d-flex gap-1">
                    <a href="{{ url()->previous() }}" class="btn btn-primary ms-1">Back</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-block">
                        <h4 class="card-title text-black">Account Information</h4>
                    </div> <!-- end card-header -->

                    <div class="card-body">
                        <form action="{{ route('admin.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="col-form-label">Profile</label>
                                    </div>
                                </div>
                                <div class="col-lg-9">
                                    <div class="mb-3">
                                        <div class="avatar-xxl">
                                            <input type="file" class="filepond filepond-input-circle" name="image"
                                                accept="image/png, image/jpeg">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="col-form-label">Name <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-lg-9">
                                    <div class="mb-3">
                                        <input type="text" name="name" class="form-control"
                                            placeholder="Enter Admin Name" required="">
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="col-form-label">E-Mail Address <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-lg-9">
                                    <div class="mb-3">
                                        <input type="email" name="email" class="form-control"
                                            placeholder="Enter Admin E-Mail Address" required="">
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="col-form-label">Role <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-lg-9">
                                    <div class="mb-3">
                                        <select class="form-select" name="role" required>
                                            <option value="">Select Admin Role</option>
                                            <option value="editor">Editor</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="col-form-label">Password <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-lg-9">
                                    <div class="mb-3">
                                        <div class="input-group">
                                            <input type="password" name="password" id="password"
                                                class="form-control @error('password') is-invalid @enderror"
                                                placeholder="Enter password" required>
                                            @error('password')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                            <div class="input-group-text password-eye" data-password="false">
                                                <i class="ti ti-eye d-none"></i>
                                                <i class="ti ti-eye-closed d-block"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="col-form-label">Confirm Password <span
                                                class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-lg-9">
                                    <div class="mb-3">
                                        <div class="input-group">
                                            <input type="password" id="confirm-password" name="password_confirmation"
                                                class="form-control @error('password_confirmation') is-invalid @enderror"
                                                placeholder="Enter Confirm password" required>
                                            @error('password_confirmation')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                            <div class="input-group-text password-eye" data-password="false">
                                                <i class="ti ti-eye d-none"></i>
                                                <i class="ti ti-eye-closed d-block"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2 mb-4 d-flex gap-2 justify-content-end">
                                <button type="submit" class="btn btn-primary">Create Admin</button>
                            </div>
                        </form>
                    </div> <!-- end card-body-->
                </div> <!-- end card-->
            </div><!-- end col -->

        </div><!-- end row -->
    </div>
    <!-- container -->
    <script>
        function togglePasswordVisibility(inputId, iconContainer) {
            const passwordField = document.getElementById(inputId);
            const eyeIcon = iconContainer;
            const eyeOpen = eyeIcon.querySelector('.ti-eye');
            const eyeClosed = eyeIcon.querySelector('.ti-eye-closed');

            eyeIcon.addEventListener('click', function() {
                const isPasswordVisible = eyeIcon.getAttribute('data-password') === 'true';

                if (isPasswordVisible) {
                    // Hide password and show closed eye
                    passwordField.type = 'password';
                    eyeIcon.setAttribute('data-password', 'false');
                    eyeOpen.classList.add('d-none');
                    eyeClosed.classList.remove('d-none');
                } else {
                    // Show password and open eye
                    passwordField.type = 'text';
                    eyeIcon.setAttribute('data-password', 'true');
                    eyeOpen.classList.remove('d-none');
                    eyeClosed.classList.add('d-none');
                }
            });
        }

        // Call the togglePasswordVisibility function for both password and confirm password fields
        togglePasswordVisibility('password', document.querySelector('#password + .input-group-text'));
        togglePasswordVisibility('confirm-password', document.querySelector('#confirm-password + .input-group-text'));
    </script>
@endsection

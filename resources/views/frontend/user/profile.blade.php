@extends('frontend.layouts.index')
@section('title', 'Connect To Myanmar')
@section('content')
    @include('components.alert')
    <style>
        .profile-edit-form .password-wrap {
            position: relative;
        }

        .profile-edit-form .password-toggle {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #7a7a7a;
            font-size: 14px;
            font-weight: 600;
            padding: 0;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .profile-edit-form .password-toggle:focus {
            outline: none;
        }
    </style>
    <!-- Sub-Banner -->
    @php
        $file = get_banner('my_account');
        $image = $file !== null ? 'banner/' . $file : 'assets/images/default-banner.png';

    @endphp
    <div class="sub-banner" style="background-image: url({{ asset($image) }})">
        <section class="banner-section">
            <figure class="mb-0 bgshape">
                <img src="./assets/images/homebanner-bgshape.png" alt="" class="img-fluid">
            </figure>
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="banner_content">
                            <h1>{{ $banner->title ?? '' }}</h1>
                            <p>{{ $banner->subtitle ?? '' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="box">
            <span class="mb-0 text-size-16">Home</span><span class="mb-0 text-size-16 dash">-</span><span
                class="mb-0 text-size-16 box_span">{{ $banner->page ?? '' }}</span>
        </div>
        <div class="row m-0">
            <div class="col-12 bg-light">
                <div class="container">
                    <article class="card card-out-of-container border-0 bg-transparent py-4">
                        <!-- Card Body -->
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start text-left">
                                <div class="d-flex justify-content-start align-items-center">
                                    <img src="{{ auth()->user()->profile_image
                                        ? asset('storage/profile_images/' . auth()->user()->profile_image)
                                        : asset('assets/images/user-3.jpg') }}"
                                        alt="avatar-2" class="rounded-circle me-2"
                                        style="
                                                width: 70px;
                                                height: 70px;
                                                object-fit: cover;
                                            ">
                                    <div class="ml-2">
                                        <h4 class="text-nowrap fw-bold mb-1">
                                            {{ auth()->user()->name }}
                                        </h4>
                                        <span class="fw-medium text-size-16">
                                            {{ auth()->user()->email }}
                                        </span>
                                    </div>
                                </div>
                                <div class="d-flex gap-3">
                                    <a class="btn btn-primary mr-2" href="#" data-bs-toggle="modal"
                                        data-bs-target="#userEditModal">Edit Profile</a>
                                    <a class="btn btn-primary" href="#" data-bs-toggle="modal"
                                        data-bs-target="#pwdEditModal">Change Password</a>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            </div> <!-- end col-->
        </div> <!-- end row-->
    </div>
    <!--About-->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <h4 class="mb-3">Order History</h4>
                    <div class="table-responsive">
                        <table class="table table-custom table-centered table-select table-hover w-100 mb-0">
                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                <tr class="text-uppercase fs-xxs">
                                    <th data-table-sort>Order ID</th>
                                    <th data-table-sort data-column="date">Date</th>
                                    <th data-table-sort>Product Name</th>
                                    <th data-table-sort>Amount</th>
                                    <th data-table-sort>Payment Method</th>
                                    <th data-table-sort data-column="payment-status">Status</th>
                                    <th class="text-center" style="width: 1%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><a href="#" class="text-body">#JOYTEL-20100</a></td>
                                    <td>9 May, 2025 <small class="text-muted">10:10 AM</small></td>
                                    <td>eSim-AIS-SIM2FLY399</td>
                                    <td>$129.45</td>
                                    <td>UAB Pay</td>
                                    <td class="text-success fw-semibold"><i class="ti ti-point-filled fs-sm"></i> Paid
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#viewOrderModal"
                                                class="btn btn-light btn-icon btn-sm rounded-circle"><i
                                                    class="fa-solid fa-eye"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><a href="#" class="text-body">#ROAM-20100</a></td>
                                    <td>9 May, 2025 <small class="text-muted">10:10 AM</small></td>
                                    <td>eSim-AIS-SIM2FLY399</td>
                                    <td>$129.45</td>
                                    <td>Direct Transfer</td>
                                    <td class="text-success fw-semibold"><i class="ti ti-point-filled fs-sm"></i> Paid
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('customer.order.detail') }}"
                                                class="btn btn-light btn-icon btn-sm rounded-circle"><i
                                                    class="fa-solid fa-eye"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> <!-- end row-->
        </div>
    </section>

    <div class="modal fade" id="userEditModal" tabindex="-1" aria-labelledby="userEditModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form
                action="{{ route('frontend.customer.edit', ['customer' => auth()->user()->id, 'edit_type' => 'profile']) }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userEditModalLabel">Edit Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        @csrf
                        <div class="mb-3">
                            <label for="name" class="col-form-label">Name:</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ auth()->user()->name }}">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="col-form-label">Email:</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="{{ auth()->user()->email }}">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="col-form-label">Photo:</label>
                            <input type="file" accept="image/jpg,image/jpeg,image/png" class="form-control"
                                id="file" name="file">
                            @if (auth()->user()->profile_image)
                                <div class="mt-2">
                                    <a class="text-primary" target="_blank"
                                        href="{{ asset('storage/profile_images/' . auth()->user()->profile_image) }}">{{ asset('storage/profile_images/' . auth()->user()->profile_image) }}</a>
                                </div>
                            @endif
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="pwdEditModal" tabindex="-1" aria-labelledby="pwdEditModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="profile-edit-form"
                action="{{ route('frontend.customer.edit', ['customer' => auth()->user()->id, 'edit_type' => 'password']) }}"
                method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pwdEditModalLabel">Change Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <div class="form-group mb-3">
                            <label for="password">Old Password <span class="required text-danger"
                                    aria-hidden="true">*</span></label>
                            <div class="password-wrap">
                                <input class="input-field form-control pr-5" type="password" id="old_password"
                                    name="old_password" placeholder="Enter Old Password" autocomplete="old-password">
                                <button type="button" class="password-toggle" data-target="#old_password"
                                    data-label-show="Show" data-label-hide="Hide" aria-label="Show Old password">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                            @error('old_password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="password">New Password <span class="required text-danger"
                                    aria-hidden="true">*</span></label>
                            <div class="password-wrap">
                                <input class="input-field form-control pr-5" type="password" id="new_password"
                                    name="new_password" placeholder="Enter New Password" autocomplete="new-password">
                                <button type="button" class="password-toggle" data-target="#new_password"
                                    data-label-show="Show" data-label-hide="Hide" aria-label="Show new password">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                            @error('new_password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="password">Confirm Password <span class="required text-danger"
                                    aria-hidden="true">*</span></label>
                            <div class="password-wrap">
                                <input class="input-field form-control pr-5" type="password" id="confirm_password"
                                    name="confirm_password" placeholder="Enter Confirm Password"
                                    autocomplete="new-password">
                                <button type="button" class="password-toggle" data-target="#confirm_password"
                                    data-label-show="Show" data-label-hide="Hide" aria-label="Show confirm password">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                            @error('confirm_password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var storageKey = 'profile_password_visibility';

            document.querySelectorAll('.password-toggle').forEach(function(button) {
                var targetSelector = button.getAttribute('data-target');
                var input = document.querySelector(targetSelector);

                if (!input) {
                    return;
                }

                var savedVisibility = sessionStorage.getItem(storageKey + targetSelector);
                if (savedVisibility === 'visible') {
                    input.setAttribute('type', 'text');
                    button.innerHTML = '<i class="fa-regular fa-eye-slash"></i>';
                    button.setAttribute('aria-label', 'Hide password');
                }

                button.addEventListener('click', function() {
                    var isPassword = input.getAttribute('type') === 'password';

                    input.setAttribute('type', isPassword ? 'text' : 'password');
                    button.innerHTML = isPassword ?
                        '<i class="fa-regular fa-eye-slash"></i>' :
                        '<i class="fa-regular fa-eye"></i>';
                    button.setAttribute('aria-label', isPassword ? 'Hide password' :
                        'Show password');
                    sessionStorage.setItem(storageKey + targetSelector, isPassword ? 'visible' :
                        'hidden');
                });
            });
        });
    </script>
@endsection

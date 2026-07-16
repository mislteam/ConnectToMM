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

        .order-detail-link {
            color: #0d6efd;
            text-decoration: underline;
            font-weight: 600;
        }

        .order-detail-link:hover,
        .order-detail-link:focus {
            color: #084298;
        }

        .order-history-shell {
            max-width: 1080px;
            margin: 0 auto;
        }

        .order-history-title {
            color: #1d2a7a;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 22px;
        }

        .order-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 18px;
        }

        .order-tab {
            flex: 0 1 190px;
            border: 1px solid #cfd7ea;
            background: #fff;
            color: #173f93;
            padding: 13px 20px;
            font-size: 15px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 4px;
            box-shadow: 0 4px 14px rgba(13, 34, 79, 0.05);
            transition: .2s ease;
            outline: none;
        }

        .order-tab:hover,
        .order-tab:focus {
            color: #173f93;
            border-color: #214fa6;
            background: #f8fbff;
            box-shadow: none;
            outline: none;
        }

        .order-tab:focus-visible,
        .order-tab:active {
            outline: none;
            box-shadow: none;
        }

        .order-tab.is-active {
            background: linear-gradient(180deg, #214fa6 0%, #173f93 100%);
            border-color: #173f93;
            color: #fff;
            box-shadow: 0 10px 24px rgba(23, 63, 147, 0.22);
        }

        .order-history-panel {
            display: none;
        }

        .order-history-panel.is-active {
            display: block;
        }

        .order-provider-card {
            background: #fff;
            border: 1px solid #ececf4;
            border-radius: 0;
            box-shadow: none;
            padding: 18px 18px 12px;
            margin-bottom: 22px;
        }

        .order-provider-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
        }

        .order-provider-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
        }

        .order-provider-title::before {
            content: "";
            width: 4px;
            height: 24px;
            border-radius: 0;
            background: currentColor;
        }

        .provider-roam,
        .provider-joytel {
            color: #173f93;
        }

        .order-provider-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #5f6b89;
            font-size: 16px;
            text-align: right;
        }

        .order-provider-link a {
            color: #173f93;
            font-weight: 800;
            text-decoration: underline;
        }

        .order-provider-link a:hover,
        .order-provider-link a:focus {
            text-decoration: underline;
        }

        .order-search {
            position: relative;
            width: min(100%, 270px);
        }

        .order-search input {
            width: 100%;
            height: 42px;
            border-radius: 10px;
            border: 1px solid #e3e5ef;
            background: #fff;
            padding: 10px 42px 10px 14px;
            color: #636b80;
            font-size: 14px;
        }

        .order-search i {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #bcc3d4;
            font-size: 15px;
        }

        .order-history-table {
            margin-bottom: 0;
        }

        .order-history-table thead th {
            border-bottom: 1px solid #ececf4;
            color: #151515;
            font-size: 12px;
            font-weight: 700;
            padding: 14px 10px;
            white-space: nowrap;
        }

        .order-history-table tbody td {
            border-color: #f0f1f6;
            color: #313749;
            font-size: 14px;
            padding: 16px 10px;
            vertical-align: middle;
        }

        .order-history-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .order-muted-time {
            color: #9aa2b5;
            font-size: 12px;
            font-weight: 500;
        }

        .order-status {
            font-weight: 700;
        }

        .order-status.text-warning {
            color: #ff9c1a !important;
        }

        .order-status.text-success {
            color: #1ea85d !important;
        }

        .order-status.text-info {
            color: #1f9ec9 !important;
        }

        .order-status.text-danger {
            color: #e45757 !important;
        }

        .order-action-group {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .order-pagination-wrap {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            padding: 18px 0 6px;
            color: #6c768f;
            font-size: 14px;
        }

        .order-pagination-wrap .pagination {
            margin-bottom: 0;
        }

        .order-pagination-wrap nav {
            display: block !important;
        }

        .order-pagination-wrap nav .small.text-muted {
            display: none;
        }

        .order-pagination-wrap nav .d-none.flex-sm-fill.d-sm-flex {
            display: block !important;
        }

        .order-pagination-wrap nav .d-none.flex-sm-fill.d-sm-flex>div:last-child {
            display: flex;
            justify-content: flex-end;
        }

        .order-pagination-wrap nav .page-item+.page-item .page-link {
            margin-left: 0;
        }

        .order-pagination-wrap .page-link {
            color: #173f93;
            min-width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 12px;
            border-color: #d8dfef;
            background: #fff;
        }

        .order-pagination-wrap .page-item.active .page-link {
            background-color: #173f93;
            border-color: #173f93;
            color: #fff;
            box-shadow: 0 6px 16px rgba(23, 63, 147, 0.15);
        }

        .order-pagination-wrap .page-item.disabled .page-link {
            color: #9aa6c1;
            background: #f7f9fd;
            border-color: #d8dfef;
        }

        .order-pay-btn {
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 700;
        }

        .joytel-usage-result {
            border: 1px solid #e7ebf3;
            border-radius: 8px;
            background: #f9fbff;
            padding: 12px;
        }

        .joytel-usage-result table {
            margin-bottom: 0;
        }

        .joytel-usage-result th,
        .joytel-usage-result td {
            font-size: 13px;
            vertical-align: top;
            word-break: break-word;
        }

        .provider-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 12px 4px 4px;
            color: #8690a8;
            font-size: 14px;
        }

        .provider-footer strong {
            color: inherit;
            font-weight: 700;
        }

        .provider-footer-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            text-decoration: none;
        }

        .provider-footer-link.roam-link {
            color: #1ea85d;
        }

        .provider-footer-link.joytel-link {
            color: #8a35ff;
        }

        .order-empty-row td {
            padding: 42px 16px;
        }

        .order-empty-state {
            max-width: 360px;
            margin: 0 auto;
            text-align: center;
        }

        .order-empty-state h6 {
            color: #1d2a7a;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .order-empty-state p {
            color: #8a92a6;
            font-size: 14px;
            margin-bottom: 0;
        }

        .order-row-hidden {
            display: none;
        }

        .profile-summary {
            gap: 20px;
        }

        .profile-user {
            gap: 14px;
            min-width: 0;
        }

        .profile-user-copy {
            min-width: 0;
        }

        .profile-user-copy h4,
        .profile-user-copy span {
            overflow-wrap: anywhere;
        }

        .profile-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .profile-actions .btn {
            white-space: nowrap;
        }

        @media (max-width: 991.98px) {
            .profile-summary {
                align-items: flex-start !important;
                flex-direction: column;
            }

            .profile-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .order-history-shell {
                max-width: 100%;
            }

            .order-provider-card {
                padding: 16px 14px 10px;
            }

            .order-history-table thead th {
                font-size: 11px;
                padding: 12px 8px;
            }

            .order-history-table tbody td {
                font-size: 13px;
                padding: 14px 8px;
            }
        }

        @media (max-width: 767.98px) {
            .order-history-title {
                font-size: 1.6rem;
            }

            .profile-user {
                align-items: flex-start !important;
            }

            .profile-user img {
                width: 58px !important;
                height: 58px !important;
            }

            .profile-user-copy h4 {
                font-size: 1.2rem;
                white-space: normal !important;
            }

            .profile-user-copy span {
                font-size: 14px;
            }

            .profile-actions {
                flex-direction: column;
            }

            .profile-actions .btn {
                width: 100%;
                margin-right: 0 !important;
            }

            .order-tab {
                flex: 1 1 100%;
                border-radius: 4px !important;
            }

            .order-provider-head,
            .provider-footer {
                flex-direction: column;
                align-items: flex-start;
            }

            .order-search {
                width: 100%;
            }

            .order-provider-title {
                flex-wrap: wrap;
            }

            .order-provider-card {
                padding: 14px 12px 10px;
                border-radius: 0;
            }

            .order-pagination-wrap {
                flex-direction: column;
                align-items: flex-start;
            }

            .order-pagination-wrap nav .d-flex.justify-content-between.flex-fill.d-sm-none {
                display: block !important;
            }

            .order-pagination-wrap nav .d-flex.justify-content-between.flex-fill.d-sm-none .pagination {
                width: 100%;
                justify-content: space-between;
            }

            .order-pagination-wrap .page-link {
                min-width: 44px;
                height: 44px;
            }

            .order-history-table,
            .order-history-table tbody,
            .order-history-table tr,
            .order-history-table td {
                display: block;
                width: 100%;
            }

            .order-history-table thead {
                display: none;
            }

            .order-history-table tbody tr {
                border: 1px solid #edf0f7;
                border-radius: 14px;
                padding: 10px 12px;
                margin-bottom: 12px;
                box-shadow: 0 10px 22px rgba(17, 24, 39, 0.04);
            }

            .order-history-table tbody tr:last-child {
                margin-bottom: 0;
            }

            .order-history-table tbody td {
                border: 0;
                padding: 8px 0;
                text-align: left !important;
            }

            .order-history-table tbody td::before {
                content: attr(data-label);
                display: block;
                margin-bottom: 4px;
                color: #8a92a6;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .04em;
            }

            .order-history-table tbody td[colspan]::before {
                display: none;
            }

            .order-action-group {
                justify-content: flex-start;
            }

            .provider-footer {
                padding-top: 4px;
            }

            .order-empty-row td {
                padding: 26px 8px;
            }
        }

        @media (min-width: 768px) and (max-width: 991.98px) {
            .order-tabs {
                gap: 10px;
            }

            .order-tab {
                flex: 1 1 calc(50% - 10px);
                border-radius: 4px !important;
            }
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
                            <div class="d-flex justify-content-between align-items-start text-left profile-summary">
                                <div class="d-flex justify-content-start align-items-center profile-user">
                                    <img src="{{ auth()->user()->profile_image
                                        ? asset('storage/profile_images/' . auth()->user()->profile_image)
                                        : asset('assets/images/user-3.jpg') }}"
                                        alt="avatar-2" class="rounded-circle me-2"
                                        style="
                                                width: 70px;
                                                height: 70px;
                                                object-fit: cover;
                                            ">
                                    <div class="ml-2 profile-user-copy">
                                        <h4 class="text-nowrap fw-bold mb-1">
                                            {{ auth()->user()->name }}
                                        </h4>
                                        <span class="fw-medium text-size-16">
                                            {{ auth()->user()->email }}
                                        </span>
                                    </div>
                                </div>
                                <div class="profile-actions">
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
                    @php
                        $roamOrderGroups = $roamOrderGroups ?? null;
                        $joytelOrderGroups = $joytelOrderGroups ?? null;
                        $activeOrderTab = $activeOrderTab ?? 'roam';
                    @endphp

                    <div class="order-history-shell">
                        <h4 class="order-history-title">Order History</h4>

                        <div class="order-tabs" role="tablist" aria-label="Order providers">
                            <button type="button" class="order-tab {{ $activeOrderTab === 'roam' ? 'is-active' : '' }}"
                                data-target="roam">
                                <i class="ti ti-credit-card"></i>
                                <span>Roam Orders</span>
                            </button>
                            <button type="button" class="order-tab {{ $activeOrderTab === 'joytel' ? 'is-active' : '' }}"
                                data-target="joytel">
                                <i class="ti ti-clock-hour-4"></i>
                                <span>Joytel Orders</span>
                            </button>
                        </div>

                        <div class="order-history-panel {{ $activeOrderTab === 'roam' ? 'is-active' : '' }}"
                            data-panel="roam">
                            <div class="order-provider-card">
                                <div class="order-provider-head">
                                    <h5 class="order-provider-title provider-roam">
                                        <span>Roam Orders</span>
                                    </h5>
                                    <div class="order-provider-link">
                                        <a href="https://globalesimstore.com/E" target="_blank"
                                            rel="noopener noreferrer">Check Roam Orders</a>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table order-history-table">
                                        <thead>
                                            <tr>
                                                <th>ORDER ID</th>
                                                <th>PRODUCT NAME</th>
                                                <th>STATUS</th>
                                                <th class="text-center">ACTIONS</th>
                                            </tr>
                                        </thead>
                                        <tbody data-order-table="roam-only">
                                            @forelse ($roamOrderGroups as $group)
                                                <tr data-order-row
                                                    data-search="{{ strtolower(trim(($group['outer_order_id'] ?? '') . ' ' . ($group['product_name'] ?? '') . ' ' . ($group['payment_method'] ?? '') . ' ' . ($group['status_label'] ?? ''))) }}">
                                                    <td data-label="Order ID">{{ $group['outer_order_id'] ?? '-' }}</td>
                                                    <td data-label="Product Name">
                                                        @php($productName = $group['product_name'] ?? '-')
                                                        {!! nl2br(e($productName)) !!}
                                                    </td>
                                                    <td data-label="Status"
                                                        class="order-status {{ $group['status_class'] ?? '' }}">
                                                        {{ $group['status_label'] ?? '-' }}
                                                    </td>
                                                    <td data-label="Actions">
                                                        <div class="order-action-group">
                                                            <a href="{{ route('customer.roam.order.detail', ['outerOrderId' => $group['outer_order_id']]) }}"
                                                                class="order-detail-link">Detail</a>
                                                            @if (!empty($group['can_pay']))
                                                                <a href="{{ route('roam.payment.show', ['outerOrderId' => $group['outer_order_id']]) }}"
                                                                    class="btn btn-primary btn-sm order-pay-btn">Pay</a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr class="order-empty-row">
                                                    <td colspan="4">
                                                        <div class="order-empty-state">
                                                            <h6>No Roam orders yet</h6>
                                                            <p>Your Roam order history will appear here after checkout.</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                @if ($roamOrderGroups && $roamOrderGroups->total() > 0)
                                    <div class="order-pagination-wrap">
                                        <div>
                                            Showing {{ $roamOrderGroups->firstItem() ?? 0 }} to
                                            {{ $roamOrderGroups->lastItem() ?? 0 }} of {{ $roamOrderGroups->total() }}
                                            Roam orders
                                        </div>
                                        <div>
                                            {{ $roamOrderGroups->appends(array_merge(request()->except('roam_page'), ['orders_tab' => 'roam']))->links('pagination::bootstrap-5') }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="order-history-panel {{ $activeOrderTab === 'joytel' ? 'is-active' : '' }}"
                            data-panel="joytel">
                            <div class="order-provider-card">
                                <div class="order-provider-head">
                                    <h5 class="order-provider-title provider-joytel">
                                        <span>Joytel Orders</span>
                                    </h5>
                                    <div class="order-provider-link">
                                        <a href="#" class="joytel-usage-check-btn" data-bs-toggle="modal"
                                            data-bs-target="#joytelUsageModal" data-manual="1" data-service-type="esim"
                                            data-outer-order-id="" data-items="[]">
                                            Check Joytel Orders
                                        </a>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table order-history-table">
                                        <thead>
                                            <tr>
                                                <th>ORDER ID</th>
                                                <th>PRODUCT NAME</th>
                                                {{-- <th>AMOUNT</th>
                                                <th>DATE</th>
                                                <th>PAYMENT METHOD</th> --}}
                                                <th>SERVICE TYPE</th>
                                                <th>STATUS</th>
                                                <th>ACTIONS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($joytelOrderGroups as $group)
                                                <tr>
                                                    <td data-label="Order ID">{{ $group['order_id'] ?? '-' }}</td>
                                                    <td data-label="Product Name">
                                                        {!! nl2br(e($group['product_name'] ?? '-')) !!}
                                                    </td>
                                                    {{-- <td data-label="Amount">
                                                        {{ number_format((float) ($group['amount'] ?? 0), 2) }}
                                                    </td>
                                                     <td data-label="Date">
                                                        {{ optional($group['created_at'])->format('d M Y, h:i A') ?? '-' }}
                                                    </td>
                                                    <td data-label="Payment Method">
                                                        {{ $group['payment_method'] ?: $group['service_type'] ?? '-' }}
                                                    </td> --}}
                                                    <td data-label="Service Method">
                                                        {{ $group['service_type'] ?? '-' }}
                                                        {{ $group['order_type'] ?? '-' }}
                                                    </td>
                                                    <td data-label="Status"
                                                        class="order-status {{ $group['status_class'] ?? '' }}">
                                                        {{ $group['status_label'] ?? '-' }}
                                                    </td>
                                                    <td data-label="Actions">
                                                        <div class="order-action-group">
                                                            <a href="{{ route('customer.order.detail', ['outerOrderId' => $group['outer_order_id'] ?? $group['order_id']]) }}"
                                                                class="order-detail-link">Detail</a>
                                                            @if (!empty($group['can_pay']))
                                                                <a href="{{ route('joytel.payment.show', ['outerOrderId' => $group['outer_order_id'] ?? $group['order_id']]) }}"
                                                                    class="btn btn-primary btn-sm order-pay-btn">Pay</a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr class="order-empty-row">
                                                    <td colspan="7">
                                                        <div class="order-empty-state">
                                                            <h6>No Joytel orders yet</h6>
                                                            <p>Your Joytel order history will appear here after checkout.
                                                            </p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                @if ($joytelOrderGroups && $joytelOrderGroups->total() > 0)
                                    <div class="order-pagination-wrap">
                                        <div>
                                            Showing {{ $joytelOrderGroups->firstItem() ?? 0 }} to
                                            {{ $joytelOrderGroups->lastItem() ?? 0 }} of
                                            {{ $joytelOrderGroups->total() }} Joytel orders
                                        </div>
                                        <div>
                                            {{ $joytelOrderGroups->appends(array_merge(request()->except('joytel_page'), ['orders_tab' => 'joytel']))->links('pagination::bootstrap-5') }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
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

    <div class="modal fade" id="joytelUsageModal" tabindex="-1" aria-labelledby="joytelUsageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="joytelUsageForm">
                @csrf
                <input type="hidden" name="outer_order_id" id="joytel_usage_outer_order_id">
                <input type="hidden" name="service_type" id="joytel_usage_service_type">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="joytelUsageModalLabel">Check Joytel Usage</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger d-none" id="joytelUsageError"></div>
                        <div class="alert alert-success d-none" id="joytelUsageSuccess"></div>

                        <div class="mb-3" id="joytelUsageTypeWrap">
                            <label class="form-label d-block">Service Type</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input joytel-usage-type-radio" type="radio"
                                    name="usage_type_choice" id="joytel_usage_type_esim" value="esim" checked>
                                <label class="form-check-label" for="joytel_usage_type_esim">eSIM</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input joytel-usage-type-radio" type="radio"
                                    name="usage_type_choice" id="joytel_usage_type_physical" value="physical">
                                <label class="form-check-label" for="joytel_usage_type_physical">Recharge</label>
                            </div>
                        </div>

                        <div class="mb-3 d-none" id="joytelUsageItemWrap">
                            <label for="joytel_usage_item" class="form-label">Joytel Item</label>
                            <select id="joytel_usage_item" class="form-control"></select>
                        </div>

                        <div id="joytelUsageEsimFields">
                            <div class="mb-3">
                                <label for="joytel_usage_sn_pin" class="form-label">SN PIN <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="sn_pin" id="joytel_usage_sn_pin"
                                    placeholder="Enter SN PIN">
                            </div>
                        </div>

                        <div id="joytelUsageRechargeFields" class="d-none">
                            <div class="mb-3">
                                <label for="joytel_usage_cid" class="form-label">SN Code / CID <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="cid" id="joytel_usage_cid"
                                    placeholder="Enter SN Code or CID">
                            </div>
                            <div class="mb-3">
                                <label for="joytel_usage_rsp_order_id" class="form-label">RSP Order ID <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="rsp_order_id"
                                    id="joytel_usage_rsp_order_id" placeholder="Enter RSP Order ID">
                            </div>
                            {{-- <div class="mb-3">
                                <label for="joytel_usage_imsi" class="form-label">IMSI</label>
                                <input type="text" class="form-control" name="imsi" id="joytel_usage_imsi"
                                    placeholder="Optional">
                            </div> --}}
                        </div>

                        <div id="joytelUsageResult" class="joytel-usage-result d-none"></div>
                    </div>
                    <div class="modal-footer">
                        {{-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> --}}
                        <button type="submit" class="btn btn-primary" id="joytelUsageSubmit">Check Usage</button>
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

            var tabs = document.querySelectorAll('.order-tab');
            var panels = document.querySelectorAll('.order-history-panel');

            function setActiveOrderTab(target) {
                tabs.forEach(function(item) {
                    item.classList.toggle('is-active', item.getAttribute('data-target') === target);
                });

                panels.forEach(function(panel) {
                    panel.classList.toggle('is-active', panel.getAttribute('data-panel') === target);
                });

                var url = new URL(window.location.href);
                url.searchParams.set('orders_tab', target);
                window.history.replaceState({}, '', url.toString());
            }

            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    setActiveOrderTab(tab.getAttribute('data-target'));
                });
            });

            document.querySelectorAll('[data-order-search]').forEach(function(input) {
                input.addEventListener('input', function() {
                    var tableName = input.getAttribute('data-order-search');
                    var tbody = document.querySelector('[data-order-table="' + tableName + '"]');

                    if (!tbody) {
                        return;
                    }

                    var keyword = input.value.trim().toLowerCase();

                    tbody.querySelectorAll('[data-order-row]').forEach(function(row) {
                        var haystack = row.getAttribute('data-search') || '';
                        row.classList.toggle('order-row-hidden', keyword !== '' && haystack
                            .indexOf(keyword) === -1);
                    });
                });
            });

            var joytelUsageForm = document.getElementById('joytelUsageForm');
            var joytelUsageItems = [];
            var joytelUsageItemSelect = document.getElementById('joytel_usage_item');
            var joytelUsageItemWrap = document.getElementById('joytelUsageItemWrap');
            var joytelUsageError = document.getElementById('joytelUsageError');
            var joytelUsageSuccess = document.getElementById('joytelUsageSuccess');
            var joytelUsageResult = document.getElementById('joytelUsageResult');
            var joytelUsageSubmit = document.getElementById('joytelUsageSubmit');
            var joytelUsageEsimFields = document.getElementById('joytelUsageEsimFields');
            var joytelUsageRechargeFields = document.getElementById('joytelUsageRechargeFields');
            var joytelUsageServiceType = document.getElementById('joytel_usage_service_type');
            var joytelUsageTypeWrap = document.getElementById('joytelUsageTypeWrap');
            var joytelUsageTypeRadios = document.querySelectorAll('.joytel-usage-type-radio');

            function clearJoytelUsageAlerts() {
                joytelUsageError.classList.add('d-none');
                joytelUsageSuccess.classList.add('d-none');
                joytelUsageResult.classList.add('d-none');
                joytelUsageError.textContent = '';
                joytelUsageSuccess.textContent = '';
                joytelUsageResult.innerHTML = '';
            }

            function setJoytelUsageMode(serviceType) {
                var mode = (serviceType || 'esim').toLowerCase();
                var isRecharge = mode === 'physical';

                joytelUsageServiceType.value = isRecharge ? 'physical' : 'esim';
                document.getElementById(isRecharge ? 'joytel_usage_type_physical' : 'joytel_usage_type_esim')
                    .checked = true;
                joytelUsageEsimFields.classList.toggle('d-none', isRecharge);
                joytelUsageRechargeFields.classList.toggle('d-none', !isRecharge);
            }

            function setJoytelUsageReadonly(readonly) {
                document.getElementById('joytel_usage_sn_pin').readOnly = readonly;
                document.getElementById('joytel_usage_cid').readOnly = readonly;
                document.getElementById('joytel_usage_rsp_order_id').readOnly = readonly;
            }

            function fillJoytelUsageFields(item, options) {
                item = item || {};
                options = options || {};
                setJoytelUsageMode(item.service_type || joytelUsageServiceType.value || 'esim');

                document.getElementById('joytel_usage_sn_pin').value = item.sn_pin || '';
                document.getElementById('joytel_usage_cid').value = item.cid || item.sn_code || '';
                document.getElementById('joytel_usage_rsp_order_id').value = item.rsp_order_id || '';
                if (document.getElementById('joytel_usage_imsi')) {
                    document.getElementById('joytel_usage_imsi').value = '';
                }
                setJoytelUsageReadonly(!!options.readonly);
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function humanizeKey(key) {
                return String(key)
                    .replace(/_/g, ' ')
                    .replace(/([a-z])([A-Z])/g, '$1 $2')
                    .replace(/\b\w/g, function(char) {
                        return char.toUpperCase();
                    });
            }

            function renderJoytelUsageValue(value) {
                if (Array.isArray(value)) {
                    if (value.length === 0) {
                        return '<span class="text-muted">-</span>';
                    }

                    return value.map(function(item, index) {
                        return '<div class="mb-2"><strong>Item ' + (index + 1) + '</strong>' +
                            renderJoytelUsageValue(item) + '</div>';
                    }).join('');
                }

                if (value && typeof value === 'object') {
                    if (value.__joytelUsageSummary) {
                        var mainRows = (value.rows || []).map(function(row) {
                            return '<tr><td>' + escapeHtml(row.label || '-') +
                                '</td><td class="text-right">' +
                                escapeHtml(row.value || '-') + '</td></tr>';
                        }).join('');
                        var usageRecords = value.usage_records || [];
                        var usageHtml = '';

                        if (usageRecords.length > 0) {
                            usageHtml = '<div class="mt-3"><strong>Daily Usage</strong>' +
                                usageRecords.map(function(record, index) {
                                    return '<div class="mt-2"><strong>Item ' + (index + 1) + '</strong>' +
                                        renderJoytelUsageValue(record) + '</div>';
                                }).join('') + '</div>';
                        }

                        return '<div class="table-responsive"><table class="table table-sm table-bordered">' +
                            '<tbody><tr class="table-info"><th colspan="2">' +
                            escapeHtml(value.title || 'Total Usage') +
                            '</th></tr>' + mainRows + '</tbody></table></div>' + usageHtml;
                    }

                    var rows = Object.keys(value).map(function(key) {
                        return '<tr><th style="width: 34%;">' + escapeHtml(humanizeKey(key)) +
                            '</th><td>' + renderJoytelUsageValue(value[key]) + '</td></tr>';
                    }).join('');

                    return '<div class="table-responsive"><table class="table table-sm table-bordered">' +
                        '<tbody>' + rows + '</tbody></table></div>';
                }

                return value === null || value === '' || typeof value === 'undefined' ?
                    '<span class="text-muted">-</span>' :
                    escapeHtml(value);
            }

            document.querySelectorAll('.joytel-usage-check-btn').forEach(function(button) {
                button.addEventListener('click', function() {
                    clearJoytelUsageAlerts();
                    var isManual = button.getAttribute('data-manual') === '1';
                    document.getElementById('joytel_usage_outer_order_id').value = button
                        .getAttribute(
                            'data-outer-order-id') || '';
                    setJoytelUsageMode(button.getAttribute('data-service-type') || 'esim');
                    joytelUsageTypeWrap.classList.toggle('d-none', !isManual);

                    try {
                        joytelUsageItems = JSON.parse(button.getAttribute('data-items') || '[]');
                    } catch (error) {
                        joytelUsageItems = [];
                    }

                    joytelUsageItemSelect.innerHTML = '';
                    joytelUsageItemWrap.classList.add('d-none');

                    joytelUsageItems.forEach(function(item, index) {
                        var option = document.createElement('option');
                        option.value = String(index);
                        option.textContent = (item.product_name || item.product_code ||
                                'Joytel Item') +
                            ' - ' + (item.sn_pin || item.cid || item.sn_code || 'Manual');
                        joytelUsageItemSelect.appendChild(option);
                    });

                    fillJoytelUsageFields(isManual ? {
                        service_type: button.getAttribute('data-service-type') || 'esim'
                    } : (joytelUsageItems[0] || {
                        service_type: button.getAttribute('data-service-type') || 'esim'
                    }), {
                        readonly: !isManual
                    });
                });
            });

            joytelUsageItemSelect.addEventListener('change', function() {
                fillJoytelUsageFields(joytelUsageItems[parseInt(joytelUsageItemSelect.value, 10)] || {}, {
                    readonly: true
                });
                clearJoytelUsageAlerts();
            });

            joytelUsageTypeRadios.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    setJoytelUsageMode(radio.value);
                    clearJoytelUsageAlerts();
                });
            });

            if (joytelUsageForm) {
                joytelUsageForm.addEventListener('submit', function(event) {
                    event.preventDefault();
                    clearJoytelUsageAlerts();
                    joytelUsageSubmit.disabled = true;
                    joytelUsageSubmit.textContent = 'Checking...';

                    fetch("{{ route('customer.joytel.usage.check') }}", {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            },
                            body: new FormData(joytelUsageForm)
                        })
                        .then(function(response) {
                            return response.json().then(function(data) {
                                return {
                                    ok: response.ok,
                                    data: data
                                };
                            });
                        })
                        .then(function(result) {
                            if (!result.ok || !result.data.ok) {
                                joytelUsageError.textContent = result.data.message ||
                                    'Usage check failed.';
                                joytelUsageError.classList.remove('d-none');
                                return;
                            }

                            joytelUsageSuccess.textContent = result.data.message ||
                                'Usage check success.';
                            joytelUsageSuccess.classList.remove('d-none');
                            joytelUsageResult.innerHTML = renderJoytelUsageValue(result.data.summary ||
                                result
                                .data.raw || {});
                            joytelUsageResult.classList.remove('d-none');
                        })
                        .catch(function(error) {
                            joytelUsageError.textContent = error.message || 'Usage check failed.';
                            joytelUsageError.classList.remove('d-none');
                        })
                        .finally(function() {
                            joytelUsageSubmit.disabled = false;
                            joytelUsageSubmit.textContent = 'Check Usage';
                        });
                });
            }
        });
    </script>
@endsection

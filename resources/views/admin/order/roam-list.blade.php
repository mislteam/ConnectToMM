@extends('admin.layouts.index')
@section('title', 'Roam Orders')
@section('content')
    <style>
        .orders-page {
            color: #1f2937;
        }

        .orders-page-title {
            color: #111827;
        }

        .orders-breadcrumb-link,
        .orders-breadcrumb-current {
            color: #374151;
        }

        .order-reference-text {
            color: #1f2937;
            font-size: 0.88rem;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .order-detail-link {
            color: #0f172a;
            font-size: 0.82rem;
            font-weight: 500;
            text-decoration: underline;
            text-underline-offset: 0.18rem;
        }

        .order-detail-link:hover {
            color: #2563eb;
        }

        .order-meta-text {
            color: #1f2937;
            font-size: 0.88rem;
            font-weight: 500;
        }

        .order-status-text {
            font-size: 0.88rem;
            font-weight: 600;
        }

        .roam-order-list-wrap {
            padding: 0 1rem 1rem;
        }

        .roam-order-list-table {
            margin-bottom: 0;
            min-width: 1080px;
            table-layout: fixed;
            width: 100%;
        }

        .roam-order-list-table th,
        .roam-order-list-table td {
            padding-bottom: 1rem;
            padding-top: 1rem;
            vertical-align: middle;
            white-space: nowrap;
        }

        .roam-order-list-table thead th {
            color: #475569;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .roam-order-list-table th:nth-child(1),
        .roam-order-list-table td:nth-child(1) {
            width: 24%;
        }

        .roam-order-list-table th:nth-child(2),
        .roam-order-list-table td:nth-child(2) {
            width: 18%;
        }

        .roam-order-list-table th:nth-child(3),
        .roam-order-list-table td:nth-child(3) {
            width: 22%;
        }

        .roam-order-list-table th:nth-child(4),
        .roam-order-list-table td:nth-child(4) {
            width: 13%;
        }

        .roam-order-list-table th:nth-child(5),
        .roam-order-list-table td:nth-child(5) {
            width: 15%;
        }

        .roam-order-list-table th:nth-child(6),
        .roam-order-list-table td:nth-child(6) {
            text-align: left !important;
            width: 8%;
        }

        .roam-order-list-table td:nth-child(2),
        .roam-order-list-table td:nth-child(3),
        .roam-order-list-table td:nth-child(4),
        .roam-order-list-table th:nth-child(2),
        .roam-order-list-table th:nth-child(3),
        .roam-order-list-table th:nth-child(4) {
            text-align: left;
        }

        [data-table-pagination] p,
        [data-table-pagination] .small.text-muted {
            display: none;
        }

        html[data-bs-theme="dark"] .orders-page,
        html[data-bs-theme="dark"] .orders-page-title,
        html[data-bs-theme="dark"] .order-reference-text,
        html[data-bs-theme="dark"] .order-meta-text {
            color: #f3f6fb;
        }

        html[data-bs-theme="dark"] .orders-breadcrumb-link,
        html[data-bs-theme="dark"] .orders-breadcrumb-current {
            color: #b8c7e0;
        }

        html[data-bs-theme="dark"] .order-detail-link {
            color: #8fb7ff;
        }
    </style>

    <div class="container-fluid orders-page">
        @include('components.alert')

        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 orders-page-title">Roam Orders</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);" class="orders-breadcrumb-link">Home</a></li>
                    <li class="breadcrumb-item active orders-breadcrumb-current">Roam Orders</li>
                </ol>
            </div>
        </div>

        @php
            $stats = $stats ?? [];
            $orders = $orders ?? collect();
        @endphp

        <div class="row row-cols-xxl-5 row-cols-md-3 row-cols-1 align-items-center g-1">
            <div class="col">
                <div class="card mb-1">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="avatar-md flex-shrink-0">
                                <span class="avatar-title text-bg-success rounded-circle fs-22">
                                    <i class="ti ti-check"></i>
                                </span>
                            </div>
                            <h3 class="mb-0">{{ number_format((int) ($stats['completed'] ?? 0)) }}</h3>
                        </div>
                        <p class="mb-0">Completed Orders</p>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card mb-1">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="avatar-md flex-shrink-0">
                                <span class="avatar-title text-bg-warning rounded-circle fs-22">
                                    <i class="ti ti-hourglass"></i>
                                </span>
                            </div>
                            <h3 class="mb-0">{{ number_format((int) ($stats['pending_payment'] ?? 0)) }}</h3>
                        </div>
                        <p class="mb-0">Pending Payment</p>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card mb-1">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="avatar-md flex-shrink-0">
                                <span class="avatar-title text-bg-danger rounded-circle fs-22">
                                    <i class="ti ti-x"></i>
                                </span>
                            </div>
                            <h3 class="mb-0">{{ number_format((int) ($stats['cancelled'] ?? 0)) }}</h3>
                        </div>
                        <p class="mb-0">Canceled Orders</p>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card mb-1">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="avatar-md flex-shrink-0">
                                <span class="avatar-title text-bg-info rounded-circle fs-22">
                                    <i class="ti ti-shopping-cart"></i>
                                </span>
                            </div>
                            <h3 class="mb-0">{{ number_format((int) ($stats['new'] ?? 0)) }}</h3>
                        </div>
                        <p class="mb-0">Order Started</p>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card mb-1">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="avatar-md flex-shrink-0">
                                <span class="avatar-title text-bg-primary rounded-circle fs-22">
                                    <i class="ti ti-repeat"></i>
                                </span>
                            </div>
                            <h3 class="mb-0">{{ number_format((int) ($stats['failed'] ?? 0)) }}</h3>
                        </div>
                        <p class="mb-0">Failed Orders</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <form method="GET" action="{{ route('order.index') }}">
                        <div class="card-header border-light justify-content-between">
                            <div class="d-flex gap-2">
                                <div class="app-search">
                                    <input name="search" type="search" class="form-control"
                                        value="{{ request('search') }}" placeholder="Search Roam Order ID..."
                                        aria-label="Search Roam Order ID">
                                    <i data-lucide="search" class="app-search-icon text-muted"></i>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <span class="me-2 fw-semibold">Filter By:</span>

                                <div class="app-search">
                                    <select name="payment_status" class="form-select form-control my-1 my-md-0"
                                        onchange="this.form.submit()">
                                        <option value="">Current Status</option>
                                        <option value="pending_payment" @selected(request('payment_status') === 'pending_payment')>Pending Payment
                                        </option>
                                        <option value="failed" @selected(request('payment_status') === 'failed')>Failed</option>
                                        <option value="refunded" @selected(request('payment_status') === 'refunded')>Refunded</option>
                                        <option value="cancelled" @selected(request('payment_status') === 'cancelled')>Order Cancelled</option>
                                        <option value="completed" @selected(request('payment_status') === 'completed')>Completed</option>
                                        <option value="new" @selected(request('payment_status') === 'new')>Order Started</option>
                                        <option value="processing" @selected(request('payment_status') === 'processing')>Provisioning in Progress
                                        </option>
                                    </select>
                                    <i data-lucide="credit-card" class="app-search-icon text-muted"></i>
                                </div>

                                <div class="app-search">
                                    <select name="date_range" class="form-select form-control my-1 my-md-0"
                                        onchange="this.form.submit()">
                                        <option value="">Date Range</option>
                                        <option value="today" @selected(request('date_range') === 'today')>Today</option>
                                        <option value="last_7_days" @selected(request('date_range') === 'last_7_days')>Last 7 Days</option>
                                        <option value="last_30_days" @selected(request('date_range') === 'last_30_days')>Last 30 Days</option>
                                        <option value="this_year" @selected(request('date_range') === 'this_year')>This Year</option>
                                    </select>
                                    <i data-lucide="calendar" class="app-search-icon text-muted"></i>
                                </div>

                                <div>
                                    <select name="per_page" class="form-select form-control my-1 my-md-0"
                                        onchange="this.form.submit()">
                                        <option value="5" @selected((string) request('per_page', '20') === '5')>5</option>
                                        <option value="10" @selected((string) request('per_page', '20') === '10')>10</option>
                                        <option value="15" @selected((string) request('per_page', '20') === '15')>15</option>
                                        <option value="20" @selected((string) request('per_page', '20') === '20')>20</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive roam-order-list-wrap">
                        <table class="table table-custom table-centered table-hover roam-order-list-table">
                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                <tr class="text-uppercase fs-xxs">
                                    <th>Order ID</th>
                                    <th>Customer Name</th>
                                    <th>Customer Email</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr>
                                        <td class="align-middle">
                                            <span class="order-reference-text">{{ $order['reference'] }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="order-meta-text">{{ $order['customer_name'] ?? '-' }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="order-meta-text">
                                                {{ $order['customer_email'] ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="order-meta-text">
                                                {{ optional($order['created_at'] ?? null)->format('Y-m-d') ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <span
                                                class="order-status-text {{ $order['status_class'] ?? 'text-primary' }}">
                                                {{ $order['status_label'] ?? '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('order.show', ['reference' => $order['reference']]) }}"
                                                class="order-detail-link">Detail</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No Roam orders found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer border-0">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div data-table-pagination-info="orders">
                                Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of
                                {{ $orders->total() }} Roam orders
                            </div>
                            <div data-table-pagination>
                                {{ $orders->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

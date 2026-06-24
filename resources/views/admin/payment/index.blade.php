@extends('admin.layouts.index')
@section('title', 'Payment Setting')
@section('content')
    <style>
        .alert-fixed {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 250px;
        }
    </style>
    <div class="container-fluid">
        <div id="live-alert-container"></div>
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Payment Setting</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
                    <li class="breadcrumb-item active text-black">Payment Setting</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div data-table data-table-rows-per-page="8" class="card">
                    <div class="card-header fw-semibold">Payment Setting</div>
                    <div class="table-responsive">
                        <table class="table table-custom table-centered table-hover w-100 mb-0">
                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                <tr class="text-uppercase fs-xxs">
                                    <th class="ps-3" style="width: 1%;">
                                        <input data-table-select-all
                                            class="form-check-input form-check-input-light fs-14 mt-0" type="checkbox"
                                            id="select-all-files" value="option">
                                    </th>
                                    <th data-table-sort>No</th>
                                    <th data-table-sort>Type</th>
                                    <th data-table-sort>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($paymentTypes as $payment)
                                    <tr>
                                        <td class="ps-3"><input
                                                class="form-check-input form-check-input-light fs-14 file-item-check mt-0"
                                                type="checkbox" value="option"></td>
                                        <td>
                                            <h5 class="m-0"><a href="#"
                                                    class="link-reset">{{ $loop->iteration }}</a>
                                            </h5>
                                        </td>
                                        <td>{{ $payment->type }}</td>
                                        <td>
                                            <div class="form-check form-switch form-check-secondary fs-xxl mb-2">
                                                <input data-id="{{ $payment->id }}" type="checkbox"
                                                    class="form-check-input mt-1 all-btn status-btn"
                                                    {{ $payment->status == 1 ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <x-action-button :url="route('admin.payment.edit', [
                                                    'payment' => $payment->id,
                                                    'type' => $payment->type,
                                                ])" permission="payment.edit"
                                                    icon="ti-edit" />
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div data-table-pagination-info="payment"></div>
                            <div data-table-pagination></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkEls = document.querySelectorAll('.status-btn');
            const liveAlertContainer = document.getElementById('live-alert-container');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const basePath = window.location.pathname.replace(/\/$/, '');
            const updateStatusUrl = `${window.location.origin}${basePath}/update-status`;
            const showAlert = async options => {
                if (window.Swal && typeof window.Swal.fire === 'function') {
                    return window.Swal.fire({
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#0049ad',
                        allowOutsideClick: false,
                        ...options
                    });
                }

                window.alert(options.text || options.title || 'Notification');
                return Promise.resolve();
            };

            if (liveAlertContainer) {
                liveAlertContainer.innerHTML = '';
            }

            checkEls.forEach(el => {
                el.addEventListener('change', async function() {
                    const previousState = !el.checked;
                    el.disabled = true;

                    try {
                        const response = await fetch(updateStatusUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                status: el.checked ? 1 : 0,
                                id: el.dataset.id
                            })
                        });

                        if (!response.ok) {
                            const errorText = await response.text();
                            throw new Error(
                                `Request failed with status ${response.status}: ${errorText}`
                                );
                        }

                        const data = await response.json();

                        if (!data?.success) {
                            throw new Error(data?.message || 'Status update failed.');
                        }

                        await showAlert({
                            icon: 'success',
                            title: 'Success',
                            text: data.message || 'Status updated successfully.'
                        });

                        window.location.reload();
                    } catch (err) {
                        console.error(err);
                        el.checked = previousState;

                        await showAlert({
                            icon: 'error',
                            title: 'Update failed',
                            text: 'Status update failed. Please check the server route, session, and APP_URL/public path on UAT.'
                        });
                    } finally {
                        el.disabled = false;
                    }
                });
            });
        });
    </script>
@endsection

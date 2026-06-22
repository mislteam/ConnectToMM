@extends('admin.layouts.index')
@section('title', 'Payment Setting')
@section('content')
    @include('components.alert')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 text-black">Payment Setting</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
                    <li class="breadcrumb-item text-black"><a href="{{ route('admin.payment.index') }}">Payment Setting</a>
                    </li>
                    <li class="breadcrumb-item active text-black">{{ $payment->type }}</li>
                </ol>
            </div>

            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('admin.payment.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div data-table data-table-rows-per-page="8" class="card">
                    <div class="card-header fw-semibold">Payment Setting</div>
                    <form action="{{ $type == 'uab_pay' ? route('payment.uab.update') : '#' }}" method="POST">
                        @csrf
                        @method('patch')
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="col-form-label">Payment Type</label>
                                    </div>
                                </div>
                                <div class="col-lg-9">
                                    <div class="mb-3 mx-2">
                                        <label class="col-form-label">{{ $payment->type }}</label>
                                    </div>
                                </div>

                                @if ($type == 'direct_bank_transfer')
                                    <div class="col-lg-3 mt-4">
                                        <div class="mb-3">
                                            <label class="col-form-label">Account Details</label>
                                        </div>
                                    </div>
                                    <div class="col-lg-9 mt-4">
                                        <div class="card mb-3">
                                            <div class="table-responsive">
                                                <table
                                                    class="table table-border table-custom table-centered table-hover w-100 mb-0">
                                                    <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                                        <tr class="text-uppercase fs-xs">
                                                            <th>Bank</th>
                                                            <th>Account Name</th>
                                                            <th>Account Number</th>
                                                            <th class="text-center col-2">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($payment->directBankCredentials as $credential)
                                                            <tr>
                                                                <td>{{ $credential->bank_name }}</td>
                                                                <td>{{ $credential->account_name }}</td>
                                                                <td>{{ $credential->account_number }}</td>
                                                                <td>
                                                                    <div class="d-flex justify-content-center gap-2">
                                                                        <x-action-button permission="payment.edit"
                                                                            :data-id="$credential->id" :data-bank="$credential->bank_name"
                                                                            :data-setting-id="$credential->payment_setting_id" :data-name="$credential->account_name"
                                                                            :data-number="$credential->account_number" icon="ti-edit"
                                                                            target-name="edit-account"
                                                                            class="edit-account-btn" />
                                                                        <x-action-button :data-id="$credential->id"
                                                                            permission="permission.delete" icon="ti-trash"
                                                                            target-name="account-delete"
                                                                            data-url="/payment/direct-bank/delete"
                                                                            class="delete-btn" />
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="card-footer border-0">
                                                <div class="d-flex justify-content-end gap-1">
                                                    <x-create-action :data-id="1" menu-text="Add Account"
                                                        permission="payment.create" target-name="add-account"
                                                        icon="ti-plus" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @elseif ($type == 'uab_pay')
                                    @php
                                        $uab_credential = \App\Models\UabCredential::first();
                                    @endphp
                                    <div class="col-lg-3">
                                        <div class="mb-3">
                                            <label class="col-form-label">Channel</label>
                                        </div>
                                    </div>
                                    <div class="col-lg-9">
                                        <div class="mb-3 mx-2">
                                            <label class="col-form-label">{{ $uab_credential->channel }}</label>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="mb-3">
                                            <label class="col-form-label">Merchant User ID<span
                                                    class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-lg-9">
                                        <div class="mb-3">
                                            <input type="text" class="form-control" placeholder="Enter Merchant User ID"
                                                value="{{ $uab_credential->merchant_user_id }}" required
                                                name="merchant_user_id">
                                            <div class="my-1">
                                                @error('merchant_user_id')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="mb-3">
                                            <label class="col-form-label">API Url<span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-lg-9">
                                        <div class="mb-3">
                                            <input type="text" class="form-control" placeholder="Enter API Url"
                                                value="{{ $uab_credential->api_url }}" required name="api_url">
                                            <div class="my-1">
                                                @error('api_url')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="mb-3">
                                            <label class="col-form-label">Access Key<span
                                                    class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-lg-9">
                                        <div class="mb-3">
                                            <input type="text" class="form-control" placeholder="Enter Access Key"
                                                value="{{ $uab_credential->access_key }}" required name="access_key">
                                            <div class="my-1">
                                                @error('access_key')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="mb-3">
                                            <label class="col-form-label">Secret Key<span
                                                    class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-lg-9">
                                        <div class="mb-3">
                                            <input type="text" class="form-control" placeholder="Enter Secret Key"
                                                value="{{ $uab_credential->secret_key }}" required name="secret_key">
                                            <div class="my-1">
                                                @error('secret_key')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="mb-3">
                                            <label class="col-form-label">Client Secret<span
                                                    class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-lg-9">
                                        <div class="mb-3">
                                            <input type="text" class="form-control" placeholder="Enter Client Secret"
                                                value="{{ $uab_credential->client_secret }}" required
                                                name="client_secret">
                                            <div class="my-1">
                                                @error('client_secret')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <x-delete-modal-box id="account-delete" message="Are you sure you want to delete this Bank Account?" />

        <div class="modal fade" id="edit-account" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Edit Bank Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('payment.direct.update') }}" method="POST">
                        @csrf
                        @method('patch')
                        <input type="hidden" name="credential_id" id="credentail_id">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="bank-name" class="col-form-label">Bank<span
                                        class="text-danger">*</span></label>
                                <input name="bank_name" type="text" class="form-control" value="KBZ Bank"
                                    id="edit-bank-name">
                            </div>
                            <div class="mb-3">
                                <label for="edit-account-name" class="col-form-label">Account
                                    Name<span class="text-danger">*</span></label>
                                <input name="account_name" type="text" class="form-control" value="HNIN SHWE SIN"
                                    id="edit-account-name">
                            </div>
                            <div class="mb-3">
                                <label for="edit-account-number" class="col-form-label">Account
                                    Number<span class="text-danger">*</span></label>
                                <input name="account_number" type="text" class="form-control" value="123456789123"
                                    id="edit-account-number">
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="add-account" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Add Bank Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('payment.direct.store') }}" method="POST">
                        <div class="modal-body">

                            @csrf
                            <input type="hidden" name="payment_setting_id" value="{{ $payment->id }}">
                            <div class="mb-3">
                                <label for="bank-name" class="col-form-label">Bank<span
                                        class="text-danger">*</span></label>
                                <input type="text" name="bank_name" class="form-control" id="bank-name">
                            </div>
                            <div class="mb-3">
                                <label for="account-name" class="col-form-label">Account Name<span
                                        class="text-danger">*</span></label>
                                <input type="text" name="account_name" class="form-control" id="account-name">
                            </div>
                            <div class="mb-3">
                                <label for="account-number" class="col-form-label">Account Number<span
                                        class="text-danger">*</span></label>
                                <input type="number" name="account_number" class="form-control" id="account-number">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

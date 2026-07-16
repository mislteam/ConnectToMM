@extends('admin.layouts.index')
@section('title', 'All Messages')
@section('content')
    <style>
        .bold_text {
            font-weight: 700;
        }

        .message-page-title,
        .message-breadcrumb-current {
            color: #111827;
        }

        .message-breadcrumb-link {
            color: #4b5563;
        }

        .message-breadcrumb-link:hover {
            color: #1f2937;
        }

        html[data-bs-theme="dark"] .message-page-title,
        html[data-bs-theme="dark"] .message-breadcrumb-current {
            color: #e5edf9;
        }

        html[data-bs-theme="dark"] .message-breadcrumb-link {
            color: #9fb1cc;
        }

        html[data-bs-theme="dark"] .message-breadcrumb-link:hover {
            color: #dbe7ff;
        }
    </style>
    @include('components.alert')
    <div class="container-fluid">
        <div class="page-title-head d-flex align-items-center">
            <div class="flex-grow-1 py-3">
                <h4 class="fs-sm fw-bold m-0 message-page-title">Message</h4>
                <ol class="breadcrumb m-0 py-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);" class="message-breadcrumb-link">Home</a></li>
                    <li class="breadcrumb-item active message-breadcrumb-current">All Messages</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-custom table-centered table-select table-hover w-100 mb-0">
                            <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                <tr class="text-uppercase fs-xxs">
                                    <th data-table-sort>No</th>
                                    <th data-table-sort>Username</th>
                                    <th data-table-sort>Message</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($contact_details as $msg)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="{{ !$msg->status ? 'bold_text' : '' }}">{{ $msg->name }}</td>
                                        <td class="{{ !$msg->status ? 'bold_text' : '' }}">
                                            {{ Str::limit($msg->message, 50) }}</td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <x-action-button :url="route('message.show', $msg->id)" permission="message.view"
                                                    icon="ti-eye" />
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <p class="text-center">Nothing Found.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

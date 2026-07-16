<header class="app-topbar">
    <div class="container-fluid topbar-menu">
        <div class="d-flex align-items-center gap-2">
            <!-- Sidebar Menu Toggle Button -->
            <button class="sidenav-toggle-button btn btn-dark btn-icon">
                <i class="ti ti-menu-4 fs-22"></i>
            </button>

            <!-- step 1 -->
            {{-- <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#search-modal">
                Search
            </button> --}}

            <!-- Horizontal Menu Toggle Button -->
            <button class="topnav-toggle-button px-2" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                <i class="ti ti-menu-4 fs-22"></i>
            </button>
        </div> <!-- .d-flex-->

        <div class="d-flex align-items-center gap-2">
            <!-- Messages Dropdown -->
            @php
                $unreadContactMessages = $unreadContactMessages ?? collect();
                $unreadContactMessageCount = $unreadContactMessageCount ?? $unreadContactMessages->count();
            @endphp
            <div class="topbar-item">
                <div class="dropdown">
                    <button class="topbar-link dropdown-toggle drop-arrow-none" data-bs-toggle="dropdown"
                        data-bs-offset="0,22" type="button" data-bs-auto-close="outside" aria-haspopup="false"
                        aria-expanded="false">
                        <i data-lucide="mails" class="fs-xxl"></i>
                        @if ($unreadContactMessageCount > 0)
                            <span class="badge text-bg-success badge-circle topbar-badge">{{ $unreadContactMessageCount }}</span>
                        @endif
                    </button>

                    {{-- <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg">
                        <div class="px-3 py-2 border-bottom">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="m-0 fs-md fw-semibold">Messages</h6>
                                </div>
                                <div class="col text-end">
                                    <a href="#!" class="badge badge-soft-success badge-label py-1">09
                                        Notifications</a>
                                </div>
                            </div>
                        </div>
                        @foreach ($contacts as $contact)
                            <div style="max-height: 300px;" data-simplebar>
                                <!-- item 1 -->
                                <div class="dropdown-item notification-item py-2 text-wrap active" id="message-1">
                                    <span class="d-flex gap-3">
                                        <span class="flex-shrink-0">
                                            <i data-lucide="mails" class="fs-xxl"></i>
                                        </span>
                                        <span class="flex-grow-1 text-muted">
                                            <span class="fw-medium text-body">{{ $contact->username }}</span> uploaded a
                                            new
                                            message.
                                            <span class="fw-medium text-body"></span>
                                            <br>
                                            <span class="fs-xs">5 minutes ago</span>
                                        </span>
                                        <button type="button" class="flex-shrink-0 text-muted btn btn-link p-0"
                                            data-dismissible="#message-1">
                                            <i class="ti ti-xbox-x-filled fs-xxl"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        @endforeach

                        <!-- All-->
                        <a href="javascript:void(0);"
                            class="dropdown-item text-center text-reset text-decoration-underline link-offset-2 fw-bold notify-item border-top border-light py-2">
                            Read All Messages
                        </a>

                    </div> --}}
                    <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg">
                        <div class="px-3 py-2 border-bottom">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="m-0 fs-md fw-semibold">Messages</h6>
                                </div>
                                <div class="col text-end">
                                    <a href="#!" class="badge badge-soft-success badge-label py-1">
                                        {{ $unreadContactMessageCount }} Notifications
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div style="max-height:300px;" data-simplebar>
                            @forelse ($unreadContactMessages as $contact)
                                <div class="dropdown-item notification-item py-2 text-wrap" id="contact-message-{{ $contact->id }}">
                                    <span class="d-flex gap-3 align-items-start">
                                        <span class="flex-shrink-0">
                                            <i data-lucide="mails" class="fs-xxl"></i>
                                        </span>

                                        <a href="{{ route('message.show', $contact->id) }}"
                                            class="flex-grow-1 text-muted text-decoration-none">
                                            <span class="fw-medium text-body">
                                                {{ $contact->name }}
                                            </span>
                                            uploaded a new message.
                                            <br>
                                            <span class="fs-xs">
                                                {{ $contact->created_at->diffForHumans() }}
                                            </span>
                                        </a>
                                        <form method="POST" action="{{ route('message.read', $contact->id) }}" class="flex-shrink-0 m-0">
                                            @csrf
                                            <button type="submit" class="text-muted btn btn-link p-0" title="Dismiss">
                                                <i class="ti ti-xbox-x-filled fs-xxl"></i>
                                            </button>
                                        </form>
                                    </span>

                                </div>
                            @empty
                                <div class="dropdown-item notification-item py-3 text-center text-muted">
                                    No unread messages
                                </div>
                            @endforelse
                        </div>
                    </div>
                    <!-- End dropdown-menu -->
                </div> <!-- end dropdown-->
            </div> <!-- end topbar item-->

            <!-- Theme Toggle -->
            <div class="topbar-item">
                <button type="button" class="topbar-link px-2" id="light-dark-mode" aria-label="Toggle dark mode"
                    title="Toggle theme">
                    <i data-lucide="moon" class="fs-xxl mode-light-moon" aria-hidden="true"></i>
                    <i data-lucide="sun" class="fs-xxl mode-light-sun" aria-hidden="true"></i>
                </button>
            </div>

            @php
                $canLoadAdminNotifications = \Illuminate\Support\Facades\Schema::hasTable('notifications');
                $adminNotifications = $canLoadAdminNotifications
                    ? auth()->user()?->unreadNotifications()->latest()->limit(10)->get() ?? collect()
                    : collect();
                $adminNotificationCount = $canLoadAdminNotifications
                    ? auth()->user()?->unreadNotifications()->count() ?? 0
                    : 0;
            @endphp
            <div class="topbar-item">
                <div class="dropdown">
                    <button class="topbar-link dropdown-toggle drop-arrow-none" data-bs-toggle="dropdown"
                        data-bs-offset="0,22" type="button" data-bs-auto-close="outside" aria-haspopup="false"
                        aria-expanded="false">
                        <i data-lucide="bell" class="fs-xxl"></i>
                        @if ($adminNotificationCount > 0)
                            <span
                                class="badge badge-square text-bg-warning topbar-badge">{{ $adminNotificationCount }}</span>
                        @endif
                    </button>

                    <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg">
                        <div class="px-3 py-2 border-bottom">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="m-0 fs-md fw-semibold">Notifications</h6>
                                </div>
                                <div class="col text-end">
                                    <span class="badge text-bg-light badge-label py-1">{{ $adminNotificationCount }}
                                        Alerts</span>
                                </div>
                            </div>
                        </div>

                        <div style="max-height: 300px;" data-simplebar>
                            @forelse ($adminNotifications as $notification)
                                @php
                                    $data = $notification->data;
                                    $tone = $data['tone'] ?? 'primary';
                                    $toneClass = match ($tone) {
                                        'success' => 'bg-success-subtle text-success',
                                        'warning' => 'bg-warning-subtle text-warning',
                                        'info' => 'bg-info-subtle text-info',
                                        'danger' => 'bg-danger-subtle text-danger',
                                        default => 'bg-primary-subtle text-primary',
                                    };
                                @endphp
                                <div class="dropdown-item notification-item py-2 text-wrap"
                                    id="notification-{{ $notification->id }}">
                                    <span class="d-flex gap-2">
                                        <span class="avatar-md flex-shrink-0">
                                            <span class="avatar-title {{ $toneClass }} rounded fs-22">
                                                <i data-lucide="{{ $data['icon'] ?? 'bell' }}" class="fs-xl"></i>
                                            </span>
                                        </span>
                                        <a href="{{ route('notifications.open', ['notification' => $notification->id]) }}"
                                            class="flex-grow-1 text-muted text-decoration-none">
                                            <span
                                                class="fw-medium text-body">{{ $data['title'] ?? 'Order notification' }}</span>
                                            <br>
                                            <span>{{ $data['message'] ?? '' }}</span>
                                            <br>
                                            <span
                                                class="fs-xs">{{ $notification->created_at?->diffForHumans() }}</span>
                                        </a>
                                        <form method="POST"
                                            action="{{ route('notifications.read', ['notification' => $notification->id]) }}"
                                            class="flex-shrink-0 m-0">
                                            @csrf
                                            <button type="submit" class="text-muted btn btn-link p-0" title="Dismiss">
                                                <i class="ti ti-xbox-x-filled fs-xxl"></i>
                                            </button>
                                        </form>
                                    </span>
                                </div>
                            @empty
                                <div class="dropdown-item notification-item py-3 text-center text-muted">
                                    No unread notifications
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Dropdown -->
            <div class="topbar-item nav-user">
                <div class="dropdown">
                    <a class="topbar-link dropdown-toggle drop-arrow-none px-2" data-bs-toggle="dropdown"
                        data-bs-offset="0,16" href="#!" aria-haspopup="false" aria-expanded="false">
                        <img src="{{ auth()->user()->profile_image ? asset('storage/profile_images/' . auth()->user()->profile_image) : asset('assets/images/user-3.jpg') }}"
                            width="32" class="rounded-circle me-lg-2 d-flex" alt="user-image">
                        <div class="d-lg-flex align-items-center gap-1 d-none">
                            <h5 class="my-0">{{ auth()->user()->name }}</h5>
                            <i class="ti ti-chevron-down align-middle"></i>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- Header -->
                        <div class="dropdown-header noti-title">
                            <h6 class="text-overflow m-0">Welcome back!</h6>
                        </div>

                        <!-- My Profile -->
                        <a href="{{ route('view.admin', auth()->user()->id) }}" class="dropdown-item">
                            <i class="ti ti-user-circle me-2 fs-17 align-middle"></i>
                            <span class="align-middle">Profile</span>
                        </a>

                        <!-- Settings -->
                        <a href="{{ route('admin.edit', auth()->user()->id) }}" class="dropdown-item">
                            <i class="ti ti-settings-2 me-2 fs-17 align-middle"></i>
                            <span class="align-middle">Account Settings</span>
                        </a>

                        <!-- Divider -->
                        <div class="dropdown-divider"></div>

                        <!-- Logout -->
                        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger fw-semibold">
                                <i class="ti ti-logout-2 me-2 fs-17 align-middle"></i>
                                <span class="align-middle">Log Out</span>
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</header>
<!-- Topbar End -->
<div class="modal fade" id="search-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <input type="search" id="modal-search-input" class="form-control w-100"
                    placeholder="Search for something...">
            </div>
            <div class="modal-body" id="modal-search-results"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

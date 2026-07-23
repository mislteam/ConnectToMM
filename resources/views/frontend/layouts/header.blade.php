 @php
     $logo = \App\Models\GeneralSetting::where('type', 'file')->first();
     $customer = Auth::guard('customers')->user();
     $roamCart = session()->get('roam_order_cart', []);
     $joytelCart = session()->get('joytel_cart', []);
     $roamCartCount = is_array($roamCart) ? count($roamCart) : 0;
     $joytelCartCount = is_array($joytelCart) ? count($joytelCart) : 0;
     $cartCount = $roamCartCount + $joytelCartCount;
     $simType = strtolower((string) session('sim_type'));
     $roamCartFirst = is_array($roamCart) ? collect($roamCart)->first(fn($item) => is_array($item)) : null;
     $cartServiceType = strtolower((string) data_get($roamCartFirst, 'service_type', ''));
     $isPhysicalFlow =
         $cartServiceType === 'physical' ||
         request()->routeIs('roam.physical.*', 'physical.*') ||
         str_contains($simType, 'physical');
     if ($joytelCartCount > 0) {
         $cartRoute = route('joytelpackage.cartpage');
     } else {
         $cartRoute = $isPhysicalFlow ? route('roam.physical.cartpage') : route('roam.esim.cartpage');
     }
     $canLoadCustomerNotifications = $customer ? \Illuminate\Support\Facades\Schema::hasTable('notifications') : false;
     $customerNotifications = $canLoadCustomerNotifications
         ? $customer->unreadNotifications()->latest()->limit(8)->get()
         : collect();
     $customerNotificationCount = $canLoadCustomerNotifications ? $customer->unreadNotifications()->count() : 0;
     $selectedCurrency = session('currency', config('currency.default'));
 @endphp
 <div class="fixed-top">
     <div class="topbar">
         <div class="container">
             <div class="row">
                 <div class="col-lg-6 col-md-6 sol-sm-12">
                     <div class="email">
                         <figure class="mb-0 emailicon">
                             <img src="{{ asset('assets/images/header-emailicon.png') }}" alt="" class="img-fluid">
                         </figure>
                         <a href="mailto:support@repay.com"
                             class="mb-0 text-size-16 text-white">support@connect2mm.com</a>
                     </div>
                 </div>
                 <div class="col-lg-6 col-md-6 sol-sm-12 d-md-block d-none">
                     <div class="mb-0 social-icons">
                         <ul class="mb-0 list-unstyled">
                             <li>Follow us on:</li>
                             <li>
                                 <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                             </li>
                             <li>
                                 <a href="#"><i class="fa-brands fa-twitter"></i></a>
                             </li>
                             <li>
                                 <a href="#"><i class="fa-brands fa-pinterest-p"></i></a>
                             </li>
                             <li>
                                 <a href="#"><i class="fa-brands fa-instagram"></i></a>
                             </li>
                         </ul>
                     </div>
                 </div>
             </div>
         </div>
     </div>
     <!--Header-->
     <header class="header bg-light">
         <div class="container">
             <nav class="navbar position-relative navbar-expand-lg navbar-light">
                 <a class="navbar-brand" href="{{ route('Index') }}">
                     <figure class="mb-0"><img src="{{ asset('general/logo/' . $logo->value) }}"
                             class="img-fluid w-75" alt=""></figure>
                 </a>
                 <div class="mobile-header-actions d-lg-none">
                     <div class="dropdown header-currency-item mobile-currency-item">
                         <button class="btn btn-secondary dropdown-toggle" type="button" id="currencyDropdownMobile"
                             data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                             {{ $selectedCurrency }}
                         </button>
                         <div class="dropdown-menu dropdown-menu-right mobile-currency-menu"
                             aria-labelledby="currencyDropdownMobile">
                             <form action="{{ route('currency.change') }}" method="POST" data-request-loader data-currency-form>
                                 @csrf
                                 <button type="submit" name="currency" value="MMK"
                                     class="dropdown-item {{ $selectedCurrency === 'MMK' ? 'active' : '' }}"
                                     data-currency="MMK">MMK</button>
                                 <button type="submit" name="currency" value="USD"
                                     class="dropdown-item {{ $selectedCurrency === 'USD' ? 'active' : '' }}"
                                     data-currency="USD">USD</button>
                             </form>
                         </div>
                     </div>
                     <a class="mobile-header-action position-relative" href="{{ $cartRoute }}">
                         <i class="fa-solid fa-cart-arrow-down"></i>
                         <span class="position-absolute text-white badge badge-square bg-primary"
                             style="top: -8px; right: -10px;" data-order-count="{{ $cartCount }}">
                             {{ $cartCount }}
                         </span>
                     </a>
                     @if ($customer)
                         <div class="dropdown">
                             <a class="mobile-header-action position-relative" href="#"
                                 id="customerNotificationsMobile" data-toggle="dropdown" aria-haspopup="true"
                                 aria-expanded="false">
                                 <i class="fa-solid fa-bell"></i>
                                 @if ($customerNotificationCount > 0)
                                     <span class="position-absolute text-white badge badge-square bg-danger"
                                         style="top: -8px; right: -10px;">
                                         {{ $customerNotificationCount }}
                                     </span>
                                 @endif
                             </a>
                             <div class="dropdown-menu dropdown-menu-right notification-dropdown-menu mobile-notification-menu p-0"
                                 aria-labelledby="customerNotificationsMobile">
                                 <div class="dropdown-header d-flex justify-content-between align-items-center">
                                     <strong>Notifications</strong>
                                     <small class="text-muted">{{ $customerNotificationCount }} unread</small>
                                 </div>
                                 <div class="dropdown-divider m-0"></div>
                                 <div style="max-height: 320px; overflow-y: auto;">
                                     @forelse ($customerNotifications as $notification)
                                         @php
                                             $data = $notification->data;
                                         @endphp
                                         <div class="dropdown-item customer-notification-item text-wrap py-2"
                                             id="customer-notification-mobile-{{ $notification->id }}">
                                             <div class="d-flex align-items-start">
                                                 <a href="{{ route('notifications.open', ['notification' => $notification->id]) }}"
                                                     class="notification-link flex-grow-1 text-decoration-none text-dark pr-2">
                                                     <strong
                                                         class="d-block">{{ $data['title'] ?? 'Order notification' }}</strong>
                                                     <span
                                                         class="d-block small text-muted">{{ $data['message'] ?? '' }}</span>
                                                     <span
                                                         class="d-block small text-muted">{{ $notification->created_at?->diffForHumans() }}</span>
                                                 </a>
                                                 <form method="POST"
                                                     action="{{ route('notifications.read', ['notification' => $notification->id]) }}"
                                                     class="m-0">
                                                     @csrf
                                                     <button type="submit" class="btn btn-link p-0 text-muted"
                                                         title="Dismiss">
                                                         <i class="fa-solid fa-xmark"></i>
                                                     </button>
                                                 </form>
                                             </div>
                                         </div>
                                     @empty
                                         <div class="dropdown-item py-3 text-center text-muted">
                                             No unread notifications
                                         </div>
                                     @endforelse
                                 </div>
                             </div>
                         </div>
                         <div class="dropdown">
                             <a class="mobile-header-action mobile-user-action" href="#" id="customerMenuMobile"
                                 data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                 <i class="fa-solid fa-user-check"></i>
                             </a>
                             <div class="dropdown-menu dropdown-menu-right customer-dropdown mobile-customer-menu"
                                 aria-labelledby="customerMenuMobile">
                                 <div class="dropdown-header">
                                     <small class="text-muted d-block">Welcome back!</small>
                                 </div>
                                 <!-- My Profile -->
                                 <a href="{{ route('customer.profile.index') }}" class="dropdown-item">
                                     <i class="fa-solid fa-user"></i>
                                     <span class="dropdown-item-label align-middle">Profile</span>
                                 </a>

                                 <a href="{{ route('frontend.user.wallet') }}" class="dropdown-item">
                                     <i class="fa-solid fa-wallet"></i>
                                     <span class="dropdown-item-label align-middle">Wallet</span>
                                 </a>
                                 <div class="dropdown-divider"></div>
                                 <form method="POST" action="{{ route('customer.logout') }}" class="m-0">
                                     @csrf
                                     <button type="submit" class="dropdown-item customer-logout-btn">Logout</button>
                                 </form>
                             </div>
                         </div>
                     @else
                         <a class="mobile-header-action mobile-user-action" href="{{ route('user.register') }}">
                             <i class="fa-solid fa-user-lock"></i>
                         </a>
                     @endif
                 </div>
                 <button class="navbar-toggler collapsed" type="button" data-toggle="collapse"
                     data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                     aria-expanded="false" aria-label="Toggle navigation">
                     <span class="navbar-toggler-icon"></span>
                     <span class="navbar-toggler-icon"></span>
                     <span class="navbar-toggler-icon"></span>
                 </button>
                 <div class="collapse navbar-collapse" id="navbarSupportedContent">
                     <ul class="navbar-nav ml-auto">
                         <li class="nav-item {{ request()->routeIs('Index') ? 'active' : '' }}">
                             <a class="nav-link" href="{{ route('Index') }}">Home</a>
                         </li>
                         <li class="nav-item {{ request()->routeIs('About') ? 'active' : '' }}">
                             <a class="nav-link" href="{{ route('About') }}">About Us</a>
                         </li>
                         @php
                             $isServiceActive = request()->routeIs(
                                 'esimIndex',
                                 'esim.search',

                                 'joytel.esim.packageview',
                                 'joytel.physical.packageview',

                                 'esim.roam',
                                 'esim.roamsearch',
                                 'esim.roampackageview',
                                 'physicalIndex',
                                 'physical.search',
                                 'physical.roam',
                                 'physical.roamsearch',
                                 'physical.roampackageview',
                             );
                             $joytel_sim_types = json_decode($settings['joytel_sim_types']?->value, true) ?? [];
                             $roam_sim_types = json_decode($settings['roam_sim_types']?->value, true) ?? [];
                         @endphp

                         <li class="nav-item dropdown megamenu {{ $isServiceActive ? 'active' : '' }}">
                             <a class="nav-link dropdown-toggle" href="#" id="megamenu"
                                 data-toggle="dropdown">Our Services</a>
                             <div class="dropdown-menu" aria-labelledby="megamenu">
                                 <div class="row">
                                     <!-- Column 1 -->
                                     <div class="col-md-6">
                                         <h5 class="font-weight-bold">E-SIM</h5>
                                         <ul class="list-unstyled drop-down-pages">
                                             @if ($joytel_sim_types['esim'] == 1)
                                                 <li
                                                     class="nav-item {{ request()->routeIs('esimIndex', 'esim.search', 'joytel.esim.packageview') ? 'active' : '' }}">
                                                     <a class="nav-link" data-request-loader
                                                         href="{{ route('esimIndex') }}">{{ $settings['joytel_title']->value ?? 'Joytel' }}</a>
                                                 </li>
                                             @endif
                                             @if ($roam_sim_types['esim'] == 1)
                                                 <li
                                                     class="nav-item {{ request()->routeIs('esim.roam', 'esim.roamsearch', 'esim.roampackageview') ? 'active' : '' }}">
                                                     <a class="nav-link" data-request-loader
                                                         href="{{ route('esim.roam') }}">{{ $settings['roam_title']->value ?? 'Joytel' }}</a>
                                                 </li>
                                             @endif
                                         </ul>
                                     </div>
                                     <!-- Column 2 with submenu -->
                                     <div class="col-md-6">
                                         <h5 class="font-weight-bold">Physical SIM Recharge</h5>
                                         <ul class="list-unstyled drop-down-pages">
                                             @if ($joytel_sim_types['physical'] === 1)
                                                 <li
                                                     class="nav-item {{ request()->routeIs('physicalIndex', 'physical.search', 'joytel.physical.packageview') ? 'active' : '' }}">
                                                     <a class="nav-link" data-request-loader
                                                         href="{{ route('physicalIndex') }}">{{ $settings['joytel_title']->value ?? 'Joytel' }}</a>
                                                 </li>
                                             @endif
                                             @if ($roam_sim_types['physical'] === 1)
                                                 <li
                                                     class="nav-item {{ request()->routeIs('physical.roam', 'physical.roamsearch', 'physical.roampackageview') ? 'active' : '' }}">
                                                     <a class="nav-link" data-request-loader
                                                         href="{{ route('physical.roam') }}">{{ $settings['roam_title']->value ?? 'Joytel' }}</a>
                                                 </li>
                                             @endif
                                         </ul>
                                     </div>
                                 </div>
                             </div>
                         </li>
                         <li class="nav-item {{ request()->routeIs('Faq') ? 'active' : '' }}">
                             <a class="nav-link" href="{{ route('Faq') }}">FAQ</a>
                         </li>
                         <li class="nav-item {{ request()->routeIs('Blog', 'blogDetail') ? 'active' : '' }}">
                             <a class="nav-link" href="{{ route('Blog') }}">Blog</a>
                         </li>
                         <li class="nav-item {{ request()->routeIs('Contact') ? 'active' : '' }}">
                             <a class="nav-link" href="{{ route('Contact') }}">Contact Us</a>
                         </li>

                         <li class="nav-item header-currency-item">
                             <div class="dropdown">
                                 <button class="btn btn-secondary dropdown-toggle" type="button"
                                     id="currencyDropdown" data-toggle="dropdown" aria-haspopup="true"
                                     aria-expanded="false">
                                     {{ $selectedCurrency }}
                                 </button>
                                 <div class="dropdown-menu" aria-labelledby="currencyDropdown">
                                     <form action="{{ route('currency.change') }}" method="POST" data-request-loader data-currency-form>
                                         @csrf
                                         <button type="submit" name="currency" value="MMK"
                                             class="dropdown-item {{ $selectedCurrency === 'MMK' ? 'active' : '' }}"
                                             data-currency="MMK">MMK</button>
                                         <button type="submit" name="currency" value="USD"
                                             class="dropdown-item {{ $selectedCurrency === 'USD' ? 'active' : '' }}"
                                             data-currency="USD">USD</button>
                                     </form>
                                 </div>
                             </div>
                         </li>
                         <li
                             class="nav-item header-action-item header-cart-item {{ request()->routeIs('roam.esim.*', 'roam.physical.*', 'joytelpackage.*') ? 'active' : '' }}">
                             <a class="nav-link position-relative d-inline-block" href="{{ $cartRoute }}">
                                 <i class="fa-solid fa-cart-arrow-down fs-4"></i>
                                 <span class="position-absolute text-white badge badge-square bg-primary"
                                     style="top: -1px; right: -5px;" data-order-count="{{ $cartCount }}"
                                     id="order_count">
                                     {{ $cartCount }}
                                 </span>

                             </a>
                         </li>
                         @if ($customer)
                             <li class="nav-item dropdown customer-notification-nav header-action-item header-notification-item">
                                 <a class="nav-link  position-relative d-inline-block" href="#"
                                     id="customerNotifications" data-toggle="dropdown" aria-haspopup="true"
                                     aria-expanded="false">
                                     <i class="fa-solid fa-bell fs-4"></i>
                                     @if ($customerNotificationCount > 0)
                                         <span class="position-absolute text-white badge badge-square bg-danger"
                                             style="top: -1px; right: -5px;">
                                             {{ $customerNotificationCount }}
                                         </span>
                                     @endif
                                 </a>
                                 <div class="dropdown-menu dropdown-menu-end notification-dropdown-menu mt-4 p-0"
                                     aria-labelledby="customerNotifications" style="min-width: 320px;">
                                     <div class="dropdown-header d-flex justify-content-between align-items-center">
                                         <strong>Notifications</strong>
                                         <small class="text-muted">{{ $customerNotificationCount }} unread</small>
                                     </div>
                                     <div class="dropdown-divider m-0"></div>
                                     <div style="max-height: 320px; overflow-y: auto;">
                                         @forelse ($customerNotifications as $notification)
                                             @php
                                                 $data = $notification->data;
                                             @endphp
                                             <div class="dropdown-item customer-notification-item text-wrap py-2"
                                                 id="customer-notification-{{ $notification->id }}">
                                                 <div class="d-flex align-items-start">
                                                     <a href="{{ route('notifications.open', ['notification' => $notification->id]) }}"
                                                         class="notification-link flex-grow-1 text-decoration-none text-dark pr-2">
                                                         <strong
                                                             class="d-block">{{ $data['title'] ?? 'Order notification' }}</strong>
                                                         <span
                                                             class="d-block small text-muted">{{ $data['message'] ?? '' }}</span>
                                                         <span
                                                             class="d-block small text-muted">{{ $notification->created_at?->diffForHumans() }}</span>
                                                     </a>
                                                     <form method="POST"
                                                         action="{{ route('notifications.read', ['notification' => $notification->id]) }}"
                                                         class="m-0">
                                                         @csrf
                                                         <button type="submit" class="btn btn-link p-0 text-muted"
                                                             title="Dismiss">
                                                             <i class="fa-solid fa-xmark"></i>
                                                         </button>
                                                     </form>
                                                 </div>
                                             </div>
                                         @empty
                                             <div class="dropdown-item py-3 text-center text-muted">
                                                 No unread notifications
                                             </div>
                                         @endforelse
                                     </div>
                                 </div>
                             </li>
                             <li class="nav-item dropdown header-action-item">
                                 <a class="nav-link dropdown-toggle signup" href="#" id="customerMenu"
                                     data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                     <i class="fa-solid fa-user-check"></i>
                                 </a>
                                 <div class="dropdown-menu dropdown-menu-end customer-dropdown mt-4"
                                     aria-labelledby="customerMenu">
                                     <!-- Header -->
                                     <div class="dropdown-header">
                                         <small class="text-muted d-block">Welcome back!</small>
                                     </div>

                                     <!-- My Profile -->
                                     <a href="{{ route('customer.profile.index') }}" class="dropdown-item">
                                         <i class="fa-solid fa-user"></i>
                                         <span class="dropdown-item-label align-middle">Profile</span>
                                     </a>

                                     <a href="{{ route('frontend.user.wallet') }}" class="dropdown-item">
                                         <i class="fa-solid fa-wallet"></i>
                                         <span class="dropdown-item-label align-middle">Wallet</span>
                                     </a>

                                     <!-- Divider -->
                                     <div class="dropdown-divider"></div>

                                     <!-- Logout -->
                                     <form method="POST" action="{{ route('customer.logout') }}" class="m-0">
                                         @csrf
                                         <button type="submit"
                                             class="dropdown-item customer-logout-btn">Logout</button>
                                     </form>
                                 </div>
                             </li>
                         @else
                             <li
                                 class="nav-item header-action-item {{ request()->routeIs('user.register') ? 'active' : '' }}">
                                 <a class="nav-link signup" href="{{ route('user.register') }}"><i
                                         class="fa-solid fa-user-lock"></i></a>
                             </li>
                         @endif
                     </ul>
                 </div>
             </nav>
         </div>
     </header>
 </div>
 <script>
     document.addEventListener('click', function(e) {
         const navbar = document.getElementById('navbarSupportedContent');
         const navbarWrapper = document.querySelector('.navbar');

         if (
             navbar.classList.contains('show') &&
             !navbarWrapper.contains(e.target)
         ) {
             $('.navbar-collapse').collapse('hide');
         }
     });

     document.addEventListener('click', function(e) {
         const button = e.target.closest('[data-currency-form] button[name="currency"]');

         if (!button) {
             return;
         }

         const form = button.form;

         if (!form) {
             return;
         }

         if (form.dataset.submitting === 'true') {
             e.preventDefault();
             return;
         }

         form.dataset.submitting = 'true';

         const dropdown = button.closest('.dropdown');
         const dropdownMenu = button.closest('.dropdown-menu');
         const dropdownToggle = dropdown ? dropdown.querySelector('[data-toggle="dropdown"]') : null;

         if (dropdownToggle) {
             dropdownToggle.textContent = button.value;
             dropdownToggle.setAttribute('aria-expanded', 'false');
         }

         if (dropdownMenu) {
             dropdownMenu.classList.remove('show');
         }

         if (dropdown) {
             dropdown.classList.remove('show');
         }

         if (window.requestLoader) {
             window.requestLoader.show();
         }

         window.setTimeout(function() {
             document
                 .querySelectorAll('[data-currency-form] button[name="currency"]')
                 .forEach(function(currencyButton) {
                     currencyButton.disabled = true;
                 });
         }, 0);
     }, true);
 </script>

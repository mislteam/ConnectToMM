 @php
     $logo = \App\Models\GeneralSetting::where('type', 'file')->first();
     $customer = Auth::guard('customers')->user();
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
                 <a class="navbar-brand col-8 col-md-6 col-lg-3" href="{{ route('Index') }}">
                     <figure class="mb-0"><img src="{{ asset('general/logo/' . $logo->value) }}"
                             class="img-fluid w-75" alt=""></figure>
                 </a>
                 <button class="navbar-toggler collapsed" type="button" data-toggle="collapse"
                     data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                     aria-label="Toggle navigation">
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
                                 'joytel.packageview',
                                 'esim.roam',
                                 'esim.roamsearch',
                                 'esim.roampackageview',
                                 'physicalIndex',
                                 'physical.search',
                                 'physical.roam',
                                 'physical.roamsearch',
                                 'physical.roampackageview',
                             );
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
                                             <li
                                                 class="nav-item {{ request()->routeIs('esimIndex', 'esim.search') || (request()->routeIs('joytel.packageview') && request()->type == 'esim') ? 'active' : '' }}">
                                                 <a class="nav-link" data-request-loader
                                                     href="{{ route('esimIndex') }}">{{ $settings['joytel_title']->value ?? 'Joytel' }}</a>
                                             </li>
                                             <li
                                                 class="nav-item {{ request()->routeIs('esim.roam', 'esim.roamsearch', 'esim.roampackageview') ? 'active' : '' }}">
                                                 <a class="nav-link" data-request-loader
                                                     href="{{ route('esim.roam') }}">{{ $settings['roam_title']->value ?? 'Joytel' }}</a>
                                             </li>
                                         </ul>
                                     </div>
                                     <!-- Column 2 with submenu -->
                                     <div class="col-md-6">
                                         <h5 class="font-weight-bold">Physical SIM</h5>
                                         <ul class="list-unstyled drop-down-pages">
                                             <li
                                                 class="nav-item {{ request()->routeIs('physicalIndex', 'physical.search') || (request()->routeIs('joytel.packageview') && request()->type == 'physical') ? 'active' : '' }}">
                                                 <a class="nav-link" data-request-loader
                                                     href="{{ route('physicalIndex') }}">{{ $settings['joytel_title']->value ?? 'Joytel' }}</a>
                                             </li>
                                             <li
                                                 class="nav-item {{ request()->routeIs('physical.roam', 'physical.roamsearch', 'physical.roampackageview') ? 'active' : '' }}">
                                                 <a class="nav-link" data-request-loader
                                                     href="{{ route('physical.roam') }}">{{ $settings['roam_title']->value ?? 'Joytel' }}</a>
                                             </li>
                                         </ul>
                                     </div>
                                 </div>
                             </div>
                         </li>
                         <li class="nav-item {{ request()->routeIs('Faq') ? 'active' : '' }}">
                             <a class="nav-link" href="{{ route('Faq') }}">FAQ</a>
                         </li>
                         <li class="nav-item {{ request()->routeIs('Blog') ? 'active' : '' }}">
                             <a class="nav-link" href="{{ route('Blog') }}">Blog</a>
                         </li>
                         <li class="nav-item {{ request()->routeIs('Contact') ? 'active' : '' }}">
                             <a class="nav-link" href="{{ route('Contact') }}">Contact Us</a>
                         </li>
                         @php
                             $simType = strtolower((string) session('sim_type'));
                             $isPhysicalFlow =
                                 request()->routeIs('roam.physical.*', 'physical.*') ||
                                 str_contains($simType, 'physical');
                             $cartRoute = $isPhysicalFlow
                                 ? route('roam.physical.cartpage')
                                 : route('roam.esim.cartpage');
                         @endphp
                         <li
                             class="nav-item {{ request()->routeIs('roam.esim.*', 'roam.physical.*') ? 'active' : '' }}">
                             <a class="nav-link position-relative d-inline-block" href="{{ $cartRoute }}">
                                 <i class="fa-solid fa-cart-arrow-down fs-4"></i>
                                 <span class="position-absolute text-white badge badge-square bg-primary"
                                     style="top: -1px; right: -5px;"
                                     data-order-count="{{ count(session()->get('roam_order_cart', [])) }}"
                                     id="order_count">
                                     {{ count(session()->get('roam_order_cart', [])) }}
                                 </span>

                             </a>
                         </li>
                         @if ($customer)
                             <li class="nav-item dropdown">
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
                                         <i class="ti ti-user-circle me-2 fs-17 align-middle"></i>
                                         <span class="dropdown-item-label align-middle">Profile</span>
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
                             <li class="nav-item {{ request()->routeIs('user.register') ? 'active' : '' }}">
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
 </script>

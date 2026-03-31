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
                     <figure class="mb-0"><img src="{{ asset('assets/images/connect-logo-01.png') }}" alt=""
                             class="img-fluid"></figure>
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
                         <li class="nav-item dropdown megamenu">
                             <a class="nav-link dropdown-toggle" href="#" id="megamenu"
                                 data-toggle="dropdown">Our Services</a>
                             <div class="dropdown-menu" aria-labelledby="megamenu">
                                 <div class="row">
                                     <!-- Column 1 -->
                                     <div class="col-md-6">
                                         <h5 class="font-weight-bold">E-SIM</h5>
                                         <ul class="list-unstyled drop-down-pages">
                                             <li class="nav-item {{ request()->routeIs('esimIndex') ? 'active' : '' }}">
                                                 <a class="nav-link" href="{{ route('esimIndex') }}">Joytel</a>
                                             </li>
                                             <li class="nav-item {{ request()->routeIs('esim.roam') ? 'active' : '' }}">
                                                 <a class="nav-link" href="{{ route('esim.roam') }}">Roam</a>
                                             </li>
                                         </ul>
                                     </div>
                                     <!-- Column 2 with submenu -->
                                     <div class="col-md-6">
                                         <h5 class="font-weight-bold">Physical SIM</h5>
                                         <ul class="list-unstyled drop-down-pages">
                                             <li
                                                 class="nav-item {{ request()->routeIs('physicalIndex') ? 'active' : '' }}">
                                                 <a class="nav-link" href="{{ route('physicalIndex') }}">Joytel</a>
                                             </li>
                                             <li
                                                 class="nav-item {{ request()->routeIs('physical.roam') ? 'active' : '' }}">
                                                 <a class="nav-link" href="{{ route('physical.roam') }}">Roam</a>
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
                         <li class="nav-item {{ request()->routeIs('user.register') ? 'active' : '' }}">
                             <a class="nav-link signup" href="{{ route('user.register') }}"><i
                                     class="fa-solid fa-user-lock"></i>Sign
                                 Up</a>
                         </li>
                     </ul>
                 </div>
             </nav>
         </div>
     </header>
 </div>

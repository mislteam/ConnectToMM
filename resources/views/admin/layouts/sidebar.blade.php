<div class="sidenav-menu">
    <!-- Sidebar Hover Menu Toggle Button -->
    <button class="button-on-hover">
        <i class="ti ti-menu-4 fs-22 align-middle"></i>
    </button>

    <!-- Full Sidebar Menu Close Button -->
    <button class="button-close-offcanvas">
        <i class="ti ti-x align-middle"></i>
    </button>

    <div class="scrollbar" data-simplebar>
        <div class="sidenav-user mt-2">
            <div class="justify-content-between align-items-center">
                <div>
                    <a href="#" class="link-reset">
                        <img src="{{ asset('general/logo/' . $logo->value) }}" alt="user-image" class="img-fluid">
                    </a>

                </div>
            </div>
        </div>

        <!--- Sidenav Menu -->
        <ul class="side-nav">
            <li class="side-nav-item">
                <a href="{{ route('dashboard.admin') }}" aria-controls="sidebarDashboards" class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-layout-dashboard"></i></span>
                    <span class="menu-text" data-lang="dashboards">Dashboards</span>
                </a>
            </li>

            <li class="side-nav-item">
                <a href="{{ route('order.index') }}" class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-shopping-cart"></i></span>
                    <span class="menu-text">All Orders</span>
                </a>
            </li>

            <li class="side-nav-item">
                <a href="{{ route('customer.index') }}" class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-users-group"></i></span>
                    <span class="menu-text">All Customer</span>
                </a>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarjoytel" aria-expanded="false" aria-controls="sidebarjoytel"
                    class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-device-sim"></i></span>
                    <span class="menu-text">Joytel</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarjoytel">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('esim.index') }}" class="side-nav-link">
                                <span class="menu-text">E-Sim</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('physical.index') }}" class="side-nav-link">
                                <span class="menu-text">Physical Sim</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('region.index') }}" class="side-nav-link">
                                <span class="menu-text">Region</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">API Credentials</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarRoam" aria-expanded="false" aria-controls="sidebarRoam"
                    class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-device-sim"></i></span>
                    <span class="menu-text">Roam Sim</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarRoam">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('roamEsimIndex') }}" class="side-nav-link">
                                <span class="menu-text">E-Sim</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('roamphysical.Index') }}" class="side-nav-link">
                                <span class="menu-text">Physical Sim</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('roamSkuIndex') }}" class="side-nav-link">
                                <span class="menu-text">eSIM SKU List</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('roamphysical.SkuIndex') }}" class="side-nav-link">
                                <span class="menu-text">Physical SKU List</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('roamApiIndex') }}" class="side-nav-link">
                                <span class="menu-text">API Credentials</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('updateData') }}" class="side-nav-link">
                                <span class="menu-text">eSIM Update Data</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('physical.updateData') }}" class="side-nav-link">
                                <span class="menu-text">Physical Update Data</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="side-nav-item">
                <a href="{{ route('show.admin') }}" class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-users"></i></span>
                    <span class="menu-text" data-lang="all-admin"> All Admin </span>
                </a>
            </li>

            <li class="side-nav-item">
                <a href="{{ route('message.index') }}" class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-message"></i></span>
                    <span class="menu-text" data-lang="all-admin"> Messages </span>
                </a>
            </li>

            @php
                $isPageActive = request()->routeIs('blog.*');
                $isCatActive = request()->routeIs('blog.category.*');
            @endphp
            <li class="side-nav-item {{ $isPageActive ? 'active' : '' }}">
                <a data-bs-toggle="collapse" href="#sidebarBlog"
                    aria-expanded="{{ $isPageActive ? 'true' : 'false' }}" aria-controls="sidebarBlog"
                    class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-file-pencil"></i></span>
                    <span class="menu-text">Blog</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ $isPageActive ? 'show' : '' }}" id="sidebarBlog">
                    <ul class="sub-menu">
                        <x-page-nav route="blog.index" title="Blog" :active="$isPageActive" />
                        <x-page-nav route="blog.category.index" title="Categories" :active="$isCatActive" />
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarSetting" aria-expanded="false"
                    aria-controls="sidebarSetting" class="side-nav-link">

                    <span class="menu-icon"><i class="ti ti-settings"></i></span>
                    <span class="menu-text" data-lang="setting"> Setting </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarSetting">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('generalIndex') }}" class="side-nav-link">
                                <span class="menu-text" data-lang="general-setting">General Setting</span>
                            </a>
                        </li>

                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text" data-lang="permissions">Permissions</span>
                            </a>
                        </li>

                        <li class="side-nav-item">
                            <a href="{{ route('currency.index') }}" class="side-nav-link">
                                <span class="menu-text">Currency</span>
                            </a>
                        </li>

                    </ul>
                </div>
            </li>

            @php
                $isPageActive = request()->routeIs('page.*');
                $isFooterActive = request()->routeIs('footer.*');
                $isBannerActive = request()->routeIs('page.banner.*');
                $isFaqActive = request()->routeIs('page.faq.*');

                $home_section_keys = section_keys_by_page('home');
                $about_section_keys = section_keys_by_page('aboutus');
                $common_section_keys = section_keys_by_page('all');
                $isHomeActive =
                    request()->routeIs('page.section.edit') &&
                    in_array(request()->route('section_key'), $home_section_keys, true);
                $isAboutActive =
                    request()->routeIs('page.section.edit') &&
                    in_array(request()->route('section_key'), $about_section_keys, true);
                $isCommonActive =
                    request()->routeIs('page.section.edit') &&
                    in_array(request()->route('section_key'), $common_section_keys, true);

            @endphp
            <li class="side-nav-item {{ $isPageActive ? 'active' : '' }}">
                <a data-bs-toggle="collapse" href="#sidebarPage"
                    aria-expanded="{{ $isPageActive ? 'true' : 'false' }}" aria-controls="sidebarPage"
                    class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-clipboard"></i></span>
                    <span class="menu-text"> Page </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ $isPageActive ? 'show' : '' }}" id="sidebarPage">
                    <ul class="sub-menu">
                        <x-page-nav route="page.home.index" title="Home" :active="$isHomeActive" />
                        <x-page-nav route="page.about.index" title="About Us" :active="$isAboutActive" />
                        <x-page-nav route="page.banner.index" title="Banners" :active="$isBannerActive" />
                        <x-page-nav route="page.faq.index" title="FAQs" :active="$isFaqActive" />
                        <li class="side-nav-item">
                            <a data-bs-toggle="collapse" href="#footerPage" aria-expanded="false"
                                aria-controls="footerPage" class="side-nav-link">
                                <span class="menu-text"> Footer </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="footerPage">
                                <ul class="sub-menu">
                                    <x-page-nav route="footer.important.index" title="Important Links" />
                                    <x-page-nav route="footer.support.index" title="Support" />
                                    <x-page-nav route="footer.contact.index" title="Get In Touch" />
                                </ul>
                            </div>
                        </li>
                        <x-page-nav route="page.common.index" title="Common Sections" :active="$isCommonActive" />
                    </ul>
                </div>
            </li>
        </ul>
    </div>
</div>
<!-- Sidenav Menu End -->

<div class="sidenav-menu">
    <button class="button-on-hover">
        <i class="ti ti-menu-4 fs-22 align-middle"></i>
    </button>

    <button class="button-close-offcanvas">
        <i class="ti ti-x align-middle"></i>
    </button>

    <div class="scrollbar" data-simplebar>
        <div class="sidenav-user mt-2">
            <div class="justify-content-between align-items-center">
                <div>
                    <a href="#" class="link-reset">
                        <img src="{{ $settings['logo'] ? asset('general/logo/' . $settings['logo']->value) : '' }}"
                            alt="user-image" class="img-fluid">
                    </a>
                </div>
            </div>
        </div>

        @php
            $orderPermissions = ['order.menu'];
            $joytelPermissions = [
                'joytel.esim.menu',
                'joytel.physical.menu',
                'joytel.region.menu',
                'joytel.api-credentials.menu',
                'joytel.coupon.menu',
            ];
            $roamPermissions = [
                'roam.esim.menu',
                'roam.physical.menu',
                'roam.esimSKU.menu',
                'roam.physicalSKU.menu',
                'roam.api-credentials.menu',
                'roam.esim-update.menu',
                'roam.physical-update.menu',
                'roam.coupon.menu',
            ];
            $blogPermissions = ['blog.menu', 'blog.category.menu'];
            $generalPermissions = ['general.menu', 'permission.menu', 'currency.menu'];
            $pagePermissions = ['page.menu'];

            $isOrderActive = request()->routeIs('order.*');
            $isJoyActive =
                ((request()->is('joytel/*') && request()->routeIs('esim.*', 'physical.*')) ||
                    (request()->is('region/*') && request()->routeIs('region.*'))) ||
                request()->routeIs('joytel.coupon.*');
            $isRoamActive = request()->routeIs(
                'roamEsimEdit',
                'roamEsimIndex',
                'roamphysical.Index',
                'roamPhysicalEdit',
                'roam.coupon.*',
            );

            $isFooterActive = request()->routeIs(
                'footer.*',
                'footer.important.*',
                'footer.support.*',
                'footer.contact.*',
            );
            $isBannerActive = request()->routeIs('page.banner.*');
            $isFaqActive = request()->routeIs('page.faq.*');

            $homeSectionKeys = section_keys_by_page('home');
            $aboutSectionKeys = section_keys_by_page('aboutus');
            $commonSectionKeys = section_keys_by_page('all');

            $isHomeActive =
                request()->routeIs('page.section.edit') &&
                in_array(request()->route('section_key'), $homeSectionKeys, true);
            $isAboutActive =
                request()->routeIs('page.section.edit') &&
                in_array(request()->route('section_key'), $aboutSectionKeys, true);
            $isCommonActive =
                request()->routeIs('page.section.edit') &&
                in_array(request()->route('section_key'), $commonSectionKeys, true);
        @endphp

        <ul class="side-nav">
            <x-page-nav permission="dashboard.menu" :url="route('dashboard.index')" title="Dashboards" :active="request()->routeIs('dashboard.index')"
                :icon-exist="true" icon="ti-layout-dashboard" />

            @if (auth()->user()->can('order.menu'))
                <li class="side-nav-item {{ $isOrderActive ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#sidebarOrders"
                        aria-expanded="{{ $isOrderActive ? 'true' : 'false' }}" aria-controls="sidebarOrders"
                        class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-shopping-cart"></i></span>
                        <span class="menu-text">All Orders</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse {{ $isOrderActive ? 'show' : '' }}" id="sidebarOrders">
                        <ul class="sub-menu">
                            <li class="side-nav-item {{ request()->routeIs('order.index', 'order.show') ? 'active' : '' }}">
                                <a href="{{ route('order.index') }}" class="side-nav-link">
                                    <span class="menu-text">Roam Orders</span>
                                </a>
                            </li>
                            <li class="side-nav-item {{ request()->routeIs('order.joytel', 'order.joytel.show') ? 'active' : '' }}">
                                <a href="{{ route('order.joytel') }}" class="side-nav-link">
                                    <span class="menu-text">Joytel Orders</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif

            <x-page-nav permission="customer.menu" :url="route('customer.index')" title="All Customer" :active="request()->routeIs('customer.*')"
                :icon-exist="true" icon="ti-users-group" />

            <x-page-nav-group :active="$isJoyActive" :menu-text="$settings['joytel_title']->value ?? 'Joytel'" :any-permission="$joytelPermissions"
                side-link-name="sidebarjoytel" icon="ti-device-sim">
                <x-page-nav permission="joytel.esim.menu" :url="route('esim.index')" title="E-sim"
                    :active="request()->is('joytel/*') && request()->routeIs('esim.*')" />

                <x-page-nav permission="joytel.physical.menu" :url="route('physical.index')" title="Physical Sim"
                    :active="request()->is('joytel/*') && request()->routeIs('physical.*')" />

                <x-page-nav permission="joytel.region.menu" :url="route('region.index')" title="Region"
                    :active="request()->is('region/*') && request()->routeIs('region.*')" />

                <x-page-nav permission="joytel.api-credentials.menu" :url="route('joytelApiIndex')" title="API Credentials"
                    :active="request()->routeIs('joytelApiIndex')" />

                <x-page-nav permission="joytel.coupon.menu" :url="route('joytel.coupon.index')" title="Coupon"
                    :active="request()->routeIs('joytel.coupon.*')" />
            </x-page-nav-group>

            <x-page-nav-group :active="$isRoamActive" :menu-text="$settings['roam_title']->value ?? 'ROAM'" :any-permission="$roamPermissions"
                side-link-name="sidebarRoam" icon="ti-device-sim">
                <x-page-nav permission="roam.esim.menu" :url="route('roamEsimIndex')" title="E-sim"
                    :active="request()->routeIs('roamEsimEdit', 'roamEsimIndex')" />

                <x-page-nav permission="roam.physical.menu" :url="route('roamphysical.Index')" title="Physical Sim"
                    :active="request()->routeIs('roamphysical.Index', 'roamPhysicalEdit')" />

                <x-page-nav permission="roam.esimSKU.menu" :url="route('roamSkuIndex')" title="eSIM SKU List" />
                <x-page-nav permission="roam.physicalSKU.menu" :url="route('roamphysical.SkuIndex')" title="Physical SKU List" />
                <x-page-nav permission="roam.api-credentials.menu" :url="route('roamApiIndex')" title="API Credentials" />
                <x-page-nav permission="roam.esim-update.menu" :url="route('updateData')" title="eSIM Update Data" />
                <x-page-nav permission="roam.physical-update.menu" :url="route('physical.updateData')" title="Physical Update Data" />
                <x-page-nav :url="route('roam.coupon.index')" title="Coupon" :active="request()->routeIs('roam.coupon.*')" />
            </x-page-nav-group>

            <x-page-nav permission="admin.menu" :url="route('show.admin')" title="All Admin"
                :active="request()->routeIs('admin.edit', 'show.admin', 'create.admin', 'view.admin')" :icon-exist="true"
                icon="ti-users" />

            <x-page-nav permission="message.menu" :url="route('message.index')" title="Messages" :active="request()->routeIs('message.*')"
                :icon-exist="true" icon="ti-message" />

            <x-page-nav-group :any-permission="$blogPermissions" menu-text="Blog" :active="request()->routeIs('blog.*', 'blog.category.*')"
                side-link-name="sidebarBlog" icon="ti-file-pencil">
                <x-page-nav permission="blog.menu" :url="route('blog.index')" title="Blog" :active="request()->routeIs('blog.*')" />
                <x-page-nav permission="blog.category.menu" :url="route('blog.category.index')" title="Categories"
                    :active="request()->routeIs('blog.category.*')" />
            </x-page-nav-group>

            <x-page-nav-group :active="request()->is('setting/*') || request()->routeIs('currency.*', 'permission.*')" menu-text="Setting"
                side-link-name="sidebarSetting" icon="ti-settings" :any-permission="$generalPermissions">
                <x-page-nav permission="general.menu" :url="route('generalIndex')" title="General Setting"
                    :active="request()->routeIs('generalEdit', 'generalIndex')" />

                <x-page-nav permission="permission.menu" :url="route('permission.index')" title="Permissions"
                    :active="request()->routeIs('permission.*')" />

                <x-page-nav permission="currency.menu" :url="route('currency.index')" title="Currency" :active="request()->routeIs('currency.*')" />
            </x-page-nav-group>

            <x-page-nav permission="payment.menu" :url="route('admin.payment.index')" title="Payment Setting"
                :active="request()->routeIs('admin.payment.*')" :icon-exist="true" icon="ti-brand-mastercard" />

            <x-page-nav-group :active="request()->routeIs('page.*', 'footer.important.*', 'footer.support.*', 'footer.contact.*')"
                menu-text="Page" side-link-name="sidebarPage" icon="ti-clipboard" :any-permission="$pagePermissions">
                <x-page-nav permission="page.menu" :url="route('page.home.index')" title="Home" :active="$isHomeActive" />
                <x-page-nav permission="page.menu" :url="route('page.about.index')" title="About Us" :active="$isAboutActive" />
                <x-page-nav permission="page.menu" :url="route('page.banner.index')" title="Banners" :active="$isBannerActive" />
                <x-page-nav permission="page.menu" :url="route('page.faq.index')" title="FAQs" :active="$isFaqActive" />

                <x-page-nav-group :active="$isFooterActive" menu-text="Footer" side-link-name="footerPage" :any-permission="$pagePermissions">
                    <x-page-nav permission="page.menu" :url="route('footer.important.index')" title="Important Links"
                        :active="request()->routeIs('footer.important.*')" />
                    <x-page-nav permission="page.menu" :url="route('footer.support.index')" title="Support"
                        :active="request()->routeIs('footer.support.*')" />
                    <x-page-nav permission="page.menu" :url="route('footer.contact.index')" title="Get In Touch"
                        :active="request()->routeIs('footer.contact.*')" />
                </x-page-nav-group>

                <x-page-nav permission="page.menu" :url="route('page.common.index')" title="Common Sections" :active="$isCommonActive" />
                <x-page-nav permission="page.menu" :url="route('page.refunds.index')" title="Refunds Policy"
                    :active="request()->routeIs('page.refunds.*')" />
            </x-page-nav-group>
        </ul>
    </div>
</div>

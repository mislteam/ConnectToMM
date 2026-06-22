@if (auth()->user()->hasAnyPermission($anyPermission))
    <li class="side-nav-item {{ $active ? 'active' : '' }}">
        <a data-bs-toggle="collapse" href="{{ '#' . $sideLinkName }}" aria-expanded="{{ $active ? 'ture' : 'false' }}"
            aria-controls="sidebarjoytel" class="side-nav-link">
            @if ($icon)
                <span class="menu-icon"><i class="ti {{ $icon }}"></i></span>
            @endif
            <span class="menu-text">{{ $menuText }}</span>
            <span class="menu-arrow"></span>
        </a>
        <div class="collapse {{ $active ? 'show' : '' }}" id="{{ $sideLinkName }}">
            <ul class="sub-menu">
                {{ $slot }}
            </ul>
        </div>
    </li>
@endif

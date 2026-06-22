@if (blank($permission) || auth()->user()->can($permission))
    <li class="side-nav-item {{ $active ? 'active' : '' }}">
        <a href="{{ $url }}" class="side-nav-link {{ $active ? 'active' : '' }}">
            @if ($iconExist)
                <span class="menu-icon"><i class="ti {{ $icon }}"></i></span>
            @endif
            <span class="menu-text">{{ $title }}</span>
        </a>
    </li>
@endif

@props(['route', 'title', 'active' => false])

<li class="side-nav-item {{ $active ? 'active' : '' }}">
    <a href="{{ route($route) }}" class="side-nav-link {{ $active ? 'active' : '' }}">
        <span class="menu-text">{{ $title }}</span>
    </a>
</li>

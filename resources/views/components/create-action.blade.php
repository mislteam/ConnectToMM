@if (blank($permission) || auth()->user()->can($permission))
    <a href="{{ $url ?? '#' }}" {{ $attributes->merge(['class' => 'btn btn-primary ms-1']) }}
        @if ($targetName) data-bs-toggle="modal" 
    data-bs-target="#{{ $targetName }}" @endif>
        @if ($icon)
            <i class="ti {{ $icon }} fs-sm me-2"></i>
        @endif
        {{ $menuText }}
    </a>
@endif

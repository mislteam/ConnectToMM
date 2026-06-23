@if (blank($permission) || auth()->user()->can($permission))
    <a href="{{ $url ?? '#' }}" {{ $attributes->merge(['class' => 'btn btn-light btn-icon btn-sm rounded-circle']) }}
        @if ($dataId) data-id="{{ $dataId }}" data-bs-toggle="modal" data-bs-target="#{{ $targetName }}" @endif>
        <i class="ti {{ $icon }} fs-lg"></i>
    </a>
@endif

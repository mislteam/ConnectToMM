<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ActionButton extends Component
{
    public function __construct(
        public string $icon,
        public ?string $target = null,
        public ?string $url = null,
        public ?string $dataId = null,
        public ?string $targetName = null,
        public ?string $permission = null,
    ) {}
    public function render(): View|Closure|string
    {
        return view('components.action-button');
    }
}

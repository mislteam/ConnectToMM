<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CreateAction extends Component
{
    public function __construct(
        public string $menuText,
        public ?string $permission = null,
        public ?string $icon = null,
        public ?string $targetName = null,
        public ?string $url = null,
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.create-action');
    }
}

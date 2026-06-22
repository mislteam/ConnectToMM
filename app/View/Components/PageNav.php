<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PageNav extends Component
{
    public function __construct(
        public string $url,
        public ?string $title = null,
        public bool $active = false,
        public bool $iconExist = false,
        public ?string $icon = null,
        public ?string $permission = null,
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.page-nav');
    }
}

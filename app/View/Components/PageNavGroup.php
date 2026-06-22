<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PageNavGroup extends Component
{
    public function __construct(
        public string $menuText,
        public bool $active,
        public ?string $icon = null,
        public array $anyPermission = [],
        public string $sideLinkName = ""
    ) {
        //
    }

    public function render(): View|Closure|string
    {
        return view('components.page-nav-group');
    }
}

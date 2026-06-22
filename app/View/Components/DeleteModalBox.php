<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DeleteModalBox extends Component
{
    public function __construct(
        public string $id,
        public string $message,
    ) {
        //
    }

    public function render(): View|Closure|string
    {
        return view('components.delete-modal-box');
    }
}

<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Modal extends Component
{
    public string $title;
    public bool $showClose = false;
    /**
     * Create a new component instance.
     */
    public function __construct(string $title, bool $showClose = false)
    {
        $this->title = $title;
        $this->showClose = $showClose;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.modal');
    }
}
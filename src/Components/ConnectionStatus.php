<?php

namespace Hibira\Livewire\Components;

use Illuminate\View\Component;

/** <x-hibira-connection-status />: a dot that shows whether the page's realtime connection is up. */
class ConnectionStatus extends Component
{
    public function __construct(public bool $label = false)
    {
    }

    public function render()
    {
        return view('hibira-livewire::components.connection-status');
    }
}

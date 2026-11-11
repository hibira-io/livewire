<?php

namespace Hibira\Livewire\Components;

use Hibira\Livewire\HibiraLivewire;
use Illuminate\View\Component;

/**
 * <x-hibira-whos-here />: avatars of the signed-in people on this page right
 * now. room="order-42" puts everyone on any page with that room together.
 * Needs a signed-in user (it joins a presence channel) and window.Echo.
 */
class WhosHere extends Component
{
    public string $channel;

    public function __construct(?string $room = null, public int $max = 5, public bool $self = false)
    {
        $room = $room !== null && $room !== '' ? HibiraLivewire::cleanRoom($room) : HibiraLivewire::roomFor(request()->path());
        $this->channel = 'hibira-livewire.room.'.$room;
    }

    public function shouldRender(): bool
    {
        return auth()->check();
    }

    public function render()
    {
        return view('hibira-livewire::components.whos-here', ['me' => auth()->id()]);
    }
}

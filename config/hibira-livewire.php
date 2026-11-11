<?php

return [

    // Broadcast job batch progress to <livewire:hibira-batch-progress> bars.
    // false: no listener, no messages.
    'batches' => (bool) env('HIBIRA_LIVEWIRE_BATCHES', true),

];

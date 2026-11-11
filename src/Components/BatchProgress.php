<?php

namespace Hibira\Livewire\Components;

use Hibira\Livewire\Events\BatchProgressed;
use Hibira\Livewire\HibiraLivewire;
use Illuminate\Support\Facades\Bus;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * <livewire:hibira-batch-progress :batch="$batchId" />: a job batch's
 * progress bar that moves as the jobs run, with no wire:poll. When the batch
 * finishes it dispatches the Livewire event "hibira-batch-finished" with
 * {batchId, failed}, so the page can react with #[On('hibira-batch-finished')].
 */
class BatchProgress extends Component
{
    #[Locked]
    public string $batch = '';

    #[Locked]
    public ?string $label = null;

    /** @var array{total: int, processed: int, failed: int, progress: int, finished: bool, cancelled: bool} */
    public array $state = ['total' => 0, 'processed' => 0, 'failed' => 0, 'progress' => 0, 'finished' => false, 'cancelled' => false];

    public function mount(string $batch, ?string $label = null): void
    {
        $this->batch = $batch;
        $this->label = $label;
        $this->refreshState();
    }

    /** Called by the bar when it (re)connects, in case it missed an update. */
    public function refreshState(): void
    {
        $found = Bus::findBatch($this->batch);
        if ($found) {
            $u = BatchProgressed::from($found);
            $this->state = ['total' => $u->total, 'processed' => $u->processed, 'failed' => $u->failed, 'progress' => $u->progress, 'finished' => $u->finished, 'cancelled' => $u->cancelled];
        }
    }

    public function render()
    {
        return view('hibira-livewire::livewire.batch-progress', [
            'channel' => HibiraLivewire::batchChannel($this->batch),
        ]);
    }
}

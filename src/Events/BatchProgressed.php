<?php

namespace Hibira\Livewire\Events;

use Hibira\Livewire\HibiraLivewire;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

/** A job batch moved: sent to the batch's private channel as "progress". */
class BatchProgressed implements ShouldBroadcastNow
{
    public function __construct(
        public string $batchId,
        public int $total,
        public int $processed,
        public int $failed,
        public int $progress,
        public bool $finished,
        public bool $cancelled,
    ) {
    }

    public static function from(Batch $batch): self
    {
        return new self(
            $batch->id,
            $batch->totalJobs,
            $batch->processedJobs(),
            $batch->failedJobs,
            $batch->progress(),
            $batch->finished(),
            $batch->cancelled(),
        );
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(HibiraLivewire::batchChannel($this->batchId));
    }

    public function broadcastAs(): string
    {
        return 'progress';
    }
}

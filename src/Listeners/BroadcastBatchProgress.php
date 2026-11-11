<?php

namespace Hibira\Livewire\Listeners;

use Hibira\Livewire\Events\BatchProgressed;
use Illuminate\Bus\BatchRepository;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;

/**
 * After each batched job, tells the batch's progress bars. Sends only when
 * the whole-number percentage, the failure count or the finished state
 * changed, so a batch of 10,000 jobs costs at most about 100 messages.
 */
class BroadcastBatchProgress
{
    public function __construct(protected BatchRepository $batches, protected Cache $cache, protected Encrypter $encrypter)
    {
    }

    public function handle(JobProcessed|JobFailed $event): void
    {
        $batchId = $this->batchId($event->job->payload());
        if ($batchId === null || ! ($batch = $this->batches->find($batchId))) {
            return;
        }

        $update = BatchProgressed::from($batch);
        $key = 'hibira-livewire:batch:'.$batchId;
        $mark = $update->progress.'/'.$update->failed.'/'.(int) $update->finished.'/'.(int) $update->cancelled;
        if ($this->cache->get($key) === $mark) {
            return;
        }
        $this->cache->put($key, $mark, now()->addDay());

        try {
            event($update);
        } catch (\Throwable $e) {
            report($e); // a broadcast problem must not fail the job
        }
    }

    /** Reads Batchable's $batchId from the serialized job, without waking the job (and its models) up. */
    protected function batchId(array $payload): ?string
    {
        // Newer Laravel versions put the batch ID in the payload itself.
        if (is_string($payload['data']['batchId'] ?? null) && $payload['data']['batchId'] !== '') {
            return $payload['data']['batchId'];
        }
        $command = $payload['data']['command'] ?? null;
        if (! is_string($command)) {
            return null;
        }
        if (! str_starts_with($command, 'O:')) {
            try {
                $command = (string) $this->encrypter->decrypt($command);
            } catch (\Throwable) {
                return null;
            }
        }

        return preg_match('/s:7:"batchId";s:\d+:"([A-Za-z0-9-]{1,64})";/', $command, $m) === 1 ? $m[1] : null;
    }
}

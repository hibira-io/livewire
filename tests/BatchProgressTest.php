<?php

namespace Hibira\Livewire\Tests;

use Hibira\Livewire\Components\BatchProgress;
use Hibira\Livewire\Events\BatchProgressed;
use Hibira\Livewire\HibiraLivewire;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

require_once __DIR__.'/Jobs.php';

class BatchProgressTest extends TestCase
{
    public function test_each_step_of_a_batch_is_broadcast_once(): void
    {
        Event::fake([BatchProgressed::class]);

        $batch = Bus::batch([new ResizeImage(1), new ResizeImage(2), new ResizeImage(3), new ResizeImage(4)])->dispatch();

        $sent = [];
        Event::assertDispatched(BatchProgressed::class, function (BatchProgressed $e) use ($batch, &$sent) {
            $this->assertSame($batch->id, $e->batchId);
            $sent[] = $e->progress.($e->finished ? ' done' : '');

            return true;
        });
        $this->assertSame(['25', '50', '75', '100 done'], $sent);

        $last = new BatchProgressed($batch->id, 4, 4, 0, 100, true, false);
        $this->assertSame('private-'.HibiraLivewire::batchChannel($batch->id), $last->broadcastOn()->name);
        $this->assertSame('progress', $last->broadcastAs());
    }

    public function test_encrypted_jobs_are_followed_too(): void
    {
        Event::fake([BatchProgressed::class]);

        Bus::batch([new EncryptedJob(1), new EncryptedJob(2)])->dispatch();

        Event::assertDispatchedTimes(BatchProgressed::class, 2);
    }

    public function test_jobs_outside_a_batch_send_nothing(): void
    {
        Event::fake([BatchProgressed::class]);

        ResizeImage::dispatch();

        Event::assertNotDispatched(BatchProgressed::class);
    }

    public function test_channel_needs_the_signature(): void
    {
        $user = User::create(['name' => 'Aoi', 'email' => 'aoi@example.com']);
        $authorize = Broadcast::driver()->getChannels()['hibira-livewire.batch.{batch}.{signature}'];

        $this->assertTrue($authorize($user, 'abc-123', HibiraLivewire::batchSignature('abc-123')));
        $this->assertFalse($authorize($user, 'abc-123', HibiraLivewire::batchSignature('abc-124')));
        $this->assertFalse($authorize($user, 'abc-123', 'guess'));
    }

    public function test_component_shows_the_current_state(): void
    {
        Event::fake([BatchProgressed::class]);
        $batch = Bus::batch([new ResizeImage(1), new ResizeImage(2)])->allowFailures()->dispatch();

        Livewire::test(BatchProgress::class, ['batch' => $batch->id, 'label' => 'Resizing photos'])
            ->assertSet('state.progress', 100)
            ->assertSet('state.finished', true)
            ->assertSee('Resizing photos')
            ->assertSee(HibiraLivewire::batchChannel($batch->id))
            ->assertSee('role="progressbar"', false)
            ->call('refreshState')
            ->assertSet('state.processed', 2);
    }

    public function test_unknown_batch_renders_empty_bar(): void
    {
        Livewire::test(BatchProgress::class, ['batch' => 'nope'])
            ->assertSet('state.progress', 0)
            ->assertSet('state.finished', false);
    }
}

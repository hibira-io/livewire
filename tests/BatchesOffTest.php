<?php

namespace Hibira\Livewire\Tests;

use Hibira\Livewire\Events\BatchProgressed;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;

require_once __DIR__.'/Jobs.php';

class BatchesOffTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('hibira-livewire.batches', false);
    }

    public function test_can_be_turned_off(): void
    {
        Event::fake([BatchProgressed::class]);

        Bus::batch([new ResizeImage(1)])->dispatch();

        Event::assertNotDispatched(BatchProgressed::class);
    }
}

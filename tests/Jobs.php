<?php

namespace Hibira\Livewire\Tests;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class ResizeImage implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(public int $n = 0)
    {
    }

    public function handle(): void
    {
    }
}

class EncryptedJob extends ResizeImage implements ShouldBeEncrypted
{
}

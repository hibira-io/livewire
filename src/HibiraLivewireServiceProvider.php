<?php

namespace Hibira\Livewire;

use Hibira\Livewire\Components\BatchProgress;
use Hibira\Livewire\Components\ConnectionStatus;
use Hibira\Livewire\Components\WhosHere;
use Hibira\Livewire\Listeners\BroadcastBatchProgress;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class HibiraLivewireServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/hibira-livewire.php', 'hibira-livewire');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'hibira-livewire');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'hibira-livewire');

        Blade::component('hibira-whos-here', WhosHere::class);
        Blade::component('hibira-connection-status', ConnectionStatus::class);
        Livewire::component('hibira-batch-progress', BatchProgress::class);

        Broadcast::channel('hibira-livewire.room.{room}', fn ($user, string $room) => HibiraLivewire::joinRoom($user, $room));
        Broadcast::channel('hibira-livewire.batch.{batch}.{signature}', fn ($user, string $batch, string $signature) => hash_equals(HibiraLivewire::batchSignature($batch), $signature));

        if (config('hibira-livewire.batches', true)) {
            Event::listen([JobProcessed::class, JobFailed::class], BroadcastBatchProgress::class);
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../config/hibira-livewire.php' => config_path('hibira-livewire.php')], 'hibira-livewire-config');
            $this->publishes([__DIR__.'/../resources/views' => resource_path('views/vendor/hibira-livewire')], 'hibira-livewire-views');
            $this->publishes([__DIR__.'/../lang' => $this->app->langPath('vendor/hibira-livewire')], 'hibira-livewire-lang');
        }
    }
}

<div
    x-data="{
        state: @js($state),
        channel: @js($channel),
        init() {
            if (! window.Echo) return;
            window.Echo.private(this.channel)
                .listen('.progress', (e) => this.apply(e))
                // Jobs may have run between the page render and the subscription.
                .subscribed(() => $wire.refreshState().then(() => this.apply($wire.state)));
        },
        apply(e) {
            const was = this.state.finished;
            this.state = { total: e.total, processed: e.processed, failed: e.failed, progress: e.progress, finished: e.finished, cancelled: e.cancelled };
            if (e.finished && ! was) $wire.dispatch('hibira-batch-finished', { batchId: $wire.batch, failed: e.failed });
        },
        destroy() { if (window.Echo) window.Echo.leave(this.channel); },
    }"
    class="hibira-batch-progress"
    role="progressbar"
    aria-valuemin="0"
    aria-valuemax="100"
    :aria-valuenow="state.progress"
    @if ($label) aria-label="{{ $label }}" @endif
    style="font: inherit;"
>
    @if ($label)
        <div style="display:flex;justify-content:space-between;gap:1rem;margin-bottom:.375rem;font-size:.875rem;">
            <span>{{ $label }}</span>
            <span x-text="state.progress + '%'" style="font-variant-numeric:tabular-nums;opacity:.75;"></span>
        </div>
    @endif
    <div style="height:.5rem;border-radius:9999px;background:color-mix(in srgb, currentColor 12%, transparent);overflow:hidden;">
        <div
            :style="{ width: state.progress + '%', background: state.failed > 0 ? '#e5484d' : (state.finished ? '#30a46c' : '#3e63dd') }"
            style="height:100%;width:{{ $state['progress'] }}%;background:#3e63dd;border-radius:inherit;transition:width .3s ease;"
        ></div>
    </div>
    <div style="margin-top:.375rem;font-size:.75rem;opacity:.75;font-variant-numeric:tabular-nums;" aria-live="polite">
        <span x-show="!state.finished" x-text="@js(__('hibira-livewire::batch.running', ['processed' => ':p', 'total' => ':t'])).replace(':p', state.processed).replace(':t', state.total)"></span>
        <span x-show="state.finished && !state.cancelled" x-cloak>{{ __('hibira-livewire::batch.done') }}</span>
        <span x-show="state.cancelled" x-cloak>{{ __('hibira-livewire::batch.cancelled') }}</span>
        <span x-show="state.failed > 0" x-cloak x-text="' · ' + @js(__('hibira-livewire::batch.failed', ['count' => ':n'])).replace(':n', state.failed)"></span>
    </div>
</div>

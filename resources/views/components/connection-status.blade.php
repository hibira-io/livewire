<span
    wire:ignore
    x-data="{
        state: 'connecting',
        init() {
            const pusher = window.Echo && window.Echo.connector && window.Echo.connector.pusher;
            if (! pusher) { this.state = 'unavailable'; return; }
            this.state = pusher.connection.state;
            pusher.connection.bind('state_change', (s) => { this.state = s.current });
        },
        get color() { return { connected: '#30a46c', connecting: '#f5a524', initialized: '#f5a524' }[this.state] || '#e5484d' },
        get text() { return (@js([
            'connected' => __('hibira-livewire::status.connected'),
            'connecting' => __('hibira-livewire::status.connecting'),
            'initialized' => __('hibira-livewire::status.connecting'),
        ]))[this.state] || @js(__('hibira-livewire::status.offline')) },
    }"
    {{ $attributes->merge(['class' => 'hibira-connection-status']) }}
    :title="text"
    role="status"
    style="display:inline-flex;align-items:center;gap:.375rem;font-size:.75rem;"
>
    <span :style="'width:.5rem;height:.5rem;border-radius:9999px;background:' + color" aria-hidden="true"></span>
    @if ($label)
        <span x-text="text"></span>
    @else
        <span x-text="text" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;"></span>
    @endif
</span>

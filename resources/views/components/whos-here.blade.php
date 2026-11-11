<div
    wire:ignore
    x-data="{
        channel: @js($channel),
        me: @js($me),
        self: @js($self),
        max: @js($max),
        people: [],
        init() {
            if (! window.Echo) return;
            window.Echo.join(this.channel)
                .here((users) => { this.people = users })
                .joining((user) => { this.people = this.people.filter((p) => p.id !== user.id).concat([user]) })
                .leaving((user) => { this.people = this.people.filter((p) => p.id !== user.id) });
        },
        destroy() { if (window.Echo) window.Echo.leave(this.channel); },
        get others() { return this.people.filter((p) => this.self || String(p.id) !== String(this.me)) },
        initials(name) { return String(name || '?').trim().split(/\s+/).map((w) => w[0]).slice(0, 2).join('').toUpperCase() },
        color(id) { let h = 0; for (const c of String(id)) h = (h * 31 + c.charCodeAt(0)) % 360; return 'hsl(' + h + ' 55% 45%)' },
    }"
    {{ $attributes->merge(['class' => 'hibira-whos-here']) }}
    style="display:inline-flex;align-items:center;"
    aria-live="polite"
>
    <template x-for="(person, i) in others.slice(0, max)" :key="person.id">
        <span
            :title="person.name"
            :aria-label="person.name"
            :style="'display:inline-flex;align-items:center;justify-content:center;width:1.75rem;height:1.75rem;border-radius:9999px;overflow:hidden;font-size:.6875rem;font-weight:600;color:#fff;box-shadow:0 0 0 2px var(--hibira-ring, #fff);' + (i ? 'margin-left:-.375rem;' : '') + 'background:' + color(person.id)"
        >
            <template x-if="person.avatar"><img :src="person.avatar" alt="" style="width:100%;height:100%;object-fit:cover;"></template>
            <template x-if="! person.avatar"><span x-text="initials(person.name)"></span></template>
        </span>
    </template>
    <span
        x-show="others.length > max"
        x-cloak
        x-text="'+' + (others.length - max)"
        :title="others.slice(max).map((p) => p.name).join(', ')"
        style="margin-left:.375rem;font-size:.75rem;opacity:.75;"
    ></span>
</div>

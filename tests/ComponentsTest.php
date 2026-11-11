<?php

namespace Hibira\Livewire\Tests;

use Hibira\Livewire\HibiraLivewire;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Broadcast;

class ComponentsTest extends TestCase
{
    public function test_whos_here_needs_a_signed_in_user(): void
    {
        $this->assertSame('', trim(Blade::render('<x-hibira-whos-here />')));
    }

    public function test_whos_here_joins_the_page_room(): void
    {
        $this->actingAs(User::create(['name' => 'Aoi', 'email' => 'aoi@example.com']));
        $this->get('/orders/42');

        $html = Blade::render('<x-hibira-whos-here :max="3" class="ml-2" />');

        $this->assertStringContainsString("'hibira-livewire.room.".HibiraLivewire::roomFor('orders/42')."'", $html);
        $this->assertStringContainsString('class="hibira-whos-here ml-2"', $html);
        $this->assertStringContainsString('window.Echo.join', $html);

        $html = Blade::render('<x-hibira-whos-here room="order 42/<x>" />');
        $this->assertStringContainsString("'hibira-livewire.room.order-42--x-'", $html);
    }

    public function test_room_channel_returns_the_member(): void
    {
        $user = User::create(['name' => 'Aoi Tanaka', 'email' => 'aoi@example.com']);
        $join = Broadcast::driver()->getChannels()['hibira-livewire.room.{room}'];

        $this->assertSame(['id' => $user->id, 'name' => 'Aoi Tanaka', 'avatar' => null], $join($user, 'p123'));

        HibiraLivewire::memberUsing(fn ($u) => ['avatar' => 'https://example.com/a.png']);
        $this->assertSame('https://example.com/a.png', $join($user, 'p123')['avatar']);

        HibiraLivewire::authorizeRoomsUsing(fn ($u, $room) => $room !== 'secret');
        $this->assertFalse($join($user, 'secret'));
        $this->assertIsArray($join($user, 'p123'));
    }

    public function test_connection_status(): void
    {
        app()->setLocale('ja');
        $html = Blade::render('<x-hibira-connection-status label />');

        $this->assertStringContainsString('role="status"', $html);
        $this->assertStringContainsString('state_change', $html);
        $this->assertStringContainsString('ライブ', $html);
    }

    public function test_every_language_has_every_string(): void
    {
        $base = __DIR__.'/../lang';
        foreach (['batch', 'status'] as $file) {
            $keys = array_keys(require "$base/en/$file.php");
            foreach (glob("$base/*/$file.php") as $path) {
                $this->assertSame($keys, array_keys(require $path), $path);
            }
        }
    }
}

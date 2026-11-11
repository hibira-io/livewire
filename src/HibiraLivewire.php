<?php

namespace Hibira\Livewire;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Settings for the who's-here rooms and batch channels. Use the facade-free
 * static API from a service provider:
 *
 *     HibiraLivewire::memberUsing(fn ($user) => ['name' => $user->name, 'avatar' => $user->avatar_url]);
 *     HibiraLivewire::authorizeRoomsUsing(fn ($user, string $room) => $user->team_id !== null);
 */
class HibiraLivewire
{
    /** @var (Closure(Authenticatable): array<string, mixed>)|null */
    protected static ?Closure $memberUsing = null;

    /** @var (Closure(Authenticatable, string): bool)|null */
    protected static ?Closure $authorizeRooms = null;

    public static function memberUsing(?Closure $callback): void
    {
        static::$memberUsing = $callback;
    }

    public static function authorizeRoomsUsing(?Closure $callback): void
    {
        static::$authorizeRooms = $callback;
    }

    /** What other people in a room see of $user: id, name and, when you have one, avatar (a URL). */
    public static function member(Authenticatable $user): array
    {
        $info = static::$memberUsing ? (static::$memberUsing)($user) : [];

        return array_merge([
            'id' => $user->getAuthIdentifier(),
            'name' => (string) ($user->name ?? $user->email ?? 'User '.$user->getAuthIdentifier()),
            'avatar' => $user->avatar_url ?? $user->profile_photo_url ?? null,
        ], $info);
    }

    /** The presence channel answer for a room: the member array, or false. */
    public static function joinRoom(Authenticatable $user, string $room): array|false
    {
        if (static::$authorizeRooms && ! (static::$authorizeRooms)($user, $room)) {
            return false;
        }

        return static::member($user);
    }

    /** A room name for a path: the same people on the same page meet. */
    public static function roomFor(string $path): string
    {
        return 'p'.substr(hash('sha256', '/'.ltrim($path, '/')), 0, 24);
    }

    public static function cleanRoom(string $room): string
    {
        $room = preg_replace('/[^A-Za-z0-9_\-=@,;]/', '-', $room) ?? '';

        return substr($room, 0, 120);
    }

    /** The batch channel's signature: only pages that rendered the progress bar know it. */
    public static function batchSignature(string $batchId): string
    {
        return substr(hash_hmac('sha256', 'hibira-livewire.batch.'.$batchId, (string) config('app.key')), 0, 20);
    }

    public static function batchChannel(string $batchId): string
    {
        return 'hibira-livewire.batch.'.$batchId.'.'.static::batchSignature($batchId);
    }

    public static function flush(): void
    {
        static::$memberUsing = null;
        static::$authorizeRooms = null;
    }
}

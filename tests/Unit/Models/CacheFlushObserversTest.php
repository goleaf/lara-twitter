<?php

namespace Tests\Unit\Models;

use App\Models\Block;
use App\Models\Mute;
use App\Models\MutedTerm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use ReflectionProperty;
use Tests\TestCase;

class CacheFlushObserversTest extends TestCase
{
    use RefreshDatabase;

    private const CACHE_PROPS = [
        'activeMutedTermsCache',
        'activeNotificationMutedTermsCache',
        'excludedUserIdsCache',
        'mutedUserIdsCache',
        'blockedUserIdsCache',
        'blockedByUserIdsCache',
        'followingIdsCache',
        'followingIdsWithSelfCache',
    ];

    public function test_block_flushes_caches_for_blocker(): void
    {
        $blocker = User::factory()->create();
        $blocked = User::factory()->create();
        $this->actingAs($blocker);

        $this->primeCaches($blocker);

        Block::query()->create([
            'blocker_id' => $blocker->id,
            'blocked_id' => $blocked->id,
        ]);

        $this->assertCachesCleared($this->currentUser());
    }

    public function test_block_flushes_caches_for_blocked(): void
    {
        $blocker = User::factory()->create();
        $blocked = User::factory()->create();
        $this->actingAs($blocked);

        $this->primeCaches($blocked);

        Block::query()->create([
            'blocker_id' => $blocker->id,
            'blocked_id' => $blocked->id,
        ]);

        $this->assertCachesCleared($this->currentUser());
    }

    public function test_block_does_not_flush_unrelated_user(): void
    {
        $blocker = User::factory()->create();
        $blocked = User::factory()->create();
        $viewer = User::factory()->create();

        $this->actingAs($viewer);
        $this->primeCaches($viewer);

        Block::query()->create([
            'blocker_id' => $blocker->id,
            'blocked_id' => $blocked->id,
        ]);

        $this->assertCachesPrimed($this->currentUser());
    }

    public function test_block_does_not_flush_when_not_authenticated(): void
    {
        $blocker = User::factory()->create();
        $blocked = User::factory()->create();

        $this->primeCaches($blocker);

        Block::query()->create([
            'blocker_id' => $blocker->id,
            'blocked_id' => $blocked->id,
        ]);

        $this->assertCachesPrimed($blocker);
    }

    public function test_mute_flushes_caches_for_muter_on_save(): void
    {
        $muter = User::factory()->create();
        $muted = User::factory()->create();
        $this->actingAs($muter);

        $this->primeCaches($muter);

        Mute::query()->create([
            'muter_id' => $muter->id,
            'muted_id' => $muted->id,
        ]);

        $this->assertCachesCleared($this->currentUser());
    }

    public function test_mute_does_not_flush_for_non_muter(): void
    {
        $muter = User::factory()->create();
        $muted = User::factory()->create();
        $viewer = User::factory()->create();

        $this->actingAs($viewer);
        $this->primeCaches($viewer);

        Mute::query()->create([
            'muter_id' => $muter->id,
            'muted_id' => $muted->id,
        ]);

        $this->assertCachesPrimed($this->currentUser());
    }

    public function test_mute_does_not_flush_when_not_authenticated(): void
    {
        $muter = User::factory()->create();
        $muted = User::factory()->create();

        $this->primeCaches($muter);

        Mute::query()->create([
            'muter_id' => $muter->id,
            'muted_id' => $muted->id,
        ]);

        $this->assertCachesPrimed($muter);
    }

    public function test_muted_term_flushes_caches_for_owner_on_save_and_delete(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $this->primeCaches($owner);

        $term = MutedTerm::query()->create([
            'user_id' => $owner->id,
            'term' => 'spoilers',
            'whole_word' => true,
            'only_non_followed' => false,
            'mute_timeline' => true,
            'mute_notifications' => true,
            'expires_at' => null,
        ]);

        $this->assertCachesCleared($this->currentUser());

        $this->primeCaches($owner);
        $term->delete();

        $this->assertCachesCleared($this->currentUser());
    }

    public function test_muted_term_does_not_flush_for_other_user(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();

        $this->actingAs($viewer);
        $this->primeCaches($viewer);

        MutedTerm::query()->create([
            'user_id' => $owner->id,
            'term' => 'spoilers',
            'whole_word' => true,
            'only_non_followed' => false,
            'mute_timeline' => true,
            'mute_notifications' => true,
            'expires_at' => null,
        ]);

        $this->assertCachesPrimed($this->currentUser());
    }

    public function test_muted_term_does_not_flush_when_not_authenticated(): void
    {
        $owner = User::factory()->create();

        $this->primeCaches($owner);

        MutedTerm::query()->create([
            'user_id' => $owner->id,
            'term' => 'spoilers',
            'whole_word' => true,
            'only_non_followed' => false,
            'mute_timeline' => true,
            'mute_notifications' => true,
            'expires_at' => null,
        ]);

        $this->assertCachesPrimed($owner);
    }

    private function primeCaches(User $user): void
    {
        foreach (self::CACHE_PROPS as $prop) {
            $this->setCacheValue($user, $prop, collect([1]));
        }
    }

    private function assertCachesCleared(User $user): void
    {
        foreach (self::CACHE_PROPS as $prop) {
            $this->assertNull($this->getCacheValue($user, $prop));
        }
    }

    private function assertCachesPrimed(User $user): void
    {
        foreach (self::CACHE_PROPS as $prop) {
            $value = $this->getCacheValue($user, $prop);
            $this->assertInstanceOf(Collection::class, $value);
        }
    }

    private function setCacheValue(User $user, string $property, mixed $value): void
    {
        $ref = new ReflectionProperty(User::class, $property);
        $ref->setAccessible(true);
        $ref->setValue($user, $value);
    }

    private function getCacheValue(User $user, string $property): mixed
    {
        $ref = new ReflectionProperty(User::class, $property);
        $ref->setAccessible(true);

        return $ref->getValue($user);
    }

    private function currentUser(): User
    {
        $user = auth()->user();
        $this->assertNotNull($user);

        return $user;
    }
}

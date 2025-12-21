<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Blocks\Pages\ListBlocks;
use App\Filament\Resources\Bookmarks\Pages\ListBookmarks;
use App\Filament\Resources\Follows\Pages\ListFollows;
use App\Filament\Resources\Likes\Pages\ListLikes;
use App\Filament\Resources\Mutes\Pages\ListMutes;
use App\Models\Block;
use App\Models\Bookmark;
use App\Models\Follow;
use App\Models\Like;
use App\Models\Mute;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentAdminCompositeBulkActionsTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);
        Filament::setCurrentPanel('admin');

        return $admin;
    }

    public function test_admin_can_bulk_delete_blocks(): void
    {
        $admin = $this->actingAsAdmin();

        $blocks = Block::factory()->count(2)->create();

        Livewire::actingAs($admin)
            ->test(ListBlocks::class)
            ->callTableBulkAction('delete', $blocks->all());

        $this->assertDatabaseCount('blocks', 0);
    }

    public function test_admin_can_bulk_delete_bookmarks(): void
    {
        $admin = $this->actingAsAdmin();

        $bookmarks = Bookmark::factory()->count(2)->create();

        Livewire::actingAs($admin)
            ->test(ListBookmarks::class)
            ->callTableBulkAction('delete', $bookmarks->all());

        $this->assertDatabaseCount('bookmarks', 0);
    }

    public function test_admin_can_bulk_delete_follows(): void
    {
        $admin = $this->actingAsAdmin();

        $follows = Follow::factory()->count(2)->create();

        Livewire::actingAs($admin)
            ->test(ListFollows::class)
            ->callTableBulkAction('delete', $follows->all());

        $this->assertDatabaseCount('follows', 0);
    }

    public function test_admin_can_bulk_delete_likes(): void
    {
        $admin = $this->actingAsAdmin();

        $likes = Like::factory()->count(2)->create();

        Livewire::actingAs($admin)
            ->test(ListLikes::class)
            ->callTableBulkAction('delete', $likes->all());

        $this->assertDatabaseCount('likes', 0);
    }

    public function test_admin_can_bulk_delete_mutes(): void
    {
        $admin = $this->actingAsAdmin();

        $mutes = Mute::factory()->count(2)->create();

        Livewire::actingAs($admin)
            ->test(ListMutes::class)
            ->callTableBulkAction('delete', $mutes->all());

        $this->assertDatabaseCount('mutes', 0);
    }
}

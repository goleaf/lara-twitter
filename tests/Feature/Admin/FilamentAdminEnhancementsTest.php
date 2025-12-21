<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Reports\ReportResource;
use App\Filament\Resources\Spaces\Pages\ListSpaces;
use App\Filament\Resources\Spaces\SpaceResource;
use App\Models\Post;
use App\Models\Report;
use App\Models\Space;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentAdminEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);
        Filament::setCurrentPanel('admin');

        return $admin;
    }

    public function test_admin_can_bulk_publish_and_unpublish_posts(): void
    {
        $admin = $this->actingAsAdmin();

        $published = Post::factory()->create(['is_published' => true]);
        $unpublished = Post::factory()->create(['is_published' => false]);

        Livewire::actingAs($admin)
            ->test(ListPosts::class)
            ->callTableBulkAction('unpublish', [$published]);

        $this->assertFalse($published->fresh()->is_published);

        Livewire::actingAs($admin)
            ->test(ListPosts::class)
            ->callTableBulkAction('publish', [$unpublished]);

        $this->assertTrue($unpublished->fresh()->is_published);
    }

    public function test_admin_can_end_space_from_table(): void
    {
        $admin = $this->actingAsAdmin();

        $space = Space::factory()->create([
            'started_at' => now()->subMinutes(5),
            'ended_at' => null,
        ]);

        Livewire::actingAs($admin)
            ->test(ListSpaces::class)
            ->callTableAction('end-space', $space);

        $this->assertNotNull($space->fresh()->ended_at);
    }

    public function test_navigation_badges_reflect_admin_counts(): void
    {
        Cache::flush();

        Report::factory()->count(2)->create(['status' => Report::STATUS_OPEN]);
        Report::factory()->create(['status' => Report::STATUS_RESOLVED]);
        Post::factory()->create(['is_published' => false]);
        Space::factory()->create(['started_at' => now()->subMinutes(2), 'ended_at' => null]);

        $this->assertSame('2', ReportResource::getNavigationBadge());
        $this->assertSame('1', PostResource::getNavigationBadge());
        $this->assertSame('1', SpaceResource::getNavigationBadge());
    }
}

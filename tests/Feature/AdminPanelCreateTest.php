<?php

namespace Tests\Feature;

use App\Filament\Resources\Moments\Pages\CreateMoment;
use App\Filament\Resources\Reports\Pages\CreateReport;
use App\Filament\Resources\Spaces\Pages\CreateSpace;
use App\Models\Moment;
use App\Models\Post;
use App\Models\Report;
use App\Models\Space;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelCreateTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Filament::setCurrentPanel('admin');

        return $admin;
    }

    public function test_admin_can_create_moment(): void
    {
        Storage::fake('public');

        $admin = $this->actingAsAdmin();
        $owner = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(CreateMoment::class)
            ->fillForm([
                'owner_id' => $owner->id,
                'title' => 'Storytime',
                'description' => 'A curated story.',
                'is_public' => true,
                'cover_image_path' => UploadedFile::fake()->image('cover.jpg', 1200, 630),
            ])
            ->call('create')
            ->assertHasNoErrors();

        $moment = Moment::query()->firstOrFail();

        $this->assertSame('Storytime', $moment->title);
        $this->assertSame($owner->id, $moment->owner_id);
        $this->assertNotNull($moment->cover_image_path);
        Storage::disk('public')->assertExists($moment->cover_image_path);
    }

    public function test_admin_can_create_space(): void
    {
        $admin = $this->actingAsAdmin();
        $host = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(CreateSpace::class)
            ->fillForm([
                'host_user_id' => $host->id,
                'title' => 'Founder AMA',
                'description' => 'Let’s talk product.',
                'recording_enabled' => true,
            ])
            ->call('create')
            ->assertHasNoErrors();

        $space = Space::query()->firstOrFail();

        $this->assertSame('Founder AMA', $space->title);
        $this->assertSame($host->id, $space->host_user_id);
        $this->assertTrue($space->recording_enabled);
    }

    public function test_admin_can_create_report(): void
    {
        $admin = $this->actingAsAdmin();
        $reporter = User::factory()->create();
        $post = Post::factory()->create();

        Livewire::actingAs($admin)
            ->test(CreateReport::class)
            ->fillForm([
                'reporter_id' => $reporter->id,
                'reportable_type' => Post::class,
                'reportable_id' => $post->id,
                'reason' => Report::REASON_SPAM,
                'details' => 'Automated test report.',
                'status' => Report::STATUS_OPEN,
            ])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $reporter->id,
            'reportable_type' => Post::class,
            'reportable_id' => $post->id,
            'reason' => Report::REASON_SPAM,
            'status' => Report::STATUS_OPEN,
        ]);
    }
}

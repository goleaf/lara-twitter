<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentAdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);
        Filament::setCurrentPanel('admin');

        return $admin;
    }

    public function test_admin_can_bulk_manage_user_flags(): void
    {
        $admin = $this->actingAsAdmin();

        $userA = User::factory()->create([
            'is_verified' => false,
            'is_premium' => false,
            'analytics_enabled' => false,
        ]);
        $userB = User::factory()->create([
            'is_verified' => false,
            'is_premium' => true,
            'analytics_enabled' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->callTableBulkAction('mark-verified', [$userA, $userB]);

        $this->assertTrue($userA->fresh()->is_verified);
        $this->assertTrue($userB->fresh()->is_verified);

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->callTableBulkAction('remove-premium', [$userB]);

        $this->assertFalse($userB->fresh()->is_premium);

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->callTableBulkAction('enable-analytics', [$userA]);

        $this->assertTrue($userA->fresh()->analytics_enabled);
    }

    public function test_admin_can_verify_and_clear_email_from_user_table(): void
    {
        $admin = $this->actingAsAdmin();

        $user = User::factory()->unverified()->create();

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->callTableAction('verify-email', $user);

        $this->assertNotNull($user->fresh()->email_verified_at);

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->callTableAction('clear-email-verification', $user);

        $this->assertNull($user->fresh()->email_verified_at);
    }
}

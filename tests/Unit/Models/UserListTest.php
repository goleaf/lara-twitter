<?php

namespace Tests\Unit\Models;

use App\Models\UserList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserListTest extends TestCase
{
    use RefreshDatabase;

    public function test_userList_can_be_created(): void
    {
        $userList = UserList::factory()->create();

        $this->assertInstanceOf(UserList::class, $userList);
        $this->assertDatabaseHas('user_lists', [
            'id' => $userList->id,
        ]);
    }

    public function test_userList_has_factory(): void
    {
        $userList = UserList::factory()->make();

        $this->assertInstanceOf(UserList::class, $userList);
    }
}
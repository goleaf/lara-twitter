<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'bio' => 'Admin account (Filament access).',
        ]);

        $users = User::factory(25)->create();

        $users->push($admin);

        foreach ($users as $user) {
            Post::factory(fake()->numberBetween(3, 12))
                ->for($user)
                ->create();
        }

        foreach (Post::query()->inRandomOrder()->limit(30)->get() as $parent) {
            $replier = $users->random();
            Post::factory(fake()->numberBetween(1, 3))
                ->state([
                    'user_id' => $replier->id,
                    'reply_to_id' => $parent->id,
                ])
                ->create();
        }

        $allUsers = User::query()->pluck('id', 'username');
        foreach (User::query()->inRandomOrder()->limit(20)->get() as $user) {
            $followed = User::query()
                ->whereKeyNot($user->id)
                ->inRandomOrder()
                ->limit(fake()->numberBetween(3, 10))
                ->pluck('id');

            $user->following()->syncWithoutDetaching($followed);
        }

        $usernames = $allUsers->keys()->values();
        foreach (Post::query()->inRandomOrder()->limit(40)->get() as $post) {
            $mention = $usernames->random();
            $post->update([
                'body' => trim($post->body.' @'.$mention.' #laravel'),
            ]);
        }

        foreach (Post::query()->inRandomOrder()->limit(120)->get() as $post) {
            $likers = User::query()
                ->inRandomOrder()
                ->limit(fake()->numberBetween(0, 10))
                ->pluck('id');

            foreach ($likers as $userId) {
                $post->likes()->firstOrCreate(['user_id' => $userId]);
            }
        }
    }
}

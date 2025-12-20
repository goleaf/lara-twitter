<?php

namespace Tests;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected bool $seed = true;

    protected string $seeder = DatabaseSeeder::class;

    protected function beforeRefreshingDatabase()
    {
        config([
            'seeding.model_count' => (int) env('SEED_MODEL_COUNT', 0),
            'seeding.relation_count' => (int) env('SEED_RELATION_COUNT', 0),
        ]);
    }
}

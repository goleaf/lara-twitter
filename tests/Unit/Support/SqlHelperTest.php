<?php

namespace Tests\Unit\Support;

use App\Support\SqlHelper;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class SqlHelperTest extends TestCase
{
    public function test_lower_with_padding_uses_sqlite_syntax_by_default(): void
    {
        $this->assertSame("(' ' || lower(posts.body) || ' ')", SqlHelper::lowerWithPadding('posts.body'));
    }

    public function test_lower_with_padding_uses_concat_for_mysql(): void
    {
        $mock = Mockery::mock(ConnectionInterface::class);
        $mock->shouldReceive('getDriverName')->andReturn('mysql');

        DB::shouldReceive('connection')->andReturn($mock);

        $this->assertSame("concat(' ', lower(posts.body), ' ')", SqlHelper::lowerWithPadding('posts.body'));

        DB::swap(app('db'));
    }
}

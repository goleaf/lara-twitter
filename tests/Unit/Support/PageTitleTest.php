<?php

namespace Tests\Unit\Support;

use App\Models\Moment;
use App\Models\Space;
use App\Models\User;
use App\Models\UserList;
use App\Support\PageTitle;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Tests\TestCase;

class PageTitleTest extends TestCase
{
    public function test_resolve_returns_override_when_provided(): void
    {
        $this->assertSame('Custom', PageTitle::resolve('Custom'));
    }

    public function test_resolve_returns_expected_titles_for_routes(): void
    {
        $user = new User(['name' => 'Alice']);
        $list = new UserList(['name' => 'My List']);
        $space = new Space(['title' => 'Product Space']);
        $moment = new Moment(['title' => 'Launch Day']);

        $cases = [
            ['timeline', [], 'Home'],
            ['explore', [], 'Explore'],
            ['search', [], 'Search'],
            ['trending', [], 'Trending'],
            ['hashtags.show', ['tag' => '#laravel'], '#laravel'],
            ['hashtags.show', ['tag' => ''], 'Hashtag'],
            ['notifications', [], 'Notifications'],
            ['messages.show', [], 'Messages'],
            ['bookmarks', [], 'Bookmarks'],
            ['lists.show', ['list' => $list], 'My List'],
            ['lists.show', ['list' => 'not-a-list'], 'List'],
            ['lists.index', [], 'Lists'],
            ['mentions', [], 'Mentions'],
            ['reports.index', [], 'Reports'],
            ['analytics', [], 'Analytics'],
            ['profile', [], 'Settings'],
            ['help.index', [], 'Help'],
            ['terms', [], 'Terms'],
            ['privacy', [], 'Privacy'],
            ['cookies', [], 'Cookies'],
            ['about', [], 'About'],
            ['profile.show', ['user' => $user], 'Alice'],
            ['profile.show', ['user' => 'not-a-user'], 'Profile'],
            ['spaces.show', ['space' => $space], 'Product Space'],
            ['spaces.index', [], 'Spaces'],
            ['moments.show', ['moment' => $moment], 'Launch Day'],
            ['moments.index', [], 'Moments'],
            ['posts.show', [], 'Post'],
            ['login', [], 'Log in'],
            ['register', [], 'Create account'],
            ['password.request', [], 'Forgot password'],
            ['password.reset', [], 'Reset password'],
            ['verification.notice', [], 'Verify email'],
            ['password.confirm', [], 'Confirm password'],
            ['unknown.route', [], config('app.name', 'MiniTwitter')],
        ];

        foreach ($cases as [$routeName, $params, $expected]) {
            $this->assertSame($expected, $this->resolveForRoute($routeName, $params));
        }
    }

    public function test_document_title_includes_app_name(): void
    {
        $appName = config('app.name', 'MiniTwitter');

        $this->assertSame($appName, PageTitle::documentTitle($appName));
        $this->assertSame('Home '."\xC2\xB7".' '.$appName, PageTitle::documentTitle('Home'));
    }

    private function resolveForRoute(string $name, array $params): string
    {
        $route = new Route(['GET'], '/', fn () => null);
        $route->name($name);
        $request = Request::create('/');
        $route->bind($request);
        foreach ($params as $key => $value) {
            $route->setParameter($key, $value);
        }
        $request->setRouteResolver(fn () => $route);

        $original = app('request');
        app()->instance('request', $request);

        try {
            return PageTitle::resolve();
        } finally {
            app()->instance('request', $original);
        }
    }
}

<?php

namespace Tests\Unit\Services;

use App\Services\PostLinkPreviewService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PostLinkPreviewServiceTest extends TestCase
{
    public function test_extract_first_url_trims_suffixes(): void
    {
        $service = new PostLinkPreviewService();

        $this->assertSame(
            'https://example.com/path',
            $service->extractFirstUrl('See https://example.com/path).')
        );
        $this->assertNull($service->extractFirstUrl('no links here'));
    }

    public function test_fetch_returns_metadata_for_safe_url(): void
    {
        $html = <<<'HTML'
<!doctype html>
<html>
<head>
<meta property="og:site_name" content="Example">
<meta property="og:title" content="Example Title">
<meta property="og:description" content="Example description">
<meta property="og:image" content="/image.png">
<title>Fallback Title</title>
</head>
<body>OK</body>
</html>
HTML;

        Http::fake(function () use ($html) {
            return Http::response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
        });

        $service = new PostLinkPreviewService();
        $result = $service->fetch('http://1.1.1.1/path');

        $this->assertSame('Example', $result['site_name']);
        $this->assertSame('Example Title', $result['title']);
        $this->assertSame('Example description', $result['description']);
        $this->assertSame('http://1.1.1.1/image.png', $result['image_url']);
    }

    public function test_fetch_handles_redirects(): void
    {
        $html = <<<'HTML'
<!doctype html>
<html>
<head>
<meta property="og:title" content="Final Title">
</head>
<body>OK</body>
</html>
HTML;

        Http::fakeSequence()
            ->push('', 302, ['Location' => '/final'])
            ->push('', 200, ['Content-Type' => 'text/html'])
            ->push($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);

        $service = new PostLinkPreviewService();
        $result = $service->fetch('http://1.1.1.1/start');

        $this->assertSame('Final Title', $result['title']);
    }

    public function test_fetch_returns_null_for_non_html(): void
    {
        Http::fake(function () {
            return Http::response('binary', 200, ['Content-Type' => 'image/png']);
        });

        $service = new PostLinkPreviewService();

        $this->assertNull($service->fetch('http://1.1.1.1/image'));
    }

    public function test_fetch_returns_null_for_unsafe_url(): void
    {
        $service = new PostLinkPreviewService();

        $this->assertNull($service->fetch('ftp://example.com/file'));
        $this->assertNull($service->fetch('http://localhost/test'));
    }
}

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

    public function test_fetch_returns_null_for_non_ok_response(): void
    {
        Http::fake(function () {
            return Http::response('fail', 500, ['Content-Type' => 'text/html']);
        });

        $service = new PostLinkPreviewService();

        $this->assertNull($service->fetch('http://1.1.1.1/bad'));
    }

    public function test_fetch_returns_null_for_empty_body(): void
    {
        Http::fake(function () {
            return Http::response('', 200, ['Content-Type' => 'text/html']);
        });

        $service = new PostLinkPreviewService();

        $this->assertNull($service->fetch('http://1.1.1.1/empty'));
    }

    public function test_fetch_trims_large_html(): void
    {
        $padding = str_repeat('a', 1024 * 1024 + 10);
        $html = "<html><head><title>Big</title></head><body>{$padding}</body></html>";

        Http::fake(function () use ($html) {
            return Http::response($html, 200, ['Content-Type' => 'text/html']);
        });

        $service = new PostLinkPreviewService();
        $result = $service->fetch('http://1.1.1.1/big');

        $this->assertSame('Big', $result['title']);
    }

    public function test_fetch_sets_null_image_url_when_unsafe(): void
    {
        $longPath = '/'.str_repeat('a', 2100);
        $html = <<<HTML
<!doctype html>
<html>
<head>
<meta property="og:image" content="{$longPath}">
</head>
<body>OK</body>
</html>
HTML;

        Http::fake(function () use ($html) {
            return Http::response($html, 200, ['Content-Type' => 'text/html']);
        });

        $service = new PostLinkPreviewService();
        $result = $service->fetch('http://1.1.1.1/page');

        $this->assertNull($result['image_url']);
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

    public function test_private_helpers_handle_common_cases(): void
    {
        $service = new PostLinkPreviewService();

        $this->assertFalse($this->callPrivate($service, 'isSafeOutboundUrl', ['']));
        $this->assertFalse($this->callPrivate($service, 'isSafeOutboundUrl', ['ftp://example.com']));
        $this->assertTrue($this->callPrivate($service, 'isSafeOutboundUrl', ['http://example.com']));
        $this->assertFalse($this->callPrivate($service, 'isSafeOutboundUrl', ['http:///bad']));

        $this->assertSame('https://example.com/child', $this->callPrivate($service, 'resolveUrl', ['https://example.com/base', '/child']));
        $this->assertSame('https://example.com/child', $this->callPrivate($service, 'resolveUrl', ['https://example.com/base', 'child']));
        $this->assertSame('https://example.com/child', $this->callPrivate($service, 'resolveUrl', ['https://example.com/base', '//example.com/child']));

        $this->assertNull($this->callPrivate($service, 'trimTo', [null, 10]));
        $this->assertNull($this->callPrivate($service, 'trimTo', ['   ', 10]));
        $trimmed = $this->callPrivate($service, 'trimTo', [str_repeat('a', 10), 5]);
        $this->assertTrue(str_ends_with($trimmed, "\u{2026}"));

        $doc = new \DOMDocument();
        $doc->loadHTML('<html><head><meta property="og:title" content="Hello"><title>Fallback</title></head></html>');
        $xpath = new \DOMXPath($doc);

        $this->assertSame('Hello', $this->callPrivate($service, 'firstMetaContent', [$xpath, 'property', 'og:title']));
        $this->assertSame('Fallback', $this->callPrivate($service, 'firstTitle', [$xpath]));
    }

    public function test_is_safe_to_fetch_rejects_bad_hosts_ports_and_ips(): void
    {
        $service = new PostLinkPreviewService();

        $this->assertFalse($this->callPrivate($service, 'isSafeToFetch', ['ftp://example.com']));
        $this->assertFalse($this->callPrivate($service, 'isSafeToFetch', ['http://localhost']));
        $this->assertFalse($this->callPrivate($service, 'isSafeToFetch', ['http://example.local']));
        $this->assertFalse($this->callPrivate($service, 'isSafeToFetch', ['http://example.com:1234']));
        $this->assertFalse($this->callPrivate($service, 'isSafeToFetch', ['http://127.0.0.1']));
        $this->assertTrue($this->callPrivate($service, 'isSafeToFetch', ['http://1.1.1.1']));
    }

    public function test_is_safe_to_fetch_uses_dns_cache(): void
    {
        $service = new PostLinkPreviewService();

        $this->setPrivateProperty($service, 'dnsCache', [
            'empty.test' => [],
            'private.test' => [['ip' => '192.168.0.1']],
            'public.test' => [['ip' => '1.1.1.1']],
        ]);

        $this->assertFalse($this->callPrivate($service, 'isSafeToFetch', ['http://empty.test']));
        $this->assertFalse($this->callPrivate($service, 'isSafeToFetch', ['http://private.test']));
        $this->assertTrue($this->callPrivate($service, 'isSafeToFetch', ['http://public.test']));
    }

    public function test_follow_redirects_returns_null_when_location_missing(): void
    {
        $service = new PostLinkPreviewService();

        Http::fakeSequence()
            ->push('', 302);

        $this->assertNull($this->callPrivate($service, 'followRedirects', ['http://1.1.1.1/start']));
    }

    public function test_follow_redirects_returns_last_location_after_max_redirects(): void
    {
        $service = new PostLinkPreviewService();

        Http::fakeSequence()
            ->push('', 302, ['Location' => '/one'])
            ->push('', 302, ['Location' => '/two'])
            ->push('', 302, ['Location' => '/three']);

        $this->assertSame('http://1.1.1.1/three', $this->callPrivate($service, 'followRedirects', ['http://1.1.1.1/start']));
    }

    public function test_split_url_suffix_trims_multiple_characters(): void
    {
        $service = new PostLinkPreviewService();

        [$url] = $this->callPrivate($service, 'splitUrlSuffix', ['https://example.com/path))]}.,!?:;"\'']);

        $this->assertSame('https://example.com/path', $url);
    }

    private function callPrivate(object $object, string $method, array $args = [])
    {
        $ref = new \ReflectionMethod($object, $method);
        $ref->setAccessible(true);

        return $ref->invokeArgs($object, $args);
    }

    private function setPrivateProperty(object $object, string $property, mixed $value): void
    {
        $ref = new \ReflectionProperty($object, $property);
        $ref->setAccessible(true);
        $ref->setValue($object, $value);
    }
}

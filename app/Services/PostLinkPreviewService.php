<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PostLinkPreviewService
{
    public function extractFirstUrl(string $text): ?string
    {
        if (! preg_match('/https?:\\/\\/[^\s<]+/i', $text, $matches)) {
            return null;
        }

        $url = $matches[0] ?? null;
        if (! is_string($url) || $url === '') {
            return null;
        }

        [$url] = $this->splitUrlSuffix($url);

        return $url !== '' ? $url : null;
    }

    /**
     * @return array{site_name: string|null, title: string|null, description: string|null, image_url: string|null}|null
     */
    public function fetch(string $url): ?array
    {
        if (! $this->isSafeToFetch($url)) {
            return null;
        }

        $finalUrl = $this->followRedirects($url);
        if (! $finalUrl || ! $this->isSafeToFetch($finalUrl)) {
            return null;
        }

        $response = Http::withoutRedirecting()
            ->timeout(5)
            ->withHeaders([
                'User-Agent' => 'MiniTwitterBot/1.0 (+https://example.com)',
                'Accept' => 'text/html,application/xhtml+xml',
            ])
            ->get($finalUrl);

        if (! $response->ok()) {
            return null;
        }

        $contentType = strtolower((string) $response->header('Content-Type', ''));
        if ($contentType !== '' && ! str_starts_with($contentType, 'text/html')) {
            return null;
        }

        $html = $response->body();
        if ($html === '') {
            return null;
        }

        if (strlen($html) > 1024 * 1024) {
            $html = substr($html, 0, 1024 * 1024);
        }

        $doc = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new \DOMXPath($doc);

        $siteName = $this->firstMetaContent($xpath, 'property', 'og:site_name');
        $title = $this->firstMetaContent($xpath, 'property', 'og:title') ?? $this->firstTitle($xpath);
        $description = $this->firstMetaContent($xpath, 'property', 'og:description') ?? $this->firstMetaContent($xpath, 'name', 'description');
        $image = $this->firstMetaContent($xpath, 'property', 'og:image');

        $siteName = $this->trimTo($siteName, 100);
        $title = $this->trimTo($title, 255);
        $description = $this->trimTo($description, 255);

        $imageUrl = null;
        if (is_string($image) && $image !== '') {
            $imageUrl = $this->resolveUrl($finalUrl, $image);
            if (! $this->isSafeOutboundUrl($imageUrl)) {
                $imageUrl = null;
            }
        }

        return [
            'site_name' => $siteName,
            'title' => $title,
            'description' => $description,
            'image_url' => $imageUrl,
        ];
    }

    private function followRedirects(string $url): ?string
    {
        $current = $url;
        $max = 3;

        for ($i = 0; $i < $max; $i++) {
            if (! $this->isSafeToFetch($current)) {
                return null;
            }

            $response = Http::withoutRedirecting()
                ->timeout(5)
                ->withHeaders([
                    'User-Agent' => 'MiniTwitterBot/1.0 (+https://example.com)',
                    'Accept' => 'text/html,application/xhtml+xml',
                ])
                ->get($current);

            if (! $response->redirect()) {
                return $current;
            }

            $location = $response->header('Location');
            if (! is_string($location) || $location === '') {
                return null;
            }

            $current = $this->resolveUrl($current, $location);
        }

        return $current;
    }

    private function isSafeToFetch(string $url): bool
    {
        if (! $this->isSafeOutboundUrl($url)) {
            return false;
        }

        $parts = parse_url($url);
        if (! is_array($parts)) {
            return false;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        if ($host === '' || $host === 'localhost' || str_ends_with($host, '.local')) {
            return false;
        }

        if (isset($parts['port']) && ! in_array((int) $parts['port'], [80, 443], true)) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return $this->isPublicIp($host);
        }

        $records = @dns_get_record($host, DNS_A + DNS_AAAA);
        if (! is_array($records) || count($records) === 0) {
            return false;
        }

        foreach ($records as $record) {
            $ip = $record['ip'] ?? $record['ipv6'] ?? null;
            if (! is_string($ip) || $ip === '') {
                continue;
            }

            if (! $this->isPublicIp($ip)) {
                return false;
            }
        }

        return true;
    }

    private function isPublicIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        ) !== false;
    }

    private function isSafeOutboundUrl(?string $url): bool
    {
        if (! is_string($url) || $url === '' || strlen($url) > 2048) {
            return false;
        }

        $parts = parse_url($url);
        if (! is_array($parts)) {
            return false;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if (! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        return true;
    }

    private function firstMetaContent(\DOMXPath $xpath, string $attr, string $value): ?string
    {
        $nodes = $xpath->query("//meta[translate(@{$attr}, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz') = '".mb_strtolower($value)."']/@content");
        if (! $nodes || $nodes->length === 0) {
            return null;
        }

        $content = $nodes->item(0)?->nodeValue;

        return is_string($content) ? trim(html_entity_decode($content, ENT_QUOTES | ENT_HTML5)) : null;
    }

    private function firstTitle(\DOMXPath $xpath): ?string
    {
        $nodes = $xpath->query('//title');
        if (! $nodes || $nodes->length === 0) {
            return null;
        }

        $value = $nodes->item(0)?->textContent;

        return is_string($value) ? trim($value) : null;
    }

    private function trimTo(?string $value, int $max): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (mb_strlen($value) <= $max) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $max - 1)).'…';
    }

    private function resolveUrl(string $baseUrl, string $maybeRelative): string
    {
        if (preg_match('/^https?:\\/\\//i', $maybeRelative)) {
            return $maybeRelative;
        }

        $base = parse_url($baseUrl);
        if (! is_array($base)) {
            return $maybeRelative;
        }

        $scheme = $base['scheme'] ?? 'https';
        $host = $base['host'] ?? '';
        $port = isset($base['port']) ? ':'.$base['port'] : '';

        if ($host === '') {
            return $maybeRelative;
        }

        $prefix = $scheme.'://'.$host.$port;

        if (str_starts_with($maybeRelative, '/')) {
            return $prefix.$maybeRelative;
        }

        $path = $base['path'] ?? '/';
        $dir = rtrim(str_replace('\\', '/', dirname($path)), '/');

        return $prefix.($dir ? "/{$dir}" : '').'/'.$maybeRelative;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitUrlSuffix(string $url): array
    {
        $suffix = '';

        while ($url !== '') {
            $last = substr($url, -1);

            $shouldTrim = match ($last) {
                '.', ',', '!', '?', ':', ';', '"', '\'' => true,
                ')' => substr_count($url, ')') > substr_count($url, '('),
                ']' => substr_count($url, ']') > substr_count($url, '['),
                '}' => substr_count($url, '}') > substr_count($url, '{'),
                default => false,
            };

            if (! $shouldTrim) {
                break;
            }

            $suffix = $last.$suffix;
            $url = substr($url, 0, -1);
        }

        return [$url, $suffix];
    }
}


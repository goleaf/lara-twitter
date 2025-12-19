<?php

namespace App\Services;

use Illuminate\Support\HtmlString;

class PostBodyRenderer
{
    public function render(string $text, ?int $postId = null): HtmlString
    {
        $pattern = '/(?P<url>https?:\\/\\/[^\s<]+)|(?P<hashtag>(?P<hashtag_prefix>^|[^\pL\pN_])#(?P<hashtag_tag>[\pL\pN][\pL\pN_]{0,49}))|(?P<mention>(?P<mention_prefix>^|[^A-Za-z0-9_])@(?P<mention_username>[A-Za-z0-9_]{3,30}))/u';

        $output = '';
        $offset = 0;

        while (preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            $matchText = $matches[0][0];
            $matchPos = $matches[0][1];

            $output .= e(substr($text, $offset, $matchPos - $offset));

            if ($matches['url'][1] !== -1) {
                [$url, $suffix] = $this->splitUrlSuffix($matches['url'][0]);

                $href = $postId
                    ? route('links.redirect', ['post' => $postId, 'u' => $url])
                    : $url;

                $output .= '<a class="link link-primary" href="'.e($href).'" target="_blank" rel="nofollow noopener noreferrer">'.e($url).'</a>';
                $output .= e($suffix);
            } elseif ($matches['hashtag_tag'][1] !== -1) {
                $prefix = $matches['hashtag_prefix'][0];
                $tag = $matches['hashtag_tag'][0];
                $url = route('hashtags.show', ['tag' => mb_strtolower($tag)]);

                $output .= e($prefix).'<a class="link link-primary" href="'.e($url).'" wire:navigate>#'.e($tag).'</a>';
            } elseif ($matches['mention_username'][1] !== -1) {
                $prefix = $matches['mention_prefix'][0];
                $username = $matches['mention_username'][0];
                $url = route('profile.show', ['user' => mb_strtolower($username)]);

                $output .= e($prefix).'<a class="link link-primary" href="'.e($url).'" wire:navigate>@'.e($username).'</a>';
            } else {
                $output .= e($matchText);
            }

            $offset = $matchPos + strlen($matchText);
        }

        $output .= e(substr($text, $offset));
        $output = nl2br($output);

        return new HtmlString($output);
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

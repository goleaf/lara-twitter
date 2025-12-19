<?php

namespace App\Services;

class PostTextParser
{
    /**
     * @return array{hashtags: array<int, string>, mentions: array<int, string>}
     */
    public function parse(string $text): array
    {
        $hashtags = [];
        if (preg_match_all('/(^|[^\pL\pN_])#([\pL\pN][\pL\pN_]{0,49})/u', $text, $matches)) {
            $hashtags = array_map(
                static fn (string $tag): string => mb_strtolower($tag),
                $matches[2] ?? [],
            );
            $hashtags = array_values(array_unique($hashtags));
        }

        $mentions = [];
        if (preg_match_all('/(^|[^A-Za-z0-9_])@([A-Za-z0-9_]{3,30})/', $text, $matches)) {
            $mentions = array_map(
                static fn (string $username): string => mb_strtolower($username),
                $matches[2] ?? [],
            );
            $mentions = array_values(array_unique($mentions));
        }

        return [
            'hashtags' => $hashtags,
            'mentions' => $mentions,
        ];
    }
}

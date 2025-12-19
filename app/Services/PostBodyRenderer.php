<?php

namespace App\Services;

use Illuminate\Support\HtmlString;

class PostBodyRenderer
{
    public function render(string $text): HtmlString
    {
        $escaped = e($text);

        $escaped = preg_replace_callback(
            '/(^|[^\pL\pN_])#([\pL][\pL\pN_]{0,49})/u',
            static function (array $matches): string {
                $prefix = $matches[1];
                $tag = $matches[2];
                $url = route('hashtags.show', ['tag' => mb_strtolower($tag)]);

                return $prefix.'<a class="link link-primary" href="'.$url.'" wire:navigate>#'.$tag.'</a>';
            },
            $escaped,
        ) ?? $escaped;

        $escaped = preg_replace_callback(
            '/(^|[^A-Za-z0-9_])@([A-Za-z0-9_]{3,30})/',
            static function (array $matches): string {
                $prefix = $matches[1];
                $username = $matches[2];
                $url = route('profile.show', ['user' => mb_strtolower($username)]);

                return $prefix.'<a class="link link-primary" href="'.$url.'" wire:navigate>@'.$username.'</a>';
            },
            $escaped,
        ) ?? $escaped;

        $escaped = nl2br($escaped);

        return new HtmlString($escaped);
    }
}


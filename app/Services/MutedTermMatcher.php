<?php

namespace App\Services;

use App\Models\MutedTerm;

class MutedTermMatcher
{
    public function matches(?string $text, MutedTerm $term): bool
    {
        $haystack = mb_strtolower((string) ($text ?? ''));
        $needle = mb_strtolower(trim((string) ($term->term ?? '')));

        if ($needle === '') {
            return false;
        }

        // Support hashtag-style mutes even if stored without "#".
        if (str_starts_with($needle, '#')) {
            $needle = '#'.ltrim($needle, '#');
        }

        if ($term->whole_word && preg_match('/^[a-z0-9_]+$/i', $needle)) {
            return (bool) preg_match('/(^|[^a-z0-9_])'.preg_quote($needle, '/').'([^a-z0-9_]|$)/i', $haystack);
        }

        return str_contains($haystack, $needle);
    }
}


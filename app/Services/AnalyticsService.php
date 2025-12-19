<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function recordUnique(string $type, int $entityId): void
    {
        $viewerKey = $this->viewerKey();
        if (! $viewerKey) {
            return;
        }

        $day = now()->toDateString();

        DB::table('analytics_uniques')->insertOrIgnore([
            'type' => $type,
            'entity_id' => $entityId,
            'day' => $day,
            'viewer_key' => $viewerKey,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function viewerKey(): ?string
    {
        if (auth()->check()) {
            return 'user:'.auth()->id();
        }

        $sessionId = session()->getId();
        if (! $sessionId) {
            return null;
        }

        return 'guest:'.substr(hash('sha256', $sessionId), 0, 32);
    }
}


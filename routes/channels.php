<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('online', function ($user): array {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'username' => $user->username,
        'avatar_url' => $user->avatar_url,
    ];
});

<?php

return [
    // Twitter uses a follower threshold for hosting Spaces (e.g. 600).
    // Keep this configurable for demos/dev.
    'min_followers_to_host' => (int) env('SPACES_MIN_FOLLOWERS_TO_HOST', 0),
];


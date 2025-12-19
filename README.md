# MiniTwitter (Laravel 12)

A small Twitter-like app built with:

- Laravel 12 + PHP 8.2+
- Livewire 3 (frontend), Volt (auth/profile UI)
- Tailwind CSS 3 + daisyUI
- Filament 4.3 (admin panel)

## Features

- Timeline (shows your posts + people you follow)
- Profiles (`/@username`), follow/unfollow
- Likes
- Direct Messages (1:1 + groups, requests, rich media)
- Hashtags (`/tags/{tag}`) and mentions (`/mentions`)
- Post threads (`/posts/{id}`) with replies
- Image uploads for posts (up to 4) + avatar upload
- Seeded demo data (users, posts, follows, likes, mentions, hashtags, replies)

## Setup

```bash
composer setup
composer dev
```

## Admin panel (Filament)

- URL: `http://localhost:8000/admin`
- Seeded admin:
  - Email: `admin@example.com`
  - Password: `password`

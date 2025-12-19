<?php

namespace App\Livewire;

use App\Http\Requests\Posts\StorePostRequest;
use App\Models\Post;
use App\Services\PostTextParser;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class ReplyComposer extends Component
{
    use WithFileUploads;

    public Post $post;

    public string $body = '';

    public int $maxLength = 280;

    /** @var array<int, mixed> */
    public array $images = [];

    public function mount(Post $post): void
    {
        $this->post = $post->loadMissing(['user', 'mentions']);

        if (Auth::check()) {
            $this->maxLength = Auth::user()->is_premium ? 25000 : 280;
        }

        if ($this->body !== '') {
            return;
        }

        $parser = app(PostTextParser::class);
        $parsed = $parser->parse($this->post->body);

        $usernames = collect([$this->post->user->username])
            ->merge($parsed['mentions'])
            ->filter()
            ->unique()
            ->values();

        $this->body = $usernames->map(fn (string $u) => "@{$u}")->implode(' ').' ';
    }

    public function save(): void
    {
        abort_unless(Auth::check(), 403);

        abort_if(! $this->post->canBeRepliedBy(Auth::user()), 403);

        $validated = $this->validate(StorePostRequest::rulesFor(Auth::user()));

        $reply = Post::query()->create([
            'user_id' => Auth::id(),
            'reply_to_id' => $this->post->id,
            'body' => $validated['body'],
            'reply_policy' => Post::REPLY_EVERYONE,
        ]);

        foreach ($validated['images'] as $index => $image) {
            $path = $image->storePublicly("posts/{$reply->id}", ['disk' => 'public']);

            $reply->images()->create([
                'path' => $path,
                'sort_order' => $index,
            ]);
        }

        $this->reset(['body', 'images']);
        $this->dispatch('reply-created');
    }

    public function render()
    {
        return view('livewire.reply-composer');
    }
}

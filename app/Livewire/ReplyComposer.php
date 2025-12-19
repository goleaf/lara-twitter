<?php

namespace App\Livewire;

use App\Http\Requests\Posts\StorePostRequest;
use App\Models\Post;
use App\Models\PostPoll;
use App\Services\PostTextParser;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class ReplyComposer extends Component
{
    use WithFileUploads;

    public Post $post;

    public string $body = '';
    public string $reply_policy = Post::REPLY_EVERYONE;

    public int $maxLength = 280;

    /** @var array<int, mixed> */
    public array $images = [];

    public mixed $video = null;

    /** @var array<int, string> */
    public array $poll_options = [];

    public ?int $poll_duration = null;

    public function mount(Post $post): void
    {
        $this->post = $post->loadMissing(['user', 'mentions']);

        if (Auth::check()) {
            $this->maxLength = Auth::user()->is_premium ? 25000 : 280;
        }

        if ($this->body !== '') {
            return;
        }

        $this->body = $this->prefilledBody();
    }

    public function updatedImages(): void
    {
        if (! empty($this->images)) {
            $this->video = null;
        }
    }

    public function updatedVideo(): void
    {
        if ($this->video) {
            $this->images = [];
        }
    }

    public function removeVideo(): void
    {
        $this->video = null;
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

        if (! empty($validated['video'])) {
            $path = $validated['video']->storePublicly("posts/{$reply->id}", ['disk' => 'public']);

            $reply->update([
                'video_path' => $path,
                'video_mime_type' => $validated['video']->getMimeType() ?? 'video/mp4',
            ]);
        }

        $pollOptions = $this->normalizedPollOptions($validated['poll_options']);
        if (count($pollOptions)) {
            $poll = PostPoll::query()->create([
                'post_id' => $reply->id,
                'ends_at' => now()->addMinutes((int) $validated['poll_duration']),
            ]);

            foreach ($pollOptions as $index => $optionText) {
                $poll->options()->create([
                    'option_text' => $optionText,
                    'sort_order' => $index,
                ]);
            }
        }

        $this->reset(['images', 'video', 'poll_options', 'poll_duration']);
        $this->body = $this->prefilledBody();
        $this->dispatch('reply-created');
        $this->dispatch('reply-created.'.$this->post->id);
    }

    private function prefilledBody(): string
    {
        $this->post->loadMissing('user');

        $parser = app(PostTextParser::class);
        $parsed = $parser->parse($this->post->body);

        $usernames = collect([$this->post->user->username])
            ->merge($parsed['mentions'])
            ->filter()
            ->unique()
            ->values();

        if ($usernames->isEmpty()) {
            return '';
        }

        return $usernames->map(fn (string $u) => "@{$u}")->implode(' ').' ';
    }

    /**
     * @param  array<int, string>  $options
     * @return array<int, string>
     */
    private function normalizedPollOptions(array $options): array
    {
        $options = array_map('trim', $options);
        $options = array_values(array_filter($options, static fn (string $v): bool => $v !== ''));

        return array_slice($options, 0, 4);
    }

    public function render()
    {
        return view('livewire.reply-composer');
    }
}

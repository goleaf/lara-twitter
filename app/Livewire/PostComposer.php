<?php

namespace App\Livewire;

use App\Http\Requests\Posts\StorePostRequest;
use App\Models\Post;
use App\Models\PostPoll;
use App\Services\ImageService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class PostComposer extends Component
{
    use WithFileUploads;

    public string $body = '';
    public string $reply_policy = \App\Models\Post::REPLY_EVERYONE;

    public string $location = '';

    public ?string $scheduled_for = null;

    /** @var array<int, mixed> */
    public array $images = [];

    public mixed $video = null;

    /** @var array<int, string> */
    public array $poll_options = [];

    public ?int $poll_duration = null;

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

        $disk = $this->mediaDisk();
        $imageService = app(ImageService::class);

        $this->location = trim($this->location);

        if (is_string($this->scheduled_for) && trim($this->scheduled_for) === '') {
            $this->scheduled_for = null;
        }

        $validated = $this->validate(array_merge(StorePostRequest::rulesFor(Auth::user()), [
            'scheduled_for' => ['nullable', 'date', 'after_or_equal:now'],
            'location' => ['nullable', 'string', 'max:80'],
        ]));

        $scheduledFor = isset($validated['scheduled_for']) && is_string($validated['scheduled_for'])
            ? Carbon::createFromFormat('Y-m-d\\TH:i', $validated['scheduled_for'], config('app.timezone'))
            : null;

        $isScheduled = $scheduledFor && $scheduledFor->isFuture();

        $location = isset($validated['location']) ? trim((string) $validated['location']) : null;
        $location = $location === '' ? null : $location;

        $post = Post::query()->create([
            'user_id' => Auth::id(),
            'body' => $validated['body'],
            'reply_policy' => $validated['reply_policy'] ?? Post::REPLY_EVERYONE,
            'location' => $location,
            'is_published' => ! $isScheduled,
            'scheduled_for' => $isScheduled ? $scheduledFor : null,
        ]);

        foreach ($validated['images'] as $index => $image) {
            $path = $imageService->optimizeAndUpload($image, "posts/{$post->id}", $disk);

            $post->images()->create([
                'path' => $path,
                'sort_order' => $index,
            ]);
        }

        if (! empty($validated['video'])) {
            $path = $validated['video']->storePublicly("posts/{$post->id}", ['disk' => $disk]);

            $post->update([
                'video_path' => $path,
                'video_mime_type' => $validated['video']->getMimeType() ?? 'video/mp4',
            ]);
        }

        $pollOptions = $this->normalizedPollOptions($validated['poll_options']);
        if (count($pollOptions)) {
            $pollStartAt = $isScheduled ? $scheduledFor : now();

            $poll = PostPoll::query()->create([
                'post_id' => $post->id,
                'ends_at' => $pollStartAt->copy()->addMinutes((int) $validated['poll_duration']),
            ]);

            foreach ($pollOptions as $index => $optionText) {
                $poll->options()->create([
                    'option_text' => $optionText,
                    'sort_order' => $index,
                ]);
            }
        }

        $this->reset(['body', 'location', 'scheduled_for', 'images', 'video', 'reply_policy', 'poll_options', 'poll_duration']);
        $this->dispatch('post-created');
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

    private function mediaDisk(): string
    {
        return config('filesystems.media_disk', 'public');
    }

    public function render()
    {
        return view('livewire.post-composer');
    }
}

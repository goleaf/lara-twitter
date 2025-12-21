<?php

namespace App\Livewire;

use App\Http\Requests\Posts\StorePostRequest;
use App\Models\Post;
use App\Models\PostPoll;
use App\Services\ImageService;
use App\Services\PostTextParser;
use Illuminate\Http\UploadedFile;
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
    public array $media = [];

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

    public function updatedMedia(): void
    {
        $this->resetValidation('media');
        $this->media = array_values(array_filter($this->media));
    }

    public function removeMedia(int $index): void
    {
        if (! isset($this->media[$index])) {
            return;
        }

        unset($this->media[$index]);
        $this->media = array_values($this->media);
        $this->resetValidation('media');
    }

    public function save(): void
    {
        abort_unless(Auth::check(), 403);

        abort_if(! $this->post->canBeRepliedBy(Auth::user()), 403);

        $disk = $this->mediaDisk();
        $imageService = app(ImageService::class);

        $validated = $this->validate(StorePostRequest::rulesFor(Auth::user()));

        $reply = Post::query()->create([
            'user_id' => Auth::id(),
            'reply_to_id' => $this->post->id,
            'body' => $validated['body'],
            'reply_policy' => Post::REPLY_EVERYONE,
        ]);

        [$images, $video] = $this->splitMedia($validated['media'] ?? []);

        foreach ($images as $index => $image) {
            $result = $imageService->optimizeAndUpload($image, "posts/{$reply->id}", $disk);

            $reply->images()->create([
                'path' => $result['path'],
                'thumbnail_path' => $result['thumbnail_path'],
                'sort_order' => $index,
            ]);
        }

        if ($video) {
            $path = $video->storePublicly("posts/{$reply->id}", ['disk' => $disk]);

            $reply->update([
                'video_path' => $path,
                'video_mime_type' => $video->getMimeType() ?? 'video/mp4',
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

        $this->reset(['media', 'poll_options', 'poll_duration']);
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

    private function mediaDisk(): string
    {
        return config('filesystems.media_disk', 'public');
    }

    /**
     * @param  array<int, mixed>  $media
     * @return array{0: array<int, UploadedFile>, 1: ?UploadedFile}
     */
    private function splitMedia(array $media): array
    {
        $media = array_values(array_filter($media));
        if ($media === []) {
            return [[], null];
        }

        $videos = array_values(array_filter($media, fn ($file) => $this->isVideoFile($file)));
        if ($videos !== []) {
            return [[], $videos[0]];
        }

        $images = array_values(array_filter($media, fn ($file) => $file instanceof UploadedFile));

        return [$images, null];
    }

    private function isVideoFile(mixed $file): bool
    {
        if (! $file instanceof UploadedFile) {
            return false;
        }

        $mime = (string) ($file->getMimeType() ?? '');

        return str_starts_with($mime, 'video/');
    }

    public function render()
    {
        return view('livewire.reply-composer');
    }
}

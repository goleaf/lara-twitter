<?php

namespace App\Livewire;

use App\Http\Requests\Posts\StorePostRequest;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class PostComposer extends Component
{
    use WithFileUploads;

    public string $body = '';
    public string $reply_policy = \App\Models\Post::REPLY_EVERYONE;

    /** @var array<int, mixed> */
    public array $images = [];

    public mixed $video = null;

    public function save(): void
    {
        abort_unless(Auth::check(), 403);

        $validated = $this->validate(StorePostRequest::rulesFor(Auth::user()));

        $post = Post::query()->create([
            'user_id' => Auth::id(),
            'body' => $validated['body'],
            'reply_policy' => $validated['reply_policy'] ?? Post::REPLY_EVERYONE,
        ]);

        foreach ($validated['images'] as $index => $image) {
            $path = $image->storePublicly("posts/{$post->id}", ['disk' => 'public']);

            $post->images()->create([
                'path' => $path,
                'sort_order' => $index,
            ]);
        }

        if (! empty($validated['video'])) {
            $path = $validated['video']->storePublicly("posts/{$post->id}", ['disk' => 'public']);

            $post->update([
                'video_path' => $path,
                'video_mime_type' => $validated['video']->getMimeType() ?? 'video/mp4',
            ]);
        }

        $this->reset(['body', 'images', 'video', 'reply_policy']);
        $this->dispatch('post-created');
    }

    public function render()
    {
        return view('livewire.post-composer');
    }
}

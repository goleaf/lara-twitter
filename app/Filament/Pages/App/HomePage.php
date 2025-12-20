<?php

namespace App\Filament\Pages\App;

use App\Models\Post;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Layout\View as ViewLayout;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HomePage extends Page implements HasTable
{
    use \Filament\Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $slug = 'home';
    protected static ?string $title = 'Home';
    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament-panels::pages.page';

    /**
     * @var array<string, mixed>
     */
    public array $composer = [];

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedSchema::make('composer')->visible(fn (): bool => Auth::check()),
            EmbeddedTable::make(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->timelineQuery())
            ->columns([
                ViewLayout::make('filament.app.tables.post-row'),
            ])
            ->contentGrid([
                'default' => 1,
            ])
            ->recordUrl(fn (Post $record): string => route('posts.show', $record))
            ->recordActions([
                Action::make('like')
                    ->label('')
                    ->icon(fn (): string => Auth::check() ? 'heroicon-o-heart' : 'heroicon-o-heart')
                    ->iconButton()
                    ->tooltip('Like')
                    ->visible(fn (): bool => Auth::check())
                    ->action(function (Post $record): void {
                        $this->toggleLike($record);
                    }),
                Action::make('repost')
                    ->label('')
                    ->icon('heroicon-o-arrow-path')
                    ->iconButton()
                    ->tooltip('Repost')
                    ->visible(fn (): bool => Auth::check())
                    ->action(function (Post $record): void {
                        $this->toggleRepost($record);
                    }),
                Action::make('reply')
                    ->label('')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->iconButton()
                    ->tooltip('Reply')
                    ->visible(fn (): bool => Auth::check())
                    ->form([
                        Textarea::make('body')
                            ->label('Your reply')
                            ->required()
                            ->maxLength(280)
                            ->rows(4),
                    ])
                    ->action(function (Post $record, array $data): void {
                        $this->createReply($record, $data);
                    }),
            ], position: RecordActionsPosition::AfterContent)
            ->paginated([15, 30, 50])
            ->defaultPaginationPageOption(15);
    }

    public function composer(Schema $schema): Schema
    {
        return $schema
            ->statePath('composer')
            ->components([
                Form::make([
                    Section::make('Create Post')
                        ->schema([
                            Textarea::make('body')
                                ->label('What’s happening?')
                                ->placeholder('Share your thoughts…')
                                ->required()
                                ->rows(4)
                                ->maxLength(fn (): int => Auth::user()?->is_premium ? 25000 : 280),
                            FileUpload::make('images')
                                ->label('Images')
                                ->image()
                                ->multiple()
                                ->maxFiles(4)
                                ->maxSize(4096)
                                ->disk('public')
                                ->directory('posts/tmp')
                                ->visibility('public')
                                ->reorderable(),
                            Select::make('reply_policy')
                                ->label('Who can reply?')
                                ->options([
                                    Post::REPLY_EVERYONE => 'Everyone',
                                    Post::REPLY_FOLLOWING => 'People you follow',
                                    Post::REPLY_MENTIONED => 'Only people you mention',
                                    Post::REPLY_NONE => 'No one',
                                ])
                                ->default(Post::REPLY_EVERYONE)
                                ->native(false),
                            DateTimePicker::make('scheduled_for')
                                ->label('Schedule for')
                                ->native(false)
                                ->seconds(false)
                                ->minDate(now()),
                        ])
                        ->columns(1),
                ])
                    ->livewireSubmitHandler('createPost')
                    ->footer([
                        View::make('filament.app.pages.home-post-composer-footer'),
                    ]),
            ]);
    }

    public function createPost(): void
    {
        abort_unless(Auth::check(), 403);

        $data = $this->getSchema('composer')->getState();

        $body = trim((string) ($data['body'] ?? ''));
        if ($body === '') {
            Notification::make()
                ->danger()
                ->title('Post body is required.')
                ->send();

            return;
        }

        $scheduledFor = $data['scheduled_for'] ?? null;
        if (is_string($scheduledFor) && trim($scheduledFor) === '') {
            $scheduledFor = null;
        }

        if (is_string($scheduledFor)) {
            $scheduledFor = Carbon::parse($scheduledFor, config('app.timezone'));
        }

        $isScheduled = $scheduledFor instanceof \Illuminate\Support\Carbon && $scheduledFor->isFuture();

        $post = Post::query()->create([
            'user_id' => Auth::id(),
            'body' => $body,
            'reply_policy' => $data['reply_policy'] ?? Post::REPLY_EVERYONE,
            'is_published' => ! $isScheduled,
            'scheduled_for' => $isScheduled ? $scheduledFor : null,
        ]);

        $imagePaths = $data['images'] ?? [];
        if ($imagePaths instanceof Arrayable) {
            $imagePaths = $imagePaths->toArray();
        }

        if (is_array($imagePaths)) {
            foreach (array_values($imagePaths) as $index => $path) {
                if (! is_string($path) || $path === '') {
                    continue;
                }

                $finalPath = "posts/{$post->id}/" . basename($path);

                if (Storage::disk('public')->exists($path) && ! Storage::disk('public')->exists($finalPath)) {
                    Storage::disk('public')->move($path, $finalPath);
                } else {
                    $finalPath = $path;
                }

                $post->images()->create([
                    'path' => $finalPath,
                    'sort_order' => $index,
                ]);
            }
        }

        $this->getSchema('composer')->rawState([
            'body' => '',
            'images' => [],
            'reply_policy' => Post::REPLY_EVERYONE,
            'scheduled_for' => null,
        ]);

        $this->resetTable();

        Notification::make()
            ->success()
            ->title('Posted!')
            ->send();
    }

    private function toggleLike(Post $post): void
    {
        abort_unless(Auth::check(), 403);

        $existing = $post->likes()
            ->where('user_id', Auth::id())
            ->exists();

        if ($existing) {
            $post->likes()->where('user_id', Auth::id())->delete();
        } else {
            $post->likes()->create(['user_id' => Auth::id()]);
        }

        $this->resetTable();
    }

    private function toggleRepost(Post $post): void
    {
        abort_unless(Auth::check(), 403);

        $existing = Post::query()
            ->where('user_id', Auth::id())
            ->where('repost_of_id', $post->id)
            ->whereNull('reply_to_id')
            ->where('body', '')
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            Post::query()->create([
                'user_id' => Auth::id(),
                'repost_of_id' => $post->id,
                'body' => '',
            ]);
        }

        $this->resetTable();
    }

    private function createReply(Post $post, array $data): void
    {
        abort_unless(Auth::check(), 403);

        $body = trim((string) ($data['body'] ?? ''));
        if ($body === '') {
            return;
        }

        Post::query()->create([
            'user_id' => Auth::id(),
            'reply_to_id' => $post->id,
            'body' => $body,
        ]);

        $this->resetTable();
    }

    private function timelineQuery(): Builder
    {
        $query = Post::query()
            ->with(['user', 'images'])
            ->withCount(['likes', 'reposts', 'replies'])
            ->whereNull('reply_to_id')
            ->latest();

        if (Auth::check()) {
            $followingIds = Auth::user()->following()->pluck('users.id')->push(Auth::id());
            $query->whereIn('user_id', $followingIds);
        }

        return $query;
    }
}

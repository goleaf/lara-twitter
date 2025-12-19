<?php

use App\Http\Controllers\LinkRedirectController;
use App\Livewire\HashtagPage;
use App\Livewire\BookmarksPage;
use App\Livewire\ConversationPage;
use App\Livewire\ListPage;
use App\Livewire\ListsPage;
use App\Livewire\MentionsPage;
use App\Livewire\MessagesPage;
use App\Livewire\NotificationsPage;
use App\Livewire\PostPage;
use App\Livewire\PostLikesPage;
use App\Livewire\RepostsPage;
use App\Livewire\ProfileLikesPage;
use App\Livewire\ProfileMediaPage;
use App\Livewire\ProfileRepliesPage;
use App\Livewire\ProfilePage;
use App\Livewire\ExplorePage;
use App\Livewire\FollowersPage;
use App\Livewire\FollowingPage;
use App\Livewire\SearchPage;
use App\Livewire\TimelinePage;
use App\Livewire\TrendingPage;
use App\Livewire\AnalyticsPage;
use App\Livewire\SpacePage;
use App\Livewire\SpacesPage;
use App\Livewire\MomentPage;
use App\Livewire\MomentsPage;
use App\Services\DirectMessageService;
use Illuminate\Support\Facades\Route;

Route::get('/', TimelinePage::class)->name('timeline');

Route::redirect('dashboard', '/')->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/@{user}', ProfilePage::class)->name('profile.show');
Route::get('/@{user}/likes', ProfileLikesPage::class)->name('profile.likes');
Route::get('/@{user}/replies', ProfileRepliesPage::class)->name('profile.replies');
Route::get('/@{user}/media', ProfileMediaPage::class)->name('profile.media');
Route::get('/@{user}/followers', FollowersPage::class)->name('profile.followers');
Route::get('/@{user}/following', FollowingPage::class)->name('profile.following');

Route::get('posts/{post}', PostPage::class)->name('posts.show');
Route::get('posts/{post}/likes', PostLikesPage::class)->name('posts.likes');
Route::get('posts/{post}/reposts', RepostsPage::class)->name('posts.reposts');

Route::get('l/{post}', LinkRedirectController::class)->name('links.redirect');

Route::get('tags/{tag}', HashtagPage::class)->name('hashtags.show');

Route::get('mentions', MentionsPage::class)->middleware('auth')->name('mentions');

Route::get('search', SearchPage::class)->name('search');
Route::get('trending', TrendingPage::class)->name('trending');
Route::get('explore', ExplorePage::class)->name('explore');
Route::get('spaces', SpacesPage::class)->name('spaces.index');
Route::get('spaces/{space}', SpacePage::class)->name('spaces.show');
Route::get('moments', MomentsPage::class)->name('moments.index');
Route::get('moments/{moment}', MomentPage::class)->name('moments.show');

Route::view('profile', 'profile')->middleware(['auth'])->name('profile');

Route::middleware('auth')->group(function () {
    Route::get('analytics', AnalyticsPage::class)->name('analytics');
    Route::get('notifications', NotificationsPage::class)->name('notifications');
    Route::get('bookmarks', BookmarksPage::class)->name('bookmarks');
    Route::get('messages', MessagesPage::class)->name('messages.index');
    Route::get('messages/{conversation}', ConversationPage::class)->name('messages.show');

    Route::get('messages/new/{user}', function (\App\Models\User $user, DirectMessageService $service) {
        try {
            $conversation = $service->findOrCreate(auth()->user(), $user);
        } catch (\Throwable) {
            abort(403);
        }

        return redirect()->route('messages.show', $conversation);
    })->name('messages.new');

    Route::get('lists', ListsPage::class)->name('lists.index');
});

Route::get('lists/{list}', ListPage::class)->name('lists.show');

require __DIR__.'/auth.php';

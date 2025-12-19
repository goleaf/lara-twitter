<?php

namespace App\Livewire;

use App\Models\Space;
use App\Models\SpaceParticipant;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SpacePage extends Component
{
    public Space $space;

    public function mount(Space $space): void
    {
        $this->space = $space->load(['host'])->loadCount('participants');

        if (Auth::check()) {
            $this->ensureHostParticipant();
        }
    }

    public function start(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless(Auth::id() === $this->space->host_user_id, 403);
        abort_if($this->space->isEnded(), 403);

        if (! $this->space->started_at) {
            $this->space->update(['started_at' => now()]);
        }

        $this->space->refresh();
    }

    public function end(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless(Auth::id() === $this->space->host_user_id, 403);
        abort_if($this->space->isEnded(), 403);

        $this->space->update(['ended_at' => now()]);
        $this->space->refresh();
    }

    public function join(): void
    {
        abort_unless(Auth::check(), 403);
        abort_if($this->space->isEnded(), 403);

        SpaceParticipant::query()->updateOrCreate(
            ['space_id' => $this->space->id, 'user_id' => Auth::id()],
            [
                'role' => Auth::id() === $this->space->host_user_id ? 'host' : 'listener',
                'joined_at' => now(),
                'left_at' => null,
            ],
        );

        $this->space->loadCount('participants');
    }

    public function leave(): void
    {
        abort_unless(Auth::check(), 403);

        SpaceParticipant::query()
            ->where('space_id', $this->space->id)
            ->where('user_id', Auth::id())
            ->update(['left_at' => now()]);

        $this->space->loadCount('participants');
    }

    public function myParticipant(): ?SpaceParticipant
    {
        if (! Auth::check()) {
            return null;
        }

        return SpaceParticipant::query()
            ->where('space_id', $this->space->id)
            ->where('user_id', Auth::id())
            ->first();
    }

    public function getParticipantsProperty()
    {
        return SpaceParticipant::query()
            ->where('space_id', $this->space->id)
            ->whereNull('left_at')
            ->with('user')
            ->orderByRaw("case role when 'host' then 0 when 'speaker' then 1 else 2 end")
            ->orderBy('created_at')
            ->get();
    }

    private function ensureHostParticipant(): void
    {
        if (Auth::id() !== $this->space->host_user_id) {
            return;
        }

        SpaceParticipant::query()->updateOrCreate(
            ['space_id' => $this->space->id, 'user_id' => Auth::id()],
            ['role' => 'host', 'joined_at' => now(), 'left_at' => null],
        );
    }

    public function render()
    {
        return view('livewire.space-page')->layout('layouts.app');
    }
}


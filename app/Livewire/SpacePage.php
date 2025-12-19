<?php

namespace App\Livewire;

use App\Http\Requests\Spaces\DecideSpeakerRequestRequest;
use App\Http\Requests\Spaces\SetSpaceParticipantRoleRequest;
use App\Models\Space;
use App\Models\SpaceParticipant;
use App\Models\SpaceSpeakerRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
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
        abort_unless($this->isModerator(), 403);
        abort_if($this->space->isEnded(), 403);

        if (! $this->space->started_at) {
            $this->space->update(['started_at' => now()]);
        }

        $this->space->refresh();
    }

    public function end(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->isModerator(), 403);
        abort_if($this->space->isEnded(), 403);

        $updates = ['ended_at' => now()];
        if ($this->space->recording_enabled) {
            $updates['recording_available_until'] = now()->addDays(30);
        }

        $this->space->update($updates);
        $this->space->refresh();
    }

    public function join(): void
    {
        abort_unless(Auth::check(), 403);
        abort_if($this->space->isEnded(), 403);

        $existing = SpaceParticipant::query()
            ->where('space_id', $this->space->id)
            ->where('user_id', Auth::id())
            ->first();

        $role = 'listener';
        if (Auth::id() === $this->space->host_user_id) {
            $role = 'host';
        } elseif ($existing && in_array($existing->role, ['speaker', 'cohost'], true)) {
            $role = $existing->role;
        }

        SpaceParticipant::query()->updateOrCreate(
            ['space_id' => $this->space->id, 'user_id' => Auth::id()],
            [
                'role' => $role,
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

    public function requestToSpeak(): void
    {
        abort_unless(Auth::check(), 403);
        abort_if($this->space->isEnded(), 403);

        $participant = $this->myParticipant();
        if (! $participant || $participant->left_at) {
            $this->join();
            $participant = $this->myParticipant();
        }

        if (! $participant) {
            return;
        }

        if (in_array($participant->role, ['host', 'cohost', 'speaker'], true)) {
            return;
        }

        SpaceSpeakerRequest::query()->updateOrCreate(
            ['space_id' => $this->space->id, 'user_id' => Auth::id()],
            ['status' => SpaceSpeakerRequest::STATUS_PENDING, 'decided_by' => null, 'decided_at' => null],
        );
    }

    public function decideSpeakerRequest(int $requestId, string $decision): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->isModerator(), 403);

        $validated = Validator::make(
            ['request_id' => $requestId, 'decision' => $decision],
            DecideSpeakerRequestRequest::rulesFor(),
        )->validate();

        $request = SpaceSpeakerRequest::query()
            ->where('space_id', $this->space->id)
            ->findOrFail($validated['request_id']);

        if ($request->status !== SpaceSpeakerRequest::STATUS_PENDING) {
            return;
        }

        if ($validated['decision'] === 'deny') {
            $request->update([
                'status' => SpaceSpeakerRequest::STATUS_DENIED,
                'decided_by' => Auth::id(),
                'decided_at' => now(),
            ]);

            return;
        }

        $this->ensureSpeakerCapacityFor($request->user_id);

        SpaceParticipant::query()->updateOrCreate(
            ['space_id' => $this->space->id, 'user_id' => $request->user_id],
            [
                'role' => 'speaker',
                'joined_at' => now(),
                'left_at' => null,
            ],
        );

        $request->update([
            'status' => SpaceSpeakerRequest::STATUS_APPROVED,
            'decided_by' => Auth::id(),
            'decided_at' => now(),
        ]);
    }

    public function setParticipantRole(int $participantId, string $role): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->isModerator(), 403);

        $validated = Validator::make(
            ['participant_id' => $participantId, 'role' => $role],
            SetSpaceParticipantRoleRequest::rulesFor(),
        )->validate();

        $participant = SpaceParticipant::query()
            ->where('space_id', $this->space->id)
            ->findOrFail($validated['participant_id']);

        abort_if($participant->user_id === $this->space->host_user_id, 403);

        if (in_array($validated['role'], ['speaker', 'cohost'], true)) {
            $this->ensureSpeakerCapacityFor($participant->user_id);
        }

        $participant->update(['role' => $validated['role']]);
    }

    public function removeParticipant(int $participantId): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->isModerator(), 403);

        $participant = SpaceParticipant::query()
            ->where('space_id', $this->space->id)
            ->findOrFail($participantId);

        abort_if($participant->user_id === $this->space->host_user_id, 403);

        $participant->update(['left_at' => now()]);
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

    public function mySpeakerRequest(): ?SpaceSpeakerRequest
    {
        if (! Auth::check()) {
            return null;
        }

        return SpaceSpeakerRequest::query()
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
            ->orderByRaw("case role when 'host' then 0 when 'cohost' then 1 when 'speaker' then 2 else 3 end")
            ->orderBy('created_at')
            ->get();
    }

    public function getPendingSpeakerRequestsProperty()
    {
        return SpaceSpeakerRequest::query()
            ->where('space_id', $this->space->id)
            ->where('status', SpaceSpeakerRequest::STATUS_PENDING)
            ->with('user')
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

    private function isModerator(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        if (Auth::id() === $this->space->host_user_id) {
            return true;
        }

        $participant = $this->myParticipant();

        return $participant && $participant->left_at === null && $participant->role === 'cohost';
    }

    private function ensureSpeakerCapacityFor(int $userId): void
    {
        $participant = SpaceParticipant::query()
            ->where('space_id', $this->space->id)
            ->where('user_id', $userId)
            ->whereNull('left_at')
            ->first();

        if ($participant && in_array($participant->role, ['host', 'cohost', 'speaker'], true)) {
            return;
        }

        $count = SpaceParticipant::query()
            ->where('space_id', $this->space->id)
            ->whereNull('left_at')
            ->whereIn('role', ['host', 'cohost', 'speaker'])
            ->count();

        abort_if($count >= Space::MAX_SPEAKERS, 422, 'This space already has the maximum number of speakers.');
    }

    public function render()
    {
        return view('livewire.space-page')->layout('layouts.app');
    }
}

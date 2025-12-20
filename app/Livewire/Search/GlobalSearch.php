<?php

namespace App\Livewire\Search;

use App\Models\Post;
use Livewire\Component;
use Livewire\WithPagination;

class GlobalSearch extends Component
{
    use WithPagination;

    public string $query = '';

    public int $perPage = 5;

    public function updatedQuery(): void
    {
        $this->resetPage();
    }

    public function getResultsProperty()
    {
        $query = trim($this->query);
        if ($query === '') {
            return null;
        }

        return Post::search($query)
            ->query(fn ($builder) => $builder->with(['user', 'images'])->withCount(['likes', 'reposts', 'replies']))
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.search.global-search');
    }
}

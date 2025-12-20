<?php

namespace App\Livewire\Shared;

use Livewire\Component;

class InfiniteScroll extends Component
{
    public bool $hasMore = true;

    public function render()
    {
        return view('livewire.shared.infinite-scroll');
    }
}

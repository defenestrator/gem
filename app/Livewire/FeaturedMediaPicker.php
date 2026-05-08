<?php

namespace App\Livewire;

use App\Models\Media;
use Livewire\Component;

class FeaturedMediaPicker extends Component
{
    public string $mediableType;
    public int $mediableId;
    public int $featuredId = 0;

    public function mount(string $mediableType, int $mediableId, int $featuredId = 0): void
    {
        $this->mediableType = $mediableType;
        $this->mediableId   = $mediableId;
        $this->featuredId   = $featuredId;
    }

    public function setFeatured(int $mediaId): void
    {
        abort_unless(auth()->check() && auth()->user()->is_admin, 403);

        Media::query()
            ->where('mediable_type', $this->mediableType)
            ->where('mediable_id', $this->mediableId)
            ->where('is_featured', true)
            ->update(['is_featured' => false]);

        Media::query()
            ->where('id', $mediaId)
            ->where('mediable_type', $this->mediableType)
            ->where('mediable_id', $this->mediableId)
            ->update(['is_featured' => true]);

        $this->featuredId = $mediaId;
    }

    public function render()
    {
        $media = Media::query()
            ->where('mediable_type', $this->mediableType)
            ->where('mediable_id', $this->mediableId)
            ->orderBy('id')
            ->get(['id', 'is_featured', 'thumbnail_url', 'url']);

        return view('livewire.featured-media-picker', ['media' => $media]);
    }
}

<?php

namespace Optix\MediaManager;

trait HasMedia
{
    public function media()
    {
        return $this->morphToMany(Media::class)->withPivot('collection');
    }

    public function hasMedia($collection = null)
    {
        return $this->getMedia($collection)->isNotEmpty();
    }

    public function getMedia($collection = null)
    {
        $media = $this->media;

        if ($collection) {
            $media->wherePivot('collection', $collection);
        }

        return $media;
    }

    public function firstMedia($collection = null)
    {
        return $this->getMedia($collection)->first();
    }

    public function firstMediaUrl($collection = null)
    {
        if (! $media = $this->firstMedia($collection)) {
            return null;
        }

        return $media->getUrl();
    }

    public function attachMedia($media, $collection = null)
    {
        $attach = [];

        $ids = (array) $media;

        foreach ($ids as $id) {
            $attach[$id] = [
                'collection' => $collection
            ];

            // Todo: Perform manipulations.
        }

        $this->media()->attach($attach);
    }

    public function syncMedia($media, $collection = null)
    {
        // Todo
    }

    public function detachMedia($collection = null)
    {
        // Todo
    }
}

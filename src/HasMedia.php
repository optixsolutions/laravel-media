<?php

namespace Optix\Media;

use Optix\Media\Jobs\PerformConversions;

trait HasMedia
{
    public function media()
    {
        return $this->morphToMany(config('media.model'), 'mediable')->withPivot('collection');
    }

    public function hasMedia($collection = null)
    {
        return $this->getMedia($collection)->isNotEmpty();
    }

    public function getMedia($collection = null)
    {
        $media = $this->media;

        if ($collection) {
            $media->where('pivot.collection', $collection);
        }

        return $media;
    }

    public function firstMedia($collection = null)
    {
        return $this->getMedia($collection)->first();
    }

    public function firstMediaUrl($collection = null, $conversion = null)
    {
        if (! $media = $this->firstMedia($collection)) {
            return null;
        }

        return $media->getUrl($conversion);
    }

    public function attachMedia($media, $collection, $conversions = [])
    {
        $attach = [];

        $ids = (array) $media;

        foreach ($ids as $id) {
            $attach[$id] = ['collection' => $collection];
            PerformConversions::dispatch($id, $conversions);
        }

        $this->media()->attach($attach);
    }

    public function syncMedia($media, $collection)
    {
        // Todo
    }

    public function detachMedia($collection)
    {
        // Todo
    }
}

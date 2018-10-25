<?php

namespace Optix\Media;

use Optix\Media\MediaAttacher\MediaAttacherFactory;

trait HasMedia
{
    public function media()
    {
        return $this->morphToMany(
            config('media.model'), 'mediable'
        )->withPivot('collection');
    }

    public function hasMedia($collection = null)
    {
        return $this->getMedia($collection)->isNotEmpty();
    }

    public function getMedia($collection = null)
    {
        $media = $this->media;

        if ($collection) {
            $media = $media->where('pivot.collection', $collection);
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

    public function attachMedia($media)
    {
        return MediaAttacherFactory::create($this, $media);
    }

    public function attachMultipleMedia($media)
    {
        return MediaAttacherFactory::createMultiple($this, $media);
    }

    public function detachMedia($media = null)
    {
        $this->media()->detach($media);
    }
}

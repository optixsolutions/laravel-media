<?php

namespace Optix\Media;

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
        $model = config('media.model');

        if (! $media instanceof $model) {
            $media = $model::find($media);
        }

        return app(MediaAttacher::class)
            ->setSubject($this)
            ->setMedia($media);
    }

    public function detachMedia($media = null)
    {
        $this->media()->detach($media);
    }
}

<?php

namespace Optix\Media;

trait HasMedia
{
    public function media()
    {
        return $this->morphToMany(config('media.model'), 'mediable')
                    ->withPivot('group');
    }

    public function hasMedia(string $group = 'default')
    {
        return $this->getMedia($group)->isNotEmpty();
    }

    public function getMedia(string $group = 'default')
    {
        return $this->media->where('pivot.group', $group);
    }

    public function getFirstMedia(string $group = 'default')
    {
        return $this->getMedia($group)->first();
    }

    public function getFirstMediaUrl(string $group = 'default', string $conversion = '')
    {
        if (! $media = $this->getFirstMedia($group)) {
            return '';
        }

        return $media->getUrl($conversion);
    }

    public function attachMedia($media, string $group = 'default')
    {
        $this->media()->attach($media, [
            'group' => $group
        ]);
    }

    public function detachMedia($media = null)
    {
        $this->media()->detach($media);
    }

    public function clearMediaGroup(string $group = 'default')
    {
        $this->media()->wherePivot('group', $group)->detach();
    }
}

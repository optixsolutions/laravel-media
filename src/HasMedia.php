<?php

namespace Optix\Media;

use Optix\Media\Models\Media;
use Optix\Media\Jobs\PerformConversions;
use Illuminate\Database\Eloquent\Collection;

trait HasMedia
{
    protected $mediaGroups = [];

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
        $this->registerMediaGroups();

        $ids = $this->parseMediaIds($media);

        if ($mediaGroup = $this->getMediaGroup($group)) {
            $model = config('media.model');

            $model::findMany($ids)
                ->each(function ($media) use ($mediaGroup) {
                    if ($mediaGroup->hasConversions()) {
                        PerformConversions::dispatch(
                            $media, $mediaGroup->getConversions()
                        );
                    }
                });
        }

        $this->media()->attach($ids, [
            'group' => $group
        ]);
    }

    protected function parseMediaIds($media)
    {
        if ($media instanceof Collection) {
            return $media->modelKeys();
        }

        if ($media instanceof Media) {
            return [$media->getKey()];
        }

        return (array) $media;
    }

    public function registerMediaGroups()
    {
        //
    }

    public function addMediaGroup(string $name)
    {
        $group = new MediaGroup($name);

        $this->mediaGroups[$name] = $group;

        return $group;
    }

    public function getMediaGroup(string $name)
    {
        return $this->mediaGroups[$name] ?? null;
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

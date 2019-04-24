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

    public function attachMedia($media, string $group = 'default', array $conversions = [])
    {
        $this->registerMediaGroups();

        $ids = $this->parseMediaIds($media);

        $mediaGroup = $this->getMediaGroup($group);

        if ($mediaGroup && $mediaGroup->hasConversions()) {
            $conversions = array_merge(
                $conversions, $mediaGroup->getConversions()
            );
        }

        if (! empty($conversions)) {
            $model = config('media.model');

            $media = $model::findMany($ids);

            $media->each(function ($media) use ($conversions) {
                PerformConversions::dispatch(
                    $media, $conversions
                );
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

    protected function addMediaGroup(string $name)
    {
        $group = new MediaGroup();

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

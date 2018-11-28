<?php

namespace Optix\Media\MediaAttacher;

use Exception;
use Optix\Media\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Optix\Media\Jobs\PerformConversions;

class MediaAttacher
{
    protected $subject;

    protected $media;

    protected $conversions = [];

    public function setSubject(Model $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    public function setMedia($media)
    {
        if ($media instanceof Media) {
            $this->media = $media;

            return $this;
        }

        if ($media = Media::find($media)) {
            $this->media = $media;

            return $this;
        }

        throw new Exception('Invalid media parameter.');
    }

    public function performConversion(string $conversion)
    {
        return $this->performConversions([ $conversion ]);
    }

    public function performConversions(array $conversions)
    {
        $this->conversions = $conversions;

        return $this;
    }

    public function toMediaCollection($collection)
    {
        if (! empty($this->conversions)) {
            PerformConversions::dispatch(
                $this->media, $this->conversions
            );
        }

        $this->subject->media()->attach($this->media, [
            'collection' => $collection
        ]);
    }
}

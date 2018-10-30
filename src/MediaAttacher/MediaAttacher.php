<?php

namespace Optix\Media\MediaAttacher;

use Optix\Media\Models\Media;
use Optix\Media\Jobs\PerformConversions;

class MediaAttacher
{
    protected $subject;

    protected $media;

    protected $conversions = [];

    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    public function setMedia(Media $media)
    {
        $this->media = $media;

        return $this;
    }

    public function performConversion(string $conversion)
    {
        return $this->performConversions([ $conversion ]);
    }

    public function performConversions(array $conversions)
    {
        $this->conversions = (array) $conversions;

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

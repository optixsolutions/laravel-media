<?php

namespace Optix\Media;

use Optix\Media\Models\Media;
use Optix\Media\Jobs\PerformConversions;

class MediaAttacher
{
    protected $subject;

    protected $media;

    protected $conversions = [];

    public function setSubject(HasMedia $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    public function setMedia(Media $media)
    {
        $this->media = $media;

        return $this;
    }

    public function performConversions(array $conversions)
    {
        $this->conversions = $conversions;

        return $this;
    }

    public function toCollection($collection)
    {
        PerformConversions::dispatch($this->media, $this->conversions);

        $this->subject->media()->attach($this->media, [
            'collection' => $collection
        ]);
    }
}

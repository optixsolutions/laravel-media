<?php

use Optix\Media\Media;
use Optix\Media\HasMedia;
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

    public function performConversion($conversion)
    {
        return $this->performConversions([$conversion]);
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

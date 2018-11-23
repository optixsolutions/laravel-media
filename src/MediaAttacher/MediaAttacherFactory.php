<?php

namespace Optix\Media\MediaAttacher;

class MediaAttacherFactory
{
    public static function create($subject, $media)
    {
        return app(MediaAttacher::class)
            ->setSubject($subject)
            ->setMedia($media);
    }

    public static function createMultiple($subject, array $media)
    {
        return collect($media)
            ->map(function ($media) use ($subject) {
                return static::create($subject, $media);
            });
    }
}

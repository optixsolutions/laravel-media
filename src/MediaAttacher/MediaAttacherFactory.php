<?php

namespace Optix\Media\MediaAttacher;

use Optix\Media\Models\Media;

class MediaAttacherFactory
{
    public static function create($subject, $media)
    {
        if (! $media instanceof Media) {
            $media = Media::find($media);
        }

        return app(MediaAttacher::class)
            ->setSubject($subject)
            ->setMedia($media);
    }

    public static function createMultiple($subject, array $media)
    {
        return collect($media)->map(function ($media) use ($subject) {
            return static::create($subject, $media);
        });
    }
}

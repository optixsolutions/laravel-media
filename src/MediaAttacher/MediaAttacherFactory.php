<?php

namespace Optix\Media\MediaAttacher;

use Optix\Media\HasMedia;

class MediaAttacherFactory
{
    public static function create(HasMedia $subject, $media)
    {
        $model = config('media.model');

        if (! $media instanceof $model) {
            $media = $model::find($media);
        }

        return app(MediaAttacher::class)
            ->setSubject($subject)
            ->setMedia($media);
    }

    public static function createMultiple(HasMedia $subject, array $media)
    {
        return collect($media)->map(function ($media) use ($subject) {
            return static::create($subject, $media);
        });
    }
}

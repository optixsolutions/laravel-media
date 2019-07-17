<?php

namespace Optix\Media\Facades;

use Illuminate\Support\Facades\Facade;
use Optix\Media\MediaUploader as Uploader;

class MediaUploader extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Uploader::class;
    }
}

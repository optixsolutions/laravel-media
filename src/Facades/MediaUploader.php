<?php

namespace Optix\Media\Facades;

use Illuminate\Support\Facades\Facade;

class MediaUploader extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return MediaUploader::class;
    }
}

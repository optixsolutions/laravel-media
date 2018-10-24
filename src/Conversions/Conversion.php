<?php

namespace Optix\Media\Conversions;

use Illuminate\Support\Facades\Facade;

class Conversion extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ConversionManager::class;
    }
}

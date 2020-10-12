<?php

namespace Optix\Media\Facades;

use Optix\Media\Contracts\Converter as ConverterContract;
use Illuminate\Support\Facades\Facade;
use Optix\Media\MediaConversionRegistry;

/**
 * @method static void register(string $name, ConverterContract $converter)
 * @method static ConverterContract get(string $name)
 * @method static bool exists(string $name)
 */
class MediaConversion extends Facade
{
    protected static function getFacadeAccessor()
    {
        return MediaConversionRegistry::class;
    }
}

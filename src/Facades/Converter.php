<?php

namespace Optix\Media\Facades;

use Optix\Media\Converter as ConverterContract;
use Illuminate\Support\Facades\Facade;
use Optix\Media\ConverterRegistry;

/**
 * @method static void register(string $name, ConverterContract $converter)
 * @method static ConverterContract get(string $name)
 * @method static bool exists(string $name)
 */
class Converter extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ConverterRegistry::class;
    }
}

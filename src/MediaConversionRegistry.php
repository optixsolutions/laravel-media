<?php

namespace Optix\Media;

use Exception;
use Optix\Media\Contracts\MediaConverter;

class MediaConversionRegistry
{
    /** @var MediaConverter[] */
    protected $conversions = [];

    /**
     * @param string $name
     * @param MediaConverter $converter
     */
    public function register(string $name, MediaConverter $converter)
    {
        $this->conversions[$name] = $converter;
    }

    /**
     * @param string $name
     * @return MediaConverter
     */
    public function get(string $name)
    {
        if (! $this->exists($name)) {
            throw new Exception("Media conversion does not exist: {$name}");
        }

        return $this->conversions[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function exists(string $name)
    {
        return isset($this->conversions[$name]);
    }
}

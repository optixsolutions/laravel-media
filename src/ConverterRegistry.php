<?php

namespace Optix\Media;

use Exception;
use Optix\Media\Contracts\Converter;

class ConverterRegistry
{
    /** @var Converter[] */
    protected $converters = [];

    /**
     * @param string $name
     * @param Converter $converter
     */
    public function register(string $name, Converter $converter)
    {
        $this->converters[$name] = $converter;
    }

    /**
     * @param string $name
     * @return Converter
     *
     * @throws Exception
     */
    public function get(string $name)
    {
        if (! $this->exists($name)) {
            throw new Exception("Converter does not exist: {$name}");
        }

        return $this->converters[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function exists(string $name)
    {
        return isset($this->converters[$name]);
    }
}

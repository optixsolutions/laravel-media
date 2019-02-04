<?php

namespace Optix\Media;

use Optix\Media\Exceptions\InvalidConversion;

class ConversionManager
{
    protected $conversions = [];

    public function all()
    {
        return $this->conversions;
    }

    public function register(string $name, callable $conversion)
    {
        $this->conversions[$name] = $conversion;
    }

    public function get(string $name)
    {
        if (! $this->exists($name)) {
            throw InvalidConversion::doesNotExist($name);
        }

        return $this->conversions[$name];
    }

    public function exists(string $name)
    {
        return isset($this->conversions[$name]);
    }
}

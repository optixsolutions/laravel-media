<?php

namespace Optix\Media;

use Exception;

class ConversionManager
{
    protected $conversions = [];

    public function all()
    {
        return $this->conversions;
    }

    public function register(string $name, callable $conversion)
    {
        if ($this->exists($name)) {
            throw new Exception("Conversion `{$name}` already exists.");
        }

        $this->conversions[$name] = $conversion;
    }

    public function get(string $name)
    {
        if (! $this->exists($name)) {
            throw new Exception("Conversion `{$name}` does not exist.");
        }

        return $this->conversions[$name];
    }

    public function exists(string $name)
    {
        return isset($this->conversions[$name]);
    }
}

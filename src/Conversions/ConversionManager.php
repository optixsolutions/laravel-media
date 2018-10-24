<?php

namespace Optix\Media;

class ConversionManager
{
    protected $conversions = [];

    public function register($name, callable $conversion)
    {
        $this->conversions[$name] = $conversion;
    }

    public function get($name)
    {
        return $this->conversions[$name];
    }

    public function exists($name)
    {
        return isset($this->conversions[$name]);
    }
}

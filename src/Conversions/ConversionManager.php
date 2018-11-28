<?php

namespace Optix\Media\Conversions;

use Exception;
use Intervention\Image\Image;

class ConversionManager
{
    protected $conversions = [];

    public function register($name, callable $conversion)
    {
        if ($this->exists($name)) {
            throw new Exception("Conversion `{$name}` already exists.");
        }

        $this->conversions[$name] = $conversion;
    }

    public function perform($name, Image $image)
    {
        if (! $this->exists($name)) {
            throw new Exception("Conversion `{$name}` does not exist.");
        }

        $conversion = $this->conversions[$name];

        return $conversion($image);
    }

    public function exists($name)
    {
        return isset($this->conversions[$name]);
    }
}

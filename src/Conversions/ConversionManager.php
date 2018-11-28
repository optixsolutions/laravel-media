<?php

namespace Optix\Media\Conversions;

use Intervention\Image\Image;

class ConversionManager
{
    protected $conversions = [];

    public function register($name, callable $conversion)
    {
        $this->conversions[$name] = $conversion;
    }

    public function perform($name, Image $image)
    {
        $conversion = $this->conversions[$name];

        return $conversion($image);
    }

    public function exists($name)
    {
        return isset($this->conversions[$name]);
    }
}

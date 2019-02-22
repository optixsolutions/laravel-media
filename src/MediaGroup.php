<?php

namespace Optix\Media;

class MediaGroup
{
    protected $name;

    protected $conversions = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function registerConversions(...$conversions)
    {
        $this->conversions = $conversions;

        return $this;
    }

    public function hasConversions()
    {
        return ! empty($this->conversions);
    }

    public function getConversions()
    {
        return $this->conversions;
    }
}

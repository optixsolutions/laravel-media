<?php

namespace Optix\Media;

class MediaGroup
{
    protected $conversions = [];

    public function performConversions(...$conversions)
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

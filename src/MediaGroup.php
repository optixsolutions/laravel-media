<?php

namespace Optix\Media;

class MediaGroup
{
    /**
     * @var array
     */
    protected $conversions = [];

    /**
     * Register the conversions to be performed when media is attached.
     *
     * @param  mixed  ...$conversions
     * @return $this
     */
    public function performConversions(...$conversions)
    {
        $this->conversions = $conversions;

        return $this;
    }

    /**
     * Determine if the group has any registered conversions.
     *
     * @return bool
     */
    public function hasConversions()
    {
        return ! empty($this->conversions);
    }

    /**
     * Get all the registered conversions.
     *
     * @return array
     */
    public function getConversions()
    {
        return $this->conversions;
    }
}

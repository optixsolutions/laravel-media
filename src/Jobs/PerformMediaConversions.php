<?php

namespace Optix\Media\Jobs;

use Optix\Media\Models\Media;

class PerformMediaConversions
{
    /** @var Media */
    protected $media;

    /** @var string[] */
    protected $conversionNames;

    /**
     * @param Media $media
     * @param string[] $conversionNames
     */
    public function __construct(Media $media, array $conversionNames)
    {
        $this->media = $media;
        $this->conversionNames = $conversionNames;
    }

    public function handle()
    {
        $conversionNames = array_unique($this->conversionNames);

        foreach ($conversionNames as $conversionName) {
            PerformMediaConversion::dispatch(
                $this->media,
                $conversionName
            );
        }
    }
}

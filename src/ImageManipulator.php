<?php

namespace Optix\Media;

use Optix\Media\Models\Media;
use Intervention\Image\ImageManager;

class ImageManipulator
{
    protected $conversionManager;

    protected $imageManager;

    public function __construct(ConversionManager $conversionManager, ImageManager $imageManager)
    {
        $this->conversionManager = $conversionManager;

        $this->imageManager = $imageManager;
    }

    public function manipulate(Media $media, array $conversions, $onlyIfMissing = true)
    {
        if (! $media->isOfType('image')) {
            return;
        }

        foreach ($conversions as $conversion) {
            $path = $media->getPath($conversion);

            if ($onlyIfMissing && $media->filesystem()->exists($path)) {
                continue;
            }

            $converter = $this->conversionManager->get($conversion);

            $image = $converter($this->imageManager->make($media->getFullPath()));

            $media->filesystem()->put($path, $image->stream());
        }
    }
}

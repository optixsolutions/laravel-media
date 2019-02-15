<?php

namespace Optix\Media;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Optix\Media\Models\Media;

class ImageManipulator
{
    private $conversionManager;
    private $imageManager;

    public function __construct(ConversionManager $conversionManager, ImageManager $imageManager)
    {
        $this->conversionManager = $conversionManager;
        $this->imageManager = $imageManager;
    }

    public function manipulate(Media $media, array $conversions, $onlyIfMissing = true)
    {
        foreach ($conversions as $conversion) {
            $path = $media->getPath($conversion);

            if ($onlyIfMissing && $media->filesystem()->exists($path)) {
                continue;
            }

            $converter = $this->conversionManager->get($conversion);
            /** @var Image $image */
            $image = $converter($this->imageManager->make($media->getFullPath()));

            $media->filesystem()->put($path, $image->stream());
        }
    }
}

<?php

namespace Optix\Media;

use Optix\Media\Models\Media;
use Intervention\Image\ImageManager;

class ImageManipulator
{
    protected $conversionRegistry;

    protected $imageManager;

    public function __construct(ConversionRegistry $conversionRegistry, ImageManager $imageManager)
    {
        $this->conversionRegistry = $conversionRegistry;

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

            $converter = $this->conversionRegistry->get($conversion);

            $image = $converter($this->imageManager->make($media->getFullPath()));

            $media->filesystem()->put($path, $image->stream());
        }
    }
}

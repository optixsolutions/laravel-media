<?php

namespace Optix\Media;

use Optix\Media\Models\Media;
use Intervention\Image\Facades\Image;
use Optix\Media\Conversions\ConversionManager;

class ImageManipulator
{
    protected $conversionManager;

    public function __construct(ConversionManager $conversionManager)
    {
        $this->conversionManager = $conversionManager;
    }

    public function manipulate(Media $media, array $conversions, $onlyIfMissing = false)
    {
        if (empty($conversions)) {
            return;
        }

        $image = Image::make($media->getFullPath());

        collect($conversions)
            ->reject(function ($conversion) use ($media, $onlyIfMissing) {
                return (
                    $onlyIfMissing
                    && $media->filesystem()->exists($media->getPath($conversion))
                );
            })
            ->each(function ($conversion) use ($media, $image) {
                $manipulatedImage = $this->conversionManager->perform(
                    $conversion, $image
                );

                $media->filesystem()->put(
                    $media->getPath($conversion),
                    $manipulatedImage->stream()
                );
            });
    }
}

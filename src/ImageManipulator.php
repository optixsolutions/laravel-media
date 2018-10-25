<?php

namespace Optix\Media;

use Optix\Media\Models\Media;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Optix\Media\Conversions\ConversionManager;

class ImageManipulator
{
    protected $conversions;

    public function __construct(ConversionManager $conversions)
    {
        $this->conversions = $conversions;
    }

    public function manipulate(Media $media, array $conversions)
    {
        if (empty($conversions)) {
            return;
        }

        $image = Image::make($media->getFullPath());

        $storage = Storage::disk($media->disk);

        foreach ($conversions as $conversionName) {
            if (
                $this->conversions->exists($conversionName)
                && ! $storage->exists($media->getPath($conversionName))
            ) {
                $conversion = $this->conversions->get($conversionName);

                $convertedImage = $conversion($image);

                $storage->put(
                    $media->getPath($conversionName),
                    $convertedImage->stream()
                );
            }
        }
    }
}

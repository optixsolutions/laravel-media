<?php

namespace Optix\Media;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class FileManipulator
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

        $image = Image::make($media->getPath());

        $storage = Storage::disk($media->disk);

        foreach ($conversions as $name => $conversion) {
            if (
                $this->conversions->exists($name)
                && ! $storage->exists($media->getDiskPath($name))
            ) {
                $convertedImage = $this->conversions->get($name)($image);
                $storage->put($media->getDiskPath($name), $convertedImage->stream());
            }
        }
    }
}

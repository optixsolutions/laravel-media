<?php

namespace Optix\Media;

use Optix\Media\Models\Media;
use Intervention\Image\Facades\Image;

class ImageManipulator
{
    protected $conversions;

    public function __construct(ConversionManager $conversions)
    {
        $this->conversions = $conversions;
    }

    public function manipulate(Media $media, array $conversions, $onlyIfMissing = true)
    {
        foreach ($conversions as $conversion) {
            $path = $media->getPath($conversion);

            if ($onlyIfMissing && $media->filesystem()->exists($path))  {
                continue;
            }

            $image = ($this->conversions->get($conversion))(
                Image::make($media->getFullPath())
            );

            $media->filesystem()->put($path, $image->stream());
        }
    }
}

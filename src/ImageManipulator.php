<?php

namespace Optix\Media;

use Optix\Media\Models\Media;
use Intervention\Image\ImageManager;

class ImageManipulator
{
    /**
     * @var ConversionRegistry
     */
    protected $conversionRegistry;

    /**
     * @var ImageManager
     */
    protected $imageManager;

    /**
     * Create a new ImageManipulator instance.
     *
     * @param  ConversionRegistry  $conversionRegistry
     * @param  ImageManager  $imageManager
     * @return void
     */
    public function __construct(ConversionRegistry $conversionRegistry, ImageManager $imageManager)
    {
        $this->conversionRegistry = $conversionRegistry;

        $this->imageManager = $imageManager;
    }

    /**
     * Perform the specified conversions on the given media item.
     *
     * @param  Media  $media
     * @param  array  $conversions
     * @param  bool  $onlyIfMissing
     * @return void
     */
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

            $image = $converter(
                $this->imageManager->make(
                    $media->filesystem()->readStream($media->getPath())
                )
            );

            $media->filesystem()->put($path, $image->stream());
        }
    }
}

<?php

namespace Optix\Media;

use Optix\Media\Models\Media;
use Intervention\Image\ImageManager;
use Optix\Media\Exceptions\InvalidConversion;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class ImageManipulator
{
    /** @var ConversionRegistry */
    protected $conversionRegistry;

    /** @var ImageManager */
    protected $imageManager;

    /**
     * Create a new manipulator instance.
     *
     * @param ConversionRegistry $conversionRegistry
     * @param ImageManager $imageManager
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
     * @param Media $media
     * @param array $conversions
     * @param bool $onlyIfMissing
     * @return void
     *
     * @throws InvalidConversion
     * @throws FileNotFoundException
     */
    public function manipulate(Media $media, array $conversions, $onlyIfMissing = true)
    {
        if (! $media->isOfType('image')) {
            return;
        }

        foreach ($conversions as $conversion) {
            $path = $media->getPath($conversion);

            $filesystem = $media->filesystem();

            if ($onlyIfMissing && $filesystem->exists($path)) {
                continue;
            }

            $converter = $this->conversionRegistry->get($conversion);

            $image = $converter($this->imageManager->make(
                $filesystem->readStream($media->getPath())
            ));

            $filesystem->put($path, $image->stream());
        }
    }
}

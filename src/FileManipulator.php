<?php

namespace Optix\Media;

use Illuminate\Support\Facades\Storage;

class FileManipulator
{
    protected $conversion;

    public function __construct(ConversionManager $conversion)
    {
        $this->conversion = $conversion;
    }

    public function manipulate(Media $media, array $conversionNames)
    {
        $image = Image::make($media->getPath());

        // Todo: Check if extension is convertable.
        // Todo: Check if conversion already exists.

        foreach ($conversionNames as $name) {
            if ($this->conversion->exists($name)) {
                $conversion = $this->conversion->get($name);

                $convertedImage = $conversion($image);

                $convertedImage->save(Storage::disk($media->disk)->path(
                    "{$media->id}/conversions/{$name}.{$media->extension}"
                ));
            }
        }
    }
}

<?php

namespace Optix\Media\Contracts;

use Optix\Media\Models\Media;

interface MediaConverter
{
    /**
     * @param Media $media
     * @param string $inputFilePath
     * @param string $outputFilePath
     * @return void
     */
    public function convert(Media $media, string $inputFilePath, string $outputFilePath);

    /**
     * @param Media $media
     * @return bool
     */
    public function canConvertMedia(Media $media): bool;

    /**
     * @param Media $media
     * @return string|null
     */
    public function getOutputExtension(Media $media): ?string;
}

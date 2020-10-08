<?php

namespace Optix\Media\Contracts;

use Optix\Media\Models\Media;

interface Converter
{
    public function convert(Media $media, string $inputFilePath, string $outputFilePath);

    public function canConvertMedia(Media $media): bool;

    public function getOutputExtension(Media $media): ?string;
}

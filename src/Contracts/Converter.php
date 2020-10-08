<?php

namespace Optix\Media\Contracts;

use Optix\Media\Models\Media;

interface Converter
{
    public function canConvertMedia(Media $media): bool;

    public function convert($sourceFilePath, $outputFilePath);
}

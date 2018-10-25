<?php

namespace Optix\Media\PathGenerator;

use Optix\Media\Models\Media;

interface PathGeneratorInterface
{
    public function getPath(Media $media);

    public function getConversionPath(Media $media, string $conversion);
}

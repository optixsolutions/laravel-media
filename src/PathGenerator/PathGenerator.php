<?php

namespace Optix\Media\PathGenerator;

use Optix\Media\Models\Media;

class PathGenerator implements PathGeneratorInterface
{
    public function getPath(Media $media)
    {
        return "{$this->getBasePath($media)}/{$media->file_name}";
    }

    public function getConversionPath(Media $media, string $conversion)
    {
        return "{$this->getBasePath($media)}/{$conversion}/{$media->file_name}";
    }

    protected function getBasePath(Media $media)
    {
        return $media->getKey();
    }
}

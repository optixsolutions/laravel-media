<?php

namespace Optix\Media;

use Optix\Media\Models\Media;

interface Converter
{
    public function convert($sourceFilePath, $outputFilePath);
}

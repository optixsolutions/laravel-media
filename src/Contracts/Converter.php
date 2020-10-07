<?php

namespace Optix\Media\Contracts;

interface Converter
{
    public function convert($sourceFilePath, $outputFilePath);
}

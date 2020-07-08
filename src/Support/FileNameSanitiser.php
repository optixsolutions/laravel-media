<?php

namespace Optix\Media\Support;

class FileNameSanitiser
{
    public static function sanitise(string $fileName)
    {
        return str_replace(
            ['#', '/', '\\', ' '],
            '-',
            $fileName
        );
    }
}

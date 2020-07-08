<?php

namespace Optix\Media\Support;

class FileNameSanitiser
{
    /**
     * @param string $fileName
     * @return string
     */
    public static function sanitise(string $fileName)
    {
        return str_replace(
            ['#', '/', '\\', ' '],
            '-',
            $fileName
        );
    }
}

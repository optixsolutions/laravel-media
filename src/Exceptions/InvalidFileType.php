<?php

namespace Optix\Media\Exceptions;

use Exception;

class InvalidFileType extends Exception
{
    public static function wasExpecting(string $mimeTypeExpected, string $mimeTypeActual)
    {
        // Get details of the calling class
        $trace = debug_backtrace();
        $class = $trace[1]['class'];
        $line = $trace[1]['line'];

        return new static(
            sprintf(
                'Class %s on line %d was expecting mime type "%s" but received "%s"',
                $class,
                $line,
                $mimeTypeExpected,
                $mimeTypeActual
            )
        );
    }
}

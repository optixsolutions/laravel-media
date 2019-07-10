<?php

namespace Optix\Media\Exceptions;

use Exception;

class InvalidConversion extends Exception
{
    /**
     * @param  string  $name
     * @return InvalidConversion
     */
    public static function doesNotExist($name)
    {
        $conversion = trans('media::media.conversion');
        $doesNotExist = trans('media::media.does_not_exist');

        return new static($conversion." `{$name}` ".$doesNotExist);
    }
}

<?php

namespace Optix\Media\Tests;

use Optix\Media\Models\Media;

class CustomMedia extends Media
{
    protected $fillable = [
        'name',
        'file_name',
        'disk',
        'mime_type',
        'size',
        'custom_attribute',
    ];
}

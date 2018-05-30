<?php

namespace Optix\MediaManager;

class Media
{
    protected $fillable = [
        'name', 'file_name', 'disk', 'mime_type', 'size'
    ];

    public function getUrl()
    {
        //
    }
}

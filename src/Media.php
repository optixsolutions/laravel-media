<?php

namespace Optix\MediaManager;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = [
        'name', 'file_name', 'disk', 'mime_type', 'size'
    ];

    public function getUrl()
    {
        //
    }
}

<?php

namespace Optix\Media\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Optix\Media\PathGenerator\PathGenerator;

class Media extends Model
{
    protected $fillable = [
        'name', 'file_name', 'disk', 'mime_type', 'size'
    ];

    public function getExtensionAttribute()
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    public function getUrl($conversion = null)
    {
        return $this->storage()->url($this->getPath($conversion));
    }

    public function getFullPath($conversion = null)
    {
        return $this->storage()->path($this->getPath($conversion));
    }

    public function getPath($conversion = null)
    {
        $pathGenerator = new PathGenerator();

        if ($conversion) {
            return $pathGenerator->getConversionPath($this, $conversion);
        }

        return $pathGenerator->getPath($this);
    }

    protected function storage()
    {
        return Storage::disk($this->disk);
    }
}

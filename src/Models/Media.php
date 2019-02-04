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

    public function getUrl(string $conversion = '')
    {
        return $this->filesystem()->url($this->getPath($conversion));
    }

    public function getFullPath(string $conversion = '')
    {
        return $this->filesystem()->path($this->getPath($conversion));
    }

    public function getPath(string $conversion = '')
    {
        $path = $this->getKey();

        if ($conversion) {
            $path .= '/conversions/' . $conversion;
        }

        return $path . '/' . $this->file_name;
    }

    public function filesystem()
    {
        return Storage::disk($this->disk);
    }
}

<?php

namespace Optix\Media;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = [
        'name', 'file_name', 'disk', 'mime_type', 'size'
    ];

    protected function filesystem()
    {
        return Storage::disk($this->disk);
    }

    public function getPath($conversion = null)
    {
        $path = "media/{$this->getKey()}";

        if ($conversion) {
            return "{$path}/conversions/{$conversion}.{$this->extension}";
        }

        return "{$path}/{$this->file_name}";
    }

    public function getUrl($conversion = null)
    {
        return $this->filesystem()->url($this->getPath($conversion));
    }

    public function getRelativePath($conversion = null)
    {
        return $this->filesystem()->path($this->getPath($conversion));
    }

    public function getExtensionAttribute()
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }
}

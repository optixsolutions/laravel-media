<?php

namespace Optix\Media;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
        return $this->storage()->url(
            $this->getDiskPath($conversion)
        );
    }

    public function getPath($conversion = null)
    {
        return $this->storage()->path(
            $this->getDiskPath($conversion)
        );
    }

    public function getDiskPath($conversion = null)
    {
        $basePath = $this->getKey();

        if ($conversion) {
            return "{$basePath}/conversions/{$conversion}.{$this->extension}";
        }

        return "{$basePath}/{$this->file_name}";
    }

    protected function storage()
    {
        return Storage::disk($this->disk);
    }
}

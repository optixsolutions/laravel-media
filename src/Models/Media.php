<?php

namespace Optix\Media\Models;

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

    public function getTypeAttribute()
    {
        return str_before($this->mime_type, '/') ?? null;
    }

    public function isOfType(string $type)
    {
        return $this->type === $type;
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
        $directory = $this->getDirectory();

        if ($conversion) {
            $directory .= '/conversions/' . $conversion;
        }

        return $directory . '/' . $this->file_name;
    }

    public function getDirectory()
    {
        return $this->getKey();
    }
    
    public function filesystem()
    {
        return Storage::disk($this->disk);
    }
}

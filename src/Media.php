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

    public function getUrl()
    {
        $this->filesystem()->url("{$this->id}/{$this->file_name}");
    }
}

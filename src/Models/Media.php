<?php

namespace Optix\Media\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Optix\Media\Concerns\ChangesFileExtension;
use Optix\Media\Facades\Converter;

/**
 * @property int $id
 * @property string $name
 * @property string $file_name
 * @property string $extension
 * @property string $disk
 * @property string $mime_type
 * @property int $size
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'name', 'file_name', 'disk', 'mime_type', 'size',
    ];

    public function getName()
    {
        return $this->name;
    }

    public function getFileName(string $conversionName = null)
    {
        if ($conversionName) {
            return $this->getConversionFileName($conversionName);
        }

        return $this->file_name;
    }

    public function getConversionFileName(string $conversionName)
    {
        $extension = $this->getConversionExtension($conversionName);

        return pathinfo($this->getFileName(), PATHINFO_FILENAME).'.'.$extension;
    }

    public function getExtension(string $conversionName = null)
    {
        if ($conversionName) {
            return $this->getConversionExtension($conversionName);
        }

        return pathinfo($this->getFileName(), PATHINFO_EXTENSION);
    }

    public function getConversionExtension(string $conversionName)
    {
        $converter = Converter::get($conversionName);

        if ($converter instanceof ChangesFileExtension) {
            return $converter->getExtension();
        }

        return $this->getExtension();
    }

    public function getExtensionAttribute()
    {
        return $this->getExtension();
    }

    public function getDisk()
    {
        return $this->disk;
    }

    public function getMimeType()
    {
        return $this->mime_type;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getUrl(string $conversionName = null)
    {
        if ($conversionName) {
            return $this->getConversionUrl($conversionName);
        }

        return $this->filesystem()->url($this->getPath());
    }

    public function getConversionUrl(string $conversionName)
    {
        return $this->filesystem()->url(
            $this->getConversionPath($conversionName)
        );
    }

    public function getPath(string $conversionName = null)
    {
        if ($conversionName) {
            return $this->getConversionPath($conversionName);
        }

        return $this->getDirectory().DIRECTORY_SEPARATOR.$this->getFileName();
    }

    public function getConversionPath(string $conversionName)
    {
        return $this->getConversionDirectory($conversionName)
            .DIRECTORY_SEPARATOR
            .$this->getConversionFileName($conversionName);
    }

    public function getDirectory(string $conversionName = null)
    {
        if ($conversionName) {
            return $this->getConversionDirectory($conversionName);
        }

        return $this->getKey();
    }

    public function getConversionDirectory(string $conversionName)
    {
        return $this->getDirectory().DIRECTORY_SEPARATOR.$conversionName;
    }

    public function filesystem()
    {
        return Storage::disk($this->getDisk());
    }
}

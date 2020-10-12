<?php

namespace Optix\Media\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Optix\Media\Facades\MediaConversion;

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
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'media';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'file_name', 'disk', 'mime_type', 'size',
    ];

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|null $conversionName
     * @return string
     */
    public function getFileName(string $conversionName = null)
    {
        if ($conversionName) {
            return $this->getConversionFileName($conversionName);
        }

        return $this->file_name;
    }

    /**
     * @param string $conversionName
     * @return string
     */
    public function getConversionFileName(string $conversionName)
    {
        $extension = $this->getConversionExtension($conversionName);

        return pathinfo($this->getFileName(), PATHINFO_FILENAME).'.'.$extension;
    }

    /**
     * @param string|null $conversionName
     * @return string
     */
    public function getExtension(string $conversionName = null)
    {
        if ($conversionName) {
            return $this->getConversionExtension($conversionName);
        }

        return pathinfo($this->getFileName(), PATHINFO_EXTENSION);
    }

    /**
     * @param string $conversionName
     * @return string
     */
    public function getConversionExtension(string $conversionName)
    {
        $converter = MediaConversion::get($conversionName);

        if (! $extension = $converter->getOutputExtension($this)) {
            $extension = $this->getExtension();
        }

        return $extension;
    }

    /**
     * @return string
     */
    public function getExtensionAttribute()
    {
        return $this->getExtension();
    }

    /**
     * @param string|null $conversionName
     * @return string
     */
    public function getDirectory(string $conversionName = null)
    {
        if ($conversionName) {
            return $this->getConversionDirectory($conversionName);
        }

        return (string) $this->getKey();
    }

    /**
     * @param string $conversionName
     * @return string
     */
    public function getConversionDirectory(string $conversionName)
    {
        return $this->getDirectory().DIRECTORY_SEPARATOR.$conversionName;
    }

    /**
     * @param string|null $conversionName
     * @return string
     */
    public function getPath(string $conversionName = null)
    {
        if ($conversionName) {
            return $this->getConversionPath($conversionName);
        }

        $directory = $this->getDirectory();
        $fileName = $this->getFileName();

        return $directory.DIRECTORY_SEPARATOR.$fileName;
    }

    /**
     * @param string $conversionName
     * @return string
     */
    public function getConversionPath(string $conversionName)
    {
        $directory = $this->getConversionDirectory($conversionName);
        $fileName = $this->getConversionFileName($conversionName);

        return $directory.DIRECTORY_SEPARATOR.$fileName;
    }

    /**
     * @param string|null $conversionName
     * @return string
     */
    public function getUrl(string $conversionName = null)
    {
        if ($conversionName) {
            return $this->getConversionUrl($conversionName);
        }

        return $this->filesystem()->url($this->getPath());
    }

    /**
     * @param string $conversionName
     * @return string
     */
    public function getConversionUrl(string $conversionName)
    {
        $path = $this->getConversionPath($conversionName);

        return $this->filesystem()->url($path);
    }

    /**
     * @return string
     */
    public function getDisk()
    {
        return $this->disk;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mime_type;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return Filesystem
     */
    public function filesystem()
    {
        return Storage::disk($this->getDisk());
    }
}

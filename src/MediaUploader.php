<?php

namespace Optix\Media;

use Optix\Media\Models\Media;
use Illuminate\Http\UploadedFile;

class MediaUploader
{
    protected $file;

    protected $name;

    protected $fileName;

    protected $attributes = [];

    public function __construct(UploadedFile $file)
    {
        $this->setFile($file);
    }

    public static function fromFile(UploadedFile $file)
    {
        return new static($file);
    }

    public function setFile(UploadedFile $file)
    {
        $this->file = $file;

        $fileName = $file->getClientOriginalName();
        $name = pathinfo($fileName, PATHINFO_FILENAME);

        $this->setName($name);
        $this->setFileName($fileName);

        return $this;
    }

    public function useName(string $name)
    {
        return $this->setName($name);
    }

    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function useFileName(string $fileName)
    {
        return $this->setFileName($fileName);
    }

    public function setFileName(string $fileName)
    {
        $this->fileName = $this->sanitizeFileName($fileName);

        return $this;
    }

    protected function sanitizeFileName(string $fileName)
    {
        return str_replace(['#', '/', '\\', ' '], '-', $fileName);
    }

    public function withProperties(array $properties)
    {
        return $this->withAttributes($properties);
    }

    public function withAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function upload()
    {
        $media = new Media();

        $media->name = $this->name;
        $media->file_name = $this->fileName;
        $media->disk = config('filesystems.default');
        $media->mime_type = $this->file->getMimeType();
        $media->size = $this->file->getSize();

        $media->fill($this->attributes);

        $media->save();

        $media->filesystem()->put(
            $media->getPath(), $this->file
        );

        return $media->fresh();
    }
}

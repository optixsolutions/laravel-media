<?php

namespace Optix\Media;

use Illuminate\Http\UploadedFile;

class MediaUploader
{
    /** @var UploadedFile */
    protected $file;

    /** @var string */
    protected $name;

    /** @var string */
    protected $fileName;

    /** @var string */
    protected $disk;

    /** @var array */
    protected $attributes = [];

    /**
     * Create a new uploader instance.
     *
     * @param UploadedFile $file
     * @return void
     */
    public function __construct(UploadedFile $file)
    {
        $this->setFile($file);
    }

    /**
     * @param UploadedFile $file
     * @return MediaUploader
     */
    public static function fromFile(UploadedFile $file)
    {
        return new static($file);
    }

    /**
     * Set the file to be uploaded.
     *
     * @param UploadedFile $file
     * @return MediaUploader
     */
    public function setFile(UploadedFile $file)
    {
        $this->file = $file;

        $fileName = $file->getClientOriginalName();
        $name = pathinfo($fileName, PATHINFO_FILENAME);

        $this->setName($name);
        $this->setFileName($fileName);

        return $this;
    }

    /**
     * Set the name of the media item.
     *
     * @param string $name
     * @return MediaUploader
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $name
     * @return MediaUploader
     */
    public function useName(string $name)
    {
        return $this->setName($name);
    }

    /**
     * Set the name of the file.
     *
     * @param string $fileName
     * @return MediaUploader
     */
    public function setFileName(string $fileName)
    {
        $this->fileName = $this->sanitiseFileName($fileName);

        return $this;
    }

    /**
     * @param string $fileName
     * @return MediaUploader
     */
    public function useFileName(string $fileName)
    {
        return $this->setFileName($fileName);
    }

    /**
     * Sanitise the file name.
     *
     * @param string $fileName
     * @return string
     */
    protected function sanitiseFileName(string $fileName)
    {
        return str_replace(['#', '/', '\\', ' '], '-', $fileName);
    }

    /**
     * Specify the disk where the file will be stored.
     *
     * @param string $disk
     * @return MediaUploader
     */
    public function setDisk(string $disk)
    {
        $this->disk = $disk;

        return $this;
    }

    /**
     * Set any custom attributes to be saved to the media item.
     *
     * @param array $attributes
     * @return MediaUploader
     */
    public function withAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @param array $properties
     * @return MediaUploader
     */
    public function withProperties(array $properties)
    {
        return $this->withAttributes($properties);
    }

    /**
     * Upload the file to the specified disk.
     *
     * @return mixed
     */
    public function upload()
    {
        $model = config('media.model');

        $media = new $model();

        $media->name = $this->name;
        $media->file_name = $this->fileName;
        $media->disk = $this->disk ?: config('media.disk');
        $media->mime_type = $this->file->getMimeType();
        $media->size = $this->file->getSize();

        $media->forceFill($this->attributes);

        $media->save();

        $media->filesystem()->putFileAs(
            $media->getDirectory(),
            $this->file,
            $this->fileName
        );

        return $media->fresh();
    }
}

<?php

namespace Optix\Media;

use Optix\Media\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Contracts\Filesystem\Filesystem;

class MediaUploader
{
    /**
     * @var UploadedFile
     */
    protected $file;

    /**
     * @var string
     */
    protected $model;

    /**
     * @var string
     */
    protected $disk;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var FilesystemManager
     */
    protected $filesystemManager;

    /**
     * Create a new MediaUploader instance.
     *
     * @param  FilesystemManager  $filesystemManager
     * @param  string  $model
     * @param  string  $disk
     * @return void
     */
    public function __construct(
        string $model, string $disk, FilesystemManager $filesystemManager
    ) {
        $this->setModel($model);
        $this->setDisk($disk);

        $this->filesystemManager = $filesystemManager;
    }

    /**
     * Set the file to be uploaded.
     *
     * @param  UploadedFile  $file
     * @return $this
     */
    public function fromFile(UploadedFile $file)
    {
        $this->file = $file;

        $fileName = $file->getClientOriginalName();
        $name = pathinfo($fileName, PATHINFO_FILENAME);

        $this->setName($name);
        $this->setFileName($fileName);

        return $this;
    }

    /**
     * Set the media model class.
     *
     * @param  string  $model
     * @return $this
     */
    public function setModel(string $model)
    {
        // Todo: Validate the model class

        $this->model = $model;

        return $this;
    }

    /**
     * Create a new media model instance.
     *
     * @return Media
     */
    protected function makeModel()
    {
        return new $this->model;
    }

    /**
     * Set the disk where the file will be stored.
     *
     * @param  string  $disk
     * @return $this
     */
    public function setDisk(string $disk)
    {
        $this->disk = $disk;

        return $this;
    }

    /**
     * Set the name of the media item.
     *
     * @param  string  $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the name of the file.
     *
     * @param  string  $fileName
     * @return $this
     */
    public function setFileName(string $fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Set any additional media item attributes.
     *
     * @param  array  $attributes
     * @return $this
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Get the filesystem driver.
     *
     * @return Filesystem
     */
    protected function getFilesystem()
    {
        return $this->filesystemManager->disk($this->disk);
    }

    /**
     * Upload the file and persist the media item.
     *
     * @return Media
     */
    public function upload()
    {
        $media = $this->makeModel();

        $media->name = $this->name;
        $media->file_name = $this->fileName;
        $media->disk = $this->disk;
        $media->mime_type = $this->file->getMimeType();
        $media->size = $this->file->getSize();

        $media->forceFill($this->attributes);

        $media->save();

        $this->getFilesystem()->put(
            $media->getPath(),
            $this->file
        );

        return $media;
    }
}

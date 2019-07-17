<?php

namespace Optix\Media;

use Finfo;
use Optix\Media\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Contracts\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class MediaUploader
{
    /** @var string */
    protected $model;

    /** @var string */
    protected $disk;

    /** @var string */
    protected $filePath;

    /** @var string */
    protected $fileName;

    /** @var string */
    protected $name;

    /** @var array */
    protected $attributes = [];

    /** @var bool */
    protected $preserveOriginal = false;

    /** @var FilesystemManager */
    protected $filesystemManager;

    /**
     * Create a new MediaUploader instance.
     *
     * @param  string  $model
     * @param  string  $disk
     * @param  FilesystemManager  $filesystemManager
     */
    public function __construct(
        string $model, string $disk, FilesystemManager $filesystemManager
    ) {
        $this->setModel($model);
        $this->setDisk($disk);

        $this->filesystemManager = $filesystemManager;
    }

    /***
     * Todo
     *
     * @param  string  $path
     * @return $this
     */
    public function fromPath(string $path)
    {
        $this->filePath = $path;

        $pathInfo = pathinfo($path);

        $this->setFileName($pathInfo['basename']);
        $this->setName($pathInfo['filename']);

        return $this;
    }

    /***
     * Todo
     *
     * @param  UploadedFile|File  $file
     * @return $this
     */
    public function fromFile($file)
    {
        if ($file instanceof UploadedFile) {
            $this->filePath = $file->getRealPath();

            $this->setFileName($file->getClientOriginalName());
            $this->setName(pathinfo($this->fileName, PATHINFO_FILENAME));

            return $this;
        }

        if ($file instanceof File) {
            return $this->fromPath($file->getRealPath());
        }

        // Todo: throw Exception;
    }

    /**
     * Todo
     *
     * @param  string  $url
     * @return $this
     */
    public function fromUrl(string $url)
    {
        if (! $stream = @fopen($url, 'r')) {
            // Todo: throw Exception
        }

        $this->filePath = tempnam(sys_get_temp_dir(), 'media');
        file_put_contents($this->filePath, $stream);

        $fileName = basename(urldecode(parse_url($url, PHP_URL_PATH)));

        if (strlen($fileName) === 0) {
            $fileName = 'media';
        }

        if (strpos($fileName, '.') === false) {
            $extension = explode('/', mime_content_type($this->filePath));
            $fileName = $fileName.'.'.$extension[1];
        }

        $this->setFileName($fileName);
        $this->setName(pathinfo($fileName, PATHINFO_FILENAME));

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
        // Todo: Verify model

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
     * Set the disk where the file will be uploaded.
     *
     * @param  string  $disk
     * @return $this
     */
    public function setDisk(string $disk)
    {
        // Todo: Verify Disk

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
     * Set any custom attributes to be saved on the media item.
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
     * Specify to preserve the original file.
     *
     * @return $this
     */
    public function preserveOriginal()
    {
        $this->preserveOriginal = true;

        return $this;
    }

    /**
     * Upload the file and create a media item record.
     *
     * @return Media
     */
    public function upload()
    {
        $filesystem = $this->getFilesystem();

        $media = $this->makeModel();

        $media->name = $this->name;
        $media->file_name = $this->fileName;
        $media->mime_type = $this->getMimeType();
        $media->size = filesize($this->filePath);
        $media->disk = $this->disk;

        $media->fill($this->attributes);

        $media->save();

        $file = fopen($this->filePath, 'r');
        $filesystem->put($media->getPath(), $file);
        fclose($file);

        if (! $this->preserveOriginal) {
            unlink($this->filePath);
        }

        return $media;
    }

    /**
     * Resolve the filesystem driver.
     *
     * @return Filesystem
     */
    protected function getFilesystem()
    {
        return $this->filesystemManager->disk($this->disk);
    }

    /**
     * Get the file's mime type.
     *
     * @return string
     */
    protected function getMimeType()
    {
        $finfo = new Finfo(FILEINFO_MIME_TYPE);

        return $finfo->file($this->filePath);
    }
}

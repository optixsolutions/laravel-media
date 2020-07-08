<?php

namespace Optix\Media;

use Exception;
use Illuminate\Filesystem\FilesystemManager;
use League\Flysystem\AdapterInterface;
use Optix\Media\Models\Media;
use Optix\Media\Options\UploadOptions;
use Optix\Media\Support\FileNameSanitiser;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaUploader
{
    /** @var FilesystemManager */
    protected $filesystemManager;

    /** @var array */
    protected $config;

    /** @var UploadOptions */
    protected $options;

    /**
     * Create a new media uploader instance.
     *
     * @param FilesystemManager $filesystemManager
     * @param array $config
     * @return void
     */
    public function __construct(
        FilesystemManager $filesystemManager,
        array $config
    ) {
        $this->setFilesystemManager($filesystemManager);
        $this->setConfig($config);
    }

    /**
     * Set the filesystem manager.
     *
     * @param FilesystemManager $filesystemManager
     * @return void
     */
    protected function setFilesystemManager(
        FilesystemManager $filesystemManager
    ) {
        $this->filesystemManager = $filesystemManager;
    }

    /**
     * Set the config.
     *
     * @param array $config
     * @return void
     */
    protected function setConfig(array $config)
    {
        /*
         * $config = [
         *     'model' => Media::class,
         *     'disk' => 'public',
         * ];
         */

        $this->config = $config;
    }

    /**
     * Set the upload options.
     *
     * @param UploadOptions|null $options
     * @return void
     */
    protected function setOptions(?UploadOptions $options)
    {
        $this->options = $options ?: new UploadOptions();
    }

    /**
     * Upload a file to the media manager.
     *
     * @param string|UploadedFile|File $file
     * @param UploadOptions|null $options
     * @return Media
     *
     * @throws
     */
    public function upload($file, UploadOptions $options = null)
    {
        [$filePath, $fileName] = $this->parseFileInfo($file);

        $this->setOptions($options);

        $disk = $this->getDisk();
        $filesystem = $this->getFilesystem($disk);
        $visibility = $this->getVisibility();

        $media = $this->makeModel();

        $media->fill($this->options->customAttributes);

        $media->name = $this->getMediaName($fileName);
        $media->file_name = $this->getFileName($fileName);
        $media->disk = $disk;
        $media->mime_type = mime_content_type($filePath);
        $media->size = filesize($filePath);

        $media->save();

        $fileHandle = fopen($filePath, 'r');

        $filesystem->writeStream(
            $media->getPath(),
            $fileHandle,
            $visibility
                ? ['visibility' => $visibility]
                : []
        );

        fclose($fileHandle);

        if (! $this->options->preserveOriginalFile) {
            unlink($filePath);
        }

        return $media;
    }

    protected function parseFileInfo($file)
    {
        if (is_string($file)) {
            return [
                $file,
                basename($file),
            ];
        }

        if ($file instanceof UploadedFile) {
            return [
                $file->getPathname(),
                $file->getClientOriginalName(),
            ];
        }

        if ($file instanceof File) {
            return [
                $file->getPathname(),
                $file->getFilename(),
            ];
        }

        throw new Exception('Invalid file type.');
    }

    protected function makeModel(): Media
    {
        $model = new $this->config['model'];

        if (! $model instanceof Media) {
            throw new Exception('Invalid media model.');
        }

        return $model;
    }

    protected function getDisk()
    {
        return $this->options->disk ?: $this->config['disk'];
    }

    protected function getFilesystem(string $disk)
    {
        try {
            return $this->filesystemManager->disk($disk);
        } catch (Exception $exception) {
            throw new Exception('Invalid disk.');
        }
    }

    protected function getVisibility()
    {
        if (! $visibility = $this->options->visibility) {
            return null;
        }

        if (! in_array($visibility, [
            AdapterInterface::VISIBILITY_PUBLIC,
            AdapterInterface::VISIBILITY_PRIVATE,
        ])) {
            throw new Exception('Invalid visibility.');
        }

        return $visibility;
    }

    protected function getMediaName(string $fileName)
    {
        if ($mediaName = $this->options->mediaName) {
            return $mediaName;
        }

        return pathinfo(
            $this->options->fileName ?: $fileName,
            PATHINFO_FILENAME
        );
    }

    protected function getFileName(string $fileName)
    {
        return $this->sanitiseFileName(
            $this->options->fileName ?: $fileName,
            $this->options->fileNameSanitiser
        );
    }

    protected function sanitiseFileName(string $fileName, ?callable $sanitiser)
    {
        if (is_callable($sanitiser)) {
            return $sanitiser($fileName);
        }

        return FileNameSanitiser::sanitise($fileName);
    }
}

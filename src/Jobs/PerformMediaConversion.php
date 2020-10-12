<?php

namespace Optix\Media\Jobs;

use Exception;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Queue\SerializesModels;
use Optix\Media\MediaConversionRegistry;
use Optix\Media\Models\Media;

class PerformMediaConversion
{
    use SerializesModels;

    /** @var Media */
    protected $media;

    /** @var string */
    protected $conversionName;

    /**
     * @param Media $media
     * @param string $conversionName
     */
    public function __construct(Media $media, string $conversionName)
    {
        $this->media = $media;
        $this->conversionName = $conversionName;
    }

    /**
     * @param FilesystemManager $filesystemManager
     * @param MediaConversionRegistry $conversionRegistry
     */
    public function handle(
        FilesystemManager $filesystemManager,
        MediaConversionRegistry $conversionRegistry
    ) {
        $converter = $conversionRegistry->get($this->conversionName);

        if (! $converter->canConvertMedia($this->media)) {
            return;
        }

        $disk = $filesystemManager->disk($this->media->getDisk());

        try {
            $inputFilePath = $this->createTemporaryFile('input');
            $outputFilePath = $this->createTemporaryFile('output');

            $this->copySourceFileToLocalDisk($disk, $inputFilePath);

            $converter->convert(
                $this->media, $inputFilePath, $outputFilePath
            );

            $this->assertConvertedFileIsReadable($outputFilePath);

            $this->copyConvertedFileToMediaDisk($disk, $outputFilePath);
        } finally {
            $this->deleteFileIfExists($inputFilePath);
            $this->deleteFileIfExists($outputFilePath);
        }
    }

    /**
     * @param string $prefix
     * @return string
     */
    protected function createTemporaryFile(string $prefix)
    {
        $filePath = tempnam(sys_get_temp_dir(), $prefix);

        if (! $filePath) {
            throw new Exception('Failed to create the temporary file.');
        }

        return $filePath;
    }

    /**
     * @param FilesystemAdapter $disk
     * @param string $toFilePath
     */
    protected function copySourceFileToLocalDisk(
        FilesystemAdapter $disk, string $toFilePath
    ) {
        $readStream = $disk->readStream($this->media->getPath());

        if (! $readStream) {
            throw new Exception('Failed to open a handle to the source file.');
        }

        try {
            $writeStream = fopen($toFilePath, 'w');

            if (! $writeStream) {
                throw new Exception('Failed to open a handle to the local file.');
            }

            try {
                stream_copy_to_stream($readStream, $writeStream);
            } finally {
                fclose($writeStream);
            }
        } finally {
            fclose($readStream);
        }
    }

    /**
     * @param FilesystemAdapter $disk
     * @param string $fromFilePath
     */
    protected function copyConvertedFileToMediaDisk(
        FilesystemAdapter $disk, string $fromFilePath
    ) {
        $readStream = fopen($fromFilePath, 'r');

        if (! $readStream) {
            throw new Exception('Failed to open a handle to the converted file.');
        }

        try {
            $disk->putStream(
                $this->media->getConversionPath($this->conversionName),
                $readStream
            );
        } finally {
            fclose($readStream);
        }
    }

    /**
     * @param string $filePath
     */
    protected function assertConvertedFileIsReadable(string $filePath)
    {
        if (! is_readable($filePath)) {
            throw new Exception('Failed to read the converted file.');
        }
    }

    /**
     * @param string $filePath
     */
    protected function deleteFileIfExists(string $filePath)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}

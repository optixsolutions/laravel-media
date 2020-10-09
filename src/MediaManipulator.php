<?php

namespace Optix\Media;

use Exception;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Str;
use Optix\Media\Contracts\Converter;
use Optix\Media\Models\Media;

class MediaManipulator
{
    /** @var FilesystemManager */
    protected $filesystemManager;

    /** @var ConverterRegistry */
    protected $converterRegistry;

    /**
     * @param FilesystemManager $filesystemManager
     * @param ConverterRegistry $converterRegistry
     */
    public function __construct(
        FilesystemManager $filesystemManager,
        ConverterRegistry $converterRegistry
    ) {
        $this->filesystemManager = $filesystemManager;
        $this->converterRegistry = $converterRegistry;
    }

    /**
     * @param Media $media
     * @param array $conversionNames
     * @param bool $onlyIfMissing
     */
    public function convert(Media $media, array $conversionNames, bool $onlyIfMissing = true)
    {
        $filesystem = $this->getFilesystem($media->getDisk());

        $converters = $this->getConverters(
            $media, $filesystem, $conversionNames, $onlyIfMissing
        );

        if (empty($converters)) {
            return;
        }

        $inputFilePath = $this->generateTemporaryFilePath($media->getExtension());

        $this->copySourceFileToLocalDisk(
            $filesystem, $media->getPath(), $inputFilePath
        );

        try {
            $this->performConversions(
                $media, $filesystem, $converters, $inputFilePath
            );
        } finally {
            $this->deleteFileIfExists($inputFilePath);
        }
    }

    /**
     * @param string $disk
     * @return FilesystemAdapter
     */
    protected function getFilesystem(string $disk)
    {
        return $this->filesystemManager->disk($disk);
    }

    /**
     * @param Media $media
     * @param FilesystemAdapter $filesystem
     * @param array $conversionNames
     * @param bool $onlyIfMissing
     * @return Converter[]
     */
    protected function getConverters(
        Media $media,
        FilesystemAdapter $filesystem,
        array $conversionNames,
        bool $onlyIfMissing
    ) {
        $converters = [];

        foreach ($conversionNames as $conversionName) {
            $converter = $this->getConverter($conversionName);

            if (! $converter->canConvertMedia($media)) {
                continue;
            }

            if (
                $onlyIfMissing
                && $filesystem->exists($media->getConversionPath($conversionName))
            ) {
                continue;
            }

            $converters[$conversionName] = $converter;
        }

        return $converters;
    }

    /**
     * @param string $conversionName
     * @return Converter
     */
    protected function getConverter(string $conversionName)
    {
        return $this->converterRegistry->get($conversionName);
    }

    /**
     * @param string $extension
     * @return string
     */
    protected function generateTemporaryFilePath(string $extension)
    {
        $name = Str::uuid()->toString();

        $fileName = $name.'.'.$extension;

        $directory = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR);

        return $directory.'.'.$fileName;
    }

    protected function copySourceFileToLocalDisk(
        FilesystemAdapter $filesystem,
        string $sourceFilePath,
        string $localFilePath
    ) {
        if (! $sourceFileHandle = $filesystem->readStream($sourceFilePath)) {
            throw new Exception('Failed to open file handle.');
        }

        try {
            if (! $localFileHandle = fopen($localFilePath, 'w')) {
                throw new Exception('Failed to open file handle.');
            }

            try {
                stream_copy_to_stream($sourceFileHandle, $localFileHandle);
            } finally {
                fclose($localFileHandle);
            }
        } finally {
            fclose($sourceFileHandle);
        }
    }

    /**
     * @param Media $media
     * @param FilesystemAdapter $filesystem
     * @param Converter[] $converters
     * @param string $inputFilePath
     */
    protected function performConversions(
        Media $media,
        FilesystemAdapter $filesystem,
        array $converters,
        string $inputFilePath
    ) {
        foreach ($converters as $conversionName => $converter) {
            $outputFilePath = $this->generateTemporaryFilePath(
                $converter->getOutputExtension($media) ?: $media->getExtension()
            );

            try {
                $converter->convert($media, $inputFilePath, $outputFilePath);

                if (! is_readable($outputFilePath)) {
                    throw new Exception('Failed to read converted file.');
                }

                $this->copyConvertedFileToMediaDisk(
                    $filesystem, $outputFilePath, $media->getConversionPath($conversionName)
                );
            } finally {
                $this->deleteFileIfExists($outputFilePath);
            }
        }
    }

    /**
     * @param FilesystemAdapter $filesystem
     * @param string $localFilePath
     * @param string $destinationFilePath
     */
    protected function copyConvertedFileToMediaDisk(
        FilesystemAdapter $filesystem,
        string $localFilePath,
        string $destinationFilePath
    ) {
        if (! $localFileHandle = fopen($localFilePath, 'r')) {
            throw new Exception('Failed to open file handle.');
        }

        try {
            $filesystem->putStream($destinationFilePath, $localFileHandle);
        } finally {
            fclose($localFileHandle);
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

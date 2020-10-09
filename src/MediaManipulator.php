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
            $media,
            $filesystem,
            $conversionNames,
            $onlyIfMissing
        );

        if (empty($converters)) {
            return;
        }

        $inputFilePath = $this->generateTemporaryFilePath($media->getExtension());

        $this->copySourceFileToLocalDisk(
            $filesystem,
            $media->getPath(),
            $inputFilePath
        );

        try {
            foreach ($converters as $conversionName => $converter) {
                $this->convertFile(
                    $media,
                    $filesystem,
                    $converter,
                    $conversionName,
                    $inputFilePath
                );
            }
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
     * @return array
     */
    protected function getConverters(
        Media $media,
        FilesystemAdapter $filesystem,
        array $conversionNames,
        bool $onlyIfMissing
    ) {
        $converters = [];

        foreach ($conversionNames as $name) {
            $converter = $this->getConverter($name);

            if (
                ! $converter->canConvertMedia($media)
                || (
                    $onlyIfMissing
                    && $filesystem->exists($media->getConversionPath($name))
                )
            ) {
                continue;
            }

            $converters[$name] = $converter;
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
            throw new Exception('Failed to open handle to the source file.');
        }

        try {
            if (! $localFileHandle = fopen($localFilePath, 'w')) {
                throw new Exception('Failed to open handle to the input file.');
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
     * @param Converter $converter
     * @param string $conversionName
     * @param string $inputFilePath
     */
    protected function convertFile(
        Media $media,
        FilesystemAdapter $filesystem,
        Converter $converter,
        string $conversionName,
        string $inputFilePath
    ) {
        try {
            if (! $extension = $converter->getOutputExtension($media)) {
                $extension = $media->getExtension();
            }

            $outputFilePath = $this->generateTemporaryFilePath($extension);

            $converter->convert($media, $inputFilePath, $outputFilePath);

            if (! file_exists($outputFilePath)) {
                throw new Exception('Failed to read the converted file.');
            }

            $this->copyConvertedFileToMediaDisk(
                $filesystem,
                $outputFilePath,
                $media->getConversionPath($conversionName)
            );
        } finally {
            $this->deleteFileIfExists($outputFilePath);
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
            throw new Exception('Failed to open handle to the output file.');
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

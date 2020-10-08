<?php

namespace Optix\Media;

use Exception;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Str;
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
     * @return void
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
     * @param array $converterNames
     * @param bool $onlyIfMissing
     * @return void
     *
     * @throws Exception
     */
    public function convert(Media $media, array $converterNames, bool $onlyIfMissing = true)
    {
        $disk = $this->filesystemManager->disk($media->getDisk());

        $converters = [];

        foreach ($converterNames as $name) {
            $converter = $this->converterRegistry->get($name);

            if (
                ! $converter->canConvertMedia($media)
                || (
                    $onlyIfMissing
                    && $disk->exists($media->getConversionPath($name))
                )
            ) {
                continue;
            }

            $converters[$name] = $converter;
        }

        if (empty($converters)) {
            return;
        }

        if (! $sourceFileHandle = $disk->readStream($media->getPath())) {
            throw new Exception('Failed to open handle to the source file.');
        }

        $inputFilePath = $this->createTemporaryFilePath($media->getExtension());

        if (! $inputFileHandle = fopen($inputFilePath, 'w')) {
            fclose($sourceFileHandle);

            throw new Exception('Failed to open handle to the input file.');
        }

        // Copy the source file to the input file...
        stream_copy_to_stream($sourceFileHandle, $inputFileHandle);

        fclose($sourceFileHandle);
        fclose($inputFileHandle);

        foreach ($converters as $name => $converter) {
            try {
                if (! $extension = $converter->getOutputExtension($media)) {
                    $extension = $media->getExtension();
                }

                $outputFilePath = $this->createTemporaryFilePath($extension);

                $converter->convert($media, $inputFilePath, $outputFilePath);

                if (! file_exists($outputFilePath)) {
                    throw new Exception('Failed to read the converted file.');
                }

                if (! $outputFileHandle = fopen($outputFilePath, 'r')) {
                    throw new Exception('Failed to open handle to the output file.');
                }

                try {
                    // Persist the converted file...
                    $disk->putStream(
                        $media->getConversionPath($name),
                        $outputFileHandle
                    );
                } finally {
                    fclose($outputFileHandle);
                }
            } catch (Exception $exception) {
                $this->deleteFileIfExists($inputFilePath);

                throw $exception;
            } finally {
                $this->deleteFileIfExists($outputFilePath);
            }
        }

        $this->deleteFileIfExists($inputFilePath);
    }

    /**
     * @param string $extension
     * @return string
     */
    protected function createTemporaryFilePath(string $extension)
    {
        $name = Str::uuid()->toString();

        $fileName = $name.'.'.$extension;

        return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR
            .$fileName;
    }

    /**
     * @param string $filePath
     * @return bool
     */
    protected function deleteFileIfExists(string $filePath)
    {
        if (! file_exists($filePath)) {
            return true;
        }

        return unlink($filePath);
    }
}

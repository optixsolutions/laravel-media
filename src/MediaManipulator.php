<?php

namespace Optix\Media;

use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Str;
use Optix\Media\Concerns\ChangesFileExtension;
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
        $converters = [];

        $disk = $this->filesystemManager->disk($media->getDisk());

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

        // Copy the source file to the input file...
        if (! $inputFileHandle = fopen($inputFilePath, 'w')) {
            fclose($sourceFileHandle);

            throw new Exception('Failed to open handle to the input file.');
        }

        stream_copy_to_stream($sourceFileHandle, $inputFileHandle);

        fclose($sourceFileHandle);
        fclose($inputFileHandle);

        // Perform the conversions...
        foreach ($converters as $name => $converter) {
            $extension = $converter instanceof ChangesFileExtension
                ? $converter->getExtension()
                : $media->getExtension();

            $outputFilePath = $this->createTemporaryFilePath($extension);

            try {
                $converter->convert($inputFilePath, $outputFilePath);

                if (! file_exists($outputFilePath)) {
                    throw new Exception('Failed to read the converted file.');
                }

                if (! $outputFileHandle = fopen($outputFilePath, 'r')) {
                    throw new Exception('Failed to open handle to the output file.');
                }

                try {
                    // Copy the contents of the output file to
                    // the conversion path on the media disk...
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

    protected function createTemporaryFilePath($extension)
    {
        $fileName = Str::uuid()->toString().'.'.$extension;

        return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR
            .$fileName;
    }

    protected function deleteFileIfExists($filePath)
    {
        if (! file_exists($filePath)) {
            return true;
        }

        return unlink($filePath);
    }
}

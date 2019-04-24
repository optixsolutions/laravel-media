<?php

namespace Optix\Media\Tests;

use Mockery;
use Intervention\Image\Image;
use Optix\Media\Models\Media;
use Optix\Media\ImageManipulator;
use Optix\Media\ConversionRegistry;
use Intervention\Image\ImageManager;
use Illuminate\Filesystem\Filesystem;
use Optix\Media\Exceptions\InvalidConversion;

class ImageManipulatorTest extends TestCase
{
    /** @test */
    public function it_will_apply_registered_conversions()
    {
        $conversionRegistry = new ConversionRegistry();

        $conversionRegistry->register('resize', function (Image $image) {
            return $image->resize(64);
        });

        $image = Mockery::mock(Image::class);

        // Assert that the conversion was applied...
        $image->shouldReceive('resize')->with(64)->once()->andReturnSelf();

        // Assert that stream was called...
        $image->shouldReceive('stream')->once()->andReturn('contents');

        $imageManager = Mockery::mock(ImageManager::class);
        $imageManager->shouldReceive('make')->once()->andReturn($image);

        $manipulator = new ImageManipulator($conversionRegistry, $imageManager);

        $media = Mockery::mock(Media::class)->makePartial();
        $media->file_name = 'file-name.png';
        $media->mime_type = 'image/png';

        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('path')->with($media->getPath())->andReturn('full-path');

        // Assert that the converted file is saved...
        $filesystem->shouldReceive('put')->with($media->getPath('resize'), 'contents')->once()->andReturn(true);

        $media->shouldReceive('filesystem')->andReturn($filesystem);

        $manipulator->manipulate($media, ['resize'], $onlyIfMissing = false);
    }

    /** @test */
    public function it_will_only_apply_conversions_to_an_image()
    {
        $conversionRegistry = new ConversionRegistry();

        $conversionRegistry->register('resize', function ($image) {
            return $image->resize(64);
        });

        $imageManager = Mockery::mock(ImageManager::class);

        // Assert that the conversion was not applied...
        $imageManager->shouldNotReceive('make');

        $manipulator = new ImageManipulator($conversionRegistry, $imageManager);

        $media = new Media(['mime_type' => 'text/html']);

        $manipulator->manipulate($media, ['resize'], $onlyIfMissing = false);
    }

    /** @test */
    public function it_will_ignore_unregistered_conversions()
    {
        $this->expectException(InvalidConversion::class);

        $conversionRegistry = new ConversionRegistry();

        $imageManager = Mockery::mock(ImageManager::class);

        // Assert that the conversion was not applied...
        $imageManager->shouldNotReceive('make');

        $manipulator = new ImageManipulator($conversionRegistry, $imageManager);

        $media = new Media(['mime_type' => 'image/png']);

        $manipulator->manipulate($media, ['unknown'], $onlyIfMissing = false);
    }

    /** @test */
    public function it_will_skip_conversions_if_the_converted_image_already_exists()
    {
        $conversionRegistry = new ConversionRegistry();

        $conversionRegistry->register('resize', function (Image $image) use (&$conversionApplied) {
            return $image;
        });

        $imageManager = Mockery::mock(ImageManager::class);

        // Assert that the conversion was not applied...
        $imageManager->shouldNotReceive('make');

        $manipulator = new ImageManipulator($conversionRegistry, $imageManager);

        $media = Mockery::mock(Media::class)->makePartial();
        $media->file_name = 'file-name.png';
        $media->mime_type = 'image/png';

        $filesystem = Mockery::mock(Filesystem::class);

        // Mock that the file already exists...
        $filesystem->shouldReceive('exists')->with($media->getPath('resize'))->once()->andReturn(true);

        $media->shouldReceive('filesystem')->once()->andReturn($filesystem);

        $manipulator->manipulate($media, ['resize']);
    }
}

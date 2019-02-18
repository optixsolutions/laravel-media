<?php

namespace Optix\Media\Tests;

use Mockery;
use Intervention\Image\Image;
use Optix\Media\Models\Media;
use Optix\Media\ImageManipulator;
use Optix\Media\ConversionManager;
use Intervention\Image\ImageManager;
use Optix\Media\Exceptions\InvalidConversion;
use Illuminate\Contracts\Filesystem\Filesystem;

class ImageManipulatorTest extends TestCase
{
    /** @test */
    public function it_will_ignore_unknown_conversions()
    {
        $this->expectException(InvalidConversion::class);

        $conversionManager = new ConversionManager();
        $imageManager = Mockery::mock(ImageManager::class);

        $manipulator = new ImageManipulator($conversionManager, $imageManager);

        $manipulator->manipulate(new Media(), ['unknown'], false);
    }

    /** @test */
    public function it_will_apply_known_conversions()
    {
        $conversionManager = new ConversionManager();

        $conversionManager->register('resize', function (Image $image) {
            return $image->resize(64);
        });

        // Mock that the conversion was applied...
        $image = Mockery::mock(Image::class);
        $image->shouldReceive('resize')->withArgs([64])->once()->andReturnSelf();
        $image->shouldReceive('stream')->once();

        $imageManager = Mockery::mock(ImageManager::class);
        $imageManager->shouldReceive('make')->once()->andReturn($image);

        $manipulator = new ImageManipulator($conversionManager, $imageManager);
        $manipulator->manipulate(new Media(), ['resize'], false);
    }

    /** @test */
    public function conversions_can_be_skipped_if_the_converted_file_already_exists()
    {
        $conversionManager = new ConversionManager();

        $conversionApplied = false;

        $conversionManager->register('resize', function (Image $image) use (&$conversionApplied) {
            $conversionApplied = true;
            return $image;
        });

        $imageManager = Mockery::mock(ImageManager::class);

        // Mock that the converted file already exists...
        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('exists')->andReturn(true);

        $media = Mockery::mock(Media::class)->makePartial();

        $manipulator = new ImageManipulator($conversionManager, $imageManager);
        $manipulator->manipulate($media, ['resize'], true);

        $this->assertFalse($conversionApplied);
    }
}

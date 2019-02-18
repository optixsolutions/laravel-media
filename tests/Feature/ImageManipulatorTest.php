<?php

namespace Optix\Media\Tests;

use Illuminate\Contracts\Filesystem\Filesystem;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Optix\Media\ConversionManager;
use Optix\Media\ImageManipulator;
use Optix\Media\Models\Media;

class ImageManipulatorTest extends TestCase
{
    /**
     * @test
     * @expectedException \Optix\Media\Exceptions\InvalidConversion
     */
    public function test_that_unknown_conversions_are_ignored()
    {
        $imageManager = \Mockery::mock(ImageManager::class);
        $conversionManager = new ConversionManager();
        $manipulator = new ImageManipulator($conversionManager, $imageManager);
        $media = new Media();
        $manipulator->manipulate($media, ['random_conversion'], false);
    }

    /** @test */
    public function test_that_known_conversions_are_applied()
    {
        $image = \Mockery::mock(Image::class);
        $image->shouldReceive('stream')->once();

        // Create an object that our converter can modify
        $conversionTracker = new \stdClass();
        $conversionTracker->conversionsPerformed = 0;

        // Register a converter
        $resize = function (Image $image) use ($conversionTracker) {
            $conversionTracker->conversionsPerformed++;

            return $image;
        };
        $conversionManager = new ConversionManager();
        $conversionManager->register('resize', $resize);

        $imageManager = \Mockery::mock(ImageManager::class);
        $imageManager->shouldReceive('make')->once()->andReturn($image);

        $media = new Media();
        $manipulator = new ImageManipulator($conversionManager, $imageManager);
        $manipulator->manipulate($media, ['resize'], false);

        // Test that our converter was called, by checking if dummy object has been modified
        $this->assertEquals(1, $conversionTracker->conversionsPerformed);
    }

    /** @test */
    public function test_that_existing_conversions_are_skipped()
    {
        // Create an object that our converter can modify
        $conversionTracker = new \stdClass();
        $conversionTracker->conversionsPerformed = 0;

        // Register a converter
        $resize = function (Image $image) use ($conversionTracker) {
            $conversionTracker->conversionsPerformed++;

            return $image;
        };
        $conversionManager = new ConversionManager();
        $conversionManager->register('resize', $resize);

        // Mock that our converted image already exists on the filesystem
        $filesystem = \Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('exists')->andReturn(true);

        /** @var Media $media */
        $media = \Mockery::mock(Media::class)->makePartial();
        $imageManager = \Mockery::mock(ImageManager::class);
        $manipulator = new ImageManipulator($conversionManager, $imageManager);
        $manipulator->manipulate($media, ['resize'], true);

        // Test that our converter was *not* called
        $this->assertEquals(0, $conversionTracker->conversionsPerformed);
    }
}

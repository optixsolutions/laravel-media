<?php

namespace Optix\Media\Tests;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Optix\Media\ConversionManager;
use Optix\Media\ImageManipulator;
use Optix\Media\Models\Media;

class ImageManipulatorTest extends TestCase
{
    /**
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
}

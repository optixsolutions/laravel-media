<?php

namespace Optix\Media\Tests;

use Optix\Media\Models\Media;

class MediaTest extends TestCase
{
    /** @test */
    public function it_has_a_type()
    {
        $media = new Media(['mime_type' => 'image/png']);

        $this->assertEquals('image', $media->type);
    }

    /** @test */
    public function it_can_determine_its_type()
    {
        $media = new Media(['mime_type' => 'image/png']);

        $this->assertTrue($media->isOfType('image'));
        $this->assertFalse($media->isOfType('video'));
    }
}

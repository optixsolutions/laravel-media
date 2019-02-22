<?php

namespace Optix\Media\Tests;

use Optix\Media\MediaType;
use Optix\Media\Models\Media;

class MediaTypeTest extends TestCase
{
    /** @test */
    public function it_will_extract_the_type()
    {
        $media = new Media(['mime_type' => 'image/png']);

        $this->assertSame('image', MediaType::getType($media));
    }

    /** @test */
    public function it_will_compare_types()
    {
        $media = new Media(['mime_type' => 'image/png']);

        $this->assertSame(true, MediaType::isOfType($media, 'image'));
        $this->assertSame(false, MediaType::isOfType($media, 'text'));
    }

    /** test */
    public function it_will_compare_subtypes()
    {
        $media = new Media(['mime_type' => 'audio/ogg']);

        $this->assertSame(true, MediaType::isOfSubType($media, ['text/html', 'audio/ogg', 'image/gif']));
        $this->assertSame(false, MediaType::isOfSubType($media, ['text/html', 'image/gif']));
    }
}

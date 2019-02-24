<?php

namespace Optix\Media\Tests;

use Optix\Media\MediaGroup;

class MediaGroupTest extends TestCase
{
    /** @test */
    public function it_can_register_and_retrieve_conversions()
    {
        $mediaGroup = new MediaGroup();

        $mediaGroup->performConversions('one', 'two');

        $registeredConversions = $mediaGroup->getConversions();

        $this->assertCount(2, $registeredConversions);
        $this->assertEquals(['one', 'two'], $registeredConversions);
    }

    /** @test */
    public function it_can_determine_if_any_conversions_have_been_registered()
    {
        $mediaGroup = new MediaGroup();

        $this->assertFalse($mediaGroup->hasConversions());

        $mediaGroup->performConversions('conversion');

        $this->assertTrue($mediaGroup->hasConversions());
    }
}

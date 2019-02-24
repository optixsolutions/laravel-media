<?php

namespace Optix\Media\Tests;

use Optix\Media\MediaGroup;

class MediaGroupTest extends TestCase
{
    const KNOWN_VALUE = 'KNOWN_VALUE';

    /** @test */
    public function it_will_register_conversions_correctly()
    {
        $mediaGroup = new MediaGroup('group1');

        $conversion1 = function () {
            return self::KNOWN_VALUE;
        };
        $conversion2 = function () {
            return self::KNOWN_VALUE;
        };

        $mediaGroup->performConversions($conversion1, $conversion2);

        $registeredConversions = $mediaGroup->getConversions();

        $this->assertInternalType('array', $registeredConversions);
        $this->assertEquals(2, count($registeredConversions));

        $registeredConversion1 = array_shift($registeredConversions);
        $registeredConversion2 = array_shift($registeredConversions);

        $this->assertInternalType('callable', $registeredConversion1);
        $this->assertInternalType('callable', $registeredConversion2);

        $this->assertSame(self::KNOWN_VALUE, $registeredConversion1());
        $this->assertSame(self::KNOWN_VALUE, $registeredConversion2());
    }
}

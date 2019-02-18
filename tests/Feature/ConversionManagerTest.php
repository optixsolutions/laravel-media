<?php

namespace Optix\Media\Tests;

use Optix\Media\ConversionManager;

class ConversionManagerTest extends TestCase
{
    public function test_that_converters_are_registered()
    {
        $converter1 = function () {
            return 'ONE';
        };
        $converter2 = function () {
            return 'TWO';
        };
        $conversionManager = new ConversionManager();
        $conversionManager->register('one', $converter1);
        $conversionManager->register('two', $converter2);

        $this->assertEquals($converter1(), $conversionManager->get('one')());
        $this->assertEquals($converter2(), $conversionManager->get('two')());
    }

    public function test_that_converters_cant_be_registered_more_than_once()
    {
        $converter = function () {
        };
        $conversionManager = new ConversionManager();
        $conversionManager->register('first', $converter);
        $conversionManager->register('first', $converter);
        $converters = $conversionManager->all();

        $this->assertEquals(1, count($converters));
    }
}

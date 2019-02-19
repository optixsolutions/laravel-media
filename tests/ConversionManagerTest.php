<?php

namespace Optix\Media\Tests;

use Optix\Media\ConversionManager;
use Optix\Media\Exceptions\InvalidConversion;

class ConversionManagerTest extends TestCase
{
    /** @test */
    public function it_can_register_and_retrieve_specific_conversions()
    {
        $conversionManager = new ConversionManager();

        $conversionManager->register('conversion', function () {
            return true;
        });

        $conversion = $conversionManager->get('conversion');

        $this->assertTrue($conversion());
    }

    /** @test */
    public function it_can_retrieve_all_the_registered_conversions()
    {
        $conversionManager = new ConversionManager();

        $conversionManager->register('one', function () {
            return 'one';
        });

        $conversionManager->register('two', function () {
            return 'two';
        });

        $conversions = $conversionManager->all();

        $this->assertCount(2, $conversions);
        $this->assertEquals('one', $conversions['one']());
        $this->assertEquals('two', $conversions['two']());
    }

    /** @test */
    public function there_can_only_be_one_conversion_registered_with_the_same_name()
    {
        $conversionManager = new ConversionManager();

        $conversionManager->register('conversion', function () {
            return 'one';
        });

        $conversionManager->register('conversion', function () {
            return 'two';
        });

        $this->assertCount(1, $conversionManager->all());
        $this->assertEquals('two', $conversionManager->get('conversion')());
    }
    
    /** @test */
    public function it_can_determine_if_a_conversion_has_been_registered()
    {
        $conversionManager = new ConversionManager();

        $conversionManager->register('registered', function () {});

        $this->assertTrue($conversionManager->exists('registered'));
        $this->assertFalse($conversionManager->exists('unregistered'));
    }

    /** @test */
    public function it_will_error_when_attempting_to_retrieve_an_unregistered_conversion()
    {
        $this->expectException(InvalidConversion::class);

        $conversionManager = new ConversionManager();

        $conversionManager->get('unregistered');
    }
}

<?php

namespace Optix\Media\Tests;

use Optix\Media\ConversionRegistry;
use Optix\Media\Exceptions\InvalidConversion;

class ConversionRegistryTest extends TestCase
{
    /** @test */
    public function it_can_register_and_retrieve_specific_conversions()
    {
        $conversionRegistry = new ConversionRegistry();

        $conversionRegistry->register('conversion', function () {
            return true;
        });

        $conversion = $conversionRegistry->get('conversion');

        $this->assertTrue($conversion());
    }

    /** @test */
    public function it_can_retrieve_all_the_registered_conversions()
    {
        $conversionRegistry = new ConversionRegistry();

        $conversionRegistry->register('one', function () {
            return 'one';
        });

        $conversionRegistry->register('two', function () {
            return 'two';
        });

        $conversions = $conversionRegistry->all();

        $this->assertCount(2, $conversions);
        $this->assertEquals('one', $conversions['one']());
        $this->assertEquals('two', $conversions['two']());
    }

    /** @test */
    public function there_can_only_be_one_conversion_registered_with_the_same_name()
    {
        $conversionRegistry = new ConversionRegistry();

        $conversionRegistry->register('conversion', function () {
            return 'one';
        });

        $conversionRegistry->register('conversion', function () {
            return 'two';
        });

        $this->assertCount(1, $conversionRegistry->all());
        $this->assertEquals('two', $conversionRegistry->get('conversion')());
    }
    
    /** @test */
    public function it_can_determine_if_a_conversion_has_been_registered()
    {
        $conversionRegistry = new ConversionRegistry();

        $conversionRegistry->register('registered', function () {});

        $this->assertTrue($conversionRegistry->exists('registered'));
        $this->assertFalse($conversionRegistry->exists('unregistered'));
    }

    /** @test */
    public function it_will_error_when_attempting_to_retrieve_an_unregistered_conversion()
    {
        $this->expectException(InvalidConversion::class);

        $conversionRegistry = new ConversionRegistry();

        $conversionRegistry->get('unregistered');
    }
}

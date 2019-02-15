<?php

namespace Optix\Media\Tests;

class HasMediaTest extends TestCase
{
    protected $testModel;

    protected function setUp()
    {
        parent::setUp();

        // $this->testModel = TestModel::create();
    }

    /** @test */
    public function it_can_determine_if_a_model_has_media_in_a_specified_group()
    {
        $this->assertTrue($this->testModel->hasMedia()); // default
        $this->assertFalse($this->testModel->hasMedia('an-empty-group'));
    }
}

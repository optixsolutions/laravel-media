<?php

namespace Optix\Media\Tests\Feature;

use Optix\Media\Tests\TestCase;
use Optix\Media\Tests\TestModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HasMediaTest extends TestCase
{
    use RefreshDatabase;

    protected $testModel;

    protected function setUp()
    {
        parent::setUp();

        $this->testModel = TestModel::create();
    }

    /** @test */
    public function it_can_determine_if_a_model_has_media_in_a_specified_group()
    {
        $this->assertTrue($this->testModel->hasMedia()); // default
        $this->assertTrue($this->testModel->HasMedia(''));
        $this->assertFalse($this->testModel->hasMedia('an-empty-group'));
    }
}

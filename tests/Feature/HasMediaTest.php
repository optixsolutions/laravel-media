<?php

namespace Optix\Media\Tests\Feature;

use Optix\Media\Models\Media;
use Optix\Media\Tests\TestCase;
use Optix\Media\Tests\TestModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

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
    public function it_can_handle_retrieving_media_from_an_empty_media_group()
    {
        $media = $this->testModel->getMedia();

        $this->assertInstanceOf(EloquentCollection::class, $media);
        $this->assertTrue($media->isEmpty());
    }

    /** @test */
    public function it_can_retrieve_media_from_a_specified_group()
    {
        $mediaOne = factory(Media::class)->create();
        $mediaTwo = factory(Media::class)->create();

        $this->testModel->attachMedia($mediaOne, 'group-one');
        $this->testModel->attachMedia($mediaTwo, 'group-two');
        
        $groupOneMedia = $this->testModel->getMedia('group-one');
        $groupTwoMedia = $this->testModel->getMedia('group-two');

        $this->assertEquals(1, $groupOneMedia->count());
        $this->assertEquals(1, $groupTwoMedia->count());
        $this->assertEquals($mediaOne->id, $groupOneMedia->first()->id);
        $this->assertEquals($mediaTwo->id, $groupTwoMedia->first()->id);
    }

    /** @test */
    public function it_can_determine_if_a_model_has_media_in_a_specified_group()
    {
        $media = factory(Media::class)->create();

        $this->testModel->attachMedia($media);
        $this->testModel->attachMedia($media, 'custom-group');

        $this->assertTrue($this->testModel->hasMedia()); // default
        $this->assertTrue($this->testModel->HasMedia('custom-group'));
        $this->assertFalse($this->testModel->hasMedia('empty-group'));
    }
}

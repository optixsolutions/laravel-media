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
    public function it_can_attach_media_to_a_model()
    {
        $media = factory(Media::class)->create();

        $this->testModel->attachMedia($media);

        $attachedMedia = $this->testModel->media()->first();

        $this->assertEquals($media->id, $attachedMedia->id);
    }
    
    /** @test */
    public function it_can_handle_retrieving_media_from_an_empty_group()
    {
        $media = $this->testModel->getMedia();

        $this->assertInstanceOf(EloquentCollection::class, $media);
        $this->assertTrue($media->isEmpty());
    }

    /** @test */
    public function it_can_get_all_of_the_media_in_a_group()
    {
        $media = factory(Media::class, 2)->create();

        $this->testModel->attachMedia($media, 'group');

        $allMedia = $this->testModel->getMedia('group');

        $this->assertEquals(2, $allMedia->count());
        $this->assertEmpty($media->diff($allMedia));
    }
    
    /** @test */
    public function it_can_get_the_first_media_item_in_a_group()
    {
        $media = factory(Media::class)->create();

        $this->testModel->attachMedia($media, 'group');

        $firstMedia = $this->testModel->getFirstMedia('group');

        $this->assertInstanceOf(Media::class, $firstMedia);
        $this->assertEquals($media->id, $firstMedia->id);
    }

    /** @test */
    public function it_will_only_get_media_in_the_specified_group()
    {
        $mediaOne = factory(Media::class)->create();
        $mediaTwo = factory(Media::class)->create();

        $this->testModel->attachMedia($mediaOne); // default
        $this->testModel->attachMedia($mediaTwo, 'custom');

        $defaultGroupMedia = $this->testModel->getMedia();

        $customGroupMedia = $this->testModel->getMedia('custom');
        $firstCustomGroupMedia = $this->testModel->getFirstMedia('custom');

        $this->assertCount(1, $defaultGroupMedia);
        $this->assertEquals($mediaOne->id, $defaultGroupMedia->first()->id);

        $this->assertCount(1, $customGroupMedia);
        $this->assertEquals($mediaTwo->id, $customGroupMedia->first()->id);
        $this->assertEquals($mediaTwo->id, $firstCustomGroupMedia->id);
    }

    /** @test */
    public function it_can_determine_if_a_model_has_media_in_a_group()
    {
        $media = factory(Media::class)->create();

        $this->testModel->attachMedia($media);
        $this->testModel->attachMedia($media, 'custom');

        $this->assertTrue($this->testModel->hasMedia()); // default
        $this->assertTrue($this->testModel->HasMedia('custom'));
        $this->assertFalse($this->testModel->hasMedia('empty'));
    }

    /** @test */
    public function it_can_detach_all_media_from_the_model()
    {
        $media = factory(Media::class)->create();

        $this->testModel->attachMedia($media);
        $this->testModel->attachMedia($media, 'group');

        $this->testModel->detachMedia();

        $this->assertEmpty($this->testModel->getMedia());
        $this->assertEmpty($this->testModel->getMedia('group'));
    }
    
    /** @test */
    public function it_can_detach_specific_media_items_from_the_model()
    {
        $mediaOne = factory(Media::class)->create();
        $mediaTwo = factory(Media::class)->create();

        $this->testModel->attachMedia([
            $mediaOne->id, $mediaTwo->id
        ]);

        $this->testModel->detachMedia($mediaOne->id);

        $this->assertCount(1, $this->testModel->getMedia());
        $this->assertEquals($mediaTwo->id, $this->testModel->getFirstMedia()->id);
    }
    
    /** @test */
    public function it_can_detach_all_media_in_a_group_from_the_model()
    {
        $mediaOne = factory(Media::class)->create();
        $mediaTwo = factory(Media::class)->create();

        $this->testModel->attachMedia($mediaOne->id, 'one');
        $this->testModel->attachMedia($mediaTwo->id, 'two');

        $this->testModel->clearMediaGroup('one');

        $this->assertEmpty($this->testModel->getMedia('one'));
        $this->assertCount(1, $this->testModel->getMedia('two'));
        $this->assertEquals($mediaTwo->id, $this->testModel->getFirstMedia('two')->id);
    }
}

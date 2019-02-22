<?php

namespace Optix\Media\Tests;

use Optix\Media\Models\Media;
use Illuminate\Support\Facades\Queue;
use Optix\Media\Tests\Models\TestModel;
use Optix\Media\Jobs\PerformConversions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class HasMediaTest extends TestCase
{
    use RefreshDatabase;

    /** @var TestModel */
    protected $testModel;

    protected function setUp()
    {
        parent::setUp();

        $this->testModel = TestModel::create();
    }

    /** @test */
    public function it_registers_the_media_relationship()
    {
        $this->assertInstanceOf(MorphToMany::class, $this->testModel->media());
    }

    /** @test */
    public function it_can_attach_media_to_the_default_group()
    {
        $media = factory(Media::class)->create();

        $this->testModel->attachMedia($media);

        $attachedMedia = $this->testModel->media()->first();

        $this->assertEquals($attachedMedia->id, $media->id);
        $this->assertEquals('default', $attachedMedia->pivot->group);
    }

    /** @test */
    public function it_can_attach_media_to_a_named_group()
    {
        $media = factory(Media::class)->create();

        $this->testModel->attachMedia($media, $group = 'custom');

        $attachedMedia = $this->testModel->media()->first();

        $this->assertEquals($media->id, $attachedMedia->id);
        $this->assertEquals($group, $attachedMedia->pivot->group);
    }

    /** @test */
    public function it_can_attach_a_collection_of_media()
    {
        $media = factory(Media::class, 2)->create();

        $this->testModel->attachMedia($media);

        $attachedMedia = $this->testModel->media()->get();

        $this->assertCount(2, $attachedMedia);
        $this->assertEmpty($media->diff($attachedMedia));

        $attachedMedia->each(
            function ($media) {
                $this->assertEquals('default', $media->pivot->group);
            }
        );
    }

    /** @test */
    public function it_can_get_all_the_media_in_the_default_group()
    {
        $media = factory(Media::class, 2)->create();

        $this->testModel->attachMedia($media);

        $defaultMedia = $this->testModel->getMedia();

        $this->assertEquals(2, $defaultMedia->count());
        $this->assertEmpty($media->diff($defaultMedia));
    }

    /** @test */
    public function it_can_get_all_the_media_in_a_specified_group()
    {
        $media = factory(Media::class, 2)->create();

        $this->testModel->attachMedia($media, 'gallery');

        $galleryMedia = $this->testModel->getMedia('gallery');

        $this->assertEquals(2, $galleryMedia->count());
        $this->assertEmpty($media->diff($galleryMedia));
    }

    /** @test */
    public function it_can_handle_attempts_to_get_media_from_an_empty_group()
    {
        $media = $this->testModel->getMedia();

        $this->assertInstanceOf(EloquentCollection::class, $media);
        $this->assertTrue($media->isEmpty());
    }

    /** @test */
    public function it_can_get_the_first_media_item_in_the_default_group()
    {
        $media = factory(Media::class)->create();

        $this->testModel->attachMedia($media);

        $firstMedia = $this->testModel->getFirstMedia();

        $this->assertInstanceOf(Media::class, $firstMedia);
        $this->assertEquals($media->id, $firstMedia->id);
    }

    /** @test */
    public function it_can_get_the_first_media_item_in_a_specified_group()
    {
        $media = factory(Media::class)->create();

        $this->testModel->attachMedia($media, 'gallery');

        $firstMedia = $this->testModel->getFirstMedia('gallery');

        $this->assertInstanceOf(Media::class, $firstMedia);
        $this->assertEquals($media->id, $firstMedia->id);
    }

    /** @test */
    public function it_will_only_get_media_in_the_specified_group()
    {
        $media = factory(Media::class, 2)->create();

        // Attach media to the default group...
        $this->testModel->attachMedia($defaultMediaId = $media->first()->id);

        // Attach media to the gallery group...
        $this->testModel->attachMedia($galleryMediaId = $media->last()->id, 'gallery');

        $defaultMedia = $this->testModel->getMedia();
        $galleryMedia = $this->testModel->getMedia('gallery');
        $firstGalleryMedia = $this->testModel->getFirstMedia('gallery');

        $this->assertCount(1, $defaultMedia);
        $this->assertEquals($defaultMediaId, $defaultMedia->first()->id);

        $this->assertCount(1, $galleryMedia);
        $this->assertEquals($galleryMediaId, $galleryMedia->first()->id);
        $this->assertEquals($galleryMediaId, $firstGalleryMedia->id);

    }

    /** @test */
    public function it_can_get_the_url_of_the_first_media_item_in_the_default_group()
    {
        $media = factory(Media::class)->create();

        $this->testModel->attachMedia($media);

        $url = $this->testModel->getFirstMediaUrl();

        $this->assertEquals($media->getUrl(), $url);
    }

    /** @test */
    public function it_can_get_the_url_of_the_first_media_item_in_a_specified_group()
    {
        $media = factory(Media::class)->create();

        $this->testModel->attachMedia($media, 'gallery');

        $url = $this->testModel->getFirstMediaUrl('gallery');

        $this->assertEquals($media->getUrl(), $url);
    }

    /** @test */
    public function it_can_get_the_converted_image_url_of_the_first_media_item_in_a_specified_group()
    {
        $media = factory(Media::class)->create();

        $this->testModel->attachMedia($media, 'gallery');

        $url = $this->testModel->getFirstMediaUrl('gallery', 'conversion-name');

        $this->assertEquals($media->getUrl('conversion-name'), $url);
    }

    /** @test */
    public function it_can_determine_if_there_is_media_in_the_default_group()
    {
        $media = factory(Media::class)->create();

        $this->testModel->attachMedia($media);

        $this->assertTrue($this->testModel->hasMedia());
        $this->assertFalse($this->testModel->hasMedia('empty'));
    }

    /** @test */
    public function it_can_determine_if_there_is_media_in_a_specified_group()
    {
        $media = factory(Media::class)->create();

        $this->testModel->attachMedia($media, 'gallery');

        $this->assertTrue($this->testModel->HasMedia('gallery'));
        $this->assertFalse($this->testModel->hasMedia());
    }

    /** @test */
    public function it_can_detach_all_the_media()
    {
        $media = factory(Media::class, 2)->create();

        $this->testModel->attachMedia($media->first());
        $this->testModel->attachMedia($media->last(), 'gallery');

        $this->testModel->detachMedia();

        $this->assertEmpty($this->testModel->media()->get());
    }

    /** @test */
    public function it_can_detach_specific_media_items()
    {
        $media = factory(Media::class, 2)->create();

        $this->testModel->attachMedia($media);

        $this->testModel->detachMedia($media->first());

        $this->assertCount(1, $this->testModel->getMedia());
        $this->assertEquals($media->last()->id, $this->testModel->getFirstMedia()->id);
    }

    /** @test */
    public function it_can_detach_all_the_media_in_a_specified_group()
    {
        $media = factory(Media::class, 2)->create();

        $this->testModel->attachMedia($media->first(), 'one');
        $this->testModel->attachMedia($media->last(), 'two');

        $this->testModel->clearMediaGroup('one');

        $this->assertFalse($this->testModel->hasMedia('one'));
        $this->assertCount(1, $this->testModel->getMedia('two'));
        $this->assertEquals($media->last()->id, $this->testModel->getFirstMedia('two')->id);
    }

    /** @test */
    public function it_will_dispatch_conversions_when_media_attached()
    {
        Queue::fake();
        /** @var EloquentCollection<Media> $mediaCollection */
        $mediaCollection = factory(Media::class, 2)->create();

        $mediaGroup = $this->testModel->addMediaGroup('group1');
        $mediaGroup->registerConversions([ function () {} ]);

        $this->testModel->attachMedia($mediaCollection, 'group1');

        Queue::assertPushed(PerformConversions::class, 2);
    }
}

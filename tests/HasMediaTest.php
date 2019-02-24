<?php

namespace Optix\Media\Tests;

use Optix\Media\Models\Media;
use Illuminate\Support\Facades\Queue;
use Optix\Media\Tests\Models\Subject;
use Optix\Media\Jobs\PerformConversions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class HasMediaTest extends TestCase
{
    use RefreshDatabase;

    protected $subject;

    protected function setUp()
    {
        parent::setUp();

        $this->subject = Subject::create();
    }

    /** @test */
    public function it_registers_the_media_relationship()
    {
        $this->assertInstanceOf(MorphToMany::class, $this->subject->media());
    }

    /** @test */
    public function it_can_attach_media_to_the_default_group()
    {
        $media = factory(Media::class)->create();

        $this->subject->attachMedia($media);

        $attachedMedia = $this->subject->media()->first();

        $this->assertEquals($attachedMedia->id, $media->id);
        $this->assertEquals('default', $attachedMedia->pivot->group);
    }

    /** @test */
    public function it_can_attach_media_to_a_named_group()
    {
        $media = factory(Media::class)->create();

        $this->subject->attachMedia($media, $group = 'custom');

        $attachedMedia = $this->subject->media()->first();

        $this->assertEquals($media->id, $attachedMedia->id);
        $this->assertEquals($group, $attachedMedia->pivot->group);
    }

    /** @test */
    public function it_can_attach_a_collection_of_media()
    {
        $media = factory(Media::class, 2)->create();

        $this->subject->attachMedia($media);

        $attachedMedia = $this->subject->media()->get();

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

        $this->subject->attachMedia($media);

        $defaultMedia = $this->subject->getMedia();

        $this->assertEquals(2, $defaultMedia->count());
        $this->assertEmpty($media->diff($defaultMedia));
    }

    /** @test */
    public function it_can_get_all_the_media_in_a_specified_group()
    {
        $media = factory(Media::class, 2)->create();

        $this->subject->attachMedia($media, 'gallery');

        $galleryMedia = $this->subject->getMedia('gallery');

        $this->assertEquals(2, $galleryMedia->count());
        $this->assertEmpty($media->diff($galleryMedia));
    }

    /** @test */
    public function it_can_handle_attempts_to_get_media_from_an_empty_group()
    {
        $media = $this->subject->getMedia();

        $this->assertInstanceOf(EloquentCollection::class, $media);
        $this->assertTrue($media->isEmpty());
    }

    /** @test */
    public function it_can_get_the_first_media_item_in_the_default_group()
    {
        $media = factory(Media::class)->create();

        $this->subject->attachMedia($media);

        $firstMedia = $this->subject->getFirstMedia();

        $this->assertInstanceOf(Media::class, $firstMedia);
        $this->assertEquals($media->id, $firstMedia->id);
    }

    /** @test */
    public function it_can_get_the_first_media_item_in_a_specified_group()
    {
        $media = factory(Media::class)->create();

        $this->subject->attachMedia($media, 'gallery');

        $firstMedia = $this->subject->getFirstMedia('gallery');

        $this->assertInstanceOf(Media::class, $firstMedia);
        $this->assertEquals($media->id, $firstMedia->id);
    }

    /** @test */
    public function it_will_only_get_media_in_the_specified_group()
    {
        $media = factory(Media::class, 2)->create();

        // Attach media to the default group...
        $this->subject->attachMedia($defaultMediaId = $media->first()->id);

        // Attach media to the gallery group...
        $this->subject->attachMedia($galleryMediaId = $media->last()->id, 'gallery');

        $defaultMedia = $this->subject->getMedia();
        $galleryMedia = $this->subject->getMedia('gallery');
        $firstGalleryMedia = $this->subject->getFirstMedia('gallery');

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

        $this->subject->attachMedia($media);

        $url = $this->subject->getFirstMediaUrl();

        $this->assertEquals($media->getUrl(), $url);
    }

    /** @test */
    public function it_can_get_the_url_of_the_first_media_item_in_a_specified_group()
    {
        $media = factory(Media::class)->create();

        $this->subject->attachMedia($media, 'gallery');

        $url = $this->subject->getFirstMediaUrl('gallery');

        $this->assertEquals($media->getUrl(), $url);
    }

    /** @test */
    public function it_can_get_the_converted_image_url_of_the_first_media_item_in_a_specified_group()
    {
        $media = factory(Media::class)->create();

        $this->subject->attachMedia($media, 'gallery');

        $url = $this->subject->getFirstMediaUrl('gallery', 'conversion-name');

        $this->assertEquals($media->getUrl('conversion-name'), $url);
    }

    /** @test */
    public function it_can_determine_if_there_is_media_in_the_default_group()
    {
        $media = factory(Media::class)->create();

        $this->subject->attachMedia($media);

        $this->assertTrue($this->subject->hasMedia());
        $this->assertFalse($this->subject->hasMedia('empty'));
    }

    /** @test */
    public function it_can_determine_if_there_is_media_in_a_specified_group()
    {
        $media = factory(Media::class)->create();

        $this->subject->attachMedia($media, 'gallery');

        $this->assertTrue($this->subject->HasMedia('gallery'));
        $this->assertFalse($this->subject->hasMedia());
    }

    /** @test */
    public function it_can_detach_all_the_media()
    {
        $media = factory(Media::class, 2)->create();

        $this->subject->attachMedia($media->first());
        $this->subject->attachMedia($media->last(), 'gallery');

        $this->subject->detachMedia();

        $this->assertEmpty($this->subject->media()->get());
    }

    /** @test */
    public function it_can_detach_specific_media_items()
    {
        $media = factory(Media::class, 2)->create();

        $this->subject->attachMedia($media);

        $this->subject->detachMedia($media->first());

        $this->assertCount(1, $this->subject->getMedia());
        $this->assertEquals($media->last()->id, $this->subject->getFirstMedia()->id);
    }

    /** @test */
    public function it_can_detach_all_the_media_in_a_specified_group()
    {
        $media = factory(Media::class, 2)->create();

        $this->subject->attachMedia($media->first(), 'one');
        $this->subject->attachMedia($media->last(), 'two');

        $this->subject->clearMediaGroup('one');

        $this->assertFalse($this->subject->hasMedia('one'));
        $this->assertCount(1, $this->subject->getMedia('two'));
        $this->assertEquals($media->last()->id, $this->subject->getFirstMedia('two')->id);
    }

    /** @test */
    public function it_will_perform_conversions_when_media_is_attached()
    {
        Queue::fake();

        $media = factory(Media::class, 2)->create();

        $this->subject->attachMedia($media, 'converted-images');

        Queue::assertPushed(PerformConversions::class, 2);
    }
}

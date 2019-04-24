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
    public function it_will_perform_the_given_conversions_when_media_is_attached()
    {
        Queue::fake();

        $media = factory(Media::class)->create();

        $conversions = ['conversion'];

        $this->subject->attachMedia($media, 'default', $conversions);

        Queue::assertPushed(
            PerformConversions::class, function ($job) use ($media, $conversions) {
                return (
                    $media->is($job->getMedia())
                    && empty(array_diff($conversions, $job->getConversions()))
                );
            }
        );
    }

    /** @test */
    public function it_will_perform_the_conversions_registered_by_the_group_when_media_is_attached()
    {
        Queue::fake();

        $media = factory(Media::class)->create();

        $this->subject->attachMedia($media, $group = 'converted-images');

        Queue::assertPushed(
            PerformConversions::class, function ($job) use ($media, $group) {
                $conversions = $this->subject
                    ->getMediaGroup($group)
                    ->getConversions();

                return (
                    $media->is($job->getMedia())
                    && empty(array_diff($conversions, $job->getConversions()))
                );
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
        $defaultMedia = factory(Media::class)->create();
        $galleryMedia = factory(Media::class)->create();

        // Attach media to the default group...
        $this->subject->attachMedia($defaultMedia->id);

        // Attach media to the gallery group...
        $this->subject->attachMedia($galleryMedia->id, 'gallery');

        $allDefaultMedia = $this->subject->getMedia();
        $allGalleryMedia = $this->subject->getMedia('gallery');
        $firstGalleryMedia = $this->subject->getFirstMedia('gallery');

        $this->assertCount(1, $allDefaultMedia);
        $this->assertEquals($defaultMedia->id, $allDefaultMedia->first()->id);

        $this->assertCount(1, $allGalleryMedia);
        $this->assertEquals($galleryMedia->id, $allGalleryMedia->first()->id);
        $this->assertEquals($galleryMedia->id, $firstGalleryMedia->id);
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

        $this->assertTrue($this->subject->hasMedia('gallery'));
        $this->assertFalse($this->subject->hasMedia());
    }

    /** @test */
    public function it_can_detach_all_the_media()
    {
        $mediaOne = factory(Media::class)->create();
        $mediaTwo = factory(Media::class)->create();

        $this->subject->attachMedia($mediaOne);
        $this->subject->attachMedia($mediaTwo, 'gallery');

        $this->subject->detachMedia();

        $this->assertFalse($this->subject->media()->exists());
    }

    /** @test */
    public function it_can_detach_specific_media_items()
    {
        $mediaOne = factory(Media::class)->create();
        $mediaTwo = factory(Media::class)->create();

        $this->subject->attachMedia([
            $mediaOne->id, $mediaTwo->id
        ]);

        $this->subject->detachMedia($mediaOne);

        $this->assertCount(1, $this->subject->getMedia());
        $this->assertEquals($mediaTwo->id, $this->subject->getFirstMedia()->id);
    }

    /** @test */
    public function it_can_detach_all_the_media_in_a_specified_group()
    {
        $mediaOne = factory(Media::class)->create();
        $mediaTwo = factory(Media::class)->create();

        $this->subject->attachMedia($mediaOne, 'one');
        $this->subject->attachMedia($mediaTwo, 'two');

        $this->subject->clearMediaGroup('one');

        $this->assertFalse($this->subject->hasMedia('one'));
        $this->assertCount(1, $this->subject->getMedia('two'));
        $this->assertEquals($mediaTwo->id, $this->subject->getFirstMedia('two')->id);
    }
}

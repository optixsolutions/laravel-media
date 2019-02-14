<?php

namespace Optix\Media\Tests;

use Optix\Media\Models\Media;
use Optix\Media\MediaUploader;
use Illuminate\Http\UploadedFile;

class MediaUploaderTest extends TestCase
{
    /** @test */
    public function it_can_upload_media()
    {
        $file = UploadedFile::fake()->image('image.jpg');

        $media = MediaUploader::fromFile($file)->upload();

        $this->assertInstanceOf(Media::class, $media);
        $this->assertTrue($media->filesystem()->exists($media->getPath()));
    }

    // it_can_change_the_name_of_the_media_model

    // it_can_rename_the_file_before_it_gets_uploaded

    // it_will_sanitise_the_file_name

    // it_will_use_the_given_file_name_sanitiser

    // it_can_save_custom_attributes_to_the_media_model
}

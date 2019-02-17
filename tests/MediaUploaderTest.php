<?php

namespace Optix\Media\Tests;

use Optix\Media\Models\Media;
use Optix\Media\MediaUploader;
use Illuminate\Http\UploadedFile;
use Optix\Media\Tests\Models\CustomMedia;

class MediaUploaderTest extends TestCase
{
    /** @test */
    public function it_can_upload_media()
    {
        $file = UploadedFile::fake()->image('file-name.jpg');

        $media = MediaUploader::fromFile($file)->upload();

        $this->assertInstanceOf(Media::class, $media);
        $this->assertTrue($media->filesystem()->exists($media->getPath()));
    }

    /** @test */
    public function it_can_change_the_name_of_the_media_model()
    {
        $file = UploadedFile::fake()->image('file-name.jpg');

        $media = MediaUploader::fromFile($file)
            ->useName($newName = 'New name')
            ->upload();

        $this->assertEquals($newName, $media->name);
    }

    /** @test */
    public function it_can_rename_the_file_before_it_gets_uploaded()
    {
        $file = UploadedFile::fake()->image('file-name.jpg');

        $media = MediaUploader::fromFile($file)
            ->useFileName($newFileName = 'new-file-name.jpg')
            ->upload();

        $this->assertEquals($newFileName, $media->file_name);
    }

    /** @test */
    public function it_will_sanitise_the_file_name()
    {
        $file = UploadedFile::fake()->image('bad file name#023.jpg');

        $media = MediaUploader::fromFile($file)->upload();

        $this->assertEquals('bad-file-name-023.jpg', $media->file_name);
    }

    /** @test */
    public function it_can_save_custom_attributes_to_the_media_model()
    {
        config()->set('media.model', CustomMedia::class);

        $file = UploadedFile::fake()->image('image.jpg');

        $media = MediaUploader::fromFile($file)
            ->withAttributes([
                'custom_attribute' => 'Custom attribute'
            ])
            ->upload();

        $this->assertInstanceOf(CustomMedia::class, $media);
        $this->assertEquals('Custom attribute', $media->custom_attribute);
    }
}

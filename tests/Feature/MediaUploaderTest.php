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
}

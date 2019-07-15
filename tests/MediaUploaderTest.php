<?php

namespace Optix\Media\Tests;

use Mockery;
use Optix\Media\Models\Media;
use Optix\Media\MediaUploader;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MediaUploaderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_upload_a_file_and_persist_a_media_item()
    {
        $model = Media::class;
        $disk = 'public';

        $filesystem = Storage::fake($disk);

        $filesystemManager = Mockery::mock(FilesystemManager::class);

        $filesystemManager
             ->shouldReceive('disk')
             ->with($disk)
             ->andReturn($filesystem);

        // Instantiate the media uploader...
        $mediaUploader = new MediaUploader($model, $disk, $filesystemManager);

        $file = UploadedFile::fake()->image('image.jpeg');

        // Upload the file...
        $media = $mediaUploader->fromFile($file)->upload();

        $this->assertInstanceOf($model, $media);

        $this->assertEquals('image', $media->name);
        $this->assertEquals('image.jpeg', $media->file_name);
        $this->assertEquals($disk, $media->disk);

        $this->assertTrue($filesystem->exists($media->getPath()));
    }

    // Todo: 100% Coverage
}

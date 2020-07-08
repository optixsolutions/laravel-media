<?php

namespace Optix\Media\Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Optix\Media\MediaUploader;
use Optix\Media\Models\Media;
use Optix\Media\Options\UploadOptions;
use Optix\Media\Tests\Models\Media as CustomMedia;

class MediaUploaderTest extends TestCase
{
    const DEFAULT_DISK = 'default';

    /** @test */
    public function it_can_upload_a_file_to_the_default_disk()
    {
        $file = UploadedFile::fake()->image('file-name.jpg');

        $mediaUploader = $this->mockMediaUploader();

        $media = $mediaUploader->upload($file);

        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals(self::DEFAULT_DISK, $media->disk);

        $filesystem = Storage::disk(self::DEFAULT_DISK);

        $this->assertTrue(
            $filesystem->exists($media->getPath())
        );
    }

    /** @test */
    public function it_can_upload_a_file_to_a_specific_disk()
    {
        $file = UploadedFile::fake()->image('file-name.jpg');

        $mediaUploader = $this->mockMediaUploader($customDisk = 'custom');

        $options = UploadOptions::create()->setDisk($customDisk);

        $media = $mediaUploader->upload($file, $options);

        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals($customDisk, $media->disk);

        $filesystem = Storage::disk($customDisk);

        $this->assertTrue(
            $filesystem->exists($media->getPath())
        );
    }

    /** @test */
    public function it_can_change_the_name_of_the_media_model()
    {
        $file = UploadedFile::fake()->image('file-name.jpg');

        $mediaUploader = $this->mockMediaUploader();

        $options = UploadOptions::create()->setMediaName($newName = 'New name');

        $media = $mediaUploader->upload($file, $options);

        $this->assertEquals($newName, $media->name);
    }

    /** @test */
    public function it_can_rename_the_file_before_it_gets_uploaded()
    {
        $file = UploadedFile::fake()->image('file-name.jpg');

        $mediaUploader = $this->mockMediaUploader();

        $options = UploadOptions::create()
            ->setFileName($newFileName = 'new-file-name.jpg');

        $media = $mediaUploader->upload($file, $options);

        $this->assertEquals($newFileName, $media->file_name);
    }

    /** @test */
    public function it_will_sanitise_the_file_name()
    {
        $file = UploadedFile::fake()->image('bad file name#023.jpg');

        $mediaUploader = $this->mockMediaUploader();

        $options = UploadOptions::create()->setDisk(self::DEFAULT_DISK);

        $media = $mediaUploader->upload($file, $options);

        $this->assertEquals('bad-file-name-023.jpg', $media->file_name);
    }

    /** @test */
    public function it_can_save_custom_attributes_to_the_media_model()
    {
        $mediaUploader = $this->mockMediaUploader(
            self::DEFAULT_DISK, CustomMedia::class
        );

        $file = UploadedFile::fake()->image('image.jpg');

        $options = UploadOptions::create()
            ->setCustomAttributes([
                'custom_attribute' => 'Custom attribute',
            ]);

        $media = $mediaUploader->upload($file, $options);

        $this->assertInstanceOf(CustomMedia::class, $media);
        $this->assertEquals('Custom attribute', $media->custom_attribute);
    }

    private function mockMediaUploader(
        $disk = self::DEFAULT_DISK,
        $model = Media::class
    ) {
        $filesystemManager = app(FilesystemManager::class);

        $filesystem = $this->mockFilesystem($disk, $filesystemManager);

        $filesystemManager->set($disk, $filesystem);

        $config = [
            'model' => $model,
            'disk' => $disk,
        ];

        return new MediaUploader($filesystemManager, $config);
    }

    private function mockFilesystem(
        string $disk,
        FilesystemManager $filesystemManager
    ) {
        (new Filesystem)->cleanDirectory(
            $root = storage_path('framework/testing/disks/'.$disk)
        );

        return $filesystemManager->createLocalDriver([
            'root' => $root,
        ]);
    }
}

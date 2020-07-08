<?php

namespace Optix\Media\Tests;

use ErrorException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Optix\Media\MediaUploader;
use Optix\Media\Models\Media;
use Optix\Media\Options\UploadOptions;
use Optix\Media\Tests\Models\Media as CustomMedia;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

class MediaUploaderTest extends TestCase
{
    const DEFAULT_DISK = 'default';
    const RESOURCE_DIRECTORY = __DIR__.'/resources';

    public function setUp(): void
    {
        parent::setUp();

        // Copy test resources to temporary directory...
        File::copyDirectory(
            self::RESOURCE_DIRECTORY, self::RESOURCE_DIRECTORY.'_live'
        );
    }

    public function tearDown(): void
    {
        parent::tearDown();

        // Delete temporary test resources...
        File::deleteDirectory(self::RESOURCE_DIRECTORY.'_live');
    }

    /** @test */
    public function it_can_upload_a_file_to_the_default_disk()
    {
        $file = UploadedFile::fake()->image('file-name.jpg');

        $filesystemManager = $this->mockFilesystemManager();

        $mediaUploader = $this->mockMediaUploader($filesystemManager);

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

        $customDisk = 'custom';

        $filesystemManager = $this->mockFilesystemManager($customDisk);

        $mediaUploader = $this->mockMediaUploader(
            $filesystemManager, $customDisk
        );

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
    public function it_can_upload_a_file_from_a_path()
    {
        $filesystemManager = $this->mockFilesystemManager();

        $mediaUploader = $this->mockMediaUploader($filesystemManager);

        $media = $mediaUploader->upload(
            $file = $this->getResourcePath('test_image.png')
        );

        $filesystem = Storage::disk(self::DEFAULT_DISK);

        // The uploaded file should exist...
        $this->assertTrue(
            $filesystem->exists($media->getPath())
        );

        // The original file should not exist...
        $this->assertFalse(
            $filesystem->exists($file)
        );
    }

    /** @test */
    public function it_can_upload_a_symfony_file_object()
    {
        $filesystemManager = $this->mockFilesystemManager();

        $mediaUploader = $this->mockMediaUploader($filesystemManager);

        $file = new SymfonyFile(
            $this->getResourcePath('test_image.png')
        );

        $media = $mediaUploader->upload($file);

        $filesystem = Storage::disk(self::DEFAULT_DISK);

        // The uploaded file should exist...
        $this->assertTrue(
            $filesystem->exists($media->getPath())
        );

        // The original file should not exist...
        $this->assertFalse(
            $filesystem->exists($file->getPath())
        );
    }

    /** @test */
    public function it_can_preserve_the_original_file_uploaded_by_symfony_file_object()
    {
        $filesystemManager = $this->mockFilesystemManager();

        $mediaUploader = $this->mockMediaUploader($filesystemManager);

        $file = new SymfonyFile(self::RESOURCE_DIRECTORY.'/test_image.png');

        $options = UploadOptions::create()->preserveOriginalFile();

        $media = $mediaUploader->upload($file, $options);

        $filesystem = Storage::disk(self::DEFAULT_DISK);

        // The uploaded file should exist...
        $this->assertTrue(
            $filesystem->exists($media->getPath())
        );

        // The original file should not exist...
        $this->assertTrue(
            File::exists($file->getPath())
        );
    }

    /** @test */
    public function it_can_preserve_the_original_file_uploaded_by_path()
    {
        $filesystemManager = $this->mockFilesystemManager();

        $mediaUploader = $this->mockMediaUploader($filesystemManager);

        $options = UploadOptions::create()->preserveOriginalFile();

        $media = $mediaUploader->upload(
            $file = $this->getResourcePath('test_image.png'), $options
        );

        $filesystem = Storage::disk(self::DEFAULT_DISK);

        // The uploaded file should exist...
        $this->assertTrue(
            $filesystem->exists($media->getPath())
        );

        // The original file should exist...
        $this->assertTrue(
            File::exists($file)
        );
    }

    /** @test */
    public function it_uploads_media_publicly_by_default()
    {
        $filesystemManager = $this->mockFilesystemManager();

        $mediaUploader = $this->mockMediaUploader($filesystemManager);

        $file = UploadedFile::fake()->image('file-name.jpg');

        $options = UploadOptions::create();

        $media = $mediaUploader->upload($file, $options);

        $destinationDisk = $filesystemManager->disk(self::DEFAULT_DISK)
            ->getVisibility(
                $media->getPath()
            );

        $this->assertEquals($destinationDisk, 'public');
    }

    /** @test */
    public function it_can_upload_media_publicly()
    {
        $filesystemManager = $this->mockFilesystemManager();

        $mediaUploader = $this->mockMediaUploader($filesystemManager);

        $file = UploadedFile::fake()->image('file-name.jpg');

        $options = UploadOptions::create()
            ->setVisibility($visibility = 'public');

        $media = $mediaUploader->upload($file, $options);

        $destinationDisk = $filesystemManager->disk(self::DEFAULT_DISK)
            ->getVisibility(
                $media->getPath()
            );

        $this->assertEquals($destinationDisk, $visibility);
    }

    /** @test */
    public function it_can_upload_media_privately()
    {
        $filesystemManager = $this->mockFilesystemManager();

        $mediaUploader = $this->mockMediaUploader($filesystemManager);

        $file = UploadedFile::fake()->image('file-name.jpg');

        $options = UploadOptions::create()
            ->setVisibility($visibility = 'private');

        $media = $mediaUploader->upload($file, $options);

        $destinationDisk = $filesystemManager->disk(self::DEFAULT_DISK)
            ->getVisibility(
                $media->getPath()
            );

        $this->assertEquals($destinationDisk, $visibility);
    }

    /** @test */
    public function it_cannot_upload_a_non_existent_file()
    {
        $filesystemManager = $this->mockFilesystemManager();

        $mediaUploader = $this->mockMediaUploader($filesystemManager);

        $this->expectException(ErrorException::class);

        $mediaUploader->upload(
            $this->getResourcePath('non_existent_image.png')
        );
    }

    /** @test */
    public function it_can_change_the_name_of_the_media_model()
    {
        $file = UploadedFile::fake()->image('file-name.jpg');

        $filesystemManager = $this->mockFilesystemManager();

        $mediaUploader = $this->mockMediaUploader($filesystemManager);

        $options = UploadOptions::create()->setMediaName($newName = 'New name');

        $media = $mediaUploader->upload($file, $options);

        $this->assertEquals($newName, $media->name);
    }

    /** @test */
    public function it_can_rename_the_file_before_it_gets_uploaded()
    {
        $file = UploadedFile::fake()->image('file-name.jpg');

        $filesystemManager = $this->mockFilesystemManager();

        $mediaUploader = $this->mockMediaUploader($filesystemManager);

        $options = UploadOptions::create()
            ->setFileName($newFileName = 'new-file-name.jpg');

        $media = $mediaUploader->upload($file, $options);

        $this->assertEquals($newFileName, $media->file_name);
    }

    /** @test */
    public function it_will_sanitise_the_file_name()
    {
        $file = UploadedFile::fake()->image('bad file name#023.jpg');

        $filesystemManager = $this->mockFilesystemManager();

        $mediaUploader = $this->mockMediaUploader($filesystemManager);

        $options = UploadOptions::create()->setDisk(self::DEFAULT_DISK);

        $media = $mediaUploader->upload($file, $options);

        $this->assertEquals('bad-file-name-023.jpg', $media->file_name);
    }

    /** @test */
    public function it_can_apply_a_custom_file_name_sanitiser()
    {
        $file = UploadedFile::fake()->image('file@name.jpg');

        $filesystemManager = $this->mockFilesystemManager();

        $mediaUploader = $this->mockMediaUploader($filesystemManager);

        $options = UploadOptions::create()
            ->setFileNameSanitiser(function ($name) {
                return str_replace('@', '-', $name);
            });

        $media = $mediaUploader->upload($file, $options);

        $this->assertEquals('file-name.jpg', $media->file_name);
    }

    /** @test */
    public function it_can_save_custom_attributes_to_the_media_model()
    {
        $filesystemManager = $this->mockFilesystemManager();

        $mediaUploader = $this->mockMediaUploader(
            $filesystemManager, self::DEFAULT_DISK, CustomMedia::class
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

    private function getResourcePath(string $fileName)
    {
        return self::RESOURCE_DIRECTORY."_live/{$fileName}";
    }

    private function mockMediaUploader(
        FilesystemManager $filesystemManager,
        $disk = self::DEFAULT_DISK,
        $model = Media::class
    ) {
        $config = [
            'disk' => $disk,
            'model' => $model,
        ];

        return new MediaUploader($filesystemManager, $config);
    }

    private function mockFilesystemManager($disk = self::DEFAULT_DISK)
    {
        $filesystemManager = app(FilesystemManager::class);

        (new Filesystem)->cleanDirectory(
            $root = storage_path('framework/testing/disks/'.$disk)
        );

        $filesystem = $filesystemManager->createLocalDriver([
            'root' => $root,
        ]);

        $filesystemManager->set($disk, $filesystem);

        return $filesystemManager;
    }
}

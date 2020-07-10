<?php

namespace Optix\Media\Tests;

use ErrorException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use League\Flysystem\AdapterInterface;
use Optix\Media\MediaUploader;
use Optix\Media\Models\Media;
use Optix\Media\Options\UploadOptions;
use Optix\Media\Tests\Models\Media as CustomMedia;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

class MediaUploaderTest extends TestCase
{
    /** @var FilesystemManager */
    protected $filesystemManager;

    /** @var array */
    protected $defaultConfig = [
        'model' => Media::class,
        'disk' => 'test',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystemManager = $this->app->make(
            FilesystemManager::class
        );
    }

    /** @test */
    public function it_can_upload_a_file_to_the_default_disk()
    {
        $uploadedFile = UploadedFile::fake()->image('file-name.jpg');

        $filesystem = $this->mockFilesystem(
            $disk = $this->defaultConfig['disk']
        );

        $mediaUploader = new MediaUploader(
            $this->filesystemManager,
            $this->defaultConfig
        );

        $media = $mediaUploader->upload($uploadedFile);

        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals($disk, $media->disk);

        $this->assertTrue(
            $filesystem->exists($media->getPath())
        );
    }

    /** @test */
    public function it_can_upload_a_file_to_a_specific_disk()
    {
        $uploadedFile = UploadedFile::fake()->image('file-name.jpg');

        $config = array_merge($this->defaultConfig, [
            'disk' => $customDisk = 'custom',
        ]);

        $filesystem = $this->mockFilesystem($customDisk);

        $mediaUploader = new MediaUploader(
            $this->filesystemManager,
            $config
        );

        $options = UploadOptions::create()->setDisk($customDisk);

        $media = $mediaUploader->upload($uploadedFile, $options);

        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals($customDisk, $media->disk);

        $this->assertTrue(
            $filesystem->exists($media->getPath())
        );
    }

    /** @test */
    public function it_can_upload_a_file_from_a_path()
    {
        $uploadedFile = UploadedFile::fake()->image('file-name.jpg');
        $sourceFilePath = $uploadedFile->getPathname();

        $filesystem = $this->mockFilesystem();

        $mediaUploader = new MediaUploader(
            $this->filesystemManager,
            $this->defaultConfig
        );

        $media = $mediaUploader->upload($sourceFilePath);

        $this->assertTrue(
            $filesystem->exists($media->getPath())
        );
    }

    /** @test */
    public function it_can_upload_a_symfony_file_object()
    {
        $uploadedFile = UploadedFile::fake()->image('file-name.jpg');
        $symfonyFile = new SymfonyFile($uploadedFile->getPathname());

        $filesystem = $this->mockFilesystem();

        $mediaUploader = new MediaUploader(
            $this->filesystemManager,
            $this->defaultConfig
        );

        $media = $mediaUploader->upload($symfonyFile);

        $this->assertTrue(
            $filesystem->exists($media->getPath())
        );
    }

    /** @test */
    public function it_can_preserve_the_original_file_uploaded_by_symfony_file_object()
    {
        $uploadedFile = UploadedFile::fake()->image('file-name.jpg');
        $symfonyFile = new SymfonyFile($uploadedFile->getPathname());

        $filesystem = $this->mockFilesystem();

        $mediaUploader = new MediaUploader(
            $this->filesystemManager,
            $this->defaultConfig
        );

        $options = UploadOptions::create()->preserveOriginalFile();

        $media = $mediaUploader->upload($symfonyFile, $options);

        $this->assertTrue(
            $filesystem->exists($media->getPath())
        );

        $this->assertFileExists(
            $symfonyFile->getPathname()
        );
    }

    /** @test */
    public function it_can_preserve_the_original_file_uploaded_by_path()
    {
        $uploadedFile = UploadedFile::fake()->image('file-name.jpg');
        $filePath = $uploadedFile->getPathname();

        $filesystem = $this->mockFilesystem();

        $mediaUploader = new MediaUploader(
            $this->filesystemManager,
            $this->defaultConfig
        );

        $options = UploadOptions::create()->preserveOriginalFile();

        $media = $mediaUploader->upload($filePath, $options);

        $this->assertTrue(
            $filesystem->exists($media->getPath())
        );

        $this->assertFileExists($filePath);
    }

    /** @test */
    public function it_can_upload_media_publicly()
    {
        $uploadedFile = UploadedFile::fake()->image('file-name.jpg');

        $filesystem = $this->mockFilesystem();

        $mediaUploader = new MediaUploader(
            $this->filesystemManager,
            $this->defaultConfig
        );

        $options = UploadOptions::create()->setVisibility(
            $expectedVisibility = AdapterInterface::VISIBILITY_PUBLIC
        );

        $media = $mediaUploader->upload($uploadedFile, $options);

        $actualVisibility = $filesystem->getVisibility(
            $media->getPath()
        );

        $this->assertEquals($expectedVisibility, $actualVisibility);
    }

    /** @test */
    public function it_can_upload_media_privately()
    {
        $uploadedFile = UploadedFile::fake()->image('file-name.jpg');

        $filesystem = $this->mockFilesystem();

        $mediaUploader = new MediaUploader(
            $this->filesystemManager,
            $this->defaultConfig
        );

        $options = UploadOptions::create()->setVisibility(
            $expectedVisibility = AdapterInterface::VISIBILITY_PRIVATE
        );

        $media = $mediaUploader->upload($uploadedFile, $options);

        $actualVisibility = $filesystem->getVisibility(
            $media->getPath()
        );

        $this->assertEquals($expectedVisibility, $actualVisibility);
    }

    /** @test */
    public function it_cannot_upload_a_non_existent_file()
    {
        $this->mockFilesystem();

        $mediaUploader = new MediaUploader(
            $this->filesystemManager,
            $this->defaultConfig
        );

        $this->expectException(ErrorException::class);

        $mediaUploader->upload(__DIR__.'/non/existent/file.txt');
    }

    /** @test */
    public function it_can_change_the_name_of_the_media_model()
    {
        $file = UploadedFile::fake()->image('file-name.jpg');

        $this->mockFilesystem();

        $mediaUploader = new MediaUploader(
            $this->filesystemManager,
            $this->defaultConfig
        );

        $options = UploadOptions::create()
            ->setMediaName($customName = 'Custom name');

        $media = $mediaUploader->upload($file, $options);

        $this->assertEquals($customName, $media->name);
    }

    /** @test */
    public function it_can_rename_the_file_before_it_gets_uploaded()
    {
        $uploadedFile = UploadedFile::fake()->image('file-name.jpg');

        $filesystem = $this->mockFilesystem();

        $mediaUploader = new MediaUploader(
            $this->filesystemManager,
            $this->defaultConfig
        );

        $options = UploadOptions::create()
            ->setFileName($customFileName = 'custom-file-name.jpg');

        $media = $mediaUploader->upload($uploadedFile, $options);

        $this->assertEquals($customFileName, $media->file_name);

        $this->assertTrue(
            $filesystem->exists($media->getPath())
        );
    }

    /** @test */
    public function it_will_sanitise_the_file_name()
    {
        $uploadedFile = UploadedFile::fake()->image('bad file name#023.jpg');

        $this->mockFilesystem();

        $mediaUploader = new MediaUploader(
            $this->filesystemManager,
            $this->defaultConfig
        );

        $media = $mediaUploader->upload($uploadedFile);

        $this->assertEquals('bad-file-name-023.jpg', $media->file_name);
    }

    /** @test */
    public function it_can_apply_a_custom_file_name_sanitiser()
    {
        $uploadedFile = UploadedFile::fake()->image('file@name.jpg');

        $this->mockFilesystem();

        $mediaUploader = new MediaUploader(
            $this->filesystemManager,
            $this->defaultConfig
        );

        $options = UploadOptions::create()
            ->setFileNameSanitiser(function ($name) {
                return str_replace('@', '-', $name);
            });

        $media = $mediaUploader->upload($uploadedFile, $options);

        $this->assertEquals('file-name.jpg', $media->file_name);
    }

    /** @test */
    public function it_can_save_custom_attributes_to_the_media_model()
    {
        $uploadedFile = UploadedFile::fake()->image('image.jpg');

        $this->mockFilesystem();

        $config = array_merge($this->defaultConfig, [
            'model' => CustomMedia::class,
        ]);

        $mediaUploader = new MediaUploader(
            $this->filesystemManager,
            $config
        );

        $options = UploadOptions::create()
            ->setCustomAttributes([
                'custom_attribute' => $expectedValue = 'Custom attribute',
            ]);

        $media = $mediaUploader->upload($uploadedFile, $options);

        $this->assertInstanceOf(CustomMedia::class, $media);
        $this->assertEquals($expectedValue, $media->custom_attribute);
    }

    protected function mockFilesystem(string $disk = null)
    {
        $disk = $disk ?: $this->defaultConfig['disk'];

        (new Filesystem)->cleanDirectory(
            $root = storage_path("framework/testing/disks/{$disk}")
        );

        $filesystem = $this->filesystemManager->createLocalDriver([
            'root' => $root,
        ]);

        $this->filesystemManager->set($disk, $filesystem);

        return $filesystem;
    }
}

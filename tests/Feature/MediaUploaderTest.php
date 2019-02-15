<?php

namespace Optix\Media\Tests;

use Illuminate\Http\UploadedFile;
use Optix\Media\MediaUploader;
use Optix\Media\Models\Media;

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

    /**
     * @test
     * @dataProvider provide_filenames
     *
     * @param string $initialFileName Filename provided to MediaUploader
     * @param string $expectedFileName The expected sanitised filename
     */
    public function it_will_sanitise_the_file_name(string $initialFileName, string $expectedFileName)
    {
        $file = UploadedFile::fake()->image($initialFileName);
        $media = MediaUploader::fromFile($file);
        $actualFilename = $media->getFileName();
        $this->assertEquals($expectedFileName, $actualFilename);
    }

    public function provide_filenames()
    {
        return [
            ['simple-filename.jpg', 'simple-filename.jpg'],
            ['file with spaces.doc', 'file-with-spaces.doc'],
            ['import#0001', 'import-0001'],
        ];
    }

    /**
     * @test
     * @dataProvider provide_sanitisers
     *
     * @param string $initialFileName Filename provided to MediaUploader
     * @param string $expectedFileName The expected sanitised filename
     * @param callable $sanitiser A custom file name sanitiser
     */
    public function it_will_use_the_given_file_name_sanitiser(
        string $initialFileName,
        string $expectedFileName,
        callable $sanitiser
    ) {
        $file = UploadedFile::fake()->image($initialFileName);
        $media = MediaUploader::fromFile($file, $sanitiser);
        $actualFilename = $media->getFileName();
        $this->assertEquals($expectedFileName, $actualFilename);
    }

    public function provide_sanitisers()
    {
        $noBrackets = function (string $fileName) {
            return str_replace(['(', ')'], '', $fileName);
        };

        $noUpperCase = function (string $fileName) {
            return strtolower($fileName);
        };

        return [
            ['filename-with-brackets(1).jpg', 'filename-with-brackets1.jpg', $noBrackets],
            ['SHOUTY-filename.gif', 'shouty-filename.gif', $noUpperCase],
        ];
    }

    // it_can_save_custom_attributes_to_the_media_model
}

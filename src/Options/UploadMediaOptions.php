<?php

namespace Optix\Media\Options;

class UploadMediaOptions
{
    /** @var string|null */
    public $mediaName;

    /** @var string|null */
    public $fileName;

    /** @var callable|null */
    public $fileNameSanitiser;

    /** @var string|null */
    public $disk;

    /** @var string|null */
    public $visibility;

    /** @var bool */
    public $preserveOriginalFile = false;

    /** @var array */
    public $customAttributes = [];

    public static function create()
    {
        return new self();
    }

    public function setMediaName(string $mediaName)
    {
        $this->mediaName = $mediaName;

        return $this;
    }

    public function setFileName(string $fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function setFileNameSanitiser(callable $sanitiser)
    {
        $this->fileNameSanitiser = $sanitiser;

        return $this;
    }

    public function setDisk(string $disk)
    {
        $this->disk = $disk;

        return $this;
    }

    public function setVisibility(string $visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function preserveOriginalFile()
    {
        $this->preserveOriginalFile = true;

        return $this;
    }

    public function setCustomAttributes(array $customAttributes)
    {
        $this->customAttributes = $customAttributes;

        return $this;
    }
}

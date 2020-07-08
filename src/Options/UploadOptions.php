<?php

namespace Optix\Media\Options;

class UploadOptions
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

    /**
     * @return self
     */
    public static function create()
    {
        return new self();
    }

    /**
     * @param string $mediaName
     * @return self
     */
    public function setMediaName(string $mediaName)
    {
        $this->mediaName = $mediaName;

        return $this;
    }

    /**
     * @param string $fileName
     * @return self
     */
    public function setFileName(string $fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @param callable $sanitiser
     * @return self
     */
    public function setFileNameSanitiser(callable $sanitiser)
    {
        $this->fileNameSanitiser = $sanitiser;

        return $this;
    }

    /**
     * @param string $disk
     * @return self
     */
    public function setDisk(string $disk)
    {
        $this->disk = $disk;

        return $this;
    }

    /**
     * @param string $visibility
     * @return self
     */
    public function setVisibility(string $visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * @return self
     */
    public function preserveOriginalFile()
    {
        $this->preserveOriginalFile = true;

        return $this;
    }

    /**
     * @param array $customAttributes
     * @return self
     */
    public function setCustomAttributes(array $customAttributes)
    {
        $this->customAttributes = $customAttributes;

        return $this;
    }
}

<?php

namespace Optix\Media;

use Optix\Media\Models\Media;

class MediaType
{
    /**
     * @param Media $media
     *
     * @return string|null The full mime type for the media file
     */
    public static function getType(Media $media)
    {
        $mimeParts = explode('/', $media->mime_type);

        return strtolower($mimeParts[0] ?? null);
    }

    /**
     * Detect whether the file matches a specific generic mime type, eg 'text', 'image', 'video', etc
     *
     * @param Media $media
     * @param string $type
     *
     * @return bool
     */
    public static function isOfType(Media $media, string $type): bool
    {
        $mimeType = (string)self::getType($media);

        return strtolower($mimeType) === strtolower($type);
    }

    /**
     * Detect whether the file matches one of the provided mime subtypes, eg 'text/plain', 'image/png', etc
     *
     * @param Media $media
     * @param string[] $subTypes
     *
     * @return bool
     */
    public static function isOfSubType(Media $media, array $subTypes): bool
    {
        $matchedTypes = array_filter(
            $subTypes,
            function (string $subType) use ($media) {
                return strtolower($media->mime_type) === strtolower($subType);
            }
        );

        return count($matchedTypes) > 0;
    }
}

<?php

use Optix\Media\Models\Media;
use Faker\Generator as Faker;

$factory->define(Media::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'file_name' => "file.{$faker->fileExtension}",
        'disk' => config('media.disk'),
        'mime_type' => $faker->mimeType,
        'size' => $faker->randomNumber(4)
    ];
});

# Laravel Media

Upload and attach files to eloquent models.

## Usage

```php
$media = MediaUploader::fromFile($file)->upload();
```

```php
$post = Post::create($request->all());
$post->attachMedia($mediaId)->toMediaCollection('image');
$post->getMedia('image')->first()->getUrl();
```

```php
Conversion::register('thumb', function (Image $image) {
    return $image->fit(64, 64);
});
```

```php
$post->attachMedia($mediaId)
     ->performConversion('thumb')
     ->toMediaCollection('thumb');

$post->getMedia('image')->first()->getUrl('thumb');
```

## Installation

```bash
composer require optix/media
```

```bash
php artisan vendor:publish --provider="Optix\Media\Providers\MediaServiceProvider"
```

## Documentation

Documentation is in the works - please read through the source for now.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

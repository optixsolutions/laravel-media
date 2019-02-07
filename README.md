# Laravel Media

Upload and attach files to eloquent models.

## Installation

```bash
composer require optix/media
```

```bash
php artisan vendor:publish --provider="Optix\Media\Providers\MediaServiceProvider"
```

## Usage

### Uploading media

You can use the `Optix\Media\MediaUploader` class to handle media uploads.

By default, this class uploads files to the disk specified in the media config file.
It stores them as a sanitised version of their original file name,
and creates a media record in the database with the file's details.

It's also possible to customise the way in which files are persisted on the server (as seen below).

```php
$file = $request->input('file');

// Default usage...
$media = MediaUploader::fromFile($file)->upload();

// Custom usage...
$media = MediaUploader::fromFile($file)
    ->useFileName('custom-file-name.jpg')
    ->useName('Custom name')
    ->withAttributes([
        'custom_model_attribute' => true
    ])
    ->upload();
```

### Attaching media

### Image manipulations

Todo!

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

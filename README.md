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

It's also possible to customise certain properties of the file before it gets uploaded.

```php
$file = $request->input('file');

// Default usage...
$media = MediaUploader::fromFile($file)->upload();

// Custom usage...
$media = MediaUploader::fromFile($file)
    ->useFileName('custom-file-name.jpg')
    ->useName('Custom name')
    ->upload();
```

### Attaching media

Firstly, use the `Optix\Media\HasMedia` trait on your subject model.

```php
use Optix\Media\HasMedia;

class Post extends Model
{
    use HasMedia;
}
```

Then follow the example below.

```php
$post = new Post::first();

// To the default group...
$media = $post->attachMedia($media);

// To a custom group...
$media = $post->attachMedia($media, 'group');
```

The media parameter `$media` can be an id, a media model, or an iterable group of id's or media models.

### Retrieving media

### Image manipulations

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

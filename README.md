# Laravel Media

An easy solution to attach files to your eloquent models, with image manipulation built in!

![Packagist Version](https://img.shields.io/packagist/v/optix/media.svg)
![Monthly Downloads](https://travis-ci.org/optixsolutions/laravel-media.svg?branch=master)
![GitHub](https://img.shields.io/github/license/optixsolutions/laravel-media.svg)

## Installation

You can install the package via composer:

```bash
composer require optix/media
```

Once installed, you should publish the provided assets to create the necessary migration and config files.

```bash
php artisan vendor:publish --provider="Optix\Media\Providers\MediaServiceProvider"
```

## Key concepts

There are a few key concepts that should be considered before continuing:

* Media can be any type of file, from a jpeg to a zip file. You should specify any file restrictions in your
  application's validation logic before you attempt to upload a file.

* Media is uploaded as its own entity. It does not belong to another model in the system when it's created, so it can
  be managed independently (which makes it the perfect engine for a media manager).
  
* Media must be "attached" to a model for an association to be made.

* Media items are bound to "groups". This makes it easy to associate multiple different types of media to a model. For
  example, a Post might have an "images" group and a "documents" group.
  
* You can manipulate images using conversions. You can specify conversions to be performed when a media item is
  associated to a model. For example, you can register a "thumbnail" conversion to run when images are attached to a
  model's "gallery" group.

* Conversions are registered globally. This means that they can be reused across your application, i.e a Post and a
  User can have the same sized thumbnail without having to register it twice.

## Usage

### Upload media

You should use the `Optix\Media\MediaUploader` class to handle file uploads.

By default, this class will update files to the disk specified in the media config. It saves them as a sanitised
version of their original file name, and creates a media record in the database with the file's details.

It's also possible to customise certain properties of the file before it's uploaded.

```php
$file = $request->file('file');

// Default usage
$media = MediaUploader::fromFile($file)->upload();

// Custom usage
$media = MediaUploader::fromFile($file)
    ->useFileName('custom-file-name.jpeg')
    ->useName('Custom media name')
    ->upload();
```

### Associate media with a model

In order to associate a media item with a model, you must first include the `Optix\Media\HasMedia` trait.

```php
class Post extends Model
{
    use HasMedia;
}
```

This trait will setup the relationship between your model and the media model. It's primary purpose is to provide a
fluent api for attaching and retrieving media.

Once included, you can attach media to the model as demonstrated below. The first parameter of the attach media method
can either be a media model instance, an id, or an iterable list of models / ids.

```php
$post = Post::first();

// To the default group
$post->attachMedia($media);

// To a custom group
$post->attachMedia($media, 'custom-group');
```

# Todo

You can detach media from the subject model like so...

```php
// Detach all media...
$post->detachMedia();

// Detach the specified media...
$post->detachMedia([1, 2, 3]);

// Detach all media in the specified group...
$post->clearMediaGroup('group');
```

### Retrieving media

```php
$allMedia = $post->getMedia('group');

$media = $post->getFirstMedia('group');

$url = $media->getUrl('group'); // $post->getFirstMediaUrl('group');
```

### Image manipulations

```php
use Intervention\Image\Image;
use Optix\Media\Facades\Conversion;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Conversion::register('thumb', function (Image $image) {
            return $image->fit(64, 64);
        });
    }
}
```

```php
class Post extends Model
{
    use HasMedia;
    
    public function registerMediaGroups()
    {
        $this->addMediaGroup('group')
             ->performConversions('thumb');
    }
}
```

```php
$post = Post::first();

$url = $post->getFirstMediaUrl('group', 'thumb');
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

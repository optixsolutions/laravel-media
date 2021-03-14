# Laravel Media

An easy solution to attach files to your eloquent models, with image manipulation built in!

[![Packagist Version](https://img.shields.io/packagist/v/optix/media.svg)](https://packagist.org/packages/optix/media)
[![Build Status](https://travis-ci.org/optixsolutions/laravel-media.svg?branch=master)](https://travis-ci.org/optixsolutions/laravel-media)
[![License](https://img.shields.io/github/license/optixsolutions/laravel-media.svg)](https://github.com/optixsolutions/laravel-media/blob/master/LICENSE.md)

## Installation

You can install the package via composer:

```bash
composer require optix/media
```

Once installed, you should publish the provided assets to create the necessary migration and config files.

```bash
php artisan vendor:publish --provider="Optix\Media\MediaServiceProvider"
```

## Key concepts

There are a few key concepts that should be understood before continuing:

* Media can be any type of file, from a jpeg to a zip file. You should specify any file restrictions in your
  application's validation logic before you attempt to upload a file.

* Media is uploaded as its own entity. It does not belong to another model in the system when it's created, so items can
  be managed independently (which makes it the perfect engine for a media manager).
  
* Media must be attached to a model for an association to be made.

* Media items are bound to "groups". This makes it easy to associate multiple types of media to a model. For
  example, a model might have an "images" group and a "documents" group.
  
* You can manipulate images using conversions. You can specify conversions to be performed when a media item is
  associated to a model. For example, you can register a "thumbnail" conversion to run when images are attached to a
  model's "gallery" group.

* Conversions are registered globally. This means that they can be reused across your application, i.e a Post and a
  User can have the same sized thumbnail without having to register the same conversion twice.

## Usage

### Upload media

You should use the `Optix\Media\MediaUploader` class to handle file uploads.

By default, this class will update files to the disk specified in the media config. It saves them as a sanitised
version of their original file name, and creates a media record in the database with the file's details.

You can also customise certain properties of the file before it's uploaded.

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

### Disassociate media from a model

To disassociate media from a model, you should call the provided `detachMedia` method.

```php
// Detach all the media
$post->detachMedia();

// Detach the specified media
$post->detachMedia($media);

// Detach all the media in a group
$post->clearMediaGroup('your-group');
``` 

If you want to delete a media item, you should do it the same way you would for any other model in your application.

```php
Media::first()->delete();
```

Doing so will delete the file from your filesystem, and also remove any association between the media item and your
application's models.

### Retrieve media

Another feature of the `HasMedia` trait is the ability to retrieve media.

```php
// All media in the default group
$post->getMedia();

// All media in a custom group
$post->getMedia('custom-group');

// First media item in the default group 
$post->getFirstMedia();

// First media item in a custom group
$post->getFirstMedia('custom-group');
```

As well as retrieve media items, you can also retrieve attributes of the media model directly from your model.

```php
// Url of the first media item in the default group
$post->getFirstMediaUrl();

// Url of the first media item in a custom group
$post->getFirstMediaUrl('custom-group');
```

### Manipulate Images

This package provides a fluent api to manipulate images. You can specify a model to perform "conversions" when
media is attached to a group. It uses the familiar `intervention/image` library under the hood, so images can be
manipulated using all of the library's provided options.

To get started, you should first register a conversion in one of your application's service providers:

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

Once you've registered a conversion, you should configure a media group to perform the conversion when media is
attached to your model.

```php
class Post extends Model
{
    use HasMedia;
    
    public function registerMediaGroups()
    {
        $this->addMediaGroup('gallery')
             ->performConversions('thumb');
    }
}
```

Now when a media item is attached to the "gallery" group, a converted image will be generated. You can get the url of
the converted image as demonstrated below:

```php
// The thumbnail of the first image in the gallery group
$post->getFirstMediaUrl('gallery', 'thumb');
```

## Why use this package?

There are already packages that exist to solve a similar problem to the one that this package was built to achieve.

The most popular of which are:

* [Spatie's Laravel MediaLibrary](https://github.com/spatie/laravel-medialibrary)
* [Plank's Laravel Mediable](https://github.com/plank/laravel-mediable)

There are a few key differences between this package and the ones listed above. Our package was built to power media
managers, and make it easy to perform image manipulations. This is better represented by the comparison table below:

| Comparison                      | Spatie              | Plank        | Optix                |
|---------------------------------|---------------------|--------------|----------------------|
| **Relationship type**           | One to many         | Many to many | Many to many         |
| **Provides image manipulation** | Yes                 | No           | Yes                  |
| **Definition of manipulations** | Specific to a model | -            | Global registry      |

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

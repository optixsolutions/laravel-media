# Laravel Media

An easy solution to associate media with your eloquent models, with image manipulation built in!

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

## Usage

There are a few core concepts that should be considered before continuing:

* Media can be **any** type of file, from a `png` to a `zip` file. You should
  specify any file restrictions in your application's validation logic before
  you attempt to upload a file.

* Media is uploaded as its own entity. It *does not* belong to another model in the system when it's created,
  so it can be managed independently (which makes it the perfect engine for a media manager).
  
* Media must be "attached" to a model for an association to be made.

* Media items are bound to "groups", which makes it easy to associate multiple different types of media
  to a model. For example, a `Post` might have an "images" group and a "documents" group.
  
* **Write about conversions!**

---

# TODO

### Uploading media

You can use the `Optix\Media\MediaUploader` class to handle media uploads.

By default, this class uploads files to the disk specified in the media config file.
It stores them as a sanitised version of their original file name,
and creates a media record in the database with the file's details.

It's also possible to customise certain properties of the file before it's uploaded.

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

Firstly, include the `Optix\Media\HasMedia` trait on your subject model.

```php
use Optix\Media\HasMedia;

class Post extends Model
{
    use HasMedia;
}
```

You can then attach media to your subject model like so...

```php
$post = new Post::first();

// To the 'default' group...
$media = $post->attachMedia($media);

// To a custom group...
$media = $post->attachMedia($media, 'group');
```

The `$media` parameter can either be an id, a media model, or an iterable group of ids / media models.

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

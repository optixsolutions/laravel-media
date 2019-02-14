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

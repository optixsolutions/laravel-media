# Laravel Mediable

```php
$media = MediaUploader::fromFile($file)
    ->useName('Example')
    ->useFileName('example.jpg')
    ->withAttributes([
        'folder_id' => $folderId
    ])
    ->upload();
```

<?php

namespace Optix\Media\Tests\Models;

use Optix\Media\HasMedia;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    use HasMedia;

    public function registerMediaGroups()
    {
        $this->addMediaGroup('convert-images')
             ->performConversions('conversion');
    }
}

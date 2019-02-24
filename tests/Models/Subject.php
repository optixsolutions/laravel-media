<?php

namespace Optix\Media\Tests\Models;

use Optix\Media\HasMedia;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasMedia;

    public function registerMediaGroups()
    {
        $this->addMediaGroup('converted-images')
             ->performConversions('conversion');
    }
}

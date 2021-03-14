<?php

namespace Optix\Media\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Optix\Media\HasMedia;

class Subject extends Model
{
    use HasMedia;

    public function registerMediaGroups()
    {
        $this->addMediaGroup('converted-images')
             ->performConversions('conversion');
    }
}

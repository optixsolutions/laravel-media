<?php

use Optix\Media\ConversionManager;
use Illuminate\Support\ServiceProvider;

class MediaServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->singleton(ConversionManager::class);
    }
}

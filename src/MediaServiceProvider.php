<?php

namespace Optix\Media;

use Illuminate\Support\ServiceProvider;
use Optix\Media\Conversions\ConversionManager;

class MediaServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom([__DIR__ . '/../database/migrations']);
    }

    public function register()
    {
        $this->app->singleton(ConversionManager::class);
    }
}

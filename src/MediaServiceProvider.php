<?php

namespace Optix\Media\Providers;

use Optix\Media\ConversionManager;
use Illuminate\Support\ServiceProvider;

class MediaServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../database/migrations/create_media_table.php.stub' => database_path(
                'migrations/' . date('Y_m_d_His', time()) . '_create_media_table.php'
            )
        ], 'migrations');
    }

    public function register()
    {
        $this->app->singleton(ConversionManager::class);
    }
}

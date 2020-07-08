<?php

namespace Optix\Media\Tests;

use Illuminate\Support\Facades\File;
use Optix\Media\MediaServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->withFactories(__DIR__.'/database/factories');

        // Copy test resources to temporary directory...
        File::copyDirectory(
            __DIR__.'/resources', __DIR__.'/resources_live'
        );
    }

    public function tearDown(): void
    {
        parent::tearDown();

        // Delete temporary test resources...
        File::deleteDirectory(__DIR__.'/resources_live');
    }

    protected function getPackageProviders($app)
    {
        return [
            MediaServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function getTestResourcePath(string $fileName)
    {
        return __DIR__."/resources_live/{$fileName}";
    }
}

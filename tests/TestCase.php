<?php

namespace Optix\Media\Tests;

use Optix\Media\MediaServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->withFactories(__DIR__ . '/../database/factories');

        $this->setUpDatabase($this->app);
    }

    protected function getPackageProviders($app)
    {
        return [
            MediaServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => ''
        ]);
    }

    protected function setUpDatabase($app)
    {
        $app['db']
            ->connection()
            ->getSchemaBuilder()
            ->create('test_models', function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
            });

        require_once __DIR__ . '/../database/migrations/create_media_table.stub';
        require_once __DIR__ . '/../database/migrations/create_custom_media_table.stub';
        (new \CreateMediaTable())->up();
        (new \CreateCustomMediaTable())->up();
    }
}

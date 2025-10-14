<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Emizor\SDK\EmizorServiceProvider;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            EmizorServiceProvider::class,
        ];
    }

    /**
     * Environment setup for tests with SQLite in memory
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

        $app['config']->set('emizor_sdk.owners', [
            'Tests\Models\Company' => 'Tests\Models\Company',
        ]);

        $app['config']->set('database.default', 'sqlite');

        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations automatically
        $this->loadMigrationsFrom(__DIR__ . '/../src/Database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/Database/migrations');
    }
}

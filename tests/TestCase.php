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
        $app['config']->set('database.default', 'sqlite');

        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:', // memoria
            'prefix'   => '',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations automatically
        $this->loadMigrationsFrom(__DIR__ . '/../src/Database/migrations');

    }
}

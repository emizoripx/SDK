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
     * Environment setup for tests with MySQL
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

        $app['config']->set('emizor_sdk.owners', [
            'Tests\Models\Company' => 'Tests\Models\Company',
        ]);

        $app['config']->set('database.default', 'mysql');

        $app['config']->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'mysql'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'emizor_test'),
            'username' => env('DB_USERNAME', 'dev'),
            'password' => env('DB_PASSWORD', 'devpass'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
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

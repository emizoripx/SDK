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
     * Configuración opcional de entorno para tests.
     * Aquí no necesitamos SQLite, usamos la conexión definida en .env.staging
     */
    protected function getEnvironmentSetUp($app)
    {
        // Cargar la base de datos definida en .env.staging
        $app['config']->set('database.default', env('DB_CONNECTION', 'mysql'));

        $app['config']->set('database.connections.mysql', [
            'driver'    => env('DB_CONNECTION', 'mysql'),
            'host'      => env('DB_HOST', 'mysql'),
            'port'      => env('DB_PORT', '3306'),
            'database'  => env('DB_DATABASE', 'emizor_test'),
            'username'  => env('DB_USERNAME', 'dev'),
            'password'  => env('DB_PASSWORD', 'devpass'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
        ]);
    }
    protected function setUp(): void
    {
        parent::setUp();

        // Ejecutar migraciones "inline" (return new class)
        $migrationFiles = glob(__DIR__ . '/../src/Database/migrations/*.php');

        foreach ($migrationFiles as $file) {
            $migration = require $file; // devuelve un objeto Migration
            $migration->up();           // ejecuta el up()
        }

        // Validar que se haya creado la tabla
        if (!Schema::hasTable('bei_accounts')) {
            throw new \Exception("No se pudo crear la tabla bei_accounts");
        }
    }
}

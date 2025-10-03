<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Emizor\SDK\EmizorServiceProvider;

abstract class UnitTestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            EmizorServiceProvider::class,
        ];
    }

    // No database setup for pure unit tests
}
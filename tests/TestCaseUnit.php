<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Emizor\SDK\EmizorServiceProvider;

abstract class TestCaseUnit extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            EmizorServiceProvider::class,
        ];
    }

    /**
     * Environment setup for tests without database
     */
    protected function getEnvironmentSetUp($app)
    {
        // No database setup
    }
}
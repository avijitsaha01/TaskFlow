<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        if (! $app->environment('testing') || $app['db']->connection()->getDatabaseName() !== 'taskflow_test') {
            throw new RuntimeException('Tests require the dedicated taskflow_test database. Clear any cached Laravel configuration before running tests.');
        }

        return $app;
    }
}

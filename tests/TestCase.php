<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure parallel testing database isolation
        if (isset($_SERVER['TEST_TOKEN'])) {
            $this->app['config']->set('database.connections.sqlite.database', database_path('database_test_'.$_SERVER['TEST_TOKEN'].'.sqlite'));
        }
    }
}

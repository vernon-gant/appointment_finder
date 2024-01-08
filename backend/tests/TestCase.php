<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Run the database migrations
        $this->runDatabaseMigrations();
    }

    protected function runDatabaseMigrations()
    {
        Artisan::call('migrate');
        $this->beforeApplicationDestroyed(function () {
            Artisan::call('migrate:rollback');
        });
    }
}

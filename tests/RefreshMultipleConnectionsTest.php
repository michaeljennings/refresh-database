<?php

namespace MichaelJennings\RefreshDatabase\Tests;

use Illuminate\Support\Facades\Schema;
use MichaelJennings\RefreshDatabase\RefreshDatabase;
use MichaelJennings\RefreshDatabase\Tests\Concerns\CleanUpDatabase;
use MichaelJennings\RefreshDatabase\Tests\Concerns\GeneratesConfig;

class RefreshMultipleConnectionsTest extends TestCase
{
    use GeneratesConfig, RefreshDatabase, CleanUpDatabase;

    /**
     * @test
     */
    public function it_generates_the_database_for_each_connection()
    {
        // Assert the database files were created successfully for the local
        // connection
        $this->assertTrue(file_exists(__DIR__ . '/.database/local/testing.sqlite'));
        $this->assertTrue(file_exists(__DIR__ . '/.database/local/migrations'));
        $this->assertTrue(file_exists(__DIR__ . '/.database/local/export.sql'));

        // Assert the database files were created successfully for the testing
        // connection
        $this->assertTrue(file_exists(__DIR__ . '/.database/testing/testing.sqlite'));
        $this->assertTrue(file_exists(__DIR__ . '/.database/testing/migrations'));
        $this->assertTrue(file_exists(__DIR__ . '/.database/testing/export.sql'));

        // Check that the migrations were run for both connections
        $this->assertTrue(Schema::connection('local')->hasTable('test_products'));
        $this->assertTrue(Schema::connection('testing')->hasTable('test_data'));
    }

    /**
     * @inheritdoc
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('database.connections.local', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Get the config to for multiple connections.
     *
     * @return string
     */
    protected function getConfig()
    {
        return "
connections:
  local:
    migrations:
      - tests/migrations/local
  testing:
    migrations:
      - tests/migrations

output: tests";
    }
}
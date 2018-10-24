<?php

namespace MichaelJennings\RefreshDatabase\Tests;

use MichaelJennings\RefreshDatabase\Config;
use MichaelJennings\RefreshDatabase\DatabaseMigrator;
use MichaelJennings\RefreshDatabase\Repositories\Yaml;
use MichaelJennings\RefreshDatabase\Tests\Concerns\CleanUpDatabase;
use MichaelJennings\RefreshDatabase\Tests\Concerns\WritesFiles;

class DatabaseMigratorTest extends TestCase
{
    use CleanUpDatabase, WritesFiles;

    /**
     * @test
     */
    public function it_dumps_the_database()
    {
        $migrator = new DatabaseMigrator(
            new Config(
                new Yaml([
                    'migrations' => [
                        __DIR__ . '/migrations',
                    ],
                    'output' => 'tests',
                ], __DIR__ . '/..')
            )
        );

        $migrator->migrate();

        $this->assertTrue(file_exists(__DIR__ . '/.database/testing.sqlite'));
        $this->assertTrue(file_exists(__DIR__ . '/.database/migrations'));
        $this->assertTrue(file_exists(__DIR__ . '/.database/export.sql'));
    }

    /**
     * @test
     */
    public function it_dumps_the_structure_for_multiple_connections()
    {
        $migrator = new DatabaseMigrator(
            new Config(
                new Yaml([
                    'connections' => [
                        'testing' => [
                            'migrations' => [
                                __DIR__ . '/migrations',
                            ],
                        ],
                        'local' => [
                            'migrations' => [
                                __DIR__ . '/migrations',
                            ],
                        ],
                    ],
                    'output' => 'tests',
                ], __DIR__ . '/..')
            )
        );

        $migrator->migrate();

        $this->assertTrue(file_exists(__DIR__ . '/.database/testing/testing.sqlite'));
        $this->assertTrue(file_exists(__DIR__ . '/.database/testing/migrations'));
        $this->assertTrue(file_exists(__DIR__ . '/.database/testing/export.sql'));
        $this->assertTrue(file_exists(__DIR__ . '/.database/local/testing.sqlite'));
        $this->assertTrue(file_exists(__DIR__ . '/.database/local/migrations'));
        $this->assertTrue(file_exists(__DIR__ . '/.database/local/export.sql'));
    }

    /**
     * @test
     */
    public function it_does_not_cache_the_migrations()
    {
        $migrator = new DatabaseMigrator(
            new Config(
                new Yaml([
                    'migrations' => [
                        __DIR__ . '/migrations',
                    ],
                    'output' => 'tests',
                    'cache_migrations' => false,
                ], __DIR__ . '/..')
            )
        );

        $migrator->migrate();

        $this->assertTrue(file_exists(__DIR__ . '/.database/testing.sqlite'));
        $this->assertFalse(file_exists(__DIR__ . '/.database/migrations'));
        $this->assertTrue(file_exists(__DIR__ . '/.database/export.sql'));
    }

    /**
     * @test
     */
    public function it_rebuilds_the_files_if_the_migrations_change()
    {
        // Build the migrator
        $migrator = new DatabaseMigrator(
            new Config(
                new Yaml([
                    'migrations' => [
                        __DIR__ . '/migrations',
                    ],
                    'output' => 'tests',
                ], __DIR__ . '/..')
            )
        );

        $migrator->migrate();

        // Check that the database dump was created and the migrations were cached
        $this->assertTrue(file_exists(__DIR__ . '/.database/testing.sqlite'));
        $this->assertTrue(file_exists(__DIR__ . '/.database/migrations'));
        $this->assertTrue(file_exists(__DIR__ . '/.database/export.sql'));

        // Grab the current migration structure so we can assert it has changed
        $contents = file_get_contents(__DIR__ . '/.database/migrations');

        // Create a new migration
        $migration = __DIR__ . '/migrations/2018_01_01_002000_create_test_data_table.php';

        $this->makeMigration($migration);

        // Re-run the migrator so that it rebuilds the migration cache
        $migrator->migrate();

        // Check that the database dump was created and the migrations were cached
        $this->assertTrue(file_exists(__DIR__ . '/.database/testing.sqlite'));
        $this->assertTrue(file_exists(__DIR__ . '/.database/migrations'));
        $this->assertTrue(file_exists(__DIR__ . '/.database/export.sql'));

        $newContents = file_get_contents(__DIR__ . '/.database/migrations');

        $this->assertNotEquals($contents, $newContents);

        $this->deleteFile($migration);
    }

    protected function makeMigration(string $migration)
    {
        $contents = file_get_contents(__DIR__ . '/migrations/2018_01_01_000000_create_test_data_table.php');

        $contents = str_replace('CreateTestDataTable', 'CreateTestProductsTable', $contents);
        $contents = str_replace('test_data', 'test_products', $contents);

        $this->writeFile($migration, $contents);
    }
}
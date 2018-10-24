<?php

namespace MichaelJennings\RefreshDatabase\Tests;

use MichaelJennings\RefreshDatabase\Config;
use MichaelJennings\RefreshDatabase\DatabaseMigrator;
use MichaelJennings\RefreshDatabase\Repositories\Yaml;
use MichaelJennings\RefreshDatabase\Tests\Concerns\CleanUpDatabase;

class DatabaseMigratorTest extends TestCase
{
    use CleanUpDatabase;

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
}
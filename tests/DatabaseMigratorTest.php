<?php

namespace MichaelJennings\RefreshDatabase\Tests;

use MichaelJennings\RefreshDatabase\Config;
use MichaelJennings\RefreshDatabase\DatabaseMigrator;
use MichaelJennings\RefreshDatabase\Repositories\Yaml;

class DatabaseMigratorTest extends TestCase
{
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
     * @inheritdoc
     */
    public function tearDown()
    {
        parent::tearDown();

        if (file_exists(__DIR__ . '/.database')) {
            $this->removeFile(__DIR__ . '/.database/testing.sqlite');
            $this->removeFile(__DIR__ . '/.database/migrations');
            $this->removeFile(__DIR__ . '/.database/export.sql');
            rmdir(__DIR__ . '/.database');
        }
    }

    /**
     * If the file exists delete it.
     *
     * @param $file
     */
    protected function removeFile($file)
    {
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
<?php

namespace MichaelJennings\RefreshDatabase\Tests;

use Illuminate\Support\Facades\Schema;
use MichaelJennings\RefreshDatabase\RefreshDatabase;
use MichaelJennings\RefreshDatabase\Tests\Concerns\CleanUpDatabase;
use MichaelJennings\RefreshDatabase\Tests\Concerns\WritesConfig;

class RefreshDatabaseWithoutDumpTest extends TestCase
{
    use RefreshDatabase, CleanUpDatabase, WritesConfig;

    /**
     * @before
     */
    public function setEnvVariables()
    {
        // Disable the database dump
        putenv('DUMP_DATABASE=false');

        $this->writeConfig();
    }

    /**
     * Run the migrations
     */
    public function loadFixtures()
    {
        $this->artisan('migrate', [
            '--path' => __DIR__ . '/migrations',
            '--realpath' => true,
        ]);
    }

    /**
     * @test
     */
    public function it_reverts_to_the_default_refresh_database_trait()
    {
        // Assert the database files were not created successfully
        $this->assertFalse(file_exists(__DIR__ . '/.database/testing.sqlite'));
        $this->assertFalse(file_exists(__DIR__ . '/.database/migrations'));
        $this->assertFalse(file_exists(__DIR__ . '/.database/export.sql'));
        // Check that the migrations were run
        $this->assertTrue(Schema::hasTable('test_data'));
    }

    /**
     * @after
     */
    public function removeEnvVariables()
    {
        putenv('DUMP_DATABASE=true');
    }
}
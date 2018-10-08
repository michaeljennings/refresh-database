<?php

namespace MichaelJennings\RefreshDatabase\Tests;

use Illuminate\Support\Facades\Schema;
use MichaelJennings\RefreshDatabase\RefreshDatabase;
use MichaelJennings\RefreshDatabase\Tests\Concerns\CleanUpDatabase;
use MichaelJennings\RefreshDatabase\Tests\Concerns\GeneratesConfig;

class RefreshDatabaseTest extends TestCase
{
    use GeneratesConfig, RefreshDatabase, CleanUpDatabase;

    /**
     * @test
     */
    public function it_generates_the_database()
    {
        // Assert the database files were created successfully
        $this->assertTrue(file_exists(__DIR__ . '/.database/testing.sqlite'));
        $this->assertTrue(file_exists(__DIR__ . '/.database/migrations'));
        $this->assertTrue(file_exists(__DIR__ . '/.database/export.sql'));
        // Check that the migrations were run
        $this->assertTrue(Schema::hasTable('test_data'));
    }
}
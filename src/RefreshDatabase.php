<?php

namespace MichaelJennings\RefreshDatabase;

use Illuminate\Foundation\Testing\RefreshDatabase as IlluminateRefreshDatabase;

trait RefreshDatabase
{
    use IlluminateRefreshDatabase {
        IlluminateRefreshDatabase::refreshDatabase as parentRefreshDatabase;
    }

    /**
     * Define hooks to migrate the database before and after each test.
     *
     * @return void
     */
    public function refreshDatabase()
    {
        if (Config::shouldDumpDatabase()) {
            $this->app->make('db')->unprepared(
                file_get_contents(Config::getOutputDirectory() . DIRECTORY_SEPARATOR . 'export.sql')
            );
        } else {
            $this->parentRefreshDatabase();
        }
    }
}
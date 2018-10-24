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
            if (Config::has('connections')) {
                foreach (Config::get('connections') as $connection => $config) {
                    $this->executeDump(Config::getOutputDirectory($connection, 'export.sql'), $connection);
                }
            } else {
                $this->executeDump(Config::getOutputDirectory('export.sql'));
            }
        } else {
            $this->parentRefreshDatabase();
        }
    }

    /**
     * Execute the stored database dump, if a connection is passed then
     * run the dump in that connection.
     *
     * @param string      $path
     * @param string|null $connection
     */
    protected function executeDump(string $path, string $connection = null)
    {
        $this->app->make('db')->connection($connection)->unprepared(file_get_contents($path));
    }
}
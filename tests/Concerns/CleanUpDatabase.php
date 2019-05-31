<?php

namespace MichaelJennings\RefreshDatabase\Tests\Concerns;

trait CleanUpDatabase
{
    /**
     * The DB files to purge.
     *
     * @var array
     */
    protected $files = [
        'testing.sqlite',
        'migrations',
        'export.sql',
    ];

    /**
     * @after
     */
    public function removeDatabase()
    {
        if (file_exists(__DIR__ . '/../.database')) {
            $this->removeFiles();
            $this->removeConnection('testing');
            $this->removeConnection('local');

            rmdir(__DIR__ . '/../.database');
        }
    }

    /**
     * Remove the files for the connection.
     *
     * @param string $connection
     */
    protected function removeConnection($connection)
    {
        $connectionDir = __DIR__ . "/../.database/$connection";

        if (file_exists($connectionDir)) {
            $this->removeFiles($connectionDir);

            rmdir($connectionDir);
        }
    }

    /**
     * Remove all of the test files from the directory.
     *
     * @param string $directory
     */
    protected function removeFiles($directory = __DIR__ . '/../.database/')
    {
        foreach ($this->files as $file) {
            $this->removeFile("$directory/$file");
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

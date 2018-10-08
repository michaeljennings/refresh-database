<?php

namespace MichaelJennings\RefreshDatabase\Tests\Concerns;

trait CleanUpDatabase
{
    /**
     * @after
     */
    public function removeDatabase()
    {
        if (file_exists(__DIR__ . '/../.database')) {
            $this->removeFile(__DIR__ . '/../.database/testing.sqlite');
            $this->removeFile(__DIR__ . '/../.database/migrations');
            $this->removeFile(__DIR__ . '/../.database/export.sql');
            rmdir(__DIR__ . '/../.database');
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
<?php

namespace MichaelJennings\RefreshDatabase\Tests\Concerns;

use MichaelJennings\RefreshDatabase\DatabaseMigrator;

trait GeneratesConfig
{
    protected $configLocation = __DIR__ . '/../../.refresh-database.yml';


    /**
     * @before
     */
    public function addConfig()
    {
        $handle = fopen($this->configLocation, 'w');

        fwrite($handle, $this->getConfig());

        fclose($handle);

        app(DatabaseMigrator::class)->migrate();
    }

    /**
     * @after
     */
    public function removeConfig()
    {
        if (file_exists($this->configLocation)) {
            unlink($this->configLocation);
        }
    }

    protected function getConfig()
    {
        return "migrations:
  - tests/migrations

output: tests";
    }
}
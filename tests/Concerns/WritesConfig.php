<?php

namespace MichaelJennings\RefreshDatabase\Tests\Concerns;

trait WritesConfig
{
    /**
     * The location to store the config file.
     *
     * @var string
     */
    protected $configLocation = __DIR__ . '/../../.refresh-database.yml';

    /**
     * Write the config to a yml file.
     */
    protected function writeConfig()
    {
        $handle = fopen($this->configLocation, 'w');

        fwrite($handle, $this->getConfig());

        fclose($handle);
    }

    /**
     * Get the config.
     *
     * @return string
     */
    protected function getConfig()
    {
        return "migrations:
  - tests/migrations

output: tests";
    }

    /**
     * Remove the config file after if it exists.
     *
     * @after
     */
    public function removeConfig()
    {
        if (file_exists($this->configLocation)) {
            unlink($this->configLocation);
        }
    }
}
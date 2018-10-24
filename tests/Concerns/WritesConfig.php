<?php

namespace MichaelJennings\RefreshDatabase\Tests\Concerns;

trait WritesConfig
{
    use WritesFiles;

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
        $this->writeFile($this->configLocation, $this->getConfig());
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
        $this->deleteFile($this->configLocation);
    }
}
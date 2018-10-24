<?php

namespace MichaelJennings\RefreshDatabase\Tests\Concerns;

use MichaelJennings\RefreshDatabase\DatabaseMigrator;

trait GeneratesConfig
{
    use WritesConfig;

    /**
     * Write the config file and then run the database migrator.
     *
     * @before
     */
    public function addConfig()
    {
        $this->writeConfig();

        app(DatabaseMigrator::class)->migrate();
    }
}
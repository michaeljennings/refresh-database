<?php

namespace MichaelJennings\RefreshDatabase;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class MigrationServiceProvider extends ServiceProvider
{
    /**
     * The migration paths.
     *
     * @var array
     */
    protected $paths;

    public function __construct(Application $app, array $paths)
    {
        parent::__construct($app);

        $this->paths = $paths;
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom($this->paths);
    }
}

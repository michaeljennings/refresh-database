<?php

namespace MichaelJennings\RefreshDatabase;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Foundation\Application;
use Orchestra\Testbench\Concerns\CreatesApplication;

class DatabaseMigrator
{
    use CreatesApplication, JoinDirectories;

    /**
     * The config service.
     *
     * @var Config
     */
    protected $config;

    /**
     * The path to the sqlite database file.
     *
     * @var string
     */
    protected $databasePath;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Run all of the migrations to the sqlite file.
     */
    public function migrate()
    {
        $config = $this->config->values();
        $baseDir = $this->config->getBaseDirectory();

        $migrations = array_key_exists('migrations', $config) ? $config['migrations'] : [];
        $databaseName = array_key_exists('database_name', $config) ? $config['database_name'] : 'testing.sqlite';
        $cacheMigrations = array_key_exists('cache_migrations', $config) ? $config['cache_migrations'] : true;
        $output = $this->config->getOutputDirectory();

        if ( ! file_exists($output)) {
            mkdir($output);
        }

        $this->runMigrations($migrations, $baseDir, $output, $databaseName, $cacheMigrations);
        $this->dumpDatabase($output);
    }

    /**
     * Run the migrations into the sqlite file.
     *
     * @param array  $paths
     * @param string $baseDir
     * @param string $output
     * @param string $databaseName
     * @param bool   $cacheMigrations
     */
    protected function runMigrations(
        array $paths,
        string $baseDir,
        string $output,
        string $databaseName,
        bool $cacheMigrations
    ) {
        $this->databasePath = $this->join($output, $databaseName);

        $app = $this->createApplication();

        if ( ! file_exists($this->databasePath)) {
            $this->refreshDatabase($this->databasePath);

            if ($cacheMigrations) {
                $migrations = $this->migrationContents($app, $paths);

                $this->cacheMigrations($migrations);
            }
        } else {
            if ($cacheMigrations && ! $this->shouldRunMigrations($app, $paths)) {
                return;
            }

            $this->refreshDatabase($this->databasePath);
        }

        foreach ($paths as $path) {
            $app[Kernel::class]->call('migrate', [
                '--database' => 'sqlite',
                '--path' => $this->join($baseDir, $path),
                '--realpath' => true,
            ]);
        }
    }

    /**
     * Check if the migrations have changed.
     *
     * @param Application $app
     * @param array       $paths
     * @return bool
     */
    protected function shouldRunMigrations(Application $app, array $paths)
    {
        $cachePath = $this->getCachePath();
        $migrations = $this->migrationContents($app, $paths);

        if (file_exists($cachePath)) {
            $previousMigrations = file_get_contents($cachePath);

            if ($migrations == $previousMigrations) {
                return false;
            }

            unlink($cachePath);
        }

        $this->cacheMigrations($migrations);

        return true;
    }

    /**
     * Cache the migrations to a file.
     *
     * @param string $migrations
     */
    protected function cacheMigrations(string $migrations)
    {
        $cachePath = $this->getCachePath();

        $handle = fopen($cachePath, 'w');

        fwrite($handle, $migrations);
        fclose($handle);
    }

    /**
     * Get the contents of the file at each path.
     *
     * @param Application $app
     * @param array       $paths
     * @return string
     */
    protected function migrationContents(Application $app, array $paths)
    {
        $migrations = $app->make('migrator')->getMigrationFiles($paths);

        $content = '';

        foreach ($migrations as $migration) {
            $content .= file_get_contents($migration);
        }

        return $content;
    }

    /**
     * Dump the database to the output directory.
     *
     * @param string $output
     */
    protected function dumpDatabase(string $output)
    {
        $dump = $this->join($output, 'export.sql');

        shell_exec("sqlite3 $this->databasePath .dump > $dump");
    }

    /**
     * Refresh the sqlite database.
     *
     * @param string $filename
     * @return string
     */
    protected function refreshDatabase(string $filename)
    {
        if (file_exists($filename)) {
            unlink($filename);
        }

        $handle = fopen($filename, 'w');

        fclose($handle);

        return $filename;
    }

    /**
     * Get the path to cache the migrations in.
     *
     * @return string
     */
    protected function getCachePath()
    {
        return $this->join($this->config->getOutputDirectory(), 'migrations');
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => $this->databasePath,
            'prefix' => '',
        ]);
    }
}
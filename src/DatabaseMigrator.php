<?php

namespace MichaelJennings\RefreshDatabase;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Foundation\Application;
use Orchestra\Testbench\Concerns\CreatesApplication;

class DatabaseMigrator
{
    use CreatesApplication;

    /**
     * The path to the sqlite database file.
     *
     * @var string
     */
    protected $databasePath;

    /**
     * Run all of the migrations to the sqlite file.
     *
     * @param array  $config
     * @param string $baseDir
     */
    public function migrate(array $config, string $baseDir)
    {
        $migrations = array_key_exists('migrations', $config) ? $config['migrations'] : [];
        $databaseName = array_key_exists('database_name', $config) ? $config['database_name'] : 'testing.sqlite';
        $cacheMigrations = array_key_exists('cache_migrations', $config) ? $config['cache_migrations'] : true;
        $output = $this->getOutputDirectory($config, $baseDir);

        define('REFRESH_DATABASE_DIRECTORY', $output);

        if (! file_exists($output)) {
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
    protected function runMigrations(array $paths, string $baseDir, string $output, string $databaseName, bool $cacheMigrations)
    {
        $this->databasePath = $this->join($output, $databaseName);

        $app = $this->createApplication();

        if (! file_exists($this->databasePath)) {
            $this->refreshDatabase($this->databasePath);
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
        $migrations = $this->migrationContents($app->make('migrator')->getMigrationFiles($paths));
        $cachePath = $this->join(REFRESH_DATABASE_DIRECTORY, 'migrations');

        if (file_exists($cachePath)) {
            $previousMigrations = file_get_contents($cachePath);

            if ($migrations == $previousMigrations) {
                return false;
            }

            unlink($cachePath);
        }

        $handle = fopen($cachePath, 'w');

        fwrite($handle, $migrations);
        fclose($handle);

        return true;
    }

    /**
     * Get the contents of the file at each path.
     *
     * @param array $paths
     * @return string
     */
    protected function migrationContents(array $paths)
    {
        $contents = '';

        foreach ($paths as $path) {
            $contents .= file_get_contents($path);
        }

        return $contents;
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
    protected function refreshDatabase(string $filename): string
    {
        if (file_exists($filename)) {
            unlink($filename);
        }

        $handle = fopen($filename, 'w');

        fclose($handle);

        return $filename;
    }

    /**
     * Get the output directory to store the database dump in.
     *
     * @param array  $config
     * @param string $baseDir
     * @return string
     */
    protected function getOutputDirectory(array $config, string $baseDir)
    {
        if (isset($config['output'])) {
            $containsBaseDir = starts_with($baseDir, $config['output']);
            $output = $containsBaseDir ? $config['output'] : $this->join($baseDir, $config['output']);
        } else {
            $output = $baseDir;
        }

        return $this->join($output, '.database');
    }

    /**
     * Join the filename and directory.
     *
     * @param string $directory
     * @param string $filename
     * @return string
     */
    protected function join(string $directory, string $filename): string
    {
        return $directory . DIRECTORY_SEPARATOR . $filename;
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
            'driver'   => 'sqlite',
            'database' => $this->databasePath,
            'prefix'   => '',
        ]);
    }
}
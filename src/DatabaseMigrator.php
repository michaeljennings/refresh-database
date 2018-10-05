<?php

namespace MichaelJennings\RefreshDatabase;

use Illuminate\Contracts\Console\Kernel;
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
        $migrations = ! empty($config['migrations']) ? $config['migrations'] : [];
        $databaseName = ! empty($config['database_name']) ? $config['database_name'] : 'testing.sqlite';
        $output = $this->getOutputDirectory($config, $baseDir);

        define('REFRESH_DATABASE_DIRECTORY', $output);

        if (! file_exists($output)) {
            mkdir($output);
        }

        $this->databasePath = $this->refreshDatabase($output, $databaseName);

        $this->runMigrations($migrations, $baseDir);
        $this->dumpDatabase($output);
    }

    /**
     * Run the migrations into the sqlite file.
     *
     * @param array  $migrations
     * @param string $baseDir
     */
    protected function runMigrations(array $migrations, string $baseDir)
    {
        $app = $this->createApplication();

        foreach ($migrations as $migration) {
            $app[Kernel::class]->call('migrate', [
                '--database' => 'sqlite',
                '--path' => $this->join($baseDir, $migration),
                '--realpath' => true,
            ]);
        }
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
     * @param string $output
     * @param string $databaseName
     * @return string
     */
    protected function refreshDatabase(string $output, string $databaseName): string
    {
        $filename = $this->join($output, $databaseName);

        if (file_exists($filename)) {
            unlink($filename);
        }

        fopen($filename, 'w');

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
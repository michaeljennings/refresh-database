<?php

namespace MichaelJennings\RefreshDatabase;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Migrations\Migrator;
use Orchestra\Testbench\Concerns\CreatesApplication;
use Symfony\Component\Process\Process;

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
        $cacheMigrations = $this->config->get('cache_migrations', true);
        $output = $this->config->getOutputDirectory();

        $this->makeDirectory($output);

        if ($this->config->has('connections')) {
            foreach ($this->config->get('connections') as $name => $details) {
                $migrations = $this->config->get("connections.$name.migrations", []);
                $databaseName = $this->config->get("connections.$name.database_name", 'testing.sqlite');

                $this->migrateConnection($migrations, $databaseName, $cacheMigrations, $output, $name);
            }
        } else {
            $migrations = $this->config->get('migrations', []);
            $databaseName = $this->config->get('database_name', 'testing.sqlite');

            $this->migrateConnection($migrations, $databaseName, $cacheMigrations, $output);
        }
    }

    /**
     * Run the migrations for a database connection.
     *
     * @param array       $paths
     * @param string      $databaseName
     * @param bool        $cacheMigrations
     * @param string      $output
     * @param string|null $connection
     */
    protected function migrateConnection(
        array $paths,
        string $databaseName,
        bool $cacheMigrations,
        string $output,
        string $connection = null
    ) {
        $baseDir = $this->config->getBaseDirectory();

        if ($connection) {
            $output = $this->join($output, $connection);

            $this->makeDirectory($output);
        }

        $this->runMigrations($paths, $baseDir, $output, $databaseName, $cacheMigrations);
        $this->dumpDatabase($output);
    }

    /**
     * Run the migrations into the sqlite file.
     *
     * @param array $paths
     * @param string $baseDir
     * @param string $output
     * @param string $databaseName
     * @param bool $cacheMigrations
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

                $this->cacheMigrations($migrations, $output);
            }
        } else {
            if ($cacheMigrations && ! $this->shouldRunMigrations($app, $paths, $output)) {
                return;
            }

            $this->refreshDatabase($this->databasePath);
        }

        /** @var Migrator $migrator */
        $migrator = $app->make('migrator');

        $migrator->setConnection('sqlite');

        if ( ! $migrator->repositoryExists()) {
            $repository = $migrator->getRepository();

            $repository->createRepository();
        }

        $paths = array_map(function($path) use ($baseDir) {
            return realpath($this->join($baseDir, $path));
        }, $paths);

        $migrator->run($paths);
    }

    /**
     * Check if the migrations have changed.
     *
     * @param Application $app
     * @param array       $paths
     * @param string      $output
     * @return bool
     */
    protected function shouldRunMigrations(Application $app, array $paths, string $output)
    {
        $cachePath = $this->getCachePath($output);
        $migrations = $this->migrationContents($app, $paths);

        if (file_exists($cachePath)) {
            $previousMigrations = file_get_contents($cachePath);

            if ($migrations == $previousMigrations) {
                return false;
            }

            unlink($cachePath);
        }

        $this->cacheMigrations($migrations, $output);

        return true;
    }

    /**
     * Cache the migrations to a file.
     *
     * @param string $migrations
     * @param string $output
     */
    protected function cacheMigrations(string $migrations, string $output)
    {
        $cachePath = $this->getCachePath($output);

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
     * @param string $output
     * @return string
     */
    protected function getCachePath(string $output)
    {
        return $this->join($output, 'migrations');
    }

    /**
     * Make a directory if it does not exist.
     *
     * @param string $directory
     */
    protected function makeDirectory(string $directory)
    {
        if ( ! file_exists($directory)) {
            mkdir($directory);
        }
    }

    /**
     * @inheritdoc
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

    /**
     * @inheritdoc
     */
    public function getPackageProviders($app)
    {
        return $this->config->get('providers', []);
    }

    /**
     * @inheritdoc
     */
    protected function resolveApplicationExceptionHandler($app)
    {
        $app->singleton('Illuminate\Contracts\Debug\ExceptionHandler', ExceptionHandler::class);
    }
}

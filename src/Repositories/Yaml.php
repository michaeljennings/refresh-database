<?php

namespace MichaelJennings\RefreshDatabase\Repositories;

use Illuminate\Support\Arr;
use MichaelJennings\RefreshDatabase\Contracts\Config;
use MichaelJennings\RefreshDatabase\JoinDirectories;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;

class Yaml implements Config
{
    use JoinDirectories;

    /**
     * The config values.
     *
     * @var array
     */
    protected $values;

    /**
     * The base directory for the yml file.
     *
     * @var string|null
     */
    protected $baseDirectory;

    public function __construct(array $values = [], $baseDirectory = null)
    {
        $this->values = empty($values) ? SymfonyYaml::parse(file_get_contents($this->getPath())) : $values;
        $this->baseDirectory = $baseDirectory;
    }

    /**
     * Check if a config value is set.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_has($this->values, $key);
    }

    /**
     * Get a config value by its key.
     *
     * @param string $key
     * @param null   $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return Arr::get($this->values, $key, $default);
    }

    /**
     * Set a config value.
     *
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function set(string $key, $value): Config
    {
        $this->values[$key] = $value;

        return $this;
    }

    /**
     * Get the config values.
     *
     * @return array
     */
    public function values(): array
    {
        return $this->values;
    }

    /**
     * Get the path to the config file.
     *
     * @return string
     */
    public function getPath(): string
    {
        $root = null;
        $currentDirectory = __DIR__;

        do {
            $currentDirectory = dirname($currentDirectory);
            $config = $currentDirectory . '/.refresh-database.yml';

            if (file_exists($config)) {
                $root = $currentDirectory;
            }
        } while (is_null($root) && $currentDirectory != '/');

        return $config;
    }

    /**
     * Get the base directory path.
     *
     * @return string
     */
    public function getBaseDirectory(): string
    {
        if ( ! $this->baseDirectory) {
            $this->baseDirectory = dirname($this->getPath());
        }

        return $this->baseDirectory;
    }

    /**
     * Get the output directory to store the database dump in.
     *
     * @param mixed $parts
     * @return string
     */
    public function getOutputDirectory($parts = null): string
    {
        if ( ! is_array($parts)) {
            $parts = func_get_args();
        }

        $baseDirectory = $this->getBaseDirectory();

        if ($output = $this->get('output')) {
            $containsBaseDir = starts_with($baseDirectory, $output);
            $output = $containsBaseDir ? $output : $this->join($baseDirectory, $output);
        } else {
            $output = $baseDirectory;
        }

        return $this->join($output, '.database', ...$parts);
    }

    /**
     * Check if we should dump the database.
     *
     * @return bool
     */
    public function shouldDumpDatabase(): bool
    {
        return is_null(env('DUMP_DATABASE')) ?: (boolean)env('DUMP_DATABASE');
    }
}

<?php

namespace MichaelJennings\RefreshDatabase\Repositories;

use MichaelJennings\RefreshDatabase\JoinDirectories;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;

class Yaml
{
    use JoinDirectories;

    /**
     * The config values.
     *
     * @var array
     */
    protected $values;

    protected $baseDirectory;

    public function __construct(array $values = [], $baseDirectory = null)
    {
        $this->values = empty($values) ? SymfonyYaml::parse(file_get_contents($this->getPath())) : $values;
        $this->baseDirectory = $baseDirectory;
    }

    /**
     * Get the config values.
     *
     * @return array
     */
    public function values()
    {
        return $this->values;
    }

    /**
     * Get the path to the config file.
     *
     * @return string
     */
    public function getPath()
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
    public function getBaseDirectory()
    {
        if ( ! $this->baseDirectory) {
            $this->baseDirectory = dirname($this->getPath());
        }

        return $this->baseDirectory;
    }

    /**
     * Get the output directory to store the database dump in.
     *
     * @return string
     */
    public function getOutputDirectory()
    {
        $baseDirectory = $this->getBaseDirectory();

        if (isset($this->values['output'])) {
            $containsBaseDir = starts_with($baseDirectory, $this->values['output']);
            $output = $containsBaseDir ? $this->values['output'] : $this->join($baseDirectory, $this->values['output']);
        } else {
            $output = $baseDirectory;
        }

        return $this->join($output, '.database');
    }

    /**
     * Check if we should dump the database.
     *
     * @return bool
     */
    public function shouldDumpDatabase()
    {
        return is_null(env('DUMP_DATABASE')) ?: (boolean)env('DUMP_DATABASE');
    }
}
<?php

namespace MichaelJennings\RefreshDatabase\Contracts;

interface Config
{
    /**
     * Check if a config value is set.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Get a config value by its key.
     *
     * @param string $key
     * @param null   $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Get the config values.
     *
     * @return array
     */
    public function values(): array;

    /**
     * Get the path to the config file.
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Get the base directory path.
     *
     * @return string
     */
    public function getBaseDirectory(): string;

    /**
     * Get the output directory to store the database dump in.
     *
     * @param mixed $parts
     * @return string
     */
    public function getOutputDirectory($parts = null): string;

    /**
     * Check if we should dump the database.
     *
     * @return bool
     */
    public function shouldDumpDatabase(): bool;
}
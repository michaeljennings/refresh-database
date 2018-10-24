<?php

namespace MichaelJennings\RefreshDatabase;

use MichaelJennings\RefreshDatabase\Contracts\Config as ConfigContract;
use MichaelJennings\RefreshDatabase\Repositories\Yaml;

class Config
{
    /**
     * The config repository implementation.
     *
     * @var ConfigContract
     */
    protected $repository;

    public function __construct(ConfigContract $repository = null)
    {
        $this->repository = is_null($repository) ? app(Yaml::class) : $repository;
    }

    /**
     * Dynamically call methods on the config repository.
     *
     * @param string $method
     * @param array  $arguments
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        return $this->repository->$method(...$arguments);
    }

    /**
     * Dynamically call static methods on the config repository.
     *
     * @param string $method
     * @param array  $arguments
     * @return mixed
     */
    public static function __callStatic($method, array $arguments)
    {
        return (new static)->repository->$method(...$arguments);
    }
}
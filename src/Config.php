<?php

namespace MichaelJennings\RefreshDatabase;

use MichaelJennings\RefreshDatabase\Repositories\Yaml;

class Config
{
    /**
     * The yaml config repository.
     *
     * @var Yaml
     */
    protected $repository;

    public function __construct(Yaml $repository = null)
    {
        $this->repository = is_null($repository) ? app(Yaml::class) : $repository;
    }

    /**
     * @param string $method
     * @param array  $arguments
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        return $this->repository->$method(...$arguments);
    }

    /**
     * @param string $method
     * @param array  $arguments
     * @return mixed
     */
    public static function __callStatic($method, array $arguments)
    {
        return (new static)->repository->$method(...$arguments);
    }
}
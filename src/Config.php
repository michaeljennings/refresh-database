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

    public function __construct(Yaml $repository)
    {
        $this->repository = $repository;
    }

    public function __call($method, array $arguments)
    {
        return $this->repository->$method(...$arguments);
    }

    public static function __callStatic($method, array $arguments)
    {
        return (new static)->repository->$method(...$arguments);
    }
}
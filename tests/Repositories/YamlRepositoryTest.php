<?php

namespace MichaelJennings\RefreshDatabase\Tests\Repositories;

use MichaelJennings\RefreshDatabase\Repositories\Yaml;
use MichaelJennings\RefreshDatabase\Tests\Concerns\GeneratesConfig;
use MichaelJennings\RefreshDatabase\Tests\TestCase;

class YamlRepositoryTest extends TestCase
{
    use GeneratesConfig;

    /**
     * @var Yaml
     */
    protected $repository;

    public function loadFixtures()
    {
        $this->repository = app(Yaml::class);
    }

    /**
     * @test
     */
    public function it_gets_the_config_values()
    {
        $values = $this->repository->values();

        $this->assertInternalType('array', $values);
        $this->assertArrayHasKey('migrations', $values);
        $this->assertArrayHasKey('output', $values);
    }

    /**
     * @test
     */
    public function it_gets_the_config_path()
    {
        $this->assertEquals(realpath(__DIR__ . '/../../.refresh-database.yml'), $this->repository->getPath());
    }

    /**
     * @test
     */
    public function it_gets_the_base_dir()
    {
        $this->assertEquals(realpath(__DIR__ . '/../..'), $this->repository->getBaseDirectory());
    }

    /**
     * @test
     */
    public function it_gets_the_output_directory()
    {
        $this->assertEquals(realpath(__DIR__ . '/../../tests/.database'), $this->repository->getOutputDirectory());
    }

    /**
     * @test
     */
    public function it_checks_if_we_should_dump_the_database()
    {
        $this->assertTrue($this->repository->shouldDumpDatabase());
    }
}
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
    public function it_checks_if_a_config_value_is_set()
    {
        $this->assertTrue($this->repository->has('migrations'));
        $this->assertFalse($this->repository->has('not_set'));
    }

    /**
     * @test
     */
    public function it_gets_a_value_from_the_config()
    {
        $output = $this->repository->get('output');

        $this->assertEquals('tests', $output);
    }

    /**
     * @test
     */
    public function it_uses_the_default_if_a_value_is_not_set()
    {
        $this->assertNull($this->repository->get('not_set'));
        $this->assertEquals('TEST', $this->repository->get('not_set', 'TEST'));
    }

    /**
     * @test
     */
    public function it_sets_a_config_value()
    {
        $this->assertInstanceOf(Yaml::class, $this->repository->set('foo', 'bar'));
        $this->assertEquals('bar', $this->repository->get('foo'));
    }

    /**
     * @test
     */
    public function it_gets_the_config_values()
    {
        $values = $this->repository->values();

        $this->assertIsArray($values);
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
    public function it_gets_the_base_directory_if_an_output_directory_is_not_set()
    {
        $this->repository->set('output', null);

        $this->assertEquals(realpath(__DIR__ . '/../..') . '/.database', $this->repository->getOutputDirectory());
    }

    /**
     * @test
     */
    public function it_appends_to_the_output_directory()
    {
        $this->assertEquals(realpath(__DIR__ . '/../../tests/.database/export.sql'), $this->repository->getOutputDirectory('export.sql'));
    }

    /**
     * @test
     */
    public function it_checks_if_we_should_dump_the_database()
    {
        $this->assertTrue($this->repository->shouldDumpDatabase());
    }
}

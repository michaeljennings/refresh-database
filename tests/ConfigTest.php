<?php

namespace MichaelJennings\RefreshDatabase\Tests;

use MichaelJennings\RefreshDatabase\Config;
use MichaelJennings\RefreshDatabase\Tests\Concerns\GeneratesConfig;

class ConfigTest extends TestCase
{
    use GeneratesConfig;

    /**
     * @test
     */
    public function it_dynamically_calls_a_method()
    {
        $config = app(Config::class);

        $this->assertTrue($config->shouldDumpDatabase());
    }

    /**
     * @test
     */
    public function it_dynamically_calls_a_static_method()
    {
        $this->assertTrue(Config::shouldDumpDatabase());
    }
}
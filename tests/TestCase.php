<?php

namespace MichaelJennings\RefreshDatabase\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();

        if (method_exists($this, 'loadFixtures')) {
            $this->loadFixtures();
        }
    }
}

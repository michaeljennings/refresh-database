<?php

namespace MichaelJennings\RefreshDatabase\Tests;

use Exception;
use MichaelJennings\RefreshDatabase\ExceptionHandler;
use Mockery;
use Symfony\Component\Console\Output\ConsoleOutput;

class ExceptionHandlerTest extends TestCase
{
    /**
     * @test
     */
    public function it_sets_the_output_verbosity()
    {
        $output = Mockery::mock(ConsoleOutput::class, [
            'writeln' => null,
            'getVerbosity' => ConsoleOutput::VERBOSITY_VERBOSE
        ]);

        $output->shouldReceive('setVerbosity')->once();

        $handler = app(ExceptionHandler::class);

        $handler->renderForConsole($output, new Exception('Testing'));
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
    {
        parent::tearDown();

        Mockery::close();
    }
}
<?php

namespace MichaelJennings\RefreshDatabase;

use Orchestra\Testbench\Exceptions\Handler;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class ExceptionHandler extends Handler
{
    /**
     * Render an exception to the console.
     *
     * @param  OutputInterface  $output
     * @param  Throwable  $e
     * @return void
     */
    public function renderForConsole($output, Throwable $e)
    {
        // When running the artisan command we cannot set the verbosity, which
        // means the user cannot see the stack trace when an error occurs. To
        // get around this we set the maximum verbosity so we always get the
        // stack trace.
        $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);

        parent::renderForConsole($output, $e);
    }
}

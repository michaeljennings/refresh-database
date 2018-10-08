<?php

$root = null;
$currentDirectory = __DIR__;

// Find the vendor directory.
do {
    $currentDirectory = dirname($currentDirectory);
    $vendor = $currentDirectory . '/vendor';

    if (file_exists($vendor)) {
        $root = $currentDirectory;
    }
} while (is_null($root) && $currentDirectory != '/');

// Require the composer autoload
require_once $vendor . DIRECTORY_SEPARATOR. 'autoload.php';

// Check if we should dump the database to a file.
if (\MichaelJennings\RefreshDatabase\Config::shouldDumpDatabase()) {
    // Run the migrations.
    app(MichaelJennings\RefreshDatabase\DatabaseMigrator::class)->migrate();
}
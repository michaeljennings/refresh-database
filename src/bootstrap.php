<?php

// Find the config file and the base directory.
$path = isset($path) ? $path : __DIR__ . '/../tests.yml';
$baseDir = isset($baseDir) ? $baseDir : realpath(dirname($path));

// Load the composer autoload.
require_once $baseDir . DIRECTORY_SEPARATOR. 'vendor/autoload.php';

// Check if we should dump the database to a file.
if (should_dump_database()) {
    $values = Symfony\Component\Yaml\Yaml::parse(file_get_contents($path));

    // Run the migrations.
    app(KestrelSAAS\Core\Tests\DatabaseMigrator::class)->migrate($values, $baseDir);
}
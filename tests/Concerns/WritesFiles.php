<?php

namespace MichaelJennings\RefreshDatabase\Tests\Concerns;

trait WritesFiles
{
    /**
     * Write the contents to a new file in the location.
     *
     * @param string $location
     * @param string $contents
     */
    public function writeFile(string $location, string $contents)
    {
        $handle = fopen($location, 'w');

        fwrite($handle, $contents);

        fclose($handle);
    }

    /**
     * Delete the file if it exists.
     *
     * @param string $location
     */
    public function deleteFile(string $location)
    {
        if (file_exists($location)) {
            unlink($location);
        }
    }
}
<?php

namespace MichaelJennings\RefreshDatabase;

trait JoinDirectories
{
    /**
     * Join the filename and directory.
     *
     * @param string $directory
     * @param string $filename
     * @return string
     */
    protected function join(string $directory, string $filename)
    {
        return $directory . DIRECTORY_SEPARATOR . $filename;
    }
}
<?php

namespace MichaelJennings\RefreshDatabase;

trait JoinDirectories
{
    /**
     * Join the filename and directory.
     *
     * @param $parts
     * @return string
     */
    protected function join($parts)
    {
        if ( ! is_array($parts)) {
            $parts = func_get_args();
        }

        return implode(DIRECTORY_SEPARATOR, $parts);
    }
}
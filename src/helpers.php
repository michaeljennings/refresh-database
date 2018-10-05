<?php

if ( ! function_exists('should_dump_database')) {

    /**
     * Check if we should dump the database.
     *
     * @return bool
     */
    function should_dump_database()
    {
        return is_null(env('DUMP_DATABASE')) ?: (boolean)env('DUMP_DATABASE');
    }

}
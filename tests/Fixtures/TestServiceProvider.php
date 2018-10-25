<?php

namespace MichaelJennings\RefreshDatabase\Tests\Fixtures;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Fluent;
use Illuminate\Support\ServiceProvider;

class TestServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Blueprint::macro('active', function(): Fluent {
            return self::tinyInteger('active')->default(0);
        });
    }
}
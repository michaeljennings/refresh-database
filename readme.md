# Refresh Database [![Build Status](https://travis-ci.org/michaeljennings/refresh-database.svg?branch=master)](https://travis-ci.org/michaeljennings/refresh-database) [![Latest Stable Version](https://poser.pugx.org/michaeljennings/refresh-database/v/stable)](https://packagist.org/packages/michaeljennings/refresh-database)

When running database tests in laravel one of the biggest overheads is running all of your migrations, the bigger your application gets the longer it takes.

This package speeds up your tests by running the migrations once into an sqlite file, then before each test rather than running migrations it loads the database structure from a database dump.

At the minute this package only works with phpunit and sqlite.

- [Installation](#installation)
- [Usage](#usage)
- [Environments](#environments)
- [Migration Cache](#migration-cache)

## Installation

To install the package run:

```
composer require michaeljennings/refresh-database --dev
```

Or add `michaeljennings/refresh-database` to the require-dev section of your `composer.json`.

```json
...
"require-dev": {
  "michaeljennings/refresh-database": "^1.0"
},
...
```

Then run `composer update` to install the package.

## Usage

Firstly we need to tell phpunit to bootstrap from the package `bootstrap.php` file.

```xml
<phpunit bootstrap="./vendor/michaeljennings/refresh-database/src/bootstrap.php">
  <testsuites>
    <testsuite name="Standard Test Suite">
      <directory>tests</directory>
    </testsuite>
  </testsuites>
</phpunit>
```

By default the bootstrapper will look for a config file called `.refresh-database.yml` at the same level as your vendor directory.

So your project may looks something like this:

```
tests/
vendor/
.refresh-database.yml
```

In the `.refresh-database.yml` you can define the directories we should load migrations from and where the directory to store the database dump in.

A simple config file will look like the following:

```yml
migrations:
  - database/migrations
  - vendor/other/package/database/migrations

output: tests
```

When you run your tests we will create a `.database` directory in the output directory. All of the migrations will be run into a sqlite database and then a database dump will be taken for the database structure.

Once you have your config setup you then just need to use the `MichaelJennings\RefreshDatabase\RefreshDatabase` trait in your test or test case.

```php
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use MichaelJennings\RefreshDatabase\RefreshDatabase;

class TestCase extends BaseTestCase
{
    use RefreshDatabase;
}
```

## Environments

Occasionally you might find you to want to disable the database dump in certain environments.

To disable the database dump you can set a `DUMP_DATABASE` environment variable and set it to false.

```xml
<phpunit bootstrap="./vendor/michaeljennings/refresh-database/src/bootstrap.php">
  <testsuites>
    <testsuite name="Standard Test Suite">
      <directory>tests</directory>
    </testsuite>
  </testsuites>
  <php>
    <env name="DUMP_DATABASE" value="false"></env>
  </php>
</phpunit>
```

## Migration Cache

By default this package will cache the contents of your migrations so that it only rebuilds the database if it needs to.

If you want to rebuild the database each time you run your tests you set the `cache_migrations` property to false in your .refresh-database.yml file.

```yml
migrations:
  - database/migrations
  - vendor/other/package/database/migrations

output: tests
cache_migrations: false
```

If you find the package is not rebuilding the database when you think it should be delete the .database directory from your output directory.

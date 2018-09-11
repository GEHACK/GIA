<?php

namespace App\Misc\DatabaseHelpers;

/**
 * @see \Illuminate\Database\Schema\Builder
 */
class Schema extends \Illuminate\Support\Facades\Facade {
    /**
     * Get a schema builder instance for a connection.
     *
     * @param  string $name
     * @return \Illuminate\Database\Schema\Builder
     */
    public static function connection($name) {
        $schema = static::$app['db']->connection($name)->getSchemaBuilder();

        $schema->blueprintResolver(function ($table, $callback) {
            return new Blueprint($table, $callback);
        });

        return $schema;

        return static::$app['db']->connection($name)->getSchemaBuilder();
    }

    /**
     * Get a schema builder instance for the default connection.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    protected static function getFacadeAccessor() {
        $schema = static::$app['db']->connection()->getSchemaBuilder();

        $schema->blueprintResolver(function ($table, $callback) {
            return new Blueprint($table, $callback);
        });

        return $schema;
    }
}

<?php

declare(strict_types=1);

namespace Pollora\Metabox\CustomTable;

use InvalidArgumentException;

/**
 * Name of a custom table (MB Custom Table), prefixed with the WordPress table prefix.
 */
final class TableName
{
    /**
     * @param  string  $table  The table name without prefix, or a class with a getTable() method, such as an Eloquent model.
     * @param  bool  $prefix  Whether the WordPress table prefix is added.
     */
    public function __construct(private string $table, private bool $prefix = true)
    {
        if (! self::isModelClass($table) && ! preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            throw new InvalidArgumentException("The table name '{$table}' can only contain letters, digits and underscores.");
        }
    }

    /**
     * The full table name, resolved when Meta Box reads it: the prefix is the one of the current site.
     */
    public function resolve(): string
    {
        global $wpdb;

        $table = $this->table;

        if (class_exists($table) && method_exists($table, 'getTable')) {
            $table = (new $table)->getTable();
        }

        return $this->prefix ? $wpdb->prefix.$table : $table;
    }

    private static function isModelClass(string $table): bool
    {
        return class_exists($table) && method_exists($table, 'getTable');
    }
}

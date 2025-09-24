<?php
/**
 * UpsellWP
 *
 * @package   checkout-upsell-woocommerce
 * @author    Anantharaj B <anantharaj@flycart.org>
 * @copyright 2024 UpsellWP
 * @license   GPL-3.0-or-later
 * @link      https://upsellwp.com
 */

namespace CUW\App\Models;

defined('ABSPATH') || exit;

use CUW\App\Helpers\Config;

abstract class Model
{
    /**
     * Table name and output type
     *
     * @var string
     */
    const TABLE_NAME = '', OUTPUT_TYPE = OBJECT;

    /**
     * To get wpdb instance
     */
    public static function db()
    {
        global $wpdb;
        return $wpdb;
    }

    /**
     * Create table
     *
     * @return void
     */
    public abstract function create();

    /**
     * Get table name
     */
    public static function getTableName()
    {
        $db_prefix = self::db()->prefix;
        $app_prefix = Config::get('plugin.prefix', 'cuw_');
        return $db_prefix . $app_prefix . static::TABLE_NAME;
    }

    /**
     * Get charset collate
     */
    public static function getCharsetCollate()
    {
        return self::db()->get_charset_collate();
    }

    /**
     * Execute an query (to modify table)
     *
     * @param string $query
     * @return int|bool
     */
    protected static function execQuery($query)
    {
        return self::db()->query(str_replace('{table}', self::getTableName(), $query));
    }

    /**
     * Execute a database query (to modify database)
     *
     * @param string $query
     * @return array
     */
    protected static function execDBQuery($query)
    {
        if (!function_exists('dbDelta')) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        }
        $search = ['{table}', '{charset_collate}'];
        $replace = [self::getTableName(), self::getCharsetCollate()];
        return dbDelta(str_replace($search, $replace, $query));
    }

    /**
     * Get Row
     *
     * @param array|string $where
     * @param array|null $where_format
     * @param array|null $columns
     * @return array
     */
    public static function getRow($where, $where_format = null, $columns = null)
    {
        return self::db()->get_row(self::prepareSelectQuery($where, $where_format, $columns), static::OUTPUT_TYPE);
    }

    /**
     * Get Row by ID
     *
     * @param int $id
     * @param array|null $columns
     * @return array
     */
    public static function getRowById($id, $columns = null)
    {
        return self::getRow(['id' => $id], ['%d'], $columns);
    }

    /**
     * Get Rows
     *
     * @param array|string $where
     * @param array|null $where_format
     * @param array|null $columns
     * @param array|null $args
     * @return array
     */
    public static function getRows($where, $where_format = null, $columns = null, $args = null)
    {
        return self::db()->get_results(self::prepareSelectQuery($where, $where_format, $columns, $args), static::OUTPUT_TYPE);
    }

    /**
     * Retrieves one variable.
     *
     * @param string $query
     * @param array $values
     * @return string|null
     */
    public static function getScalar($query, $values = [])
    {
        return self::db()->get_var(self::prepareQuery($query, $values));
    }

    /**
     * Retrieves one row.
     *
     * @param string $query
     * @param array $values
     * @return array|object|null
     */
    public static function getResult($query, $values = [])
    {
        return self::db()->get_row(self::prepareQuery($query, $values), static::OUTPUT_TYPE);
    }

    /**
     * Retrieves an entire SQL result set.
     *
     * @param string $query
     * @param array $values
     * @param string $pluck
     * @return array|null
     */
    public static function getResults($query, $values = [], $pluck = '')
    {
        return self::pluck(self::db()->get_results(self::prepareQuery($query, $values), static::OUTPUT_TYPE), $pluck);
    }

    /**
     * Plucks a certain field out of each object or array in an array.
     *
     * @param array|null $result
     * @param string $column
     * @return array|null
     */
    public static function pluck($result, $column)
    {
        if (!empty($column)) {
            if (!empty($result) && is_array($result) && function_exists('wp_list_pluck')) {
                return wp_list_pluck($result, $column);
            } else {
                return [];
            }
        }
        return $result;
    }

    /**
     * Prepare query.
     *
     * @param string $query
     * @param array $values
     * @return string
     */
    protected static function prepareQuery($query, $values = [])
    {
        $query = str_replace('{table}', self::getTableName(), $query);
        if (!empty($values) && is_array($values)) {
            $query = self::db()->prepare($query, $values);
        }
        return $query;
    }

    /**
     * Prepare select query
     *
     * @param array $where
     * @param array|null $where_format
     * @param array|null $columns
     * @param array|null $args
     * @return string
     */
    protected static function prepareSelectQuery($where, $where_format, $columns, $args = null)
    {
        $table = self::getTableName();
        if (is_array($columns) && !empty($columns)) {
            $fields = implode(', ', array_map(function ($column) {
                return "`$column`";
            }, $columns));
        } else {
            $fields = '*';
        }

        if (!empty($where)) {
            $where_query = self::prepareWhereQuery($where, $where_format);
            $query = "SELECT $fields FROM $table $where_query";
        } else {
            $query = "SELECT $fields FROM $table";
        }

        if (is_array($args)) {
            if (isset($args['like']) && is_array($args['like']) && !empty($args['like'])) {
                $like_operator = 'OR';
                if (isset($args['like']['operator'])) {
                    $like_operator = $args['like']['operator'];
                    unset($args['like']['operator']);
                }
                $like_queries = [];
                foreach ($args['like'] as $field => $keyword) {
                    $keyword = self::db()->esc_like($keyword);
                    $like_queries[] = "`$field` LIKE '%$keyword%'";
                }
                $query = self::addWhereQuery($query, "(" . implode(" " . $like_operator . " ", $like_queries) . ")");
            }

            if (isset($args['order_by'])) {
                $order_by = $args['order_by'];
                $sort = 'ASC';
                if (isset($args['sort']) && strtoupper($args['sort']) == 'DESC') {
                    $sort = 'DESC';
                }
                $query .= " ORDER BY `$order_by` $sort";
            }

            if (isset($args['limit'])) {
                $limit = $args['limit'];
                $query .= " LIMIT $limit";
            }

            if (isset($args['offset'])) {
                $offset = $args['offset'];
                $query .= " OFFSET $offset";
            }
        }

        return $query . ';';
    }

    /**
     * Add where query
     *
     * @param string $query
     * @param string $where_query
     * @param string $operator
     * @return string
     */
    public static function addWhereQuery($query, $where_query, $operator = 'AND')
    {
        $query .= strpos($query, 'WHERE') === false ? " WHERE " : " $operator ";
        return $query . $where_query;
    }

    /**
     * Prepare where query
     *
     * @param array $where
     * @param array|null $where_format
     * @return string
     */
    protected static function prepareWhereQuery($where, $where_format)
    {
        if (is_string($where)) {
            return $where;
        }

        $i = 0;
        $data = [];
        $values = [];
        $conditions = [];
        if (is_array($where) && !empty($where)) {
            foreach ($where as $field => $value) {
                if (isset($where_format) && isset($where_format[$i])) {
                    $format = $where_format[$i];
                    $i++;
                } else {
                    $format = "%s";
                }
                $data[$field]['value'] = $value;
                $data[$field]['format'] = $format;
            }
        }

        foreach ($data as $field => $value) {
            if (is_null($value['value'])) {
                $conditions[] = "`$field` IS NULL";
                continue;
            }
            $conditions[] = "$field = " . $value['format'];
            $values[] = $value['value'];
        }
        $conditions = implode(' AND ', $conditions);
        return self::db()->prepare("WHERE $conditions", $values);
    }

    /**
     * Inserts a row into the table
     *
     * @param array $data
     * @param array $format
     * @param bool $return_id
     * @return int|false
     */
    public static function insert($data, $format = null, $return_id = true)
    {
        $result = self::db()->insert(self::getTableName(), $data, $format);
        return $result && $return_id ? self::db()->insert_id : $result;
    }

    /**
     * Updates a row into the table
     *
     * @param array $data
     * @param array $where
     * @param array|string $format
     * @param array|string $where_format
     * @return int|false
     */
    public static function update($data, $where, $format = null, $where_format = null)
    {
        return self::db()->update(self::getTableName(), $data, $where, $format, $where_format);
    }

    /**
     * Updates a row into the table by ID
     *
     * @param int $id
     * @param array $data
     * @param array|string $format
     * @return int|false
     */
    public static function updateById($id, $data, $format = null)
    {
        return self::update($data, ['id' => $id], $format, ['%d']);
    }

    /**
     * Deletes a row into the table
     *
     * @param array $where
     * @param array|string $where_format
     * @return int|false
     */
    public static function delete($where, $where_format = null)
    {
        return self::db()->delete(self::getTableName(), $where, $where_format);
    }

    /**
     * Deletes a row into the table by ID
     *
     * @param int $id
     * @return int|false
     */
    public static function deleteById($id)
    {
        return self::delete(['id' => $id], ['%d']);
    }

    /**
     * Drop the table
     */
    public function drop()
    {
        return self::execDBQuery("DROP IF EXISTS TABLE {table};");
    }

    /**
     * Merge extra data (like action done at and action done by)
     *
     * @param array $data
     * @param array $format
     * @param string $action
     * @return array
     */
    protected static function mergeExtraData($data, $format, $action)
    {
        if ($action == 'create') {
            $data = array_merge($data, [
                'created_at' => current_time('timestamp', true),
                'created_by' => get_current_user_id(),
            ]);
            $format = array_merge($format, ['%d', '%d']);
        } elseif ($action == 'update') {
            $data = array_merge($data, [
                'updated_at' => current_time('timestamp', true),
                'updated_by' => get_current_user_id(),
            ]);
            $format = array_merge($format, ['%d', '%d']);
        }
        return [$data, $format];
    }
}
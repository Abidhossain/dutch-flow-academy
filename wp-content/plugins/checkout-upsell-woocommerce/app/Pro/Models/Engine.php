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

namespace CUW\App\Pro\Models;

use CUW\App\Helpers\Config;
use CUW\App\Helpers\Functions;
use CUW\App\Helpers\WP;
use CUW\App\Models\Model;

defined('ABSPATH') || exit;

class Engine extends Model
{
    /**
     * Table name and output type
     *
     * @var string
     */
    const TABLE_NAME = 'engines', OUTPUT_TYPE = ARRAY_A;

    /**
     * Create product recommendation table
     */
    public function create()
    {
        $query = "CREATE TABLE {table} (
                 `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                 `uuid` varchar(8) NOT NULL,
                 `type` varchar(32) NOT NULL,
                 `enabled` tinyint(1) DEFAULT 0,
                 `title` varchar(255) DEFAULT NULL,
                 `filters` text DEFAULT NULL,
                 `amplifiers` text DEFAULT NULL,
                 `hidden` tinyint(1) DEFAULT 0,
                 `created_at` bigint(20) unsigned DEFAULT NULL,
                 `created_by` bigint(20) unsigned DEFAULT NULL,
                 `updated_at` bigint(20) unsigned DEFAULT NULL,
                 `updated_by` bigint(20) unsigned DEFAULT NULL,
                 PRIMARY KEY (id),
                 UNIQUE KEY uuid (`uuid`)
            ) {charset_collate};";

        self::execDBQuery($query); // to create or update table
    }

    /**
     * Get all recommendation engines
     *
     * @param array $args
     * @return array
     */
    public static function all($args = [])
    {
        $where = '';
        if (!empty($args['status'])) {
            $status = $args['status'];
            if ($status == 'enabled' || $status == 'active') {
                $where = "WHERE enabled = 1";
            } elseif ($status == 'disabled' || $status == 'draft') {
                $where = "WHERE enabled = 0";
            }
        }
        if (!empty($args['type']) && is_string($args['type'])) {
            $where = self::addWhereQuery($where, self::db()->prepare("type = %s", [$args['type']]));
        }

        if (isset($args['hidden'])) {
            $where = self::addWhereQuery($where, self::db()->prepare("hidden = %d", $args['hidden']));
        }

        if (!empty($args['types']) && is_array($args['types'])) {
            $where = self::addWhereQuery($where, 'type IN (' . implode(',', $args['types']) . ')');
        }
        $columns = !empty($args['columns']) && is_array($args['columns']) ? $args['columns'] : null;
        $engines = self::getRows($where, null, $columns, $args);
        if (is_array($engines)) {
            foreach ($engines as $key => $engine) {
                $engines[$key] = self::parseData($engine);
            }
        }
        return $engines;
    }

    /**
     * Get engines count
     *
     * @return int
     */
    public static function getCount($filter_hidden = false)
    {
        if ($filter_hidden) {
            return (int)self::getScalar("SELECT COUNT(`id`) FROM {table} WHERE hidden = 0");
        }
        return (int)self::getScalar("SELECT COUNT(`id`) FROM {table}");
    }

    /**
     * Parse row data.
     *
     * @param array $engine
     * @return array
     */
    private static function parseData($engine)
    {
        $json_columns = ['filters', 'amplifiers'];
        foreach ($json_columns as $column) {
            if (array_key_exists($column, $engine)) {
                $engine[$column] = isset($engine[$column]) ? json_decode($engine[$column], true) : [];
            }
        }
        return $engine;
    }

    /**
     * Save campaign
     *
     * @param array $engine
     * @return array|false
     */
    public static function save($engine)
    {
        if (isset($engine['id'])) {
            $id = $engine['id'];
            $type = !empty($engine['type']) ? $engine['type'] : '';

            $data = [
                'type' => $type,
                'enabled' => !empty($engine['enabled']) ? 1 : 0,
                'title' => !empty($engine['title']) ? $engine['title'] : 'Untitled',
                'filters' => isset($engine['engine_filters']) ? json_encode($engine['engine_filters']) : null,
                'amplifiers' => isset($engine['engine_amplifiers']) ? json_encode($engine['engine_amplifiers']) : null,
                'hidden' => !empty($engine['hidden']) ? 1 : 0,
            ];

            $format = ['%s', '%d', '%s', '%s', '%s', '%d'];
            if ($id == 0) {
                $data['uuid'] = Functions::generateUuid(8);
                $format[] = '%s';
                list($data, $format) = self::mergeExtraData($data, $format, 'create');
                if (!($id = self::insert($data, $format))) {
                    return false;
                }
            } else {
                list($data, $format) = self::mergeExtraData($data, $format, 'update');
                if (!self::updateById($id, $data, $format)) {
                    return false;
                }

                self::clearCache($id);
            }
            return ['id' => $id];
        }
        return false;
    }

    /**
     * Get the engine.
     *
     * @param int|string $id_or_type
     * @param array|null $columns
     * @return array|false
     */
    public static function get($id_or_type, $columns = null)
    {
        if (is_numeric($id_or_type)) {
            $engine = self::getRowById($id_or_type, $columns);
        } else {
            $engine = self::getRow(['type' => $id_or_type], ['%s'], $columns);
        }
        if ($engine) {
            return self::parseData($engine);
        }
        return false;
    }

    /**
     * Duplicate engine.
     *
     * @param int $engine_id
     * @return array|false
     */
    public static function duplicate($engine_id)
    {
        $engine = self::get($engine_id);
        if (!empty($engine)) {
            $data = [
                'id' => 0,
                'type' => $engine['type'],
                'enabled' => $engine['enabled'],
                'title' => $engine['title'] . ' – copy',
                'engine_filters' => $engine['filters'],
                'engine_amplifiers' => $engine['amplifiers'],
                'hidden' => $engine['hidden'],
            ];
            return self::save($data);
        }
        return false;
    }

    /**
     * Check if the engine caching is enabled or not.
     *
     * @return bool
     */
    public static function isCachingEnabled()
    {
        return !empty(Config::getSetting('engine_cache_enabled'));
    }

    /**
     * Returns cached data if exists.
     *
     * @param int $engine_id
     * @param string $key
     * @return mixed
     */
    public static function getCache($engine_id, $key)
    {
        return get_transient('cuw_engine_' . $engine_id . '_' . $key);
    }

    /**
     * Set cached data.
     *
     * @param int $engine_id
     * @param string $key
     * @param mixed $data
     * @param int|null $expiration
     * @return bool
     */
    public static function setCache($engine_id, $key, $data, $expiration = null)
    {
        $expiration = $expiration ?? (int)Config::getSetting('engine_cache_expiration') * 60 * 60;
        return set_transient('cuw_engine_' . $engine_id . '_' . $key, $data, $expiration);
    }

    /**
     * Clear cache.
     *
     * @param int|string $engine_id
     * @param string $key
     * @return void
     */
    public static function clearCache($engine_id, $key = '')
    {
        if (is_numeric($engine_id) || $engine_id == 'all') {
            $option_table = self::db()->prefix . 'options';
            $option_name_like = 'cuw_engine_' . ($engine_id != 'all' ? $engine_id . '_' . $key : '');
            self::execQuery("DELETE FROM $option_table WHERE option_name LIKE '_transient_$option_name_like%';");
            self::execQuery("DELETE FROM $option_table WHERE option_name LIKE '_transient_timeout_$option_name_like%';");
        }
    }

    /**
     * Get active engines count.
     *
     * @return int
     */
    public static function getActiveEnginesCount()
    {
        return (int)self::getScalar("SELECT COUNT(`id`) FROM {table} WHERE `enabled` = 1");
    }
}

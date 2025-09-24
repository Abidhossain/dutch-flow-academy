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

use CUW\App\Helpers\Campaign as Helper;
use CUW\App\Helpers\Functions;
use CUW\App\Helpers\Plugin;
use CUW\App\Helpers\WP;

defined('ABSPATH') || exit;

class Campaign extends Model
{
    /**
     * Table name and output type
     *
     * @var string
     */
    const TABLE_NAME = 'campaigns', OUTPUT_TYPE = ARRAY_A;

    /**
     * To hold active campaign types.
     *
     * @var array
     */
    private static $active_campaigns;

    /**
     * Create campaigns table
     */
    public function create()
    {
        /**
         * Since 1.2.0 add `uuid`, `type` columns
         * Since 1.3.0 add `filters` column
         * Since 1.3.0 deprecated `offer_data` column. use `data` column instead
         * Since 1.3.3 add `usage_limit`, `usage_limit_per_user`, `usage_count` columns
         */
        $query = "CREATE TABLE {table} (
                 `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                 `uuid` varchar(8) DEFAULT NULL,
                 `enabled` tinyint(1) DEFAULT 0,
                 `priority` int(11) DEFAULT NULL,
                 `type` varchar(32) DEFAULT NULL,
                 `title` varchar(255) DEFAULT NULL,
                 `filters` text DEFAULT NULL,
                 `conditions` text DEFAULT NULL,
                 `data` text DEFAULT NULL,
                 `start_on` bigint(20) unsigned DEFAULT NULL,
                 `end_on` bigint(20) unsigned DEFAULT NULL,
                 `usage_limit` int(11) DEFAULT 0,
                 `usage_limit_per_user` int(11) DEFAULT 0,
                 `usage_count` int(11) DEFAULT 0,
                 `created_at` bigint(20) unsigned DEFAULT NULL,
                 `created_by` bigint(20) unsigned DEFAULT NULL,
                 `updated_at` bigint(20) unsigned DEFAULT NULL,
                 `updated_by` bigint(20) unsigned DEFAULT NULL,
                 `offer_data` text DEFAULT NULL,
                 PRIMARY KEY (id),
                 UNIQUE KEY uuid (`uuid`)
            ) {charset_collate};";

        self::execDBQuery($query); // to create or update table
        self::runPatch(); // to run patch
    }

    /**
     * Run patch to load missing data
     */
    private function runPatch()
    {
        // Since 1.2.0 fill empty type column with pre_purchase
        self::execQuery("UPDATE {table} SET `type` = 'pre_purchase' WHERE `type` IS NULL");

        // Since 1.2.0 fill empty uuid column with unique uuid
        $rows = self::getResults("SELECT `id` FROM {table} WHERE `uuid` IS NULL");
        if ($rows && is_array($rows)) {
            foreach ($rows as $row) {
                self::updateById($row['id'], ['uuid' => Functions::generateUuid(8)], ['%s']);
            }
        }

        // Since 1.3.0 change offer_data column data to data column
        self::execQuery("UPDATE {table} SET `data` = `offer_data` WHERE `data` IS NULL");

        // Since 1.4.0 replace pre_purchase type with checkout_upsells
        self::execQuery("UPDATE {table} SET `type` = 'checkout_upsells' WHERE `type` = 'pre_purchase'");
    }

    /**
     * Get the campaign.
     *
     * @param int|string $id_or_type
     * @param array|null $columns
     * @param bool $with_offers
     * @param array|null $offer_columns
     * @return array|false
     */
    public static function get($id_or_type, $columns = null, $with_offers = false, $offer_columns = null)
    {
        if (is_numeric($id_or_type)) {
            $campaign = self::getRowById($id_or_type, $columns);
        } else {
            $campaign = self::getRow(['type' => $id_or_type], ['%s'], $columns);
        }
        if ($campaign) {
            $campaign = self::parseData($campaign);
            if ($with_offers) {
                $campaign['offers'] = Offer::all(['campaign_id' => $campaign['id'], 'columns' => $offer_columns]);
            }
            return $campaign;
        }
        return false;
    }

    /**
     * Get all campaigns
     *
     * @param array $args
     * @return array
     */
    public static function all($args = [])
    {
        $where = '';
        if (!empty($args['status']) && is_string($args['type'])) {
            $status = $args['status'];
            $current_time = current_time('timestamp', true);
            if ($status == 'active' || $status == 'running') {
                $query = "WHERE enabled = %d AND (start_on IS NULL OR start_on <= %d) AND (end_on IS NULL OR end_on >= %d)";
                $where = self::db()->prepare($query, [1, $current_time, $current_time]);
            } elseif ($status == 'inactive' || $status == 'upcoming') {
                $where = self::db()->prepare("WHERE enabled = %d AND start_on > %d", [1, $current_time]);
            } elseif ($status == 'expired') {
                $where = self::db()->prepare("WHERE enabled = %d AND end_on < %d", [1, $current_time]);
            } elseif ($status == 'enabled' || $status == 'publish') {
                $where = "WHERE enabled = 1";
            } elseif ($status == 'disabled' || $status == 'draft') {
                $where = "WHERE enabled = 0";
            }
        }

        if (!empty($args['type']) && is_string($args['type'])) {
            $where = self::addWhereQuery($where, self::db()->prepare("type = %s", [$args['type']]));
        }
        $columns = !empty($args['columns']) && is_array($args['columns']) ? $args['columns'] : null;
        $campaigns = self::getRows($where, null, $columns, $args);
        if (is_array($campaigns)) {
            foreach ($campaigns as $key => $campaign) {
                $campaigns[$key] = self::parseData($campaign);
            }
        }
        return $campaigns;
    }

    /**
     * Parse row data.
     *
     * @param array $campaign
     * @return array
     */
    private static function parseData($campaign)
    {
        $json_columns = ['filters', 'conditions', 'data'];
        foreach ($json_columns as $column) {
            if (array_key_exists($column, $campaign)) {
                $campaign[$column] = isset($campaign[$column]) ? json_decode($campaign[$column], true) : [];
            }
        }
        return $campaign;
    }

    /**
     * Check if the campaign or type of campaigns is enabled.
     *
     * @param int|string $id_or_type
     * @return bool
     */
    public static function isEnabled($id_or_type)
    {
        if (is_numeric($id_or_type)) {
            return (bool)self::getScalar("SELECT COUNT(`id`) FROM {table} WHERE `id` = %d AND `enabled` = 1", [$id_or_type]);
        } elseif (is_string($id_or_type)) {
            $type = $id_or_type;
            if (!isset(self::$active_campaigns)) {
                $result = self::getResults("SELECT `type` FROM {table} WHERE `enabled` = 1 GROUP BY `type`", [], 'type');
                self::$active_campaigns = is_array($result) ? $result : [];
            }
            return apply_filters("cuw_{$type}_campaigns_is_enabled", in_array($type, self::$active_campaigns));
        }
        return false;
    }

    /**
     * Get campaign offers revenue
     *
     * @param int $id
     * @return float
     */
    public static function getRevenue($id)
    {
        return Stats::getRevenue($id);
    }

    /**
     * Get campaign offers total views
     *
     * @param int $id
     * @return float
     */
    public static function getTotalViews($id)
    {
        return (int)Offer::getScalar("SELECT SUM(`display_count`) FROM {table} WHERE `campaign_id` = %d", [$id]);
    }

    /**
     * Get campaigns count
     *
     * @param string|null $type
     * @return int
     */
    public static function getCount($type = null)
    {
        if (!empty($type)) {
            return (int)self::getScalar("SELECT COUNT(`id`) FROM {table} WHERE `type` = %s", [$type]);
        }
        return (int)self::getScalar("SELECT COUNT(`id`) FROM {table}");
    }

    /**
     * Save campaign
     *
     * @param array $campaign
     * @return array|false
     */
    public static function save($campaign)
    {
        if (isset($campaign['id'])) {
            $id = $campaign['id'];
            $type = !empty($campaign['type']) ? $campaign['type'] : '';
            $is_single = Helper::isSingle($type);

            $data = [
                'type' => $type,
                'title' => !empty($campaign['title']) ? $campaign['title'] : 'Untitled',
                'enabled' => !empty($campaign['enabled']) ? 1 : 0,
                'priority' => !empty($campaign['priority']) && $campaign['priority'] > 0 ? $campaign['priority'] : ($is_single ? 0 : 10),
                'filters' => isset($campaign['filters']) ? json_encode($campaign['filters']) : null,
                'conditions' => isset($campaign['conditions']) ? json_encode($campaign['conditions']) : null,
                'data' => isset($campaign['data']) ? json_encode(wp_unslash($campaign['data'])) : null,
                'start_on' => !empty($campaign['date_from']) ? strtotime(get_gmt_from_date(date($campaign['date_from'] . ' 00:00:00'))) : null,
                'end_on' => !empty($campaign['date_to']) ? strtotime(get_gmt_from_date(date($campaign['date_to'] . ' 23:59:59'))) : null,
                'usage_limit' => !empty($campaign['limit']) ? $campaign['limit'] : 0,
                'usage_limit_per_user' => !empty($campaign['limit_per_user']) ? $campaign['limit_per_user'] : 0,
            ];

            $format = ['%s', '%s', '%d', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d'];
            if ($id == 0) {
                if (!Plugin::hasPro() && self::getCount() >= 5) {
                    return false;
                }

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
            }

            $offer_ids = [];
            if (!empty($campaign['offers'])) {
                foreach ($campaign['offers'] as $offer) {
                    $offer_id = Offer::save($id, $offer);
                    if ($offer_id) {
                        $offer_ids[] = $offer_id;
                    }
                }
            }

            do_action('cuw_campaign_saved', $id, $campaign);

            return ['id' => $id, 'offer_ids' => $offer_ids];
        }
        return false;
    }

    /**
     * Duplicate campaign
     *
     * @param int $campaign_id
     * @return array|false
     */
    public static function duplicate($campaign_id)
    {
        $campaign = self::get($campaign_id, null, true);
        $campaign['id'] = '0';
        $campaign['enabled'] = '0';
        $campaign['date_from'] = !empty($campaign['start_on']) ? WP::formatDate($campaign['start_on'], 'Y-m-d', true) : '';
        $campaign['date_to'] = !empty($campaign['end_on']) ? WP::formatDate($campaign['end_on'], 'Y-m-d', true) : '';
        $campaign['title'] .= ' – copy';
        foreach ($campaign['offers'] as $key => $offer) {
            $campaign['offers'][$key]['id'] = '0';
            $campaign['offers'][$key]['old_uuid'] = $offer['uuid'];
            $campaign['offers'][$key]['uuid'] = Functions::generateUuid();
            $campaign['offers'][$key]['limit'] = $offer['usage_limit'];
            $campaign['offers'][$key]['limit_per_user'] = $offer['usage_limit_per_user'];
            unset($campaign['offers'][$key]['usage_limit'], $campaign['offers'][$key]['usage_limit_per_user']);
        }
        unset($campaign['start_on'], $campaign['end_on']);
        $campaign = apply_filters('cuw_campaign_data_before_duplicate', $campaign);
        return self::save($campaign);
    }

    /**
     * Increase campaign column count
     *
     * @param int $id
     * @param string $column
     * @param int|float $by
     * @return bool
     */
    public static function increaseCount($id, $column, $by = 1)
    {
        $query = "UPDATE {table} SET `$column` = `$column` + $by WHERE `id` = $id;";
        return (bool)self::execQuery($query);
    }
}
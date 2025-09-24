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

use CUW\App\Helpers\Functions;

defined('ABSPATH') || exit;

class Offer extends Model
{
    /**
     * Table name and output type
     *
     * @var string
     */
    const TABLE_NAME = 'offers', OUTPUT_TYPE = ARRAY_A;

    /**
     * Create offers table
     */
    public function create()
    {
        /**
         * Since 1.2.0 add `uuid` and `data` columns
         * Since 1.2.0 deprecated `template_data` column. use `data` column instead
         */
        $query = "CREATE TABLE {table} (
                 `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                 `uuid` varchar(8) DEFAULT NULL,
                 `campaign_id` bigint(20) unsigned NOT NULL,
                 `product` text DEFAULT NULL,
                 `discount` text DEFAULT NULL,
                 `data` text DEFAULT NULL,
                 `usage_limit` int(11) DEFAULT 0,
                 `usage_limit_per_user` int(11) DEFAULT 0,
                 `usage_count` int(11) DEFAULT 0,
                 `display_count` bigint(20) DEFAULT 0,
                 `created_at` bigint(20) unsigned DEFAULT NULL,
                 `created_by` bigint(20) unsigned DEFAULT NULL,
                 `updated_at` bigint(20) unsigned DEFAULT NULL,
                 `updated_by` bigint(20) unsigned DEFAULT NULL,
                 `template_data` text DEFAULT NULL,
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
        // Since 1.2.0 fill empty uuid column with unique uuid
        $rows = self::getResults("SELECT `id` FROM {table} WHERE `uuid` IS NULL");
        if ($rows && is_array($rows)) {
            foreach ($rows as $row) {
                self::updateById($row['id'], ['uuid' => Functions::generateUuid(8)], ['%s']);
            }
        }

        // Since 1.2.0 change template_data column data to data column
        self::execQuery("UPDATE {table} SET `data` = `template_data` WHERE `data` IS NULL");
    }

    /**
     * Get Offer
     *
     * @param int $id
     * @param array|null $columns
     * @return array|false
     */
    public static function get($id, $columns = null)
    {
        $offer = self::getRowById($id, $columns);
        if ($offer) {
            return self::parseData($offer);
        }
        return false;
    }

    /**
     * Get offer by uuid.
     *
     * @param int $uuid
     * @param array|null $columns
     * @return array|false
     */
    public static function getByUuid($uuid, $columns = null)
    {
        $offer = self::getRow(['uuid' => $uuid], ['%s'], $columns);
        if ($offer) {
            return self::parseData($offer);
        }
        return false;
    }

    /**
     * Get Offers
     *
     * @param bool $args
     * @return array
     */
    public static function all($args = [])
    {
        $where = '';
        if (!empty($args['campaign_id']) && is_numeric($args['campaign_id'])) {
            $where = self::addWhereQuery($where, self::db()->prepare("campaign_id = %d", [$args['campaign_id']]));
        }
        $columns = !empty($args['columns']) && is_array($args['columns']) ? $args['columns'] : null;
        $offers = self::getRows($where, null, $columns, $args);
        if (is_array($offers)) {
            foreach ($offers as $key => $offer) {
                $offers[$key] = self::parseData($offer);
            }
            return $offers;
        }
        return [];
    }

    /**
     * Parse row data.
     *
     * @param array $offer
     * @return array
     */
    private static function parseData($offer)
    {
        $json_columns = ['product', 'discount', 'data'];
        foreach ($json_columns as $column) {
            if (array_key_exists($column, $offer)) {
                $offer[$column] = isset($offer[$column]) ? json_decode($offer[$column], true) : [];
            }
        }

        // parse data column
        if (!empty($offer['data'])) {
            // name and image keys are deprecated since v1.2.0
            if (isset($offer['data']['name'])) {
                $offer['data']['template'] = $offer['data']['name'];
                unset($offer['data']['name']);
            }
            if (isset($offer['data']['image'])) {
                $offer['data']['image_id'] = $offer['data']['image'];
                unset($offer['data']['image']);
            }

            // update template name with group (directory) since v1.4.0
            if (isset($offer['data']['template']) && strpos($offer['data']['template'], "offer/") === false) {
                $offer['data']['template'] = 'offer/' . $offer['data']['template'];
            }

            // adjust offer template data and styling
            if (!empty($offer['data']['template'])) {
                if (!isset($offer['data']['custom_styling'])) {
                    $offer['data']['custom_styling'] = false;
                }
                if (!$offer['data']['custom_styling'] || !isset($offer['data']['styles'])) {
                    $template = $offer['data']['template'];
                    $templates = \CUW\App\Helpers\Template::get();
                    if (isset($templates[$template]['styles'])) {
                        $offer['data']['styles'] = $templates[$template]['styles'];
                    }
                }
            }
        }
        return $offer;
    }

    /**
     * Save offer
     *
     * @param int $campaign_id
     * @param array $offer
     * @return array|false
     */
    public static function save($campaign_id, $offer)
    {
        if (isset($offer['id'])) {
            $id = $offer['id'];
            $product = isset($offer['product']) ? $offer['product'] : [
                'id' => $offer['product_id'],
                'qty' => !empty($offer['product_qty']) ? $offer['product_qty'] : '',
            ];
            $discount = isset($offer['discount']) ? $offer['discount'] : [
                'type' => $offer['discount_type'],
                'value' => $offer['discount_value'],
            ];
            $data = [
                'campaign_id' => $campaign_id,
                'product' => json_encode($product),
                'discount' => json_encode($discount),
                'data' => is_array($offer['data']) ? json_encode($offer['data']) : wp_unslash($offer['data']),
                'usage_limit' => !empty($offer['limit']) ? $offer['limit'] : 0,
                'usage_limit_per_user' => !empty($offer['limit_per_user']) ? $offer['limit_per_user'] : 0,
            ];
            $format = ['%d', '%s', '%s', '%s', '%d', '%d'];
            if ($id == 0) {
                $data['uuid'] = !empty($offer['uuid']) ? $offer['uuid'] : Functions::generateUuid(8);
                $format[] = '%s';
                list($data, $format) = self::mergeExtraData($data, $format, 'create');
                if (!($id = self::insert($data, $format))) {
                    return false;
                }
            } else {
                list($data, $format) = self::mergeExtraData($data, $format, 'update');
                if (!self::updateById($offer['id'], $data, $format)) {
                    return false;
                }
            }

            return $id;
        }
        return false;
    }

    /**
     * Get Revenue
     *
     * @param int $id
     * @return float
     */
    public static function getRevenue($id)
    {
        return Stats::getRevenue('all', null, null, null, $id);
    }

    /**
     * Get offer count
     *
     * @param int $id
     * @param string $column
     * @return int
     */
    public static function getCount($id, $column)
    {
        return (int)self::getScalar("SELECT COUNT(`$column`) FROM {table} WHERE `id` = %d", [$id]);
    }

    /**
     * Increase offer column count
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
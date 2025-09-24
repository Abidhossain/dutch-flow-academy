<?php
/**
 * UpsellWP
 *
 * @package   checkout-upsell-and-order-bumps
 * @author    Anantharaj B <anantharaj@flycart.org>
 * @copyright 2024 UpsellWP
 * @license   GPL-3.0-or-later
 * @link      https://upsellwp.com
 */

namespace CUW\App\Models;

use CUW\App\Helpers\Functions;
use CUW\App\Helpers\WC;

use CUW\App\Helpers\WP;
use DateTime;
use DatePeriod;
use DateInterval;

defined('ABSPATH') || exit;

class Stats extends Model
{
    /**
     * Table name and output type
     *
     * @var string
     */
    const TABLE_NAME = 'stats', OUTPUT_TYPE = ARRAY_A;

    /**
     * To cache some data
     *
     * @var array
     */
    private static $campaign_usage_count, $offer_usage_count;

    /**
     * Create stats table
     */
    public function create()
    {
        /**
         * Since 1.2.0 add `campaign_type`, `currency` columns
         * Since 1.3.4 add `revenue_with_tax`, `coupon_id`, `coupon_code` columns
         */
        $query = "CREATE TABLE {table} (
                 `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                 `campaign_id` bigint(20) unsigned NOT NULL,
                 `campaign_type` varchar(32) DEFAULT NULL,
                 `offer_id` bigint(20) unsigned NOT NULL,
                 `order_id` bigint(20) unsigned NOT NULL,
                 `order_item_id` bigint(20) unsigned NOT NULL,
                 `product_id` bigint(20) unsigned NOT NULL,
                 `product_qty` double(11,3) DEFAULT 1,
                 `product_price` double(11,3) NOT NULL,
                 `offer_price` double(11,3) NOT NULL,
                 `coupon_id` bigint(20) unsigned NOT NULL DEFAULT 0,
                 `coupon_code` varchar(255) DEFAULT NULL,
                 `revenue` double(11,3) NOT NULL,
                 `revenue_with_tax` double(11,3) NOT NULL DEFAULT 0,
                 `currency` varchar(32) DEFAULT NULL,
                 `user_id` bigint(20) unsigned DEFAULT NULL,
                 `billing_email` varchar(255) DEFAULT NULL,
                 `order_status` varchar(32) DEFAULT NULL,
                 `created_at` bigint(20) unsigned DEFAULT NULL,
                 PRIMARY KEY (id)
            ) {charset_collate};";

        self::execDBQuery($query); // to create or update table
        self::runPatch(); // to run patch
    }

    /**
     * Run patch to load missing data
     */
    private function runPatch()
    {
        // Since 1.2.0 fill empty campaign_type column with pre_purchase
        self::execQuery('UPDATE {table} SET `campaign_type` = "pre_purchase" WHERE `campaign_type` IS NULL;');

        // Since 1.4.0 replace pre_purchase campaign type with checkout_upsells
        self::execQuery('UPDATE {table} SET `campaign_type` = "checkout_upsells" WHERE `campaign_type` = "pre_purchase";');
    }

    /**
     * Get revenue
     *
     * @param string|int $campaign
     * @param string|null $date_from
     * @param string|null $date_to
     * @param string $currency
     * @param int|null $offer_id
     * @return float
     */
    public static function getRevenue($campaign = 'all', $date_from = null, $date_to = null, $currency = null, $offer_id = null)
    {
        $tax = CUW()->config->get('revenue_tax_display', 'without_tax');
        $column = ($tax == 'with_tax') ? 'revenue_with_tax' : 'revenue';
        $where_query = self::prepareReportWhereQuery($campaign, $date_from, $date_to, $currency);
        if (!empty($offer_id)) {
            $where_query = self::addWhereQuery($where_query, self::db()->prepare("`offer_id` = %d", [$offer_id]));
        }
        return (float)self::getScalar("SELECT SUM($column) FROM {table} $where_query");
    }

    /**
     * To get top revenue campaign.
     *
     * @param array $date
     * @param int $limit
     * @return array|false
     */
    public static function getTopRevenueCampaign($date, $limit = 3)
    {
        if (isset($date['from']) && isset($date['to']) && !empty($limit) && $currency = WC::getCurrency()) {
            $campaign_table = Campaign::getTableName();
            return (array)self::getResults(
                "SELECT title, SUM(revenue) as revenue, `type` FROM {table} 
                        LEFT JOIN $campaign_table ON $campaign_table.id = {table}.campaign_id
                        WHERE ({table}.created_at BETWEEN %d AND %d) AND {table}.currency = %s
                        GROUP BY {table}.campaign_id
                        ORDER BY SUM({table}.revenue) DESC
                        LIMIT %d",
                [
                    'from' => strtotime(get_gmt_from_date($date['from'] . ' 00:00:00')),
                    'to' => strtotime(get_gmt_from_date($date['to'] . ' 23:59:59')),
                    'currency' => $currency,
                    'limit' => $limit,
                ]
            );
        }
        return false;
    }

    /**
     * Get revenue.
     *
     * @param string|int $campaign
     * @param string|null $date_from
     * @param string|null $date_to
     * @param string $currency
     * @return array
     */
    public static function getEachCampaignTypeRevenue($campaign = 'all', $date_from = null, $date_to = null, $currency = null)
    {
        $tax = CUW()->config->get('revenue_tax_display', 'without_tax');
        $column = ($tax == 'with_tax') ? 'revenue_with_tax' : 'revenue';
        $where_query = self::prepareReportWhereQuery($campaign, $date_from, $date_to, $currency);
        return (array)self::getResults("SELECT `campaign_type`, SUM($column) as revenue FROM {table} $where_query GROUP BY `campaign_type`");
    }

    /**
     * Get orders count.
     *
     * @param string|int $campaign
     * @param string|null $date_from
     * @param string|null $date_to
     * @param string $currency
     * @return int
     */
    public static function getOrdersCount($campaign = 'all', $date_from = null, $date_to = null, $currency = null)
    {
        $where_query = self::prepareReportWhereQuery($campaign, $date_from, $date_to, $currency);
        return (int)self::getScalar("SELECT COUNT(*) FROM (SELECT `order_id` FROM {table} $where_query GROUP BY `order_id`) as r");
    }

    /**
     * Get items count.
     *
     * @param string|int $campaign
     * @param string|null $date_from
     * @param string|null $date_to
     * @param string $currency
     * @return int
     */
    public static function getItemsCount($campaign = 'all', $date_from = null, $date_to = null, $currency = null)
    {
        $where_query = self::prepareReportWhereQuery($campaign, $date_from, $date_to, $currency);
        return (int)self::getScalar("SELECT COUNT(`id`) FROM {table} $where_query");
    }

    /**
     * Get campaigns created count
     *
     * @param string|int $campaign
     * @param string|null $date_from
     * @param string|null $date_to
     * @return int
     */
    public static function getCampaignsCreatedCount($campaign = 'all', $date_from = null, $date_to = null)
    {
        if ($campaign != 'all') {
            return '-';
        }
        $where_query = self::prepareReportWhereQuery($campaign, $date_from, $date_to, false,false);
        return (int)Campaign::getScalar("SELECT COUNT(`id`) FROM {table} $where_query");
    }

    /**
     * Get offers created count
     *
     * @param string|int $campaign
     * @param string|null $date_from
     * @param string|null $date_to
     * @return int
     */
    public static function getOffersCreatedCount($campaign, $date_from = null, $date_to = null)
    {
        if ($campaign != 'all') {
            return '-';
        }
        $where_query = self::prepareReportWhereQuery($campaign, $date_from, $date_to, false,false);
        return (int)Offer::getScalar("SELECT COUNT(`id`) FROM {table} $where_query");
    }

    /**
     * Get offer usage count by current user.
     *
     * @param int $offer_id
     * @param bool $cache
     * @return int
     */
    public static function getOfferUsageCountBasedOnCurrentUser($offer_id, $cache = true)
    {
        $current_user_id = WP::getCurrentUserId();
        if (empty($current_user_id)) {
            $current_user_email = WC::getCustomerBillingEmail();
        }
        if (!empty($current_user_id) || !empty($current_user_email)) {
            $where_query = !empty($current_user_email) ? "`billing_email` = '{$current_user_email}'" : "`user_id` = {$current_user_id}";
            if ($cache) {
                if (!isset(self::$offer_usage_count)) {
                    self::$offer_usage_count = [];
                    $rows = self::getResults("SELECT offer_id, COUNT(`id`) as usage_count FROM {table} WHERE {$where_query} GROUP BY `offer_id`;");
                    if ($rows) {
                        foreach ($rows as $row) {
                            self::$offer_usage_count[$row['offer_id']] = (int)$row['usage_count'];
                        }
                    }
                }
                if (isset(self::$offer_usage_count[$offer_id])) {
                    return self::$offer_usage_count[$offer_id];
                }
            } else {
                $offer_id = (int)$offer_id;
                $row = self::getResult("SELECT COUNT(`id`) as usage_count FROM {table} WHERE `offer_id` = {$offer_id} AND {$where_query} GROUP BY `offer_id`;");
                if ($row) {
                    return (int)$row['usage_count'];
                }
            }
        }
        return 0;
    }

    /**
     * Get available currencies.
     *
     * @return array
     */
    public static function getAvailableCurrencies()
    {
        return (array)self::getResults("SELECT `currency`, COUNT(*) as `currency_count` FROM {table} GROUP BY `currency` ORDER BY `currency_count` DESC;", [], 'currency');
    }

    /**
     * Get campaign usage count by current user.
     *
     * @param int $campaign_id
     * @param bool $cache
     * @return int
     */
    public static function getCampaignUsageCountBasedOnCurrentUser($campaign_id, $cache = true)
    {
        $current_user_id = WP::getCurrentUserId();
        if (empty($current_user_id)) {
            $current_user_email = WC::getCustomerBillingEmail();
        }
        if (!empty($current_user_id) || !empty($current_user_email)) {
            $where_query = !empty($current_user_email) ? "`billing_email` = '{$current_user_email}'" : "`user_id` = {$current_user_id}";
            if ($cache) {
                if (!isset(self::$campaign_usage_count)) {
                    self::$campaign_usage_count = [];
                    $rows = self::getResults("SELECT campaign_id, COUNT(`id`) as usage_count FROM {table} WHERE {$where_query} GROUP BY `campaign_id`;");
                    if ($rows) {
                        foreach ($rows as $row) {
                            self::$campaign_usage_count[$row['campaign_id']] = (int)$row['usage_count'];
                        }
                    }
                }
                if (isset(self::$campaign_usage_count[$campaign_id])) {
                    return self::$campaign_usage_count[$campaign_id];
                }
            } else {
                $campaign_id = (int)$campaign_id;
                $row = self::getResult("SELECT COUNT(`id`) as usage_count FROM {table} WHERE `campaign_id` = {$campaign_id} AND {$where_query} GROUP BY `campaign_id`;");
                if ($row) {
                    return (int)$row['usage_count'];
                }
            }
        }
        return 0;
    }

    /**
     * To get entry date interval in seconds.
     */
    public static function getRecordsDateIntervalInSeconds()
    {
        $new_entry_at = self::getScalar("SELECT created_at FROM {table} ORDER BY id DESC LIMIT 1");
        $old_entry_at = self::getScalar("SELECT created_at FROM {table} ORDER BY id ASC LIMIT 1");
        if (is_numeric($new_entry_at) && is_numeric($old_entry_at) && $new_entry_at > $old_entry_at) {
            return $new_entry_at - $old_entry_at;
        } else {
            return 0;
        }
    }

    /**
     * Prepare date condition query
     *
     * @param string|int $campaign
     * @param string|null $date_from
     * @param string|null $date_to
     * @return string
     */
    protected static function prepareReportWhereQuery($campaign = 'all', $date_from = null, $date_to = null, $currency = null , $is_stats = true)
    {
        $where_query = '';
        $order_status = apply_filters('cuw_stats_failed_order_status', ['cancelled', 'failed', 'checkout-draft', 'refunded']);
        if ($order_status && $is_stats) {
            $where_query = self::addWhereQuery($where_query, "`order_status` NOT IN ('". implode("','", $order_status) ."')");
        }

        if ($campaign != 'all' && $campaign != '') {
            if (is_numeric($campaign)) {
                $where_query = self::addWhereQuery($where_query, self::db()->prepare("`campaign_id` = %d", [$campaign]));
            } else {
                $where_query = self::addWhereQuery($where_query, self::db()->prepare("`campaign_type` = %s", [$campaign]));
            }
        }

        if ($currency !== false) {
            $currency = !empty($currency) ? $currency : WC::getCurrency();
            $where_query = self::addWhereQuery($where_query, self::db()->prepare("`currency` = %s", [$currency]));
        }

        if (!empty($date_from) && !empty($date_to)) {
            $from = strtotime(get_gmt_from_date($date_from . ' 00:00:00'));
            $to = strtotime(get_gmt_from_date($date_to . ' 23:59:59'));
            $where_query = self::addWhereQuery($where_query, "(`created_at` BETWEEN $from AND $to)");
        }
        return $where_query;
    }

    /**
     * Get difference percentage
     *
     * @param int $old
     * @param int $new
     * @return int
     */
    public static function getDiffPercentage($old, $new)
    {
        if ($old == 0 && $new == 0) {
            return 0;
        } elseif ($new == 0 && is_numeric($old)) {
            return round(-100 * $old, 2);
        } elseif (is_numeric($new) && is_numeric($old)) {
            return round((1 - ($old / $new)) * 100, 2);
        }
        return 0;
    }

    /**
     * Get chart data.
     *
     * @param string $tab
     * @param string $currency
     * @param string|int $campaign
     * @param string $range
     * @param array $custom_date
     * @return array
     */
    public static function getChartData($tab, $currency, $campaign, $range, $custom_date = [])
    {
        if ($range == 'custom' && !empty($custom_date['from']) && !empty($custom_date['to'])) {
            $date = ['from' => $custom_date['from'], 'to' => $custom_date['to'], 'format' => 'M j, Y'];
        } else {
            $date = self::getDateByRange($range);
            $date['format'] = 'M j';
        }

        if (!empty($date) && !empty($currency)) {
            return [
                'items' => self::prepareChartData('items', $currency, $campaign, $date),
                'revenue' => self::prepareChartData('revenue', $currency, $campaign, $date),
                'campaigns_revenue' => self::prepareChartData('campaigns_revenue', $currency, $campaign, $date),
            ];
        }
        return [];
    }

    /**
     * Get chart data
     *
     * @param string|int $tab
     * @param string|int $campaign
     * @param string $range
     * @param string $currency
     * @param array $custom_date
     * @return array
     */
    public static function getUpsellInfo($tab, $currency, $campaign, $range, $custom_date = [], $format = true)
    {
        $data = [
            'html' => [
                'items' => 0,
                'orders' => 0,
                'revenue' => 0,
                'total_revenue' => 0,
                'conversion_percentage' => 0,
                'campaigns' => 0,
                'offers' => 0
            ]
        ];
        $total_revenue_args = [
            'select' => '{order_total}',
            'sum' => 'order_total',
            'where' => [[
                'column' => 'currency',
                'operator' => '=',
                'value' => $currency,
            ]],
            'return' => 'var',
        ];

        if ($range == 'custom' && !empty($custom_date['from']) && !empty($custom_date['to'])) {
            $date = ['from' => $custom_date['from'], 'to' => $custom_date['to']];
        } else {
            $date = self::getDateByRange($range);
        }

        if (!empty($date) && !empty($currency)) {
            $data['html'] = [
                'items' => self::getItemsCount($campaign, $date['from'], $date['to'], $currency),
                'orders' => self::getOrdersCount($campaign, $date['from'], $date['to'], $currency),
                'revenue' => self::getRevenue($campaign, $date['from'], $date['to'], $currency),
            ];

            if ($tab == 'dashboard') {
                $total_revenue_args['date_after'] = $date['from'] . ' 00:00:00';
                $total_revenue_args['date_before'] = $date['to'] . ' 23:59:59';
                $data['html']['total_revenue'] = Order::performOrderQuery($total_revenue_args);
                if (!empty($data['html']['total_revenue'])) {
                    $data['html']['conversion_percentage'] = round(($data['html']['revenue'] / $data['html']['total_revenue']) * 100, 2);
                } else {
                    $data['html']['conversion_percentage'] = 0;
                }
            } else {
                $data['html']['campaigns'] = self::getCampaignsCreatedCount($campaign, $date['from'], $date['to']);
                $data['html']['offers'] = self::getOffersCreatedCount($campaign, $date['from'], $date['to']);
            }

            if (in_array($range, ['this_week', 'this_month', 'last_30_days', 'last_7_days'])) {
                if ($range == 'this_week') {
                    $since = 'last_week';
                    $old_date = self::getDateByRange('last_week');
                } elseif ($range == 'this_month') {
                    $since = 'last_month';
                    $old_date = self::getDateByRange('last_month');
                } elseif ($range == 'last_7_days') {
                    $since = 'previous_7_days';
                    $old_date['from'] = Functions::getDateByString('-14 days', 'Y-m-d');
                    $old_date['to'] = Functions::getDateByString('-7 days', 'Y-m-d');
                } else {
                    $since = 'previous_30_days';
                    $old_date['from'] = Functions::getDateByString('-60 days', 'Y-m-d');
                    $old_date['to'] = Functions::getDateByString('-30 days', 'Y-m-d');
                }

                $last = [
                    'items' => self::getItemsCount($campaign, $old_date['from'], $old_date['to'], $currency),
                    'orders' => self::getOrdersCount($campaign, $old_date['from'], $old_date['to'], $currency),
                    'revenue' => self::getRevenue($campaign, $old_date['from'], $old_date['to'], $currency),
                ];

                $data['diff'] = [
                    'since' => $since,
                    'percentages' => [
                        'items' => self::getDiffPercentage($last['items'], $data['html']['items']),
                        'orders' => self::getDiffPercentage($last['orders'], $data['html']['orders']),
                        'revenue' => self::getDiffPercentage($last['revenue'], $data['html']['revenue']),
                    ],
                ];

                if ($tab == 'dashboard') {
                    $total_revenue_args['date_after'] = $old_date['from'] . ' 00:00:00';
                    $total_revenue_args['date_before'] = $old_date['to'] . ' 23:59:59';
                    $last['total_revenue'] = Order::performOrderQuery($total_revenue_args);
                    if (!empty($last['total_revenue'])) {
                        $last['conversion_percentage'] = round(($last['revenue'] / $last['total_revenue']) * 100, 2);
                    } else {
                        $last['conversion_percentage'] = 0;
                    }
                    $data['diff']['percentages']['total_revenue'] = self::getDiffPercentage($last['total_revenue'], $data['html']['total_revenue']);
                    $data['diff']['percentages']['conversion_percentage'] = self::getDiffPercentage($last['conversion_percentage'], $data['html']['conversion_percentage']);
                } else {
                    $last['campaigns'] = self::getCampaignsCreatedCount($campaign, $old_date['from'], $old_date['to']);
                    $last['offers'] = self::getOffersCreatedCount($campaign, $old_date['from'], $old_date['to']);
                    $data['diff']['percentages']['campaigns'] = self::getDiffPercentage($last['campaigns'], $data['html']['campaigns']);
                    $data['diff']['percentages']['offers'] = self::getDiffPercentage($last['offers'], $data['html']['offers']);
                }
            }
        }

        remove_all_filters('woocommerce_currency_symbol'); // to remove third-party currency plugin hooks
        if ($tab == 'dashboard') {
            if ($format) {
                $data['html']['total_revenue'] = WC::formatPrice($data['html']['total_revenue'], ['currency' => $currency]);
                $data['html']['conversion_percentage'] .= '%';
            }
        }
        $data['html']['revenue'] = $format ? WC::formatPrice($data['html']['revenue'], ['currency' => $currency]) : $data['html']['revenue'];
        return $data;
    }

    /**
     * Get date by range.
     *
     * @param string $range
     * @return array
     */
    public static function getDateByRange($range)
    {
        $date = [];
        $day = gmdate('N', current_time('timestamp'));
        if ($range == 'last_30_days') {
            $date['from'] = Functions::getDateByString('-30 days', 'Y-m-d');
            $date['to'] = Functions::getDateByString('now', 'Y-m-d');
        } elseif ($range == 'last_7_days') {
            $date['from'] = Functions::getDateByString('-7 days', 'Y-m-d');
            $date['to'] = Functions::getDateByString('now', 'Y-m-d');
        } elseif ($range == 'this_week') {
            $date['from'] = Functions::getDateByString($day == 1 ? 'now' : 'last monday', 'Y-m-d');
            $date['to'] = Functions::getDateByString($day == 7 ? 'now' : 'next sunday', 'Y-m-d');
        } elseif ($range == 'last_week') {
            $date['from'] = Functions::getDateByString($day == 1 ? '-7 days' : 'last monday -7 days', 'Y-m-d');
            $date['to'] = Functions::getDateByString($day == 7 ? '-7 days' : 'next sunday -7 days', 'Y-m-d');
        } elseif ($range == 'this_month') {
            $date['from'] = Functions::getDateByString('first day of this month', 'Y-m-d');
            $date['to'] = Functions::getDateByString('last day of this month', 'Y-m-d');
        } elseif ($range == 'last_month') {
            $date['from'] = Functions::getDateByString('first day of last month', 'Y-m-d');
            $date['to'] = Functions::getDateByString('last day of last month', 'Y-m-d');
        }
        return $date;
    }

    /**
     * Prepare chart data.
     *
     * @param string $type
     * @param string $currency
     * @param string|int $campaign
     * @param array $date
     * @return array
     */
    public static function prepareChartData($type, $currency, $campaign, $date)
    {
        $data = [];
        if (in_array($type, ['revenue', 'items'])) {
            try {
                $start_date = new DateTime($date['from']);
                $end_date = new DateTime($date['to']);
                $end_date->modify('+1 day');
                $interval = new DateInterval('P1D');
                $period = new DatePeriod($start_date, $interval, $end_date);
                foreach ($period as $date_object) {
                    $ymd = $date_object->format('Y-m-d');
                    $fmt = isset($date['format']) ? $date['format'] : 'M j, Y';
                    $x = $date_object->format(str_replace('Y', substr($date_object->format('Y'), -2), $fmt));
                    $y = ($type == 'items') ? self::getItemsCount($campaign, $ymd, $ymd, $currency) : self::getRevenue($campaign, $ymd, $ymd, $currency);
                    $data[$x] = $y;
                }
            } catch (\Exception $e) {
            }
        } elseif ($type == 'campaigns_revenue') {
            $campaigns_revenue = self::getEachCampaignTypeRevenue($campaign, $date['from'], $date['to'], $currency);
            $available_campaigns = array_map(function () {
                return 0;
            }, \CUW\App\Helpers\Campaign::get());
            $campaign_titles = array_map(function ($campaign) {
                if ($campaign['is_pro'] && !CUW()->plugin->has_pro) {
                    return $campaign['title'] . ' [' . esc_html__('PRO', 'checkout-upsell-and-order-bumps') . ']';
                }
                return $campaign['title'];
            }, \CUW\App\Helpers\Campaign::get());
            foreach ($campaigns_revenue as $row) {
                if (isset($available_campaigns[$row['campaign_type']])) {
                    $available_campaigns[$row['campaign_type']] = $row['revenue'];
                }
            }
            $data = [
                'labels' => array_values($campaign_titles),
                'revenues' => array_values($available_campaigns),
            ];
        }
        return $data;
    }

    /**
     * To get saved order item ids.
     *
     * @param int $order_id
     * @return array
     */
    public static function getSavedItemIds($order_id)
    {
        return (array)self::getResults("SELECT `order_item_id` FROM {table} WHERE `order_id` = %d", [$order_id], 'order_item_id');
    }

    /**
     * Save stats
     *
     * @param \WC_Order $order
     * @param \WC_Order_Item_Product|\WC_Order_Item_Coupon $order_item
     * @param array $data
     * @return void
     */
    public static function save($order, $order_item, $data)
    {
        if (!isset($data['campaign_id'])) {
            return;
        }

        $offer_id = $coupon_id = $revenue = $revenue_with_tax = 0;
        $campaign_type = isset($data['campaign_type']) ? $data['campaign_type'] : \CUW\App\Helpers\Campaign::getType($data['campaign_id']);
        if (is_a($order_item, 'WC_Order_Item_Product')) {
            $product = $data['product'];
            $product_id = $product['variation_id'] > 0 ? $product['variation_id'] : $product['id'];
            $product_qty = $product['qty'];
            $product_price = $product['price'];

            if ($data['type'] == 'offer') {
                $offer_id = $data['id'];
            } elseif ($data['type'] == 'product') {
                $product_qty = $order_item->get_quantity();
            }

            $revenue = $order_item->get_subtotal();
            $revenue_with_tax = $revenue + $order_item->get_subtotal_tax();

            $discounted_price = !empty($data['discount']['is_bundle']) ? ($revenue / $product_qty) : $data['price'];
        } elseif (is_a($order_item, 'WC_Order_Item_Coupon')) {
            if (!empty($data['coupon_id'])) {
                $coupon_id = $data['coupon_id'];
            }
            if (!empty($data['coupon_code'])) {
                $coupon_code = $data['coupon_code'];
            }
            foreach (WC::getOrderItems($order) as $item) {
                if (method_exists($item, 'get_subtotal') && method_exists($item, 'get_subtotal_tax')) {
                    $revenue += $item->get_subtotal();
                    $revenue_with_tax += $item->get_subtotal() + $item->get_subtotal_tax();
                }
            }
        } else {
            return;
        }

        self::insert([
            'campaign_id' => $data['campaign_id'],
            'campaign_type' => $campaign_type,
            'offer_id' => $offer_id,
            'order_id' => $order->get_id(),
            'order_item_id' => $order_item->get_id(),
            'product_id' => !empty($product_id) ? $product_id : 0,
            'product_qty' => !empty($product_qty) ? $product_qty : 0,
            'product_price' => !empty($product_price) ? $product_price : 0,
            'offer_price' => !empty($discounted_price) ? $discounted_price : 0,
            'coupon_id' => $coupon_id,
            'coupon_code' => !empty($coupon_code) ? strtolower($coupon_code) : null,
            'revenue' => $revenue,
            'revenue_with_tax' => $revenue_with_tax,
            'currency' => $order->get_currency(),
            'billing_email' => $order->get_billing_email(),
            'order_status' => $order->get_status() ?? null,
            'user_id' => is_user_logged_in() ? get_current_user_id() : null,
            'created_at' => current_time('timestamp', true),
        ], ['%d', '%s', '%d', '%d', '%d', '%d', '%f', '%f', '%f', '%d', '%s', '%f', '%f', '%s', '%s', '%d', '%d']);
    }

    /**
     * Update order status in stats table.
     *
     * @param $order_id
     * @param $old_status
     * @param $new_status
     * @return void
     */
    public static function updateOrderStatus($order_id, $old_status, $new_status)
    {
        self::execQuery('UPDATE {table} SET `order_status` = "' . $new_status . '" WHERE `order_id` = "' . $order_id .'";');
    }
}
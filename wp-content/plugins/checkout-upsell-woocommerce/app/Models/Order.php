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
use CUW\App\Helpers\WC;
use CUW\App\Helpers\WP;

defined('ABSPATH') || exit;

class Order
{
    /**
     * To cache results.
     *
     * @var array
     */
    public static $results = [];

    /**
     * Perform order query
     *
     * @param array $args
     * @param bool $cache
     * @return mixed
     */
    public static function performOrderQuery($args, $cache = true)
    {
        $cache_key = Functions::generateHash($args);
        if ($cache && isset(self::$results[$cache_key])) {
            return self::$results[$cache_key];
        }

        global $wpdb;
        $cot_enabled = WC::customOrdersTableIsEnabled();
        $orders_table = $wpdb->prefix . ($cot_enabled ? 'wc_orders' : 'posts as posts');
        $postmeta_table = $wpdb->prefix . 'postmeta';
        $order_items_table = $wpdb->prefix . 'woocommerce_order_items';
        $order_items_meta_table = $wpdb->prefix . 'woocommerce_order_itemmeta';
        $post_meta_key_map = [
            'currency' => '_order_currency',
        ];
        $order_items_meta_key_map = [
            'product_id' => '_product_id',
            'variation_id' => '_variation_id',
            'quantity' => '_qty',
        ];

        $select = ['{id}'];
        $select_column_map = [
            '{id}' => $cot_enabled ? 'id' : 'posts.ID',
            '{order_total}' => $cot_enabled ? 'total_amount as order_total' : "(SELECT meta_value FROM $postmeta_table WHERE post_id = posts.ID AND meta_key = '_order_total') as order_total",
        ];
        $where_column_map = [
            '{id}' => $cot_enabled ? 'id' : 'posts.ID',
        ];

        if (isset($args['select'])) {
            if (is_array($args['select'])) {
                $select = esc_sql($args['select']);
            } else {
                $select = esc_sql(explode(",", $args['select']));
            }
        }
        foreach ($select as $key => $column) {
            $select[$key] = str_replace(array_keys($select_column_map), array_values($select_column_map), $column);
        }

        $where_queries = $meta_where_queries = [];
        $order_types = isset($args['types']) ? $args['types'] : ['shop_order'];
        $where_queries[] = ($cot_enabled ? 'type' : 'posts.post_type') . " IN (" . implode(",", array_map(function ($type) {
                return "'" . esc_sql($type) . "'";
            }, $order_types)) . ")";

        $order_statuses = isset($args['statuses']) ? $args['statuses'] : [];
        if (!empty($order_statuses)) {
            $where_queries[] = ($cot_enabled ? 'status' : 'posts.post_status') . " IN (" . implode(",", array_map(function ($status) {
                    return "'" . esc_sql($status) . "'";
                }, $order_statuses)) . ")";
        }

        if (!empty($args['where']) && is_array($args['where'])) {
            $where = $meta_where = [];
            foreach ($args['where'] as $data) {
                if (is_array($data['value'])) {
                    $data['value'] = '(' . implode(',', array_map(function ($value) {
                            return "'" . esc_sql($value) . "'";
                        }, $data['value'])) . ')';
                } else {
                    $data['value'] = "'" . esc_sql($data['value']) . "'";
                }
                if (!empty($data['column']) && !empty($data['operator']) && !empty($data['value'])) {
                    if (in_array($data['column'], array_keys($order_items_meta_key_map))) {
                        $meta_where[] = esc_sql($data['column']) . " " . esc_sql($data['operator']) . " " . $data['value'];
                    } else {
                        $column = str_replace(array_keys($where_column_map), array_values($where_column_map), $data['column']);
                        if (!$cot_enabled && in_array($column, array_keys($post_meta_key_map))) {
                            $column = $post_meta_key_map[$column];
                            $where[] = "(postmeta.meta_key = '$column' AND postmeta.meta_value " . esc_sql($data['operator']) . " " . $data['value'] . ")";
                        } else {
                            $where[] = esc_sql($column) . " " . esc_sql($data['operator']) . " " . $data['value'];
                        }
                    }
                }
            }
            $relation = isset($args['where_relation']) ? $args['where_relation'] : 'AND';
            if (!empty($where)) {
                $where_queries[] = "(" . implode(" $relation ", $where) . ")";
            }
            if (!empty($meta_where)) {
                $meta_where_queries[] = "(" . implode(" $relation ", $meta_where) . ")";
            }
        }

        if (!empty($args['based_on_current_user'])) {
            $conditions = $condition_args = [];
            $relation = 'AND';
            $user_id = WP::getCurrentUserId();
            $billing_email = WC::getCustomerBillingEmail();
            if (!empty($user_id)) {
                if (!empty($billing_email) && apply_filters('cuw_perform_order_query_based_on_current_user_id_and_billing_email', false)) {
                    $relation = 'OR';
                    $condition_args[$cot_enabled ? 'customer_id' : '_customer_user'] = $user_id;
                    $condition_args[$cot_enabled ? 'billing_email' : '_billing_email'] = $billing_email;
                } else {
                    $condition_args[$cot_enabled ? 'customer_id' : '_customer_user'] = $user_id;
                }
            } elseif (!empty($billing_email)) {
                $condition_args[$cot_enabled ? 'billing_email' : '_billing_email'] = $billing_email;
            } else {
                return false;
            }
            foreach ($condition_args as $column => $value) {
                if ($cot_enabled) {
                    $conditions[] = "$column = '" . esc_sql($value) . "'";
                } else {
                    $conditions[] = "(postmeta.meta_key = '$column' AND postmeta.meta_value = '" . esc_sql($value) . "')";
                }
            }
            $where_queries[] = "(" . implode(" $relation ", $conditions) . ")";
        }

        if (!empty($args['date_before'])) {
            $where_queries[] = ($cot_enabled ? 'date_created_gmt' : 'posts.post_date_gmt') . "< '" . esc_sql(get_gmt_from_date($args['date_before'])) . "'";
        }
        if (!empty($args['date_after'])) {
            $where_queries[] = ($cot_enabled ? 'date_created_gmt' : 'posts.post_date_gmt') . " > '" . esc_sql(get_gmt_from_date($args['date_after'])) . "'";
        }

        $meta_select = [];
        if (isset($args['join']) && $args['join'] == 'order_items') {
            $orders_table .= " LEFT JOIN " . $order_items_table . " AS oi ON id = oi.order_id AND oi.order_item_type = 'line_item'";

            $meta_select = $select;
            $prepared_select_queries = [];
            foreach ($select as $column) {
                $column = trim($column);
                if ($column == 'id' || $column == 'posts.ID') {
                    $prepared_select_queries[] = 'id';
                } elseif ($column == 'item_id') {
                    $prepared_select_queries[] = 'oi.order_item_id AS item_id';
                } else {
                    $meta_key = $column;
                    if (isset($order_items_meta_key_map[$meta_key])) {
                        $meta_key = $order_items_meta_key_map[$meta_key];
                    }
                    $prepared_select_queries[] = "(SELECT meta_value FROM $order_items_meta_table WHERE order_item_id = oi.order_item_id AND meta_key = '" . sanitize_key($meta_key) . "') AS " . $column;
                }
            }
            $select = $prepared_select_queries;
        }

        $select_query = implode(",", $select);
        $where_query = implode(" AND ", $where_queries);
        if ($cot_enabled) {
            $query = "SELECT $select_query FROM $orders_table WHERE " . $where_query;
        } else {
            if (!preg_match('/postmeta/i', $where_query)) {
                $where_query .= " AND postmeta.meta_key = '_order_total'"; // to avoid order duplication due to left join with postmeta table
            }
            $query = "SELECT $select_query FROM $orders_table LEFT JOIN $postmeta_table AS postmeta ON posts.ID = postmeta.post_id WHERE " . $where_query;
        }

        $sum = isset($args['sum']) ? esc_sql($args['sum']) : null;
        $count = isset($args['count']) ? esc_sql($args['count']) : null;
        $count_distinct = isset($args['count_distinct']) && $args['count_distinct'];
        if ($sum || $count) {
            $meta_select_query = '';
            if ($sum) {
                $meta_select_query .= "SUM(m.$sum) AS sum";
            }
            if ($count) {
                $meta_select_query .= "COUNT(" . ($count_distinct ? 'DISTINCT ' : '') . "m.$count) AS count";
            }
            if (empty($meta_select_query)) {
                $meta_select_query = implode(",", array_map(function ($select) {
                    return "m." . $select;
                }, $meta_select));
            }
            $query = "SELECT $meta_select_query FROM ($query) AS m";
            if (!empty($meta_where_queries)) {
                $query .= " WHERE " . implode(" AND ", $meta_where_queries);
            }
        }

        $query .= isset($args['group_by']) ? " GROUP BY " . esc_sql($args['group_by']) : "";
        $query .= isset($args['order_by']) ? " ORDER BY " . esc_sql($args['order_by']) : "";
        $query .= isset($args['limit']) ? " LIMIT " . (int)$args['limit'] : "";

        if (!empty($args['count_results'])) {
            $query = "SELECT COUNT(r.count) AS count FROM ($query) AS r";
        } elseif (!empty($args['sum_results_count'])) {
            $query = "SELECT SUM(r.count) AS count FROM ($query) AS r";
        }

        // phpcs:disable
        if (isset($args['return']) && $args['return'] == 'var') {
            $result = $wpdb->get_var($query);
        } elseif (isset($args['return']) && $args['return'] == 'row') {
            $result = $wpdb->get_row($query);
        } else {
            $result = $wpdb->get_results($query);
        }
        // phpcs:enable

        if ($cache) {
            self::$results[$cache_key] = $result;
        }
        return $result;
    }
}
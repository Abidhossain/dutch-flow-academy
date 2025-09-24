<?php

namespace ExtensionTree\WCMoneyBird;

use WC_Order;

/*******************************************************
 * Integrations with third party plugins
 *******************************************************/

/**
 * Get upsell transactions
 * @param WC_Order $order
 * @return array Transactions with id as key and amount (float) as value.
 */
function get_upsell_transactions($order) {
    return get_wfocu_transactions($order);
}


/**
 * Get upsell transactions from FunnelKit One Click Upsells plugin
 * @param WC_Order $order
 * @return array Transactions with id as key and amount (float) as value.
 */
function get_wfocu_transactions($order) {
    if (!function_exists('WFOCU_Core')) {
        return array();
    }
    try {
        $order_id = $order->get_id();
        $wfocu_session_ids = WFOCU_Core()->track->query_results(array(
            'data' => array(
                'id' => array(
                    'type' => 'col',
                    'function' => '',
                    'name' => 'session_id',
                ),
            ),
            'where' => array(
                array(
                    'key' => 'events.order_id',
                    'value' => $order_id,
                    'operator' => '=',
                ),
            ),
            'query_type' => 'get_results',
            'session_table' => true,
            'nocache' => true,
        ));

        $wfocu_session_id = '';

        if (is_array($wfocu_session_ids) && count($wfocu_session_ids) > 0) {
            $wfocu_session_ids = end($wfocu_session_ids);
            if (isset($wfocu_session_ids->session_id)) {
                $wfocu_session_id = $wfocu_session_ids->session_id;
            }
        }
        $eventsdb = WFOCU_Core()->track->query_results(array(
            'where' => array(
                array(
                    'key' => 'events.sess_id',
                    'value' => $wfocu_session_id,
                    'operator' => '=',
                ),

            ),
            'query_type' => 'get_results',
            'order_by' => 'events.timestamp',
            'order' => 'ASC',
            'nocache' => true,

        ));
        $event_ids = wc_list_pluck($eventsdb, 'id');
        $events_meta = WFOCU_Core()->track->get_meta($event_ids);
        $transactions = array();
        $events = [];
        foreach (is_array($events_meta) ? $events_meta : array() as $key => $meta) {
            if (!isset($events[$meta['event_id']])) {
                $events[$meta['event_id']] = [];
            }
            $events[$meta['event_id']][$meta['meta_key']] = $meta['meta_value'];
        }

        foreach ($events as $id => $event) {
            $event_row = $eventsdb[array_search($id, $event_ids)];
            if ('4' === $event_row->action_type_id
                && isset($event['_total_charged'])
                && isset($event['_transaction_id'])
                && !empty($event['_transaction_id'])) {

                $transaction_value = floatval($event['_total_charged']);
                if ($transaction_value) {
                    $transactions[$event['_transaction_id']] = $transaction_value;
                }
            }
        }

        return $transactions;
    } catch (Exception $e) {
        return array();
    }
}

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

namespace CUW\App\Modules\Campaigns;

defined('ABSPATH') || exit;

use CUW\App\Helpers\Campaign;
use CUW\App\Helpers\Functions;
use CUW\App\Helpers\Order;
use CUW\App\Helpers\Plugin;
use CUW\App\Helpers\Template;
use CUW\App\Helpers\WC;
use CUW\App\Helpers\WP;
use CUW\App\Models\Campaign as CampaignModel;
use CUW\App\Models\Model;
use WC_Coupon;

class NOC extends Base
{
    /**
     * Campaign type.
     *
     * @var string
     */
    const TYPE = 'noc';

    /**
     * To add hooks.
     *
     * @return void
     */
    public function init()
    {
        if (is_admin()) {
            // on campaign page
            add_action('cuw_campaign_contents', [__CLASS__, 'loadCampaignView'], 10, 2);

            // add meta box
            add_action('admin_init', function () {
                global $pagenow;
                if ($pagenow == 'post.php' && $post_id = self::app()->input->get('post', '', 'query')) {
                    if (is_numeric($post_id) && get_post_meta($post_id, 'is_cuw_noc', true)) {
                        add_meta_box(
                            'cuw_noc',
                            __('Next Order Coupon', 'checkout-upsell-and-order-bumps'),
                            function ($post) {
                                self::app()->view('Admin/Campaign/Metaboxes/NOC', ['post_id' => $post->ID]);
                            },
                            'shop_coupon',
                            'side',
                            'low'
                        );
                    }
                }
            });

            // to load noc filter on coupons list page
            add_filter('views_edit-shop_coupon', function ($views) {
                $title = esc_html__('Next Order Coupon', 'checkout-upsell-and-order-bumps');
                $views['metakey'] = '<a href="edit.php?post_type=shop_coupon&cuw_filter=noc">' . $title . '</a>';
                return $views;
            });

            // to handle noc filter on coupons list page
            add_filter('parse_query', function ($query) {
                global $pagenow;
                if (isset($_GET['cuw_filter']) && $pagenow == 'edit.php') { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    $post_type = self::app()->input->get('post_type', '', 'query');
                    $filter = self::app()->input->get('cuw_filter', '', 'query');
                    if ($post_type == 'shop_coupon' && $filter == 'noc') {
                        if (isset($query->query['post_type']) && $query->query['post_type'] == 'shop_coupon') {
                            // phpcs:disable
                            $query->query_vars['meta_key'] = 'is_cuw_noc';
                            $query->query_vars['meta_value'] = '1';
                            // phpcs:enable
                        }
                    }
                }
                return $query;
            });
        } else {
            if (self::isEnabled()) {
                add_action('wp', function () {
                    // to show actions on thankyou page
                    if (WC::is('order-received')) {
                        foreach (self::getDisplayLocations() as $location => $name) {
                            if ($location != 'do_not_display') {
                                $location = explode(":", $location);
                                add_action($location[0], [__CLASS__, 'showActions'], (isset($location[1]) ? (int)$location[1] : 10));
                            }
                        }
                    }

                    // to show actions on myaccount page
                    if (WC::is('view-order')) {
                        foreach (self::getDisplayLocationsOnMyAccountPage() as $location => $name) {
                            if ($location != 'do_not_display') {
                                $location = explode(":", $location);
                                add_action($location[0], [__CLASS__, 'showActions'], (isset($location[1]) ? (int)$location[1] : 10));
                            }
                        }
                    }
                });

                // to apply url coupon
                add_action('wp_loaded', [__CLASS__, 'applyCouponByUrl']);
            }
        }

        // to show actions on emails
        if (self::isEnabled() && Plugin::hasPro()) {
            foreach (self::getDisplayLocationsOnEmail() as $location => $name) {
                if ($location != 'do_not_display') {
                    $location = explode(":", $location);
                    add_action($location[0], [__CLASS__, 'showActions'], (isset($location[1]) ? (int)$location[1] : 10), 2);
                }
            }
        }

        // general hooks
        if (self::isEnabled()) {
            add_action('woocommerce_coupon_is_valid', [__CLASS__, 'checkCouponIsValid'], 1000, 2);
            add_filter('cuw_campaign_usage_count_based_on_current_user', [__CLASS__, 'getUsageCountBasedOnCurrentUser'], 10, 2);
            add_action('woocommerce_order_status_changed', [__CLASS__, 'processOrderCoupon'], 20);
            add_action('woocommerce_after_order_object_save', [__CLASS__, 'processOrderCoupon'], 20);
        }
    }

    /**
     * To show actions.
     */
    public static function showActions($order, $sent_to_admin = false)
    {
        if (!$sent_to_admin) {
            echo self::getActionsHtml(current_action(), self::getOrder($order)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }

    /**
     * To get the html.
     *
     * @param $location
     * @param $order
     * @return mixed|string|null
     */
    public static function getActionsHtml($location, $order)
    {
        $html = '';
        $order = WC::getOrder($order);
        if (empty($order) || !is_object($order)) {
            return $html;
        }

        if ($order->get_meta('_cuw_noc_processed')) {
            $campaign_id = $order->get_meta('_cuw_processed_campaign_id');
            $coupon_code  = $order->get_meta('_cuw_noc_coupon_code');
            if (empty($campaign_id) || empty($coupon_code)) {
                return $html;
            }
            $campaign = CampaignModel::get($campaign_id);

            if (empty($campaign) || !is_array($campaign)) {
                return $html;
            }

            $endpoint =  WC::getCurrentEndpoint();
            if ($endpoint == get_option('woocommerce_checkout_order_received_endpoint', '')) {
                $campaign_display_location = Campaign::getDisplayLocation($campaign);
            } elseif ($endpoint == get_option('woocommerce_myaccount_view_order_endpoint', '')) {
                $campaign_display_location = Campaign::getDisplayLocation($campaign, 'display_location_on_myaccount_page');
            } else {
                $campaign_display_location = Campaign::getDisplayLocation($campaign, 'display_location_on_email');
            }

            // return if display location is not matched.
            if (empty($campaign_display_location) || $campaign_display_location != $location) {
                return $html;
            }

            $campaign['data']['coupon']['code'] = $coupon_code;
            $campaign['data']['coupon']['url'] = self::getCouponUrl($coupon_code, Campaign::getRedirectUrl($campaign));
            $coupon_message = self::getCouponMessage($coupon_code);
            if (empty($coupon_message)) {
                $campaign['data']['template']['message'] = 'hide';
            }
            $campaign['data']['coupon']['message'] = $coupon_message;
            $html .= Template::getHtml($campaign);
            CampaignModel::increaseCount($campaign['id'], 'display_count');
            return apply_filters('cuw_noc_template_html', $html, $order, $campaign);
        }

        return $html;
    }

    /**
     * Get coupon message.
     *
     * @param string|int|\WC_Coupon $coupon
     * @return string
     */
    public static function getCouponMessage($coupon)
    {
        $message = '';
        if (class_exists('WC_Coupon')) {
            $coupon = new \WC_Coupon($coupon);
            if (empty($coupon->get_id())) {
                $message = __('Deleted', 'checkout-upsell-and-order-bumps');
            } else if (!empty($coupon->get_usage_limit()) && $coupon->get_usage_count() >= $coupon->get_usage_limit()) {
                $message = __('Already used', 'checkout-upsell-and-order-bumps');
            } else if (!empty($coupon->get_date_expires())) {
                if (current_time('timestamp', true) < $coupon->get_date_expires()->getTimestamp()) {
                    $date_format = apply_filters('cuw_noc_expire_date_format', WP::getFormat('datetime'));
                    // translators: %s expire date.
                    $message = sprintf(__('Expires on: %s', 'checkout-upsell-and-order-bumps'), $coupon->get_date_expires()->date($date_format));
                } else {
                    $message = __('Expired', 'checkout-upsell-and-order-bumps');
                }
            } else if (!self::isValidCoupon($coupon)) {
                $message = __('Invalid', 'checkout-upsell-and-order-bumps');
            }
            $message = apply_filters('cuw_noc_message', $message, $coupon);
        }
        return $message;
    }

    /**
     * Check if the coupon is valid.
     *
     * @param int|string|\WC_Coupon $coupon
     * @return bool
     */
    public static function isValidCoupon($coupon)
    {
        if (!class_exists('WC_Coupon')) {
            return false;
        }
        $coupon = new \WC_Coupon($coupon);

        if ($coupon->get_meta('is_cuw_noc')) {
            $oder_id = $coupon->get_meta('cuw_created_order_id');
            $campaign_id = $coupon->get_meta('cuw_created_campaign_id');
            $order = !empty($oder_id) ? WC::getOrder($oder_id) : false;
            $campaign = !empty($campaign_id) ? CampaignModel::get($campaign_id, ['data']) : false;
            if (empty($order) || empty($campaign)) {
                return false;
            }
            if (in_array('wc-' . $order->get_status(), ($campaign['data']['failed_order_statuses'] ?? []))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get coupon URL.
     *
     * @param string $coupon_code
     * @param string $base_url
     * @return string
     */
    public static function getCouponUrl($coupon_code, $base_url = '')
    {
        $url = apply_filters('cuw_coupon_base_url', (!empty($base_url) ? $base_url : home_url()));
        return $url . (strpos($base_url, '?') === false ? '?' : '&') . 'cuw_coupon=' . rawurldecode(strtoupper($coupon_code));
    }

    /**
     * To get order object.
     *
     * @param \WC_Order|int $order_or_id
     * @return \WC_Order|false
     */
    public static function getOrder($order_or_id)
    {
        if (is_object($order_or_id) || is_numeric($order_or_id)) {
            $order_object = WC::getOrder($order_or_id);
        } else {
            $order_object = WC::getOrder(self::app()->input->get('key', '', 'query'));
        }
        return $order_object;
    }

    /**
     * To process order.
     *
     * @param int $order_id
     * @return void
     */
    public static function processOrderCoupon($order_id)
    {
        $order = WC::getOrder($order_id);
        if (!empty($order)) {
            if ($order->get_meta('_cuw_noc_processed')) {
                self::updateCouponData($order);
            } else {
                self::performAction($order);
            }
        }
    }

    /**
     * Check coupon is valid before apply coupon.
     *
     * @hooked woocommerce_coupon_is_valid
     */
    public static function checkCouponIsValid($status, $coupon)
    {
        return ($status && self::isValidCoupon($coupon));
    }

    /**
     * To update coupon offer.
     *
     * @param  $order
     * @return void
     */
    public static function updateCouponData($order)
    {
        $campaign_id = $order->get_meta('_cuw_processed_campaign_id');
        $coupon_code = $order->get_meta('_cuw_noc_coupon_code');
        if (!empty($coupon_code) && !empty($campaign_id)) {
            $campaign = \CUW\App\Models\Campaign::get($campaign_id);
            $expire_coupon = false;
            if (empty($campaign)) {
                $expire_coupon = true; // expire the coupon if the campaign deleted or not exist.
            } else if (self::isOrderStatusMatchedWithCampaignFailedStatus($order, $campaign)) {
                $expire_coupon = true; // expire the coupon if the campaign status not satisfy.
            }

            if ($expire_coupon) {
                self::expireCoupon($coupon_code);
            }
        }
    }

    /**
     * Override campaign usage count getting functionality.
     *
     * @hooked cuw_campaign_usage_count_based_on_current_user
     */
    public static function getUsageCountBasedOnCurrentUser($usage_count, $campaign)
    {
        if (!empty($campaign['id']) && !empty($campaign['type']) && $campaign['type'] == self::TYPE) {
            $usage_count = 0;
            $current_user = WP::getCurrentUserId();
            if (empty($current_user)) {
                $current_user = WC::getCustomerBillingEmail();
            }
            if (!empty($current_user)) {
                $table = Model::db()->prefix . 'postmeta';
                $usage_count = (int)Model::getScalar("SELECT count(post_id) FROM {$table} 
                    WHERE post_id IN (SELECT post_id FROM {$table} WHERE meta_key = 'cuw_created_campaign_id' AND meta_value = %d) 
                    AND meta_key = 'cuw_created_for' AND `meta_value` = %s;",
                    [$campaign['id'], $current_user]
                );
            }
        }
        return $usage_count;
    }

    /**
     * To check the campaign with order status.
     *
     * @param WC_Coupon $order
     * @param array $campaign
     * @return bool
     */
    protected static function isOrderStatusMatchedWithCampaignFailedStatus($order, $campaign)
    {
        $campaign_order_status = $campaign['data']['failed_order_statuses'] ?? apply_filters('cuw_noc_default_failed_order_status', ['wc-cancelled', 'wc-refunded']);
        return in_array('wc-' . $order->get_status(), $campaign_order_status);
    }

    /**
     * To expire coupon.
     *
     * @param string $coupon_code
     * @return void
     */
    public static function expireCoupon($coupon_code)
    {
        if (empty($coupon_code) || !class_exists('WC_Coupon')) {
            return;
        }

        $coupon = new \WC_Coupon($coupon_code);
        $coupon_id = $coupon->get_id();
        if (!$coupon_id) {
            return;
        }

        $current_timestamp = Functions::getDateByString('yesterday', 'Y-m-d');
        $coupon->set_date_expires($current_timestamp);
        if ($coupon->save()) {
            // Set usage limit to zero to prevent further use
            update_post_meta($coupon_id, 'usage_limit', 0);
            update_post_meta($coupon_id, 'date_expires', $current_timestamp);
        }
    }

    /**
     * To perform action.
     *
     * @param $order_obj_or_id
     * @return void
     */
    public static function performAction($order_obj_or_id)
    {
        $order = WC::getOrder($order_obj_or_id);
        if (empty($order) || !is_object($order)) {
            return;
        }

        $campaign = self::getMatchedCampaign($order);
        if (!empty($campaign) && is_array($campaign)) {
            self::processCampaignCoupon($campaign, $order);
        }
    }

    /**
     * To process coupon and order.
     *
     * @param array $campaign
     * @param \WC_Order $order
     * @return void
     */
    public static function processCampaignCoupon($campaign, $order)
    {
        $coupon_code = self::createCoupon($campaign, $order);
        if (empty($coupon_code)) {
            return;
        }
        Order::saveMeta($order, [
            '_cuw_noc_processed' => true,
            '_cuw_noc_coupon_code' => $coupon_code,
            '_cuw_processed_campaign_id' => $campaign['id'],
        ]);
        CampaignModel::increaseCount($campaign['id'], 'usage_count'); // update campaign usage count
    }

    /**
     * Create a coupon.
     *
     * @param array $campaign
     * @param \WC_Order $order
     * @return string|false
     */
    public static function createCoupon($campaign, $order)
    {
        $coupon = $campaign['data']['coupon'];
        $discount = $campaign['data']['discount'];
        $coupon_code = self::generateCouponCode($coupon['prefix'], $coupon['length']);
        $coupon_code = apply_filters('cuw_noc_generated_coupon_code', $coupon_code, $campaign);
        $coupon_description = 'Created via UpsellWP Next order coupon campaign #' . $campaign['id'];
        $coupon_description = apply_filters('cuw_noc_generated_coupon_description', $coupon_description, $campaign);

        if ($discount['type'] == 'percentage') {
            $discount_type = 'percent';
        } elseif ($discount['type'] == 'fixed_price') {
            $discount_type = 'fixed_cart';
        } else {
            return false;
        }
        $discount_value = $discount['value'];

        if (!class_exists('WC_Coupon')) {
            return false;
        }
        $wc_coupon = new \WC_Coupon();
        $wc_coupon->set_code($coupon_code);
        $wc_coupon->set_description($coupon_description);
        $wc_coupon->set_discount_type($discount_type);
        $wc_coupon->set_amount($discount_value);
        $wc_coupon->set_usage_limit(1);
        $wc_coupon->set_free_shipping(isset($coupon['free_shipping']));
        $wc_coupon->set_individual_use(isset($coupon['individual_use']));
        $wc_coupon->set_exclude_sale_items(isset($coupon['exclude_sale_items']));
        $wc_coupon->set_product_ids(!empty($coupon['product_ids']) ? $coupon['product_ids'] : []);
        $wc_coupon->set_excluded_product_ids(!empty($coupon['exclude_product_ids']) ? $coupon['exclude_product_ids'] : []);
        $wc_coupon->set_product_categories(!empty($coupon['product_categories']) ? $coupon['product_categories'] : []);
        $wc_coupon->set_excluded_product_categories(!empty($coupon['exclude_product_categories']) ? $coupon['exclude_product_categories'] : []);
        if (!empty($coupon['date_expires']) || !empty($coupon['expire_after_x_days'])) {
            $expire_date = Functions::getDateByString(self::getExpireDays($coupon, true));
            $wc_coupon->set_date_expires(apply_filters('cuw_noc_generated_coupon_date_expires', $expire_date, $campaign));
        }
        if (!isset($coupon['allow_sharing']) && $billing_email = $order->get_billing_email()) {
            $wc_coupon->set_email_restrictions([$billing_email]);
        }
        if (!empty($coupon['minimum_amount'])) {
            $wc_coupon->set_minimum_amount($coupon['minimum_amount']);
        }
        if (!empty($coupon['maximum_amount'])) {
            $wc_coupon->set_maximum_amount($coupon['maximum_amount']);
        }

        $wc_coupon = apply_filters('cuw_noc_generated_coupon_object', $wc_coupon, $campaign, $order);
        if ($wc_coupon->save()) {
            if ($coupon_id = $wc_coupon->get_id()) {
                update_post_meta($coupon_id, 'is_cuw_noc', true);
                update_post_meta($coupon_id, 'cuw_created_order_id', $order->get_id());
                update_post_meta($coupon_id, 'cuw_created_campaign_id', $campaign['id']);
                update_post_meta($coupon_id, 'cuw_created_for', get_current_user_id() ? get_current_user_id() : $order->get_billing_email());
            }
            return $coupon_code;
        }
        return false;
    }

    /**
     * Generate coupon code.
     *
     * @param string $prefix
     * @param int $length
     * @return string
     */
    public static function generateCouponCode($prefix, $length)
    {
        $coupon_code = $prefix . Functions::generateUuid($length);
        if (WC::isCouponExists($coupon_code)) {
            return self::generateCouponCode($prefix, $length);
        }
        return $coupon_code;
    }

    /**
     * Apply coupon by URL.
     */
    public static function applyCouponByUrl()
    {
        if (!empty($_GET['cuw_coupon']) && function_exists('WC') && is_object(WC())) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $coupon_code = self::app()->input->get('cuw_coupon', '', 'query');
            if (!empty($coupon_code)) {
                WC::maybeLoadSession(); // set customer session cookie if it is not set
                WC::applyCartCoupon($coupon_code);
            }
        }
    }

    /**
     * Get expire days.
     *
     * @param array $coupon_data
     * @param bool $formatted
     * @return string|int
     */
    public static function getExpireDays($coupon_data, $formatted = false)
    {
        $days = '';
        if (!empty($coupon_data['expire_after_x_days'])) {
            $days = $coupon_data['expire_after_x_days'];
        } elseif (!empty($coupon_data['date_expires'])) {
            $part = explode(' ', ltrim($coupon_data['date_expires'], '+'));
            if (in_array($part[1], ['day', 'days'])) {
                $days = $part[0];
            } elseif (in_array($part[1], ['month', 'months'])) {
                $days = $part[0] * 30;
            }
        }
        return $formatted ? ('+' . $days . ($days == 1 ? 'day' : 'days')) : $days;
    }

    public static function getMatchedCampaign($order_obj_or_id)
    {
        $order = WC::getOrder($order_obj_or_id);
        $campaigns = self::getAllCampaigns();
        $order_data = Order::getData($order);
        if (!empty($order) && is_object($order) && !empty($campaigns) && is_array($campaigns) && !empty($order_data)) {
            foreach ($campaigns as $campaign) {
                // check order status before proceed
                if (!in_array('wc-' . $order_data['status'], ($campaign['data']['order_statuses'] ?? []))) {
                    continue;
                }

                // check order is valid
                if (!self::isValidOrder($order, $campaign)) {
                    continue;
                }

                // check usage limits
                if (!Campaign::isValid($campaign)) {
                    continue;
                }

                // check conditions
                if (!Campaign::isConditionsPassed($campaign['conditions'], $order_data)) {
                    continue;
                }

                return $campaign;
            }
        }
        return [];
    }

    /**
     * Check if the order is valid before create a coupon.
     *
     * @param \WC_Order $order
     * @param array $campaign
     * @return bool
     */
    private static function isValidOrder($order, $campaign)
    {
        $order_created = $order->get_date_created();
        if (!empty($order_created)) {
            $timestamp = strtotime(get_gmt_from_date($order_created->date('Y-m-d H:i:s')));
            if (!empty($campaign['created_at']) && $timestamp < $campaign['created_at']) {
                return false;
            }
            if (!empty($campaign['start_on']) && $timestamp < $campaign['start_on']) {
                return false;
            }
            if (!empty($campaign['end_on']) && $timestamp > $campaign['end_on']) {
                return false;
            }
        }
        return true;
    }

    /**
     * To get all campaigns.
     *
     * @return array
     */
    public static function getAllCampaigns()
    {
        return CampaignModel::all([
            'status' => 'active',
            'type' => self::TYPE,
            'columns' => ['id', 'type', 'conditions', 'data', 'usage_limit', 'usage_limit_per_user', 'usage_count', 'created_at', 'start_on', 'end_on'],
            'order_by' => 'priority',
            'sort' => 'asc',
        ]);
    }

    /**
     * To load campaign contents.
     */
    public static function loadCampaignView($campaign_type, $campaign)
    {
        if ($campaign_type == self::TYPE) {
            self::app()->view('Admin/Campaign/NOC', ['action' => current_action(), 'campaign' => $campaign]);
        }
    }

    /**
     * Get action display locations.
     *
     * @return array
     */
    public static function getDisplayLocations()
    {
        return (array)apply_filters('cuw_noc_action_display_locations', [
            'do_not_display' => esc_html__("Do not display", 'checkout-upsell-and-order-bumps'),
            'woocommerce_before_thankyou' => esc_html__("Top of the Thankyou page", 'checkout-upsell-and-order-bumps'),
            'woocommerce_thankyou' => esc_html__("Bottom of the Thankyou page", 'checkout-upsell-and-order-bumps'),
            'woocommerce_order_details_before_order_table' => esc_html__("Before the Order details", 'checkout-upsell-and-order-bumps'),
            'woocommerce_order_details_after_order_table' => esc_html__("After the Order details", 'checkout-upsell-and-order-bumps'),
        ]);
    }

    /**
     * Get action display locations on email.
     *
     * @return array
     */
    public static function getDisplayLocationsOnEmail()
    {
        return (array)apply_filters('cuw_noc_action_display_locations_on_email', [
            'do_not_display' => esc_html__("Do not display", 'checkout-upsell-and-order-bumps'),
            'woocommerce_email_before_order_table' => esc_html__("Before the Order details", 'checkout-upsell-and-order-bumps'),
            'woocommerce_email_after_order_table' => esc_html__("After the Order details", 'checkout-upsell-and-order-bumps'),
        ]);
    }

    /**
     * Get action display locations on my account page.
     *
     * @return array
     */
    public static function getDisplayLocationsOnMyAccountPage()
    {
        return (array)apply_filters('cuw_noc_action_display_locations_on_myaccount_page', [
            'do_not_display' => esc_html__("Do not display", 'checkout-upsell-and-order-bumps'),
            'woocommerce_order_details_before_order_table' => esc_html__("Before the Order details", 'checkout-upsell-and-order-bumps'),
            'woocommerce_order_details_after_order_table' => esc_html__("After the Order details", 'checkout-upsell-and-order-bumps'),
        ]);
    }
}
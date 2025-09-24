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

namespace CUW\App\Helpers;

defined('ABSPATH') || exit;

use CUW\App\Models\Campaign as CampaignModel;
use CUW\App\Models\Stats;
use CUW\App\Modules\Campaigns;

class Campaign
{
    /**
     * To hold campaigns data.
     *
     * @var array
     */
    private static $data;

    /**
     * To get campaigns data.
     *
     * @param string $type
     * @return array|false
     */
    public static function get($type = '')
    {
        if (!isset(self::$data)) {
            self::$data = apply_filters('cuw_campaigns', [
                'checkout_upsells' => [
                    'title' => __("Checkout Upsells", 'checkout-upsell-and-order-bumps'),
                    'description' => __("Choose this type to show the upsell offers on the checkout page (before customer places the order)", 'checkout-upsell-and-order-bumps'),
                    'handler' => new Campaigns\CheckoutUpsells(),
                    'categories' => ['checkout'],
                    'is_single' => false,
                    'is_in_beta' => false,
                    'is_pro' => false,
                ],
                'cart_upsells' => [
                    'title' => __("Cart Upsells", 'checkout-upsell-and-order-bumps'),
                    'description' => __('Choose this type to show the upsell offers on the cart page', 'checkout-upsell-and-order-bumps'),
                    'handler' => new Campaigns\CartUpsells(),
                    'categories' => ['cart'],
                    'is_single' => false,
                    'is_in_beta' => false,
                    'is_pro' => false,
                ],
                'fbt' => [
                    'title' => __("Frequently Bought Together", 'checkout-upsell-and-order-bumps'),
                    'description' => __('Choose this type to display the frequently bought together products on the product detail page', 'checkout-upsell-and-order-bumps'),
                    'handler' => new Campaigns\FBT(),
                    'categories' => ['product'],
                    'is_single' => false,
                    'is_in_beta' => false,
                    'is_pro' => false,
                ],
                'product_addons' => [
                    'title' => __("Product Add-Ons", 'checkout-upsell-and-order-bumps'),
                    'description' => __('Choose this type to display the add-on products on the product detail page', 'checkout-upsell-and-order-bumps'),
                    'categories' => ['product'],
                    'is_single' => false,
                    'is_in_beta' => false,
                    'is_pro' => true,
                ],
                'cart_addons' => [
                    'title' => __("Cart Add-Ons", 'checkout-upsell-and-order-bumps'),
                    'description' => __('Choose this type to display the add-on products on the cart page', 'checkout-upsell-and-order-bumps'),
                    'categories' => ['cart'],
                    'is_single' => false,
                    'is_in_beta' => false,
                    'is_pro' => true,
                ],
                'post_purchase_upsells' => [
                    'title' => __("Post-purchase Upsells", 'checkout-upsell-and-order-bumps'),
                    'description' => __('Choose this type to display the upsell offer after customer clicks the "Place order button"', 'checkout-upsell-and-order-bumps'),
                    'categories' => ['post_purchase'],
                    'is_single' => false,
                    'is_in_beta' => true,
                    'is_pro' => true,
                    'page_builder' => true,
                ],
                'upsell_popups' => [
                    'title' => __("Upsell popups", 'checkout-upsell-and-order-bumps'),
                    'description' => __('Show relevant upsells and cross-sells after a customer added an item to cart or when clicking the "Proceed to Checkout" button at the cart page', 'checkout-upsell-and-order-bumps'),
                    'categories' => ['loop', 'product', 'cart'],
                    'is_single' => false,
                    'is_in_beta' => false,
                    'is_pro' => true,
                ],
                'noc' => [
                    'title' => __("Next Order Coupons", 'checkout-upsell-and-order-bumps'),
                    'description' => __('Generates a unique coupon code after a customer places a successful order and displays the coupon on the Thank you page, My account page and order emails', 'checkout-upsell-and-order-bumps'),
                    'handler' => new Campaigns\NOC(),
                    'categories' => ['thankyou'],
                    'is_single' => false,
                    'is_in_beta' => false,
                    'is_pro' => false,
                ],
                'thankyou_upsells' => [
                    'title' => __("Thank you Page Upsells", 'checkout-upsell-and-order-bumps'),
                    'description' => __('Displays upsell on the thank you page (after a customer placed an order)', 'checkout-upsell-and-order-bumps'),
                    'categories' => ['thankyou'],
                    'is_single' => false,
                    'is_in_beta' => false,
                    'is_pro' => true,
                ],
                'product_recommendations' => [
                    'title' => __("Smart Product Recommendations", 'checkout-upsell-and-order-bumps'),
                    'description' => __('Recommend products based on a number of conditions / parameters at your shop, product, cart, checkout and thank you pages', 'checkout-upsell-and-order-bumps'),
                    'categories' => ['loop', 'product', 'cart', 'checkout', 'thankyou'],
                    'is_single' => false,
                    'is_in_beta' => false,
                    'is_pro' => true,
                ],
                'double_order' => [
                    'title' => __("Double the order", 'checkout-upsell-and-order-bumps'),
                    'description' => __('Offer customers to double whatever they are ordering at the checkout and offer them a discount with one-click', 'checkout-upsell-and-order-bumps'),
                    'categories' => ['checkout'],
                    'is_single' => false,
                    'is_in_beta' => false,
                    'is_pro' => true,
                ],
                'post_purchase' => [
                    'title' => __("Post-purchase Upsells (Old)", 'checkout-upsell-and-order-bumps'),
                    'description' => __('Choose this type to display the upsell offer after customer clicks the "Place order button"', 'checkout-upsell-and-order-bumps'),
                    'categories' => ['post_purchase'],
                    'is_single' => false,
                    'is_in_beta' => false,
                    'is_pro' => true,
                    'deprecated' => true,
                ],
            ]);
        }
        if ($type !== '') {
            return isset(self::$data[$type]) ? self::$data[$type] : false;
        }
        return self::$data;
    }

    /**
     * Returns campaign categories data.
     *
     * @return array
     */
    public static function getCategories()
    {
        return [
            'loop' => __("Shop & category page", 'checkout-upsell-and-order-bumps'),
            'product' => __("Product page", 'checkout-upsell-and-order-bumps'),
            'cart' => __("Cart page", 'checkout-upsell-and-order-bumps'),
            'checkout' => __("Checkout page", 'checkout-upsell-and-order-bumps'),
            'post_purchase' => __("Post-purchase", 'checkout-upsell-and-order-bumps'),
            'thankyou' => __("Thank you page", 'checkout-upsell-and-order-bumps'),
        ];
    }

    /**
     * To check if the campaign is active or not.
     *
     * @param string $type
     * @return bool
     */
    public static function isAvailable($type)
    {
        return !empty(self::get($type)) && !empty(self::get($type)['handler']);
    }

    /**
     * To check if the campaign is single.
     *
     * @param string $type
     * @return bool
     */
    public static function isSingle($type)
    {
        return !empty(self::get($type)) && !empty(self::get($type)['is_single']);
    }

    /**
     * To check if the campaign type is created.
     *
     * @param string $type
     * @return bool
     */
    public static function isCreated($type)
    {
        return !empty(self::get($type)) && CampaignModel::getCount($type) > 0;
    }

    /**
     * Get title
     *
     * @param int|array $campaign
     * @param bool $formatted
     * @return string|false
     */
    public static function getTitle($campaign, $formatted = false)
    {
        if (is_numeric($campaign)) {
            $campaign = CampaignModel::getRowById((int)$campaign, ['id', 'title']);
        }
        if (!is_array($campaign) || !isset($campaign['title'])) {
            return false;
        }
        return ($formatted ? '#' . $campaign['id'] . ' ' : '') . $campaign['title'];
    }

    /**
     * Get edit page url
     *
     * @param int|array $campaign
     * @return string|false
     */
    public static function getEditUrl($campaign)
    {
        if (is_numeric($campaign)) {
            $campaign_id = $campaign;
        } elseif (is_array($campaign) && isset($campaign['id'])) {
            $campaign_id = $campaign['id'];
        } else {
            return false;
        }
        return 'admin.php?' . http_build_query([
                'page' => Config::get('plugin.slug', 'checkout-upsell-and-order-bumps'),
                'tab' => 'campaigns',
                'edit' => $campaign_id,
            ]);
    }

    /**
     * Get type
     *
     * @param int|array $campaign
     * @param bool $detailed
     * @return string|array|false
     */
    public static function getType($campaign, $detailed = false)
    {
        if (is_numeric($campaign)) {
            $campaign = CampaignModel::getRowById((int)$campaign, ['type']);
        }
        if (!is_array($campaign) || !isset($campaign['type'])) {
            return false;
        }

        if ($data = self::get($campaign['type'])) {
            $type = ['type' => $campaign['type'], 'text' => $data['title']];
        }
        return isset($type) ? ($detailed ? $type : $type['type']) : false;
    }

    /**
     * Get types
     *
     * @param string $key
     * @param boolean $show_pro
     * @return array|string|false
     */
    public static function getTypes($key = '', $show_pro = false)
    {
        $types = [];
        foreach (self::get() as $type => $campaign) {
            if ($show_pro && $campaign['is_pro'] && !CUW()->plugin->has_pro) {
                $types[$type] = $campaign['title'] . ' [' . esc_html__('PRO', 'checkout-upsell-and-order-bumps') . ']';
            } else {
                $types[$type] = $campaign['title'];
            }
        }
        return $key === '' ? $types : (isset($types[$key]) ? $types[$key] : false);
    }

    /**
     * Get status
     *
     * @param int|array $campaign
     * @param bool $detailed
     * @return array|string|false
     */
    public static function getStatus($campaign, $detailed = false)
    {
        if (is_numeric($campaign)) {
            $campaign = CampaignModel::getRowById((int)$campaign, ['enabled', 'start_on', 'end_on']);
        }
        if (!is_array($campaign) || !isset($campaign['enabled'])
            || !array_key_exists('start_on', $campaign) || !array_key_exists('end_on', $campaign)
        ) {
            return false;
        }

        if ($campaign['enabled'] == 1) {
            if ($campaign['end_on'] && strtotime(get_date_from_gmt(gmdate('Y-m-d H:i:s', $campaign['end_on']))) < current_time('timestamp')) {
                $status = ['code' => 'expired', 'class' => 'danger', 'text' => self::getStatuses('expired')];
            } else if ($campaign['start_on'] && strtotime(get_date_from_gmt(gmdate('Y-m-d H:i:s', $campaign['start_on']))) > current_time('timestamp')) {
                $status = ['code' => 'scheduled', 'text' => self::getStatuses('scheduled')];
            } else {
                $status = ['code' => 'active', 'text' => self::getStatuses('active')];
            }
        } else {
            $status = ['code' => 'draft', 'text' => self::getStatuses('draft')];
        }
        return $detailed ? $status : $status['code'];
    }

    /**
     * Get statuses
     */
    public static function getStatuses($key = '')
    {
        $statuses = [
            'active' => __("Active", 'checkout-upsell-and-order-bumps'),
            'scheduled' => __("Scheduled", 'checkout-upsell-and-order-bumps'),
            'expired' => __("Expired", 'checkout-upsell-and-order-bumps'),
            'publish' => __("Publish", 'checkout-upsell-and-order-bumps'),
            'draft' => __("Draft", 'checkout-upsell-and-order-bumps'),
        ];
        return $key === '' ? $statuses : (isset($statuses[$key]) ? $statuses[$key] : false);
    }

    /**
     * Get badge
     *
     * @param string $type
     * @param bool $detailed
     * @return string|array|false
     */
    public static function getBadge($type, $detailed = false)
    {
        if ($campaign = self::get($type)) {
            if ($campaign['is_pro'] && empty($campaign['handler'])) {
                $badge = ['badge' => 'pro', 'icon' => 'pro', 'class' => 'secondary', 'color' => 'green-primary', 'text' => __("Unlock with PRO", 'checkout-upsell-and-order-bumps')];
            } elseif ($campaign['is_in_beta']) {
                $badge = ['badge' => 'beta', 'icon' => 'beta', 'class' => 'warning', 'color' => 'warning', 'text' => __("Beta", 'checkout-upsell-and-order-bumps')];
            } elseif (!empty($campaign['is_new'])) {
                $badge = ['badge' => 'new', 'icon' => 'star', 'class' => 'success', 'color' => 'success', 'text' => __("New", 'checkout-upsell-and-order-bumps')];
            } elseif (!empty($campaign['deprecated'])) {
                $badge = ['badge' => 'deprecated', 'icon' => 'deprecated', 'class' => 'grey-secondary', 'color' => 'grey-secondary', 'text' => __("Deprecated", 'checkout-upsell-and-order-bumps')];
            }
        }
        return isset($badge) ? ($detailed ? $badge : $badge['badge']) : false;
    }

    /**
     * Get notices
     *
     * @param string $type
     * @param int $id
     * @return array
     */
    public static function getNotices($type, $id = 0)
    {
        $notices = apply_filters('cuw_campaign_notices', [], $type, $id);
        return is_array($notices) && isset(current($notices)['message']) ? $notices : [];
    }

    /**
     * Get products data.
     *
     * @param array $campaign
     * @return array
     */
    public static function getProductsData($campaign)
    {
        if (isset($campaign['data']['products'])) {
            return $campaign['data']['products'];
        } elseif (!empty($campaign['data']['use'])) {
            return [
                'use' => $campaign['data']['use'],
                'bundle' => !empty($campaign['data']['bundle']),
            ];
        }
        return [];
    }

    /**
     * Get display locations.
     *
     * @param array $campaign
     * @param string $key
     * @return string
     */
    public static function getDisplayLocation($campaign, $key = 'display_location')
    {
        if (!empty($campaign['data'][$key]) && $campaign['data'][$key] != 'use_global_setting') {
            return $campaign['data'][$key];
        }

        $default_locations = self::getDefaultDisplayLocations();
        if (!empty($campaign['data'][$key]) && $campaign['data'][$key] == 'use_global_setting') {
            $default_locations = array_merge($default_locations, [
                'checkout_upsells_display_location' => Config::get('offer_location', 'woocommerce_review_order_before_payment'),
                'cart_upsells_display_location' => Config::get('offer_location_at_cart', 'woocommerce_before_cart'),
                'fbt_display_location' => Config::get('fbt_display_location', 'woocommerce_after_single_product_summary'),
                'noc_display_location' => Config::get('noc_display_location', 'woocommerce_before_thankyou'),
                'noc_display_location_on_email' => Config::get('noc_display_location_on_email', 'woocommerce_email_after_order_table'),
                'noc_display_location_on_myaccount_page' => Config::get('noc_display_location_on_myaccount_page', 'woocommerce_order_details_after_order_table'),
                'thankyou_upsells_display_location' => Config::get('thankyou_upsells_location', 'woocommerce_before_thankyou'),
                'double_order_display_location' => Config::get('double_order_display_location', 'woocommerce_review_order_before_payment')
            ]);
        }
        return $default_locations[($campaign['type'] ?? '') . '_' . $key] ?? '';
    }

    /**
     * Get default campaign display locations.
     *
     * @return array
     */
    public static function getDefaultDisplayLocations()
    {
        return apply_filters('cuw_campaign_default_display_locations', [
            'checkout_upsells_display_location' => 'woocommerce_review_order_before_payment',
            'cart_upsells_display_location' => 'woocommerce_before_cart',
            'cart_upsells_display_location_on_mini_cart' => 'do_not_display',
            'fbt_display_location' => 'woocommerce_after_single_product_summary',
            'noc_display_location' => 'woocommerce_before_thankyou',
            'noc_display_location_on_email' => 'woocommerce_email_after_order_table',
            'noc_display_location_on_myaccount_page' => 'woocommerce_order_details_after_order_table',
        ]);
    }

    /**
     * Get products extra data to load with cart item data.
     *
     * @param array $campaign
     * @return array
     */
    public static function getProductsExtraData($campaign)
    {
        $extra_data = [];
        $quantity_field = isset($campaign['data']['products']['quantity_field']) ? $campaign['data']['products']['quantity_field'] : 'sync';
        $quantity_value = isset($campaign['data']['products']['quantity_value']) ? $campaign['data']['products']['quantity_value'] : '';
        if (!empty($campaign['data']['products']['allow_remove'])) {
            $extra_data['allow_remove'] = true;
        }
        if (!empty($campaign['data']['products']['change_variant'])) {
            $extra_data['change_variant'] = true;
        }
        if ($quantity_field == 'sync') {
            $extra_data['sync_quantity'] = true;
        } else if ($quantity_field == 'fixed' && !empty($quantity_value) && is_numeric($quantity_value)) {
            $extra_data['fixed_quantity'] = $quantity_value;
        }
        return $extra_data;
    }

    /**
     * Get redirect URL from campaign options data.
     *
     * @param array $campaign
     * @return string
     */
    public static function getRedirectURL($campaign)
    {
        $redirect_url = '';
        if (!empty($campaign['data']['options']['custom_redirect_url'])) {
            $redirect_url = $campaign['data']['options']['custom_redirect_url'];
        } elseif (!empty($campaign['data']['options']['redirect_url'])) {
            $redirect_url = WC::getPageUrl($campaign['data']['options']['redirect_url']);
        }
        return $redirect_url;
    }

    /**
     * Get template name.
     *
     * @param array $campaign
     * @return string
     */
    public static function getTemplateName($campaign)
    {
        $campaign_type = isset($campaign['type']) ? $campaign['type'] : '';
        if (isset($campaign['data']['template']['name'])) {
            $template = $campaign['data']['template']['name'];
            if (in_array($template, ['template-1', 'template-2', 'template-3']) && isset($campaign['type'])) { // bw compatibility since 1.4.0
                $campaign_dir_map = ['fbt' => 'fbt', 'upsell_popups' => 'popup'];
                $template = (isset($campaign_dir_map[$campaign_type]) ? $campaign_dir_map[$campaign_type] : 'products') . '/' . $template;
            } elseif (in_array($template, ['simple-action-1', 'simple-action-2'])) {
                $template = 'action/' . $template;
            } elseif ($template == 'noc-template-1') {
                $template = 'noc/template-1';
            }
        } else {
            $template = Template::getDefault($campaign_type);
        }
        return $template;
    }

    /**
     * Check all the filters are passed
     *
     * @param array $filters
     * @param array|object $data
     * @return bool
     */
    public static function isFiltersPassed($filters, $data)
    {
        if (!is_array($filters)) {
            return false;
        }

        if (empty($filters)) {
            return true;
        }

        $relation = "and";
        if (isset($filters['relation'])) {
            $relation = $filters['relation'];
            unset($filters['relation']);
        }

        $passed = 0;
        foreach ($filters as $filter) {
            if ($relation == "or" && $passed == 1) {
                break;
            }

            $is_passed = Filter::check($filter, $data);
            if ($is_passed) {
                $passed++;
            }
        }

        $count = count($filters);
        if ($relation == 'and' && $passed == $count) {
            return true;
        } elseif ($relation == 'or' && $passed >= 1) {
            return true;
        }

        return false;
    }

    /**
     * Check all the conditions are passed
     *
     * @param array $conditions
     * @param array|object $data
     * @return bool
     */
    public static function isConditionsPassed($conditions, $data)
    {
        if (!is_array($conditions)) {
            return false;
        }

        if (empty($conditions)) {
            return true;
        }

        $relation = "and";
        if (isset($conditions['relation'])) {
            $relation = $conditions['relation'];
            unset($conditions['relation']);
        }

        $passed = 0;
        foreach ($conditions as $condition) {
            if ($relation == "or" && $passed == 1) {
                break;
            }

            $is_passed = Condition::check($condition, $data);
            if ($is_passed) {
                $passed++;
            }
        }

        $count = count($conditions);
        if ($relation == 'and' && $passed == $count) {
            return true;
        } elseif ($relation == 'or' && $passed >= 1) {
            return true;
        }

        return false;
    }

    /**
     * Check if the schedule is passed
     *
     * @param int|string|null $start_on
     * @param int|string|null $end_on
     * @return bool
     */
    public static function isSchedulePassed($start_on, $end_on)
    {
        if (!empty($start_on) && strtotime(get_date_from_gmt(gmdate('Y-m-d H:i:s', $start_on))) >= current_time('timestamp')) {
            return false;
        }
        if (!empty($end_on) && strtotime(get_date_from_gmt(gmdate('Y-m-d H:i:s', $end_on))) <= current_time('timestamp')) {
            return false;
        }

        return true;
    }

    /**
     * Check if the campaign is valid
     *
     * @param array $campaign
     * @return bool
     */
    public static function isValid($campaign)
    {
        if (!empty($campaign['usage_limit']) && $campaign['usage_limit'] <= $campaign['usage_count']) {
            return false;
        }
        if (!empty($campaign['usage_limit_per_user'])) {
            $usage_count = apply_filters('cuw_campaign_usage_count_based_on_current_user', false, $campaign);
            if ($usage_count === false) {
                $usage_count = Stats::getCampaignUsageCountBasedOnCurrentUser($campaign['id']);
            }
            if ($campaign['usage_limit_per_user'] <= $usage_count) {
                return false;
            }
        }
        return true;
    }
}
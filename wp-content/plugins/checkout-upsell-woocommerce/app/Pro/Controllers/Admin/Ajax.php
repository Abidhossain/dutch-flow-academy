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

namespace CUW\App\Pro\Controllers\Admin;

defined('ABSPATH') || exit;

use CUW\App\Controllers\Controller;
use CUW\App\Helpers\Campaign;
use CUW\App\Helpers\Cart;
use CUW\App\Helpers\WC;
use CUW\App\Models\Campaign as CampaignModel;
use CUW\App\Helpers\Input;
use CUW\App\Pro\Helpers\Validate;
use CUW\App\Pro\Helpers\License;
use CUW\App\Pro\Models\Engine;
use CUW\App\Pro\Modules\Campaigns\UpsellPopups;
use CUW\App\Pro\Modules\PostPurchase\Templates;

class Ajax extends Controller
{
    /**
     * Get authenticated user request handlers.
     *
     * @return array
     */
    private static function getAuthRequestHandlers()
    {
        return [
            'save_engine' => [__CLASS__, 'saveEngine'],
            'delete_engine' => [__CLASS__, 'deleteEngine'],
            'engine_bulk_actions' => [__CLASS__, 'engineBulkActions'],
            'enable_engine' => [__CLASS__, 'enableEngine'],
            'list_skus' => [__CLASS__, 'listSKUs'],
            'list_users' => [__CLASS__, 'listUsers'],
            'get_ppu_offer_template' => [__CLASS__, 'getPostPurchaseOfferTemplate'],
            'list_engines' => [__CLASS__, 'listEngines'],
            'set_engines_list_limit' => [__CLASS__, 'setEnginesListLimit'],
            'get_upsell_popup' => [__CLASS__, 'getUpsellPopup'],
            'add_addon_to_cart' => [__CLASS__, 'addAddonToCart'],
            'remove_addon_from_cart' => [__CLASS__, 'removeAddonFromCart'],
            'perform_license_actions' => [__CLASS__, 'performLicenseActions'],
        ];
    }

    /**
     * Get non-authenticated (guest) user request handlers.
     *
     * @return array
     */
    private static function getGuestRequestHandlers()
    {
        return [
            'get_upsell_popup' => [__CLASS__, 'getUpsellPopup'],
            'add_addon_to_cart' => [__CLASS__, 'addAddonToCart'],
            'remove_addon_from_cart' => [__CLASS__, 'removeAddonFromCart'],
        ];
    }

    /**
     * Get search list items limit.
     *
     * @return int
     */
    public static function getSearchLimit()
    {
        return \CUW\App\Controllers\Admin\Ajax::getSearchLimit();
    }

    /**
     * To load handlers.
     */
    public static function load()
    {
        add_filter('cuw_ajax_auth_request_handlers', function ($handlers) {
            return array_merge($handlers, self::getAuthRequestHandlers());
        });
        add_filter('cuw_ajax_guest_request_handlers', function ($handlers) {
            return array_merge($handlers, self::getGuestRequestHandlers());
        });
    }

    /**
     * Get post purchase upsells offer template
     *
     * @return array
     */
    public static function getPostPurchaseOfferTemplate()
    {
        $data = self::app()->input->get('data', [], 'post', 'html');
        return [
            'html' => isset($data['template']['template_id']) ? Templates::getHtml($data['template']['template_id']) : '',
        ];
    }

    /**
     * List engines.
     *
     * @return array
     */
    public static function listEngines()
    {
        $query = self::app()->input->get('query', '', 'post');
        $params = self::app()->input->get('params', [], 'post');
        if (!empty($params['campaign_type'])) {
            $engine_types = \CUW\App\Pro\Helpers\Engine::get('', $params['campaign_type']);
            foreach ($engine_types as $key => $type) {
                $engine_types[$key] = "'" . $type . "'";
            }

            $engines = Engine::all([
                'status' => 'enabled',
                'types' => $engine_types,
                'columns' => ['id', 'title'],
                'like' => ['id' => $query, 'title' => $query],
                'hidden' => 0,
            ]);
            return array_map(function ($engine) {
                return [
                    'id' => (string)$engine['id'],
                    'text' => $engine['title'],
                ];
            }, $engines);
        }
        return [];
    }

    /**
     * Save Engine.
     */
    public static function saveEngine()
    {
        $form_data = self::app()->input->get('form_data', '', 'post', false);
        if (!empty($form_data)) {
            parse_str($form_data, $data);
            $sanitized_data = Input::sanitize($data);

            $errors = Validate::engine($sanitized_data);
            if (!empty($errors)) {
                return [
                    'status' => "error",
                    'message' => $errors,
                ];
            }

            $result = Engine::save($sanitized_data);
            if ($result && !empty($result['id'])) {
                $page_no = self::app()->input->get('page_no', '', 'post');
                return [
                    'status' => "success",
                    'message' => esc_html__("Engine saved", 'checkout-upsell-woocommerce'),
                    'redirect' => "tab=engines" . ($page_no > 1 ? "&page_no=" . $page_no : '') . "&edit={$result['id']}",
                    'result' => $result,
                ];
            }
        }
        return ['status' => 'error'];
    }

    /**
     * Delete Engine
     *
     * @return array
     */
    public static function deleteEngine()
    {
        $id = self::app()->input->get('id', '', 'post');
        if ($id) {
            $result = Engine::deleteById($id);
            if ($result) {
                return [
                    'status' => "success",
                    'message' => esc_html__("Engine deleted", 'checkout-upsell-woocommerce'),
                    'remove' => ['id' => $id],
                    'refresh' => true,
                ];
            }
        }

        return ['status' => "error"];
    }

    /**
     * Bulk action for engines
     *
     * @return array
     */
    public static function engineBulkActions()
    {
        $action = self::app()->input->get('bulk_action', '', 'post');
        $ids = (array)self::app()->input->get('ids', [], 'post');
        if (!empty($action)) {
            foreach ($ids as $id) {
                if ($action == 'delete') {
                    Engine::deleteById($id);
                }
            }
            if ($action == 'delete') {
                return [
                    'status' => "success",
                    'message' => esc_html__("Engines deleted", 'checkout-upsell-woocommerce'),
                    'remove' => ['ids' => $ids],
                    'refresh' => true,
                ];
            }
        }

        return ['status' => "error"];
    }

    /**
     * Enable engine
     *
     * @return array
     */
    public static function enableEngine()
    {
        $id = self::app()->input->get('id', '', 'post');
        $enabled = self::app()->input->get('enabled', '0', 'post');
        if ($id) {
            $result = Engine::updateById($id, ['enabled' => $enabled], ['%d']);
            if ($result) {
                return [
                    'status' => "success",
                    'message' => $enabled
                        ? esc_html__("Engine published", 'checkout-upsell-woocommerce')
                        : esc_html__("Engine drafted", 'checkout-upsell-woocommerce'),
                    'change' => ['id' => $id, 'status' => \CUW\App\Pro\Helpers\Engine::getStatus($id, true)],
                ];
            }
        }

        return ['status' => "error"];
    }

    /**
     * List users.
     */
    public static function listUsers()
    {
        $query = self::app()->input->get('query', '', 'post');
        $users = get_users([
            'fields' => ['ID', 'user_nicename'],
            'search' => "*$query*",
            'number' => self::getSearchLimit(),
            'orderby' => 'user_nicename'
        ]);
        return array_map(function ($user) {
            return [
                'id' => (string)$user->ID,
                'text' => $user->user_nicename,
            ];
        }, $users);
    }

    /**
     * List SKUs.
     */
    public static function listSKUs()
    {
        $query = self::app()->input->get('query', '', 'post');
        $products = wc_get_products([
            'sku' => $query,
            'limit' => self::getSearchLimit(),
        ]);
        return array_map(function ($product) {
            return [
                'id' => $product->get_sku(),
                'text' => $product->get_sku(),
            ];
        }, $products);
    }

    /**
     * Set engines list limit per page.
     *
     * @return array
     */
    public static function setEnginesListLimit()
    {
        $value = self::app()->input->get('value', '5', 'post');
        $result = self::app()->config->set('engines_per_page', $value);
        if ($result) {
            return ['status' => "success", 'refresh' => true];
        }
        return ['status' => "error"];
    }

    /**
     * Get upsell popup.
     */
    public static function getUpsellPopup()
    {
        $campaign_id = self::app()->input->get('campaign_id', '', 'post');
        $trigger = self::app()->input->get('trigger', '', 'post');
        $product_id = self::app()->input->get('product_id', '', 'post');
        $product_id = !empty($product_id) ? $product_id : Cart::getRecentlyAddedProductId();
        $page = self::app()->input->get('page', '', 'post');
        if (empty($campaign_id)) {
            $campaign = UpsellPopups::getCampaignToDisplay($trigger, $product_id);
        } elseif (is_numeric($campaign_id)) {
            $campaign = CampaignModel::get($campaign_id);
        }
        if (!empty($campaign)) {
            return [
                'campaign_id' => $campaign['id'],
                'cart_subtotal' => WC::formatPrice(WC::getCartSubtotal(WC::getDisplayTaxSettingByPage($page))),
                'html' => UpsellPopups::getProductsHtml($campaign, $trigger, $page, $product_id),
            ];
        }
        return '';
    }

    /**
     * Add cart addon product to cart
     *
     * @return array
     */
    public static function addAddonToCart()
    {
        $campaign_id = self::app()->input->get('campaign_id', '', 'post');
        $product_id = self::app()->input->get('product_id', '', 'post');
        $quantity = self::app()->input->get('quantity', '1', 'post');
        $variation_id = self::app()->input->get('variation_id', '0', 'post');
        $main_item_key = self::app()->input->get('main_item_key', '', 'post');
        if (!empty($campaign_id) && !empty($product_id) && !empty($quantity)) {
            $campaign = CampaignModel::get($campaign_id);
            $extra_data = Campaign::getProductsExtraData($campaign);
            $extra_data = array_merge($extra_data, ['main_item_key' => $main_item_key]);
            $quantity = !empty($extra_data['fixed_quantity']) ? $extra_data['fixed_quantity'] : $quantity;
            return [
                'item_key' => Cart::addProduct($campaign_id, $product_id, $quantity, $variation_id, [], $extra_data),
            ];
        }
        return ['status' => "error"];
    }

    /**
     * Remove cart addon item from cart
     *
     * @return array
     */
    public static function removeAddonFromCart()
    {
        $item_key = self::app()->input->get('item_key', '', 'post');
        if (!empty($item_key)) {
            return ['item_removed' => WC::removeCartItem($item_key)];
        }
        return ['status' => "error"];
    }

    /**
     * Perform license actions.
     *
     * @return array
     */
    public static function performLicenseActions()
    {
        $key = self::app()->input->get('key', '', 'post');
        $perform = self::app()->input->get('perform', '', 'post');
        if (empty($key) && in_array($perform, ['activate', 'check_status'])) {
            return [
                'status' => "error",
                'message' => esc_html__("License key is required.", 'checkout-upsell-woocommerce'),
            ];
        }

        if ($perform == 'activate') {
            $result = License::activate($key);
        } elseif ($perform == 'deactivate') {
            $result = License::deactivate();
        } elseif ($perform == 'check_status') {
            $result = License::checkStatus($key);
        }

        $license = [
            'status' => License::getLicenseStatus(),
            'status_text' => License::getLicenseStatus(true),
            'response' => !empty($result) ? $result : false,
        ];
        $response = [
            'status' => 'error',
            'message' => esc_html__("Something went wrong. Try again later!", 'checkout-upsell-woocommerce'),
            'license' => $license,
        ];
        if (empty($result) || $result['status'] == 'failed') {
            if (!empty($result['error'])) {
                $response['message'] = $result['error'];
            }
            return $response;
        }

        if ($perform == 'activate') {
            if ($license['status'] == 'active') {
                $response['status'] = 'success';
                $response['message'] = esc_html__("License activated. Thank you!", 'checkout-upsell-woocommerce');
            } elseif ($license['status'] == 'expired') {
                $response['message'] = esc_html__("License is expired.", 'checkout-upsell-woocommerce');
            } else {
                $response['message'] = esc_html__("Invalid license key.", 'checkout-upsell-woocommerce');
            }
        } elseif ($perform == 'deactivate') {
            if ($license['status'] == 'inactive') {
                $response['status'] = 'success';
                $response['message'] = esc_html__("License deactivated.", 'checkout-upsell-woocommerce');
            }
        } elseif ($perform == 'check_status') {
            $response['status'] = 'info';
            if ($result['status'] == 'active') {
                $response['message'] = esc_html__("License is active.", 'checkout-upsell-woocommerce');
            } elseif ($result['status'] == 'expired') {
                $response['message'] = esc_html__("License is expired.", 'checkout-upsell-woocommerce');
            } elseif ($result['status'] == 'inactive') {
                $response['message'] = esc_html__("Inactive license.", 'checkout-upsell-woocommerce');
            } else {
                $response['message'] = esc_html__("Invalid license.", 'checkout-upsell-woocommerce');
            }
        }
        return $response;
    }
}
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

namespace CUW\App\Pro\Modules\PostPurchase;

use CUW\App\Core;
use CUW\App\Helpers\Offer as OfferHelper;
use CUW\App\Helpers\WC;
use CUW\App\Helpers\WP;
use CUW\App\Models\Offer as OfferModel;
use CUW\App\Models\Campaign;
use CUW\App\Pro\Helpers\Order;
use CUW\App\Pro\Modules\Campaigns\PostPurchase;

defined('ABSPATH') || exit;

class Offer
{
    /**
     * To hold offer data.
     *
     * @return array
     */
    private static $offer_data;

    /**
     * Get offer data.
     */
    public static function getData()
    {
        if (isset(self::$offer_data)) {
            return self::$offer_data;
        }

        global $cuw_ppu_preview;
        $app = Core::instance();
        $data = [];
        $offer_id = $app->input->get('cuw_ppu_offer', '', 'query');
        $order_id = $app->input->get('cuw_order', '', 'query');
        $is_preview = $cuw_ppu_preview || $app->input->get('cuw_ppu_preview', '', 'query');
        $process_type = PostPurchase::getProcessType();
        if (!empty($offer_id) && $offer = OfferModel::get($offer_id)) {
            $data = OfferHelper::prepareData([
                'id' => $offer['id'],
                'product' => $offer['product'],
                'discount' => $offer['discount'],
                'data' => $offer['data'],
                'load_tax' => true,
            ]);
            $campaign = Campaign::get($offer['campaign_id'], ['id', 'data']);
            $data['campaign_id'] = $campaign['id'] ?? 0;
            $data['campaign_data'] = $campaign['data'] ?? [];
            $data['is_preview'] = false;
            $data['order_id'] = $order_id;

            $order = ($process_type == 'after_payment') ? Order::getOfferOrder($order_id) : WC::getOrder($order_id);
            if (!empty($order)) {
                $data['order'] = [
                    'total' => $order->get_total(),
                ];
            }
        } elseif ($is_preview || is_admin()) {
            $preview_offer_id = $app->input->get('cuw_ppu_preview_offer', '', 'query');
            if ($preview_offer_id && $offer_data = OfferModel::get($preview_offer_id)) {
                $data = OfferHelper::prepareData([
                    'id' => $offer_data['id'],
                    'product' => $offer_data['product'],
                    'discount' => $offer_data['discount'],
                    'data' => $offer_data['data'],
                    'is_preview' => true,
                ]);
                $campaign = Campaign::get($offer_data['campaign_id'], ['id', 'data']);
                $data['campaign_id'] = $campaign['id'] ?? 0;
                $data['campaign_data'] = $campaign['data'] ?? [];
            } else {
                $product = $app->input->get('product', [], 'post');
                $discount = $app->input->get('discount', [], 'post');
                $other_data = $app->input->get('data', [], 'post', 'html');
                if (empty($product['id'])) {
                    $product_object = new \WC_Product();
                    $product_object->set_props([
                        'name' => 'T-shirt',
                        'slug' => 't-shirt',
                        'regular_price' => 40,
                        'sale_price' => 30,
                    ]);
                }
                $data = OfferHelper::prepareData([
                    'product' => [
                        'id' => !empty($product['id']) ? $product['id'] : (!empty($product_object) ? $product_object : ''),
                        'qty' => $product['qty'] ?? '',
                    ],
                    'discount' => [
                        'type' => $discount['type'] ?? 'percentage',
                        'value' => $discount['value'] ?? 20,
                    ],
                    'data' => array_merge(Templates::getDefaultTemplateData(), ($other_data['template'] ?? [])),
                    'is_preview' => true,
                ]);
                if (empty($product['id'])) {
                    $data['product']['image'] = '<img src="' . $app->assets->getUrl('img/products/t-shirt.png') . '" alt="">';
                }
                $data['campaign_id'] = 0;
                $data['campaign_data'] = [];
            }

            $data['is_preview'] = true;
            $data['order_id'] = '1028';
        }
        if (!empty($data)) {
            $data['process_type'] = $process_type;
        }
        return self::$offer_data = $data;
    }

    /**
     * To get parent offer.
     *
     * @return array|false
     */
    public static function getMainOffer($campaign, $offers)
    {
        $top_offer_uuid = '';
        if (!empty($campaign['data']['offers_map'])) {
            foreach ($campaign['data']['offers_map'] as $offer_data) {
                if (empty($offer_data['parent_uuid'])) {
                    $top_offer_uuid = $offer_data['uuid'];
                    break;
                }
            }
        }
        foreach ($offers as $offer) {
            if ($offer['uuid'] == $top_offer_uuid) {
                return $offer;
            }
        }
        return false;
    }

    /**
     * Validate offer.
     *
     * @param array $offer
     * @return bool
     */
    public static function isValid($offer)
    {
        if (empty($offer) || !OfferHelper::isValid($offer)) {
            return false;
        }
        if (!WC::isPurchasableProduct($offer['product']['id'], $offer['product']['qty'])) {
            return false;
        }
        return true;
    }

    /**
     * To get offer page url.
     *
     * @return string
     */
    public static function getPageUrl($offer, $order)
    {
        if (!empty($offer['id']) && is_object($order)) {
            $template_id = self::getTemplateId($offer['campaign_id']);
            if ($template_id && $template = Templates::getData($template_id)) {
                return add_query_arg([
                    'cuw_ppu_offer' => $offer['id'],
                    'cuw_order' => $order->get_id(),
                    'cuw_nonce' => WP::createNonce('cuw_ppu_offer'),
                ], $template['url']);
            }
        }
        return '';
    }

    /**
     * Get campaign data.
     *
     * @param int $campaign_id
     * @return array
     */
    private static function getCampaignData($campaign_id)
    {
        $campaign = Campaign::get($campaign_id, ['id', 'data']);
        return !empty($campaign) && !empty($campaign['data']) ? $campaign['data'] : [];
    }

    /**
     * Get template id.
     *
     * @param int $campaign_id
     * @return int
     */
    public static function getTemplateId($campaign_id)
    {
        return self::getCampaignData($campaign_id)['page']['template'] ?? 0;
    }

    /**
     * Get template data.
     *
     * @param int $campaign_id
     * @return array
     */
    public static function getPageData($campaign_id)
    {
        return self::getCampaignData($campaign_id)['page'] ?? [];
    }
}
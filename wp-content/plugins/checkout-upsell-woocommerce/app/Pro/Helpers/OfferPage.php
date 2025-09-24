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

namespace CUW\App\Pro\Helpers;

defined('ABSPATH') || exit;

use CUW\App\Controllers\Controller;
use CUW\App\Helpers\Offer;
use CUW\App\Helpers\WC;
use CUW\App\Helpers\WP;

class OfferPage extends Controller
{
    /**
     * To hold available page ids and default page id
     *
     * @var array|int|false
     */
    private static $ids, $default_id;

    /**
     * To hold campaign and offer data
     *
     * @var array
     */
    private static $campaign_data, $offer_data;

    /**
     * To hold offer page query args
     *
     * @var array
     */
    private static $page_args = [
        'post_type' => 'page',
        'post_status' => 'publish',
        's' => '[cuw_offer_',
        'numberposts' => -1,
    ];

    /**
     * To show offer page
     *
     * @hooked template_redirect
     */
    public static function show()
    {
        if ($page_id = self::isOfferPage()) {
            add_filter('is_woocommerce', '__return_true', 100);

            if (self::isPreview()) {
                add_action('wp_head', function () {
                    ?>
                    <style>
                        a {
                            pointer-events: none;
                        }

                        /* to disable all links */
                        html {
                            background: none !important;
                        }

                        /* to fix flatsome theme dark background */
                        #wpadminbar {
                            display: none !important;
                        }

                        /* to hide admin bar */
                    </style><?php
                }, 1);
                add_filter('the_title', function ($title, $post_id) use ($page_id) {
                    return $page_id == $post_id ? '' : $title;
                }, 100, 2);
                add_filter('body_class', function ($classes) {
                    return array_merge(['cuw-page-preview'], $classes);
                }, 1);
            }

            add_filter('body_class', function ($classes) {
                return array_merge(['cuw-page'], $classes);
            }, 1);

            do_action('cuw_before_offer_page', $page_id);

            if (self::isPreview()) {
                self::app()->template('page/full-screen.php', []);
                die();
            }
        }
    }

    /**
     * To show notice
     *
     * @param string $message
     */
    public static function showOrderStatusNotice($message)
    {
        WC::addNotice(sprintf('<a href="%s" tabindex="1" class="cuw-offer-decline-link button wc-forward">%s</a> %s',
            esc_url(do_shortcode('[cuw_offer_decline_url]')),
            esc_html__('Skip offer', 'checkout-upsell-woocommerce'),
            $message
        ), 'notice');
    }

    /**
     * Check if the page is offer page
     *
     * @return int|false
     */
    public static function isOfferPage()
    {
        if (is_page() && $page_id = WP::getID()) {
            if (in_array($page_id, self::availablePageIds())) {
                return $page_id;
            }
        }
        return false;
    }

    /**
     * Check if shows offer preview
     *
     * @return bool
     */
    public static function isPreview()
    {
        return isset($_GET['cuw_offer_preview']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    }

    /**
     * Get offer id
     *
     * @return int|false
     */
    public static function getOfferId()
    {
        if (isset($_GET['cuw_offer_id'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return self::app()->input->get('cuw_offer_id', '0', 'query');
        }
        return false;
    }

    /**
     * To get campaign data
     *
     * @return array
     */
    public static function getCampaignData()
    {
        if (isset(self::$campaign_data)) {
            return self::$campaign_data;
        }
        self::$campaign_data = [];
        if ($offer_id = self::getOfferId()) {
            $offer = \CUW\App\Models\Offer::get($offer_id, ['campaign_id']);
            if ($offer && !empty($offer['campaign_id'])) {
                $campaign = \CUW\App\Models\Campaign::get($offer['campaign_id'], ['data']);
                if ($campaign && !empty($campaign['data'])) {
                    self::$campaign_data = $campaign['data'];
                }
            }
        }
        return self::$campaign_data;
    }

    /**
     * To get offer data
     *
     * @param string $key
     * @param mixed $default
     * @param int|null $offer_id
     * @return array|false
     */
    public static function getOfferData($key = '', $default = false, $offer_id = null)
    {
        $is_preview = false;
        if ($offer_id === null) {
            $offer_id = self::getOfferId();
            $is_preview = self::isPreview();
            if (empty($offer_id) && !$is_preview) {
                return $default;
            }
        }

        if (!isset(self::$offer_data) || (isset(self::$offer_data['id']) && self::$offer_data['id'] != $offer_id) || $is_preview) {
            if ($is_preview) {
                self::$offer_data = Offer::prepareData([
                    'product' => [
                        'id' => self::app()->input->get('product_id', '0', 'query'),
                        'qty' => self::app()->input->get('product_qty', '', 'query'),
                    ],
                    'discount' => [
                        'type' => self::app()->input->get('discount_type', '', 'query'),
                        'value' => self::app()->input->get('discount_value', '0', 'query'),
                    ],
                    'data' => [
                        'title' => self::app()->input->get('offer_title', '', 'query', 'html'),
                        'description' => self::app()->input->get('offer_description', '', 'query', 'html'),
                        'cta_text' => self::app()->input->get('offer_cta_text', '', 'query', 'html'),
                        'image_id' => self::app()->input->get('image_id', '0', 'query'),
                    ],
                ]);
            } elseif ($offer = \CUW\App\Models\Offer::get($offer_id)) {
                self::$offer_data = Offer::prepareData([
                    'id' => $offer['id'],
                    'product' => $offer['product'],
                    'discount' => $offer['discount'],
                    'data' => $offer['data'],
                ]);
            }
        }

        if (!empty(self::$offer_data) && is_array(self::$offer_data)) {
            if ($key === '') {
                return self::$offer_data;
            } elseif (array_key_exists($key, self::$offer_data)) {
                return self::$offer_data[$key];
            } else if (strpos($key, '.') !== false) {
                $data = self::$offer_data;
                foreach (explode('.', $key) as $index) {
                    if (!is_array($data) || !array_key_exists($index, $data)) {
                        return $default;
                    }
                    $data = &$data[$index];
                }
                return $data;
            }
        }
        return $default;
    }

    /**
     * To get default page data.
     *
     * @return array
     */
    public static function defaultPageData()
    {
        return apply_filters('cuw_offer_default_page_data', [
            'title' => '{discount} offer',
            'description' => 'Claim this offer before its gone. Add to your order with just one-click of the button.',
            'cta_text' => 'Add offer to my order',
            'image_id' => '0',
            'page_id' => OfferPage::getId(),
        ]);
    }

    /**
     * To get all available offer pages
     *
     * @return array
     */
    public static function availablePages()
    {
        $pages = [];
        $posts = get_posts(self::$page_args);
        $default_id = self::getId();
        foreach ($posts as $post) {
            $pages[$post->ID] = [
                'url' => get_permalink($post->ID),
                'slug' => $post->post_name,
                'title' => $post->post_title,
                'default' => $post->ID == $default_id,
            ];
        }
        return $pages;
    }

    /**
     * To get all available offer page ids
     *
     * @return array
     */
    public static function availablePageIds()
    {
        if (isset(self::$ids)) {
            return self::$ids;
        }
        return self::$ids = get_posts(array_merge(self::$page_args, ['fields' => 'ids']));
    }

    /**
     * Get the page valid offer id
     *
     * @param int|null $page_id
     * @return int|false
     */
    public static function getId($page_id = null)
    {
        if (!isset(self::$default_id)) {
            $default_id = self::app()->config->get('default_offer_page_id');
            self::$default_id = $default_id && get_post_status($default_id) == 'publish' ? $default_id : false;
        }

        if (empty($page_id) && isset($_GET['cuw_page_id'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $page_id = self::app()->input->get('cuw_page_id', '', 'query');
        }
        if ($page_id && get_post_status($page_id) == 'publish') {
            return $page_id;
        } elseif (self::$default_id) {
            return self::$default_id;
        }
        return false;
    }

    /**
     * Get page url
     *
     * @param int|null $page_id
     * @param array $params
     * @return string
     */
    public static function getUrl($page_id, $params = [])
    {
        if ($page_id = self::getId($page_id)) {
            $permalink = get_permalink($page_id);
            $permalink .= !empty($params) && is_array($params) ? (strpos($permalink, '?') === false ? '?' : '&') . http_build_query($params) : '';
            return $permalink;
        }
        return '';
    }
}
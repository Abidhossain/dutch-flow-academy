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

use CUW\App\Helpers\Config;

defined('ABSPATH') || exit;

class Page
{
    /**
     * Init post-purchase offer page.
     */
    public static function init()
    {
        global $post;
        if (Templates::isValid($post) || !empty($_GET['cuw_ppu_preview'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            self::initHooks($post);
            do_action('cuw_ppu_page_init', $post);
        }
    }

    /**
     * To load extra modification.
     */
    private static function initHooks($post)
    {
        // make as woocommerce page
        add_filter('is_woocommerce', '__return_true', 1000);

        // add extra classes to body
        add_filter('body_class', function ($classes) {
            return array_merge(['cuw-page', 'cuw-ppu'], $classes);
        }, 1);

        // change page title
        $page_title = Config::getSetting('ppu_page_title');
        if (!empty($page_title)) {
            $page_title = apply_filters('cuw_ppu_page_title', __($page_title, 'checkout-upsell-woocommerce'));
        }
        $page_id = !empty($post) && !is_admin() ? $post->ID : 0;
        add_filter('the_title', function ($title, $post_id) use ($page_id, $page_title) {
            return $page_id == $post_id && !empty($page_title) ? $page_title : $title;
        }, 100, 2);

        // update page content
        add_filter('the_content', function ($content) {
            $offer = Offer::getData();
            $nonce = wp_create_nonce('cuw_ppu_action');
            if (!empty($content) && !empty($offer)) {
                $content = sprintf('<form id="cuw-ppu-offer" method="post" style="%s">
                        <input type="hidden" name="cuw_ppu_action" value="" />
                        <input type="hidden" name="cuw_ppu_nonce" value="%s" />
                        %s
                    </form>',
                    $offer['styles']['template'], $nonce, $content
                );
            }
            return $content;
        }, 1000);
    }
}
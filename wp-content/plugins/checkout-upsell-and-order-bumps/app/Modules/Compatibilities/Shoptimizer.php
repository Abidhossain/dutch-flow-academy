<?php
/**
 * UpsellWP
 *
 * @package   checkout-upsell-woocommerce
 * @author    Team UpsellWP <team@upsellwp.com>
 * @copyright 2024 UpsellWP
 * @license   GPL-3.0-or-later
 * @link      https://upsellwp.com
 */

namespace CUW\App\Modules\Compatibilities;

class Shoptimizer extends Base
{
    /**
     * To run compatibility script.
     */
    public function run()
    {
        add_filter('cuw_formatted_variation_info', [__CLASS__, 'removeUnwantedText']);
        add_filter('cuw_fbt_template_html', [__CLASS__, 'addContainer'], 100, 2);

        add_action('wp_head', [__CLASS__, 'addExtraStyles'], 100);
    }

    /**
     * Remove unwanted text.
     */
    public static function removeUnwantedText($variation_name)
    {
        return !empty($variation_name) ? preg_replace('#(<span.*?>)(.*?)(</span>)#', '', $variation_name) : $variation_name;
    }

    /**
     * To add container.
     */
    public static function addContainer($html, $template_args)
    {
        if (isset($template_args['campaign']['data']['display_location'])) {
            if ($template_args['campaign']['data']['display_location'] != 'shortcode') {
                return '<div class="related products">' . $html . '</div>';
            }
        }
        return $html;
    }

    /**
     * Add extra styles.
     */
    public static function addExtraStyles()
    {
        ?>
        <style>
            .cuw-product-addons {
                display: table !important;
            }
        </style>
        <?php
    }
}
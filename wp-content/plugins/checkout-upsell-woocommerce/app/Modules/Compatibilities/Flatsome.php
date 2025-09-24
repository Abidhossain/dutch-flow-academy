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

class Flatsome extends Base
{
    /**
     * To run compatibility script.
     */
    public function run()
    {
        add_filter('cuw_fbt_template_html', [__CLASS__, 'addContainer'], 100, 2);
    }

    /**
     * To add container.
     */
    public static function addContainer($html, $template_args)
    {
        if (isset($template_args['campaign']['data']['display_location'])) {
            if ($template_args['campaign']['data']['display_location'] == 'woocommerce_after_single_product') {
                return '<div class="container">' . $html . '</div>';
            }
        }
        return $html;
    }
}

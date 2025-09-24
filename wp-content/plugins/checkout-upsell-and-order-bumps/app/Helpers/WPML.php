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

class WPML
{
    /**
     * Get the available languages
     *
     * @retun array
     */
    public static function getActiveLanguages()
    {
        return apply_filters('wpml_active_languages', [], 'orderby=id&order=desc');
    }

    /**
     * Get the current language code
     *
     * @retun bool
     */
    public static function getCurrentLanguageCode()
    {
        return defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : null;
    }
}
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

namespace CUW\App\Controllers;

defined('ABSPATH') || exit;

use CUW\App\Core;

abstract class Controller
{
    /**
     * To get app instance
     *
     * @return Core|object
     */
    public static function app()
    {
        return Core::instance();
    }
}
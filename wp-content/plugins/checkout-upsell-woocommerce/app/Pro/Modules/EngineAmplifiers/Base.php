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

namespace CUW\App\Pro\Modules\EngineAmplifiers;

defined('ABSPATH') || exit;

use CUW\App\Controllers\Controller;

abstract class Base extends Controller
{
    /**
     * Build query args.
     *
     * @param array $amplifier
     * @param array $data
     * @return array
     */
    abstract function getQueryArgs($amplifier, $data = []);

    /**
     * To get template.
     *
     * @param array $data
     * @param bool $print
     * @return bool
     */
    abstract function template($data = [], $print = false);
}
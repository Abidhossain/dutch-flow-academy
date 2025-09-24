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

namespace CUW\App\Modules\Compatibilities;

defined('ABSPATH') || exit;

use CUW\App\Controllers\Controller;

abstract class Base extends Controller
{
    /**
     * To run compatibility script.
     *
     * @return void
     */
    abstract function run();
}
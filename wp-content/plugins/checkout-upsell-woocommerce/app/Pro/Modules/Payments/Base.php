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

namespace CUW\App\Pro\Modules\Payments;

defined('ABSPATH') || exit;

use CUW\App\Controllers\Controller;

abstract class Base extends Controller
{
    /**
     * To add hooks.
     *
     * @return void
     */
    abstract function init();

    /**
     * To process payment.
     *
     * @param \WC_Order $order
     * @param object $gateway
     * @return array
     */
    abstract function process($order, $gateway);

    /**
     * To capture payment.
     *
     * @return void
     */
    abstract function capture();
}
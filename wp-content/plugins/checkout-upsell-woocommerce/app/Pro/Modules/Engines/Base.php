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

namespace CUW\App\Pro\Modules\Engines;

defined('ABSPATH') || exit;

use CUW\App\Controllers\Controller;
use CUW\App\Pro\Helpers\Engine;

abstract class Base extends Controller
{
    /**
     * Engine type.
     *
     * @var string
     */
    const TYPE = '';

    /**
     * Returns product ids.
     *
     * @param array $data
     * @param \WC_Product|\WC_Order|null $source
     * @return int[]
     */
    abstract function getProductIds($data, $source = null);

    /**
     * Get engine data.
     *
     * @return array
     */
    public static function getData()
    {
        $data = Engine::get(static::TYPE);
        if (isset($data['handler'])) {
            unset($data['handler']);
        }
        return $data;
    }
}
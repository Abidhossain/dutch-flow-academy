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

class Name extends Base
{
    /**
     * Build query args.
     *
     * @param array $amplifier
     * @param array $data
     * @return array
     */
    public function getQueryArgs($amplifier, $data = [])
    {
        return [
            'order_by' => 'title',
            'sort' => $amplifier['operator'] ?? 'asc',
        ];
    }

    /**
     * To get template.
     *
     * @return string
     */
    public function template($data = [], $print = false)
    {
        return self::app()->view('Pro/Admin/Engine/Amplifiers/Name', $data, $print);
    }
}
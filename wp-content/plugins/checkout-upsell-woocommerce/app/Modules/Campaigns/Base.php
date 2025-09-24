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

namespace CUW\App\Modules\Campaigns;

defined('ABSPATH') || exit;

use CUW\App\Controllers\Controller;
use CUW\App\Helpers\Campaign;
use CUW\App\Models\Campaign as CampaignModel;

abstract class Base extends Controller
{
    /**
     * Campaign type.
     *
     * @var string
     */
    const TYPE = '';

    /**
     * To add hooks.
     *
     * @return void
     */
    abstract function init();

    /**
     * Get campaign data.
     *
     * @return array
     */
    public static function getData()
    {
        $data = Campaign::get(static::TYPE);
        if (isset($data['handler'])) {
            unset($data['handler']);
        }
        return $data;
    }

    /**
     * Check if the campaigns is enabled or not.
     *
     * @return bool
     */
    public static function isEnabled()
    {
        return CampaignModel::isEnabled(static::TYPE);
    }
}
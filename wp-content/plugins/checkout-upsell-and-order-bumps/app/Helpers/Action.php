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

class Action
{
    /**
     * Get active actions.
     *
     * @param string $campaign_type
     * @param int|null $campaign_id
     * @return array
     */
    public static function get($campaign_type, $campaign_id = null)
    {
        $actions = WC::getSession('cuw_actions', []);
        if (!empty($campaign_type)) {
            if (!empty($campaign_id)) {
                if (isset($actions[$campaign_type][$campaign_id])) {
                    return $actions[$campaign_type][$campaign_id];
                }
            } else {
                if (isset($actions[$campaign_type])) {
                    return $actions[$campaign_type];
                }
            }
        }
        return [];
    }

    /**
     * Set action.
     *
     * @param string $campaign_type
     * @param int $campaign_id
     * @param mixed $data
     * @return bool
     */
    public static function set($campaign_type, $campaign_id, $data)
    {
        $actions = WC::getSession('cuw_actions', []);
        if (!empty($campaign_type) && !empty($campaign_id)) {
            $actions[$campaign_type][$campaign_id] = $data;
        }
        return WC::setSession('cuw_actions', $actions);
    }

    /**
     * Remove action.
     *
     * @param string $campaign_type
     * @param int|null $campaign_id
     * @return bool
     */
    public static function remove($campaign_type, $campaign_id = null)
    {
        $actions = WC::getSession('cuw_actions', []);
        if (!empty($campaign_type)) {
            if (!empty($campaign_id)) {
                if (isset($actions[$campaign_type][$campaign_id])) {
                    unset($actions[$campaign_type][$campaign_id]);
                }
            } else {
                if (isset($actions[$campaign_type])) {
                    unset($actions[$campaign_type]);
                }
            }
        }
        return WC::setSession('cuw_actions', $actions);
    }

    /**
     * Check is if the action is active or not.
     *
     * @param int $campaign_id
     * @return bool
     */
    public static function isActive($campaign_id)
    {
        $actions = WC::getSession('cuw_actions', []);
        foreach ($actions as $campaigns) {
            foreach ($campaigns as $id => $data) {
                if ($id == $campaign_id) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get actions display locations.
     *
     * @param $campaign_type
     * @return array
     */
    public static function getDisplayLocations($campaign_type)
    {
        return (array)apply_filters('cuw_action_display_locations', [], $campaign_type);
    }
}
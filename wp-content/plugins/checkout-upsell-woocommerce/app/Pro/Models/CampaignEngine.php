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

namespace CUW\App\Pro\Models;

use CUW\App\Models\Model;

defined('ABSPATH') || exit;

class CampaignEngine extends Model
{
    /**
     * Table name and output type
     *
     * @var string
     */
    const TABLE_NAME = 'campaign_engine', OUTPUT_TYPE = ARRAY_A;

    /**
     * Create engine lookup table
     */
    public function create()
    {
        $query = "CREATE TABLE {table} (
                 `campaign_id` bigint(20) unsigned NOT NULL,
                 `engine_id` bigint(20) unsigned NOT NULL
            ) {charset_collate};";

        self::execDBQuery($query); // to create or update table
    }

    /**
     * Get campaign IDs by engine ID.
     *
     * @param int $engine_id
     * @return array
     */
    public static function getCampaignIds($engine_id)
    {
        return self::getResults("SELECT `campaign_id` FROM {table} WHERE `engine_id` = %d", [$engine_id], 'campaign_id');
    }

    /**
     * Link engine.
     *
     * @param int $campaign_id
     * @param int $engine_id
     * @return void
     */
    public static function linkEngine($campaign_id, $engine_id)
    {
        self::delete(['campaign_id' => $campaign_id], ['%d']);
        self::insert(['campaign_id' => $campaign_id, 'engine_id' => $engine_id], ['%d', '%d']);
    }

    /**
     * Unlink engine.
     *
     * @param int $campaign_id
     * @return void
     */
    public static function unLinkEngine($campaign_id)
    {
        self::delete(['campaign_id' => $campaign_id], ['%d']);
    }
}

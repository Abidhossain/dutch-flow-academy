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

use CUW\App\Pro\Helpers\Engine;
use CUW\App\Pro\Models\Product as ProductModel;

defined('ABSPATH') || exit;

class Product extends Base
{
    /**
     * Engine type.
     *
     * @var string
     */
    const TYPE = 'product';

    /**
     * Returns product ids.
     *
     * @param array $data
     * @param \WC_Product|\WC_Order|null $source
     * @return int[]
     */
    public function getProductIds($data, $source = null)
    {
        $query_args = [];
        $query_args['filters'] = Engine::getFiltersQueryArgs($data['filters'], $source);
        $query_args['amplifiers'] = Engine::getAmplifiersQueryArgs($data['amplifiers']);
        $query_args['limit'] = Engine::getProductsFetchLimit();
        return ProductModel::performQuery($query_args);
    }
}

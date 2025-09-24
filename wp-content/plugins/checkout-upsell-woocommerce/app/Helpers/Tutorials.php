<?php
/**
 * UpsellWP
 *
 * @package   checkout-upsell-woocommerce
 * @author    Team UpsellWP <team@upsellwp.com>
 * @copyright 2024 UpsellWP
 * @license   GPL-3.0-or-later
 * @link      https://upsellwp.com
 */

namespace CUW\App\Helpers;

defined('ABSPATH') || exit;

class Tutorials
{
    /**
     * Returns campaign doc links.
     *
     * @param $campaign_type
     * @return array
     */
    public static function getCampaignDocumentLinks($campaign_type)
    {
        if (empty($campaign_type)) {
            return [];
        }

        $links = [
            'cart_upsells' => [
                [
                    'title' => 'Cart Upsells documentation',
                    'url' => 'https://docs.upsellwp.com/campaigns/cart-upsell',
                ],
            ],

            'checkout_upsells' => [
                [
                    'title' => 'Checkout Upsells documentation',
                    'url' => 'https://docs.upsellwp.com/campaigns/checkout-upsell',
                ],
            ],

            'fbt' => [
                [
                    'title' => 'Frequently Bought Together documentation',
                    'url' => 'https://docs.upsellwp.com/campaigns/frequently-bought-together',
                ],
            ],

            'noc' => [
                [
                    'title' => 'Next Order Coupon documentation',
                    'url' => 'https://docs.upsellwp.com/campaigns/next-order-coupons',
                ],
            ],

            'double_order' => [
                [
                    'title' => 'Double the Order documentation',
                    'url' => 'https://docs.upsellwp.com/campaigns/double-the-order',
                ],
            ],

            'upsell_popups' => [
                [
                    'title' => 'Upsell Popups documentation',
                    'url' => 'https://docs.upsellwp.com/campaigns/upsell-popups',
                ],
            ],

            'thankyou_upsells' => [
                [
                    'title' => 'Thankyou Upsells documentation',
                    'url' => 'https://docs.upsellwp.com/campaigns/thank-you-page-upsell',
                ],
            ],

            'post_purchase' => [
                [
                    'title' => 'Post-purchase documentation',
                    'url' => 'https://docs.upsellwp.com/campaigns/post-purchase-upsell',
                ],
            ],

            'product_addons' => [
                [
                    'title' => 'Product Add-ons documentation',
                    'url' => 'https://docs.upsellwp.com/campaigns/product-add-ons',
                ],
            ],

            'cart_addons' => [
                [
                    'title' => 'Cart Add-ons documentation',
                    'url' => 'https://docs.upsellwp.com/campaigns/cart-add-ons',
                ],
            ],

            'product_recommendations' => [
                [
                    'title' => 'Product Recommendations documentation',
                    'url' => 'https://docs.upsellwp.com/campaigns/product-recommendations',
                ],
            ],

            'post_purchase_upsells' => [
              [
                  'title' => 'Post-purchase upsells documentation',
                  'url' => 'https://docs.upsellwp.com/campaigns/post-purchase-upsells-new',
              ],
            ],
        ];

        return $links[$campaign_type] ?? [];
    }

    /**
     * Returns campaign video links.
     *
     * @param $campaign_type
     * @return array
     */
    public static function getYoutubeVideoLinks($campaign_type)
    {
        if (empty($campaign_type)) {
            return [];
        }

        $links = [
            'cart_upsells' => [
                [
                    'title' => 'Cart Upsells configuration',
                    'url' => 'https://youtu.be/22qPdX2MjSY',
                ],
            ],

            'checkout_upsells' => [
                [
                    'title' => 'Checkout Upsells configuration',
                    'url' => 'https://youtu.be/ZkBipU8ly-8',
                ],
            ],

            'fbt' => [
                [
                    'title' => 'Frequently Bought Together configuration',
                    'url' => 'https://youtu.be/mxpMaMZ8Zrk',
                ],
            ],

            'noc' => [
                [
                    'title' => 'Next Order Coupon configuration',
                    'url' => 'https://youtu.be/7swjleBrSBw'
                ],
            ],

            'double_order' => [
                [
                    'title' => 'Double the Order configuration',
                    'url' => 'https://youtu.be/cqqS3yB3q_I',
                ],
            ],

            'upsell_popups' => [
                [
                    'title' => 'Upsell Popups configuration',
                    'url' => 'https://youtu.be/LijR126iQl0',
                ],
            ],

            'thankyou_upsells' => [
                [
                    'title' => 'Thankyou upsells configuration',
                    'url' => 'https://youtu.be/gVtZBVOuH9o',
                ],
            ],

            'post_purchase' => [
                [
                    'title' => 'Post-purchase configuration',
                    'url' => 'https://youtu.be/5YY4YUmZ6Q8',
                ],
            ],

            'product_addons' => [
                [
                    'title' => 'Product Add-ons configuration',
                    'url' => 'https://youtu.be/YN5Lho_9t4E',
                ],
            ],

            'cart_addons' => [
                [
                    'title' => 'Cart Add-ons configuration',
                    'url' => 'https://youtu.be/9BfvIytvuqk',
                ],
            ],

            'product_recommendations' => [
                  [
                      'title' => 'Product Recommendations configuration',
                      'url' => 'https://youtu.be/MEZe-7pMAtQ',
                  ],
            ],

            'post_purchase_upsells' => [
                  [
                      'title' => 'Post-purchase upsells configuration',
                      'url' => 'https://youtu.be/VsKCkDHvj5Y',
                  ],
            ],
        ];

        return $links[$campaign_type] ?? [];
    }
}

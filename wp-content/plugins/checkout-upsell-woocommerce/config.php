<?php
return [
    'plugin' => [
        'name' => CUW_PLUGIN_NAME,
        'version' => CUW_VERSION,
        'prefix' => 'cuw_',
        'slug' => 'checkout-upsell-woocommerce',
        'url' => 'https://upsellwp.com',
        'support_url' => 'https://upsellwp.com/support',
    ],

    'requires' => [
        'php' => '7.0',
        'wordpress' => '5.3',
        'plugins' => [
            [
                'name' => 'WooCommerce',
                'version' => '4.4',
                'file' => 'woocommerce/woocommerce.php',
                'url' => 'https://wordpress.org/plugins/woocommerce',
            ],
        ],
    ],
];
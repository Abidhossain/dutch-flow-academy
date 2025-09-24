<?php
/**
 * Premium vs Free version
 *
 * @link  https://webappick.com/
 * @since 1.0.0
 *
 * @package    Woo_woo-invoice
 * @subpackage Woo_invoice/admin/partial
 * @author     Ohidul Islam <wahid@webappick.com>
 * @version    1.3.2
 */

if ( ! function_exists('add_action') ) {
    die();
}
// ### REF > utm parameters https://webappick.com/plugin/woocommerce-pdf-invoice-packing-slips/?utm_source=customer_site&utm_medium=free_vs_pro&utm_campaign=woo_invoice_free
$woo_invoice_features         = array(
    array(
        'thumb'       => esc_url(CHALLAN_FREE_PLUGIN_URL) . '/admin/images/features/automated-invoicing.svg',
        'title'       => 'Automated Invoicing',
        'description' => 'This WooCommerce PDF invoice plugin automatically generates woocommerce invoice.',
    ),
    array(
        'thumb'       => esc_url(CHALLAN_FREE_PLUGIN_URL) . '/admin/images/features/easy-setup.svg',
        'title'       => 'Easy Setup',
        'description' => 'WooCommerce PDF Invoice pro (Challan Invoice Pro) plugin is straightforward to set up.',
    ),
    array(
        'thumb'       => esc_url(CHALLAN_FREE_PLUGIN_URL) . 'admin/images/features/packing-slip.svg',
        'title'       => 'Packing Slip',
        'description' => 'Generate, customize, and print the packing slips for your orders.',
    ),
    array(
        'thumb'       => esc_url(CHALLAN_FREE_PLUGIN_URL) . 'admin/images/features/signature.svg',
        'title'       => 'Signature',
        'description' => 'Challan Invoice Pro allows you to attach your signature with invoices.',
    ),
    array(
        'thumb'       => esc_url(CHALLAN_FREE_PLUGIN_URL) . 'admin/images/features/paid-stamp.svg',
        'title'       => 'Paid Stamp',
        'description' => 'Include Paid Stamp in your invoice. Paid Stamp indicates that the bill is already paid.',
    ),
    array(
        'thumb'       => esc_url(CHALLAN_FREE_PLUGIN_URL) . 'admin/images/features/tax.svg',
        'title'       => 'Tax',
        'description' => 'This plugin supports woocommerce multiple tax classes (rates).',
    ),
    array(
        'thumb'       => esc_url(CHALLAN_FREE_PLUGIN_URL) . 'admin/images/features/bulk-download.svg',
        'title'       => 'Bulk Download',
        'description' => 'Export multiple invoices and packaging slips between a custom date range',
    ),
    array(
        'thumb'       => esc_url(CHALLAN_FREE_PLUGIN_URL) . 'admin/images/features/wpml-compatible.svg',
        'title'       => 'WPML Compatible',
        'description' => 'Take advantage of Challan Invoice plugin, as it is WPML compatible.',
    ),
    array(
        'thumb'       => esc_url(CHALLAN_FREE_PLUGIN_URL) . 'admin/images/features/dropbox.svg',
        'title'       => 'Dropbox Backup',
        'description' => 'Easily take Backup Your Customer Invoice To Dropbox.',
    ),
);
$woo_invoice_pricing_features = array(
    __('Unlimited Orders', 'webappick-pdf-invoice-for-woocommerce'),
    __('Support for 1 Year', 'webappick-pdf-invoice-for-woocommerce'),
    __('Updates for 1 Year', 'webappick-pdf-invoice-for-woocommerce'),
    __('Automated Invoicing', 'webappick-pdf-invoice-for-woocommerce'),
    __('Easy Setup', 'webappick-pdf-invoice-for-woocommerce'),
    __('Multiple Tax Classes', 'webappick-pdf-invoice-for-woocommerce'),
    __('Attach Signature', 'webappick-pdf-invoice-for-woocommerce'),
    __('Invoice Backup to Dropbox', 'webappick-pdf-invoice-for-woocommerce'),
    __('Include Paid Stamp', 'webappick-pdf-invoice-for-woocommerce'),
    __('Generate Packing Slip', 'webappick-pdf-invoice-for-woocommerce'),
    __('Bulk Download Support', 'webappick-pdf-invoice-for-woocommerce'),
    __('WPML Compatible', 'webappick-pdf-invoice-for-woocommerce'),
    __('3<sup>rd</sup> Party Plugin Supports', 'webappick-pdf-invoice-for-woocommerce'),
);
$woo_invoice_pricing          = array(
    array(
        'title'          => __('Personal', 'webappick-pdf-invoice-for-woocommerce'),
        'currency'       => '$',
        'amount'         => 69,
        'period'         => __(' Yearly', 'webappick-pdf-invoice-for-woocommerce'),
        'allowed_domain' => 1,
        'featured'       => __('Popular', 'webappick-pdf-invoice-for-woocommerce'),
        'cart_url'       => 'https://webappick.com/plugin/woocommerce-pdf-invoice-packing-slips/?add-to-cart=51372&variation_id=51374&attribute_pa_license=single-site-119-usd',
    ),
    array(
        'title'          => __('Plus', 'webappick-pdf-invoice-for-woocommerce'),
        'currency'       => '$',
        'amount'         => 129,
        'period'         => __(' Yearly', 'webappick-pdf-invoice-for-woocommerce'),
        'allowed_domain' => 5,
        'featured'       => null,
        'cart_url'       => 'https://webappick.com/plugin/woocommerce-pdf-invoice-packing-slips/?add-to-cart=51372&variation_id=51373&attribute_pa_license=two-site-199-usd',
    ),
    array(
        'title'          => __('Expert', 'webappick-pdf-invoice-for-woocommerce'),
        'currency'       => '$',
        'amount'         => 179,
        'period'         => __(' Yearly', 'webappick-pdf-invoice-for-woocommerce'),
        'allowed_domain' => 10,
        'featured'       => null,
        'cart_url'       => 'https://webappick.com/plugin/woocommerce-pdf-invoice-packing-slips/?add-to-cart=51372&variation_id=51375&attribute_pa_license=five-site-229-usd',
    ),
);
$woo_invoice_allowed_html     = array(
    'br'   => array(),
    'code' => array(),
    'sub'  => array(),
    'sup'  => array(),
    'span' => array(),
    'a'    => array(
        'href'   => array(),
        'target' => array(),
    ),
);
ob_start();
foreach ( $woo_invoice_pricing_features as $woo_invoice_feature ) { ?>
    <li class="item">
    <span class="woo-invoice-price__table__feature">
        <span class="dashicons dashicons-yes" aria-hidden="true"></span>
        <span><?php echo wp_kses($woo_invoice_feature, $woo_invoice_allowed_html); ?></span>
    </span>
    </li>
    <?php
}
$woo_invoice_pricing_features            = ob_get_clean();
$woo_invoice_comparetable                = array(
    array(
        'title' => __('Automatic Invoicing', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => true,
    ),
    array(
        'title' => __('Attach to Order Email', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => true,
    ),
    array(
        'title' => __('Invoice Download From My Account', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => true,
    ),
    array(
        'title' => __('Custom Date Format', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => true,
    ),
    array(
        'title' => __('Display ID/SKU', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => true,
    ),
    array(
        'title' => __('Display Currency Code', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => true,
    ),
    array(
        'title' => __('Display Payment Method', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => true,
    ),
    array(
        'title' => __('Packing Slip', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => true,
    ),
    array(
        'title' => __('Footer Info Section', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => true,
    ),
    array(
        'title' => __('Bulk Invoice/Packing Slip Download', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => true,
    ),
    array(
        'title' => __('Invoice Template Translation', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => true,
    ),
    array(
        'title' => __('Display Shipping Cost With Tax / Without Tax', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => true,
    ),
    array(
        'title' => __('Total Tax', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => true,
    ),
    array(
        'title' => __('Write Custom CSS', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => false,
    ),
    array(
        'title' => __('Individual Product Tax & Tax %', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => false,
    ),
    array(
        'title' => __('Total Without Tax', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => false,
    ),
    array(
        'title' => __('Paid Stamp', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => false,
    ),
    array(
        'title' => __('Authorized Signature', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => false,
    ),
    array(
        'title' => __('Custom Background', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => false,
    ),
    array(
        'title' => __('Product per Page', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => false,
    ),
    array(
        'title' => __('Custom Invoice Numbering Options', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => false,
    ),
    array(
        'title' => __('Display Product Image', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => false,
    ),
    array(
        'title' => __('Display Product Category', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => false,
    ),
    array(
        'title' => __('Display Product Short Description', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => false,
    ),
    array(
        'title' => __('Display Discounted Price', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => false,
    ),
    array(
        'title' => __('Proforma Invoice', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => false,
    ),
    array(
        'title' => __('WPML Compatibility', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => false,
    ),
    array(
        'title' => __('WooCommerce Subscription Compatibility', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => false,
    ),
    array(
        'title' => __('Order Delivery Address Print', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => false,
    ),
    array(
        'title' => __('Download Bulk Delivery Address Print', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => false,
    ),
    array(
        'title' => __('Custom Paper Size', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => false,
    ),
	array(
		'title' => __('Pagination Style', 'webappick-pdf-invoice-for-woocommerce'),
		'free'  => false,
	),
    array(
        'title' => __('Invoice Backup To Dropbox', 'webappick-pdf-invoice-for-woocommerce'),
        'free'  => false,
    ),
	array(
		'title' => __('Bar Code', 'webappick-pdf-invoice-for-woocommerce'),
		'free'  => false,
	),
	array(
		'title' => __('QR Code', 'webappick-pdf-invoice-for-woocommerce'),
		'free'  => false,
	),
    array(
        'title' => __( 'Display refund details', 'webappick-pdf-invoice-for-woocommerce' ),
        'free'  => false,
    ),
    array(
        'title' => __( 'Display fee details', 'webappick-pdf-invoice-for-woocommerce' ),
        'free'  => false,
    ),
    array(
        'title' => __( 'Generate credit note template', 'webappick-pdf-invoice-for-woocommerce' ),
        'free'  => false,
    ),
    array(
        'title' => __( 'ZATCA QR code', 'webappick-pdf-invoice-for-woocommerce' ),
        'free'  => false,
    ),
    array(
        'title' => __( 'GST Invoice', 'webappick-pdf-invoice-for-woocommerce' ),
        'free'  => false,
    ),
    array(
		'title' => __('Header and Address section ordering', 'webappick-pdf-invoice-for-woocommerce'),
		'free'  => false,
	),
);
$woo_invoice_compare_table_free_features = '';
$woo_invoice_compare_table_pro_features  = '';
foreach ( $woo_invoice_comparetable as $woo_invoice_feature ) {
    $woo_invoice_compare_table_free_features .= sprintf('<li class="%s"><span class="dashicons dashicons-%s" aria-hidden="true"></span><span>%s</span></li>', $woo_invoice_feature['free'] ? 'available' : 'unavailable', $woo_invoice_feature['free'] ? 'yes' : 'no', wp_kses($woo_invoice_feature['title'], $woo_invoice_allowed_html));
    $woo_invoice_compare_table_pro_features  .= sprintf('<li class="available"><span class="dashicons dashicons-yes" aria-hidden="true"></span><span>%s</span></li>', wp_kses($woo_invoice_feature['title'], $woo_invoice_allowed_html));
}
$woo_invoice_testimonials = array(
    array(
        'comment' => 'Plenty of available options and everything works fine and as expected. Excellent plugin overall. Keep up the good work.',
        'name'    => 'Max D.',
        'meta'    => '',
        'avatar'  => '',
    ),
    array(
        'comment' => 'Woo Invoice plugin works very well. It\'s very professional, flexible and fantastic. Thanks for making this awesome plugin.',
        'name'    => 'Gracious T.',
        'meta'    => '',
        'avatar'  => '',
    ),
    array(
        'comment' => 'I was running a woocommerce online store and used the free version for a long time. I needed some more functionalities including price with tax and discount to add in the invoice. So I have switched to the premium version. It works fine, and now I am satisfied using this plugin.',
        'name'    => 'Rosend S.',
        'meta'    => '',
        'avatar'  => '',
    ),
    array(
        'comment' => 'It saved my valuable time. The support team replied within a couple of hours and helped me in the right direction. Thanks for the kind cooperation.',
        'name'    => 'Juan C.',
        'meta'    => '',
        'avatar'  => '',
    ),
    array(
        'comment' => 'Pongo cuatro estrellas por que no era compatible con mi versión WordPress,no añadía correctamente las variantes de algún producto en la factura, por lo demás , un plugin fácil de usar, muy cómodo y completo. Serian cinco estrellas perfectamente si fuese compatible con todas las versiones WordPress. El servio técnico es rápido y correcto.',
        'name'    => 'Rubén R.',
        'meta'    => '',
        'avatar'  => '',
    ),
);
?>

<div class="wrap woo-invoice-admin woo-invoice-woo-invoice-pro-upgrade">
    <div class="woo-invoice-section woo-invoice-woo-invoice-banner">
        <div class="woo-invoice-banner">
            <a href="https://webappick.com/plugin/woocommerce-pdf-invoice-packing-slips/?utm_source=customer_site&utm_medium=free_vs_pro&utm_campaign=woo_invoice_free" target="_blank">
                <img class="woo-invoice-banner__graphics" src="<?php echo esc_url(CHALLAN_FREE_PLUGIN_URL); ?>admin/images/challan-pro-banner.svg" alt="<?php esc_attr_e('Upgrade to Wooinvoice Pro to unlock more powerful features.', 'webappick-pdf-invoice-for-woocommerce'); ?>">
            </a>
        </div>
    </div>
    <div class="clear"></div>
    <div class="woo-invoice-section woo-invoice-features">
        <div class="section-title">
            <h2><?php esc_html_e('Why Upgrade', 'webappick-pdf-invoice-for-woocommerce'); ?></h2>
            <span class="section-sub-title"><?php esc_html_e('Super charge your Store with awesome features', 'webappick-pdf-invoice-for-woocommerce'); ?></span>
        </div>
        <div class="woo-invoice-feature__list">
            <?php foreach ( $woo_invoice_features as $woo_invoice_feature ) { ?>
                <div class="woo-invoice-feature__item">
                    <div class="woo-invoice-feature__thumb">
                        <img src="<?php echo esc_url($woo_invoice_feature['thumb']); ?>" alt="<?php echo esc_attr($woo_invoice_feature['title']); ?>" title="<?php echo esc_attr($woo_invoice_feature['title']); ?>">
                    </div>
                    <div class="woo-invoice-feature__description">
                        <h3><?php echo wp_kses($woo_invoice_feature['title'], $woo_invoice_allowed_html); ?></h3>
                        <p><?php echo wp_kses($woo_invoice_feature['description'], $woo_invoice_allowed_html); ?></p>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="woo-invoice-features__more">
            <a class="woo-invoice-button woo-invoice-button-primary woo-invoice-button-hero" href="https://webappick.com/plugin/woocommerce-pdf-invoice-packing-slips/?utm_source=customer_site&utm_medium=free_vs_pro&utm_campaign=woo_invoice_free" target="_blank">See All Features <svg aria-hidden="true" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 17.5 12.5" xml:space="preserve"><path d="M10.6,1.5c-0.4-0.4-0.4-0.9,0-1.3c0.4-0.3,0.9-0.3,1.3,0l5.3,5.3c0.2,0.2,0.3,0.4,0.3,0.7s-0.1,0.5-0.3,0.7 l-5.3,5.3c-0.4,0.4-0.9,0.4-1.3,0c-0.4-0.4-0.4-0.9,0-1.3l3.8-3.8H0.9C0.4,7.1,0,6.7,0,6.2s0.4-0.9,0.9-0.9h13.5L10.6,1.5z M10.6,1.5"></path></svg></a>
        </div>
    </div>
    <div class="clear"></div>
    <div class="woo-invoice-section woo-invoice-pro-comparison">
        <div class="section-title">
            <h2 id="comparison-header"><?php printf('%s <span>%s</span> %s', esc_html__('Free', 'webappick-pdf-invoice-for-woocommerce'), esc_html__('vs', 'webappick-pdf-invoice-for-woocommerce'), esc_html__('Pro', 'webappick-pdf-invoice-for-woocommerce')); ?></h2>
            <span class="section-sub-title" id="comparison-sub-header"><?php esc_html_e('Find the plan that suits best for you business', 'webappick-pdf-invoice-for-woocommerce'); ?></span>
        </div>
        <div class="comparison-table">
            <div class="comparison free">
                <div class="product-header">
                    <img src="<?php echo esc_url(CHALLAN_FREE_PLUGIN_URL); ?>admin/images/challan-lite.svg" alt="<?php esc_attr_e('Woowoo-invoice Lite', 'webappick-pdf-invoice-for-woocommerce'); ?>">
                </div>
                <ul class="product-features">
					<?php print( $woo_invoice_compare_table_free_features ); //phpcs:ignore ?>
                </ul>
            </div>
            <div class="comparison pro">
                <div class="product-header">
                    <img src="<?php echo esc_url(CHALLAN_FREE_PLUGIN_URL); ?>admin/images/challan-pro.svg" alt="<?php esc_attr_e('Woowoo-invoice Pro', 'webappick-pdf-invoice-for-woocommerce'); ?>">
                </div>
                <ul class="product-features">
					<?php print( $woo_invoice_compare_table_pro_features ); //phpcs:ignore ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="clear"></div>
    <div class="woo-invoice-section woo-invoice-pricing">
        <div class="section-title">
            <h2 id="pricing_header"><?php esc_html_e('Take Your Products To The Next Level', 'webappick-pdf-invoice-for-woocommerce'); ?></h2>
            <span class="section-sub-title" id="pricing_sub_header"><?php esc_html_e('Choose your subscription plan and get started', 'webappick-pdf-invoice-for-woocommerce'); ?></span>
        </div>
        <div class="woo-invoice-pricing__table">
            <?php
            foreach ( $woo_invoice_pricing as $woo_invoice_price ) {
                $woo_invoice_integer = 0;
                $woo_invoice_decimal = 0;
                if ( false !== strpos($woo_invoice_price['amount'], '.') ) {
                    list( $woo_invoice_integer, $woo_invoice_decimal ) = array_map('intval', explode('.', (string) $woo_invoice_price['amount']));
                } else {
                    $woo_invoice_integer = $woo_invoice_price['amount'];
                }
                ?>
                <div class="woo-invoice-pricing__table__item">
                    <div class="woo-invoice-price__table__wrapper">
                        <div class="woo-invoice-price__table" role="table" aria-labelledby="pricing_header" aria-describedby="pricing_sub_header">
                            <div class="woo-invoice-price__table__header">
                                <h3 class="woo-invoice-price__table__heading"><?php echo esc_html($woo_invoice_price['title']); ?></h3>
                            </div>
                            <div class="woo-invoice-price__table__price">
                                <?php if ( $woo_invoice_integer > 0 || $woo_invoice_decimal > 0 ) { ?>
                                    <span class="woo-invoice-price__table__currency"><?php echo esc_html($woo_invoice_price['currency']); ?></span>
                                <?php } ?>
                                <span class="woo-invoice-price__table__amount">
                                <?php
                                if ( 0 === $woo_invoice_integer && 0 === $woo_invoice_decimal ) {
                                    printf('<span class="free">%s</span>', esc_html_x('Free', 'Free Package Price Display', 'webappick-pdf-invoice-for-woocommerce'));
                                }
                                if ( $woo_invoice_integer > 0 || $woo_invoice_decimal > 0 ) {
                                    printf('<span class="integer-part">%d</span>', esc_html($woo_invoice_integer));
                                }
                                if ( $woo_invoice_decimal > 0 ) {
                                    printf('<span class="decimal-part">.%d</span>', esc_html($woo_invoice_integer));
                                }
                                if ( ! empty($woo_invoice_price['period']) ) {
                                    printf('<span class="period">/%s</span>', esc_html($woo_invoice_price['period']));
                                }
                                ?>
                                    </span>
                                <?php
                                if ( ! empty($woo_invoice_price['allowed_domain']) ) {
                                    if ( is_numeric($woo_invoice_price['allowed_domain']) ) {
                                        $woo_invoice_allowed_domain = sprintf(
                                      _n( 'For %d Site', 'For %d Sites', $woo_invoice_price['allowed_domain'], 'webappick-pdf-invoice-for-woocommerce' ), //phpcs:ignore
                                            $woo_invoice_price['allowed_domain']
                                        );
                                    } else {
                                        $woo_invoice_allowed_domain = esc_html($woo_invoice_price['allowed_domain']);
                                    }
                                    printf('<span class="woo-invoice-price__table__amount___legend">%s</span>', esc_html($woo_invoice_allowed_domain));
                                }
                                ?>
                            </div>
             <?php printf( '<ul class="woo-invoice-price__table__features">%s</ul>', $woo_invoice_pricing_features ); // phpcs:ignore ?>
                            <div class="woo-invoice-price__table__footer">
                                <a href="<?php echo esc_url($woo_invoice_price['cart_url'] . '&utm_source=freePlugin&utm_medium=go_premium&utm_campaign=free_to_pro&utm_term=woowoo-invoice'); ?>" class="woo-invoice-button woo-invoice-button-primary woo-invoice-button-hero" target="_blank"><?php esc_html_e('Buy Now', 'webappick-pdf-invoice-for-woocommerce'); ?></a>
                            </div>
                        </div>
                <?php if ( ! empty($woo_invoice_price['featured']) ) { ?>
                            <div class="woo-invoice-price__table__ribbon">
                                <div class="woo-invoice-price__table__ribbon__inner"><?php echo esc_html($woo_invoice_price['featured']); ?></div>
                            </div>
                <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="clear"></div>
    <div class="woo-invoice-section woo-invoice-payment">
        <div class="payment-guarantee">
            <div class="guarantee-seal">
                <img src="<?php echo esc_url(CHALLAN_FREE_PLUGIN_URL); ?>admin/images/30-days-money-back-guarantee.svg" alt="<?php esc_html_e('30 Days Money Back Guarantee', 'webappick-pdf-invoice-for-woocommerce'); ?>">
            </div>
            <div class="guarantee-detail">
                <h2><?php esc_html_e('30 Days Money Back Guarantee', 'webappick-pdf-invoice-for-woocommerce'); ?></h2>
                <p><?php esc_html_e('After successful purchase, you will be eligible for conditional refund', 'webappick-pdf-invoice-for-woocommerce'); ?></p>
                <a href="https://webappick.com/refund-policy/" target="_blank"><span class="dashicons dashicons-visibility" aria-hidden="true"></span> <?php esc_html_e('Refund Policy', 'webappick-pdf-invoice-for-woocommerce'); ?></a>
            </div>
        </div>
        <div class="payment-options">
            <h3><?php esc_html_e('Payment Options:', 'webappick-pdf-invoice-for-woocommerce'); ?></h3>
            <div class="options">
                <h4><?php esc_attr_e('Credit Cards (Stripe)', 'webappick-pdf-invoice-for-woocommerce'); ?></h4>
                <ul>
                    <li><img src="<?php echo esc_url(CHALLAN_FREE_PLUGIN_URL); ?>admin/images/payment-options/visa.svg" alt="<?php esc_attr_e('Visa', 'webappick-pdf-invoice-for-woocommerce'); ?>"></li>
                    <li><img src="<?php echo esc_url(CHALLAN_FREE_PLUGIN_URL); ?>admin/images/payment-options/amex.svg" alt="<?php esc_attr_e('American Express', 'webappick-pdf-invoice-for-woocommerce'); ?>"></li>
                    <li><img src="<?php echo esc_url(CHALLAN_FREE_PLUGIN_URL); ?>admin/images/payment-options/mastercard.svg" alt="<?php esc_attr_e('Mastercard', 'webappick-pdf-invoice-for-woocommerce'); ?>"></li>
                    <li><img src="<?php echo esc_url(CHALLAN_FREE_PLUGIN_URL); ?>admin/images/payment-options/discover.svg" alt="<?php esc_attr_e('Discover', 'webappick-pdf-invoice-for-woocommerce'); ?>"></li>
                    <li><img src="<?php echo esc_url(CHALLAN_FREE_PLUGIN_URL); ?>admin/images/payment-options/jcb.svg" alt="<?php esc_attr_e('JCB', 'webappick-pdf-invoice-for-woocommerce'); ?>"></li>
                    <li><img src="<?php echo esc_url(CHALLAN_FREE_PLUGIN_URL); ?>admin/images/payment-options/diners.svg" alt="<?php esc_attr_e('Diners', 'webappick-pdf-invoice-for-woocommerce'); ?>"></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="clear"></div>
    <div class="woo-invoice-section woo-invoice-testimonial">
        <div class="section-title">
            <h2><?php esc_html_e('Our Happy Customer', 'webappick-pdf-invoice-for-woocommerce'); ?></h2>
            <span class="section-sub-title"><?php esc_html_e('Join the squad today!', 'webappick-pdf-invoice-for-woocommerce'); ?></span>
        </div>
        <div class="woo-invoice-testimonial-wrapper">
            <div class="woo-invoice-slider">
                <?php foreach ( $woo_invoice_testimonials as $woo_invoice_testimonial ) { ?>
                    <div class="testimonial-item">
                        <div class="testimonial-item__comment">
                            <p><?php echo wp_kses($woo_invoice_testimonial['comment'], $woo_invoice_allowed_html); ?></p>
                        </div>
                        <div class="testimonial-item__user">
                    <?php

                    ?>
                            <h4 class="author-name"><?php echo esc_html($woo_invoice_testimonial['name']); ?></h4>
                    <?php if ( isset($woo_invoice_testimonial['meta']) && ! empty($woo_invoice_testimonial['meta']) ) { ?>
                                <span class="author-meta"><?php echo esc_html($woo_invoice_testimonial['meta']); ?></span>
                    <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="clear"></div>
    <div class="woo-invoice-section woo-invoice-woo-invoice-cta">
        <div class="woo-invoice-cta">
            <div class="woo-invoice-cta-icon">
                <span class="dashicons dashicons-editor-help" aria-hidden="true"></span>
            </div>
            <div class="woo-invoice-cta-content">
                <h2><?php esc_html_e('Still need help?', 'webappick-pdf-invoice-for-woocommerce'); ?></h2>
                <p><?php esc_html_e('Have we not answered your question? Don\'t worry, you can contact us for more information...', 'webappick-pdf-invoice-for-woocommerce'); ?></p>
            </div>
            <div class="woo-invoice-cta-action">
                <a href="<?php echo esc_url_raw('https://wordpress.org/support/plugin/webappick-pdf-invoice-for-woocommerce/#new-topic-0');?>" class="woo-invoice-button woo-invoice-button-primary" target="_blank"><?php esc_html_e('Get Support', 'webappick-pdf-invoice-for-woocommerce'); ?></a>
            </div>
        </div>
    </div>
</div>

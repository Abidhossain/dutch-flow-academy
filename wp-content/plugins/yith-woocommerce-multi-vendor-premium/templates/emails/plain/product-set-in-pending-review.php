<?php
/**
 * Product set in pending review email
 *
 * @author YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 *
 * @var string $email_heading The email heading.
 * @var WC_Product $product The product object.
 * @var YITH_Vendor $vendor The vendor object.
 * @var WC_Email $email The email object.
 * @var bool $sent_to_admin True if it is an admin email, false otherwise.
 * @var bool $plain_text True if is plain email, false otherwise.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

$email_content = __( 'The product {product_name} has been edited by vendor {vendor}. Please, visit this link {post_link} to take a look at the changes.', 'yith-woocommerce-product-vendors' );

echo '= ' . esc_html( wp_strip_all_tags( $email_heading ) ) . " =\n\n";

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo wp_kses_post( apply_filters( "yith_wcmv_email_{$email->id}_content", $email_content, $email ) );

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo esc_html( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ), $email ) );

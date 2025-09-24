<?php
/**
 * Vendor rejected account
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 5.0.0
 *
 * @var string   $email_heading     The email heading.
 * @var string   $blogname          The blog name.
 * @var string   $user_display_name The user display name.
 * @var string   $feedback_message  Additional feedback message to add in the email.
 * @var WC_Email $email             The email object.
 * @var bool     $sent_to_admin     True if it is an admin email, false otherwise.
 * @var bool     $plain_text        True if is plain email, false otherwise.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
/* translators: %s: Customer username */
echo sprintf( esc_html__( 'Hi %s,', 'woocommerce' ), esc_html( $user_display_name ) ) . "\n\n";
echo esc_html__( 'After a careful review of your vendor application, we regret to inform you that your request has been declined.', 'yith-woocommerce-product-vendors' ) . "\n\n";
if ( ! empty( $feedback_message ) ) :
	echo esc_html__( 'Our feedback:', 'yith-woocommerce-product-vendors' ) . "\n\n";
	echo esc_html( $feedback_message ) . "\n\n";
endif;
echo esc_html__( 'We wish you all the best and thank you for taking the time to apply.', 'yith-woocommerce-product-vendors' ) . "\n\n";
echo esc_html__( 'Kind regards', 'yith-woocommerce-product-vendors' ) . ",\n\n";
echo esc_html( $blogname ) . "\n\n";
echo "\n\n----------------------------------------\n\n";
echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );

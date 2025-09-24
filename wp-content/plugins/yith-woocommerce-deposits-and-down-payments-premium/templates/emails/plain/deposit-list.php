<?php
/**
 * Deposit list (plain)
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Templates\Emails
 * @version 1.0.0
 */

/**
 * Template variables:
 *
 * @var $email        YITH_WCDP_Email
 * @var $parent_order WC_Order
 * @var $suborders    array
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

$items     = $parent_order->get_items();
$suborders = ! empty( $suborder ) ? (array) $suborder : YITH_WCDP_Suborders()->get_suborders( $parent_order->get_id() );

if ( ! empty( $suborders ) ) :
	foreach ( $suborders as $suborder ) :
		if ( ! $suborder instanceof WC_Order ) {
			$suborder = wc_get_order( $suborder );
		}

		if ( ! $suborder || $suborder->has_status( array( 'completed', 'processing' ) ) ) {
			continue;
		}

		$suborder_items = $suborder->get_items();

		foreach ( $suborder_items as $item ) :
			echo esc_html( apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, false ) );
			echo ' - ';
			echo esc_html( wp_strip_all_tags( wc_price( $item->get_total(), array( 'currency' => $suborder->get_currency() ) ) ) );
			echo "\n";

			if ( $suborder->needs_payment() ) {
				// Translators: 1. Action url.
				echo esc_html( sprintf( __( 'Pay now (%s)', 'yith-woocommerce-deposits-and-down-payments' ), esc_url( $suborder->get_checkout_payment_url() ) ) );
			} else {
				// Translators: 1. Action url.
				echo esc_html( sprintf( __( 'View (%s)', 'yith-woocommerce-deposits-and-down-payments' ), esc_url( $suborder->get_view_order_url() ) ) );
			}

			echo "\n\n";
		endforeach;
	endforeach;
endif;

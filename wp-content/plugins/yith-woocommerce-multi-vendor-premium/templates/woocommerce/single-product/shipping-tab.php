<?php
/**
 * Product page shipping tab template.
 *
 * @author     YITH
 * @package YITH\MultiVendor
 * @var string $shipping_processing_time The shipping processing time.
 * @var string $shipping_location_from The shipping location from time.
 * @var string $processing_time_title Processing time title.
 * @var string $shipping_location_from_prefix Shipping location from prefix.
 * @var string $shipping_processing_time_prefix Shipping location time prefix.
 * @var string $shipping_location_from_title Shipping location from title.
 * @var string $shipping_policy_title Shipping policy title.
 * @var string $refund_policy_title Refund policy title.
 */

/*
 * This file belongs to the YIT Framework.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.
?>

<?php if ( ! empty( $shipping_processing_time ) || ! empty( $shipping_location_from ) ) : ?>

	<div id="ready-to-ship">
		<?php

		if ( ! empty( $shipping_processing_time ) ) {

			$title_text                  = $processing_time_title;
			$shipping_location_from_part = ! empty( $shipping_location_from ) ? sprintf( '%s <strong>%s</strong>', $shipping_location_from_prefix, $shipping_location_from ) : '';

			$ready_to_ship = sprintf(
				'<p>%s <strong>%s</strong> %s</p>',
				$shipping_processing_time_prefix,
				$shipping_processing_time,
				$shipping_location_from_part
			);
		} else {
			$title_text    = $shipping_location_from_title;
			$ready_to_ship = sprintf( '<p>%s</p>', $shipping_location_from );
		}

		echo wp_kses_post( sprintf( '<h4 class="yith_wcmv_shipping_tab_title">%s</h4>', $title_text ) . $ready_to_ship );
		?>
	</div>

<?php endif; ?>

<?php if ( ! empty( $shipping_policy ) ) : ?>

	<div id="shipping-policy">
		<?php echo wp_kses_post( sprintf( '<h4 class="yith_wcmv_shipping_tab_title">%s</h4><p>%s</p>', $shipping_policy_title, $shipping_policy ) ); ?>
	</div>

<?php endif; ?>

<?php if ( ! empty( $shipping_refund_policy ) ) : ?>

	<div id="refund-policy">
		<?php echo wp_kses_post( sprintf( '<h4 class="yith_wcmv_shipping_tab_title">%s</h4><div>%s</div>', $refund_policy_title, $shipping_refund_policy ) ); ?>
	</div>

<?php endif; ?>

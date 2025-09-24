<?php
/**
 * Vendor reject modal in list table
 *
 * @since 5.0.0
 * @author YITH
 * @package YITH\MultiVendor
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>
<script type="text/template" id="tmpl-yith-wcmv-modal-vendor-reject">
	<p><?php echo wp_kses_post( __( 'You are about to reject <strong>{{data.vendor}}</strong> application for your vendor program. Once the application is rejected, the vendor will be deleted.', 'yith-woocommerce-product-vendors' ) ); ?></p>
	<p><?php echo wp_kses_post( __( 'Add an optional reason if you have an explanation for why his/her application is being denied.', 'yith-woocommerce-product-vendors' ) ); ?></p>
	<form>
		<div class="textarea-wrapper">
			<label for="reject-reason" class="screen-reader-text"><?php echo esc_html__( 'Reject message', 'yith-woocommerce-product-vendors' ); ?></label>
			<textarea class="" id="reject-reason" name="reject-reason" rows="5"></textarea>
		</div>
		<div class="submit-wrapper">
			<button class="submit button-primary"><?php echo esc_html__( 'Reject vendor', 'yith-woocommerce-product-vendors' ); ?></button>
		</div>
	</form>
</script>

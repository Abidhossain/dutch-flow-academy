<?php
/**
 * Add deposit to cart (loop product)
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Templates
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

global $product;
?>

<div class="yith-wcdp">
	<div class="yith-wcdp-loop-add-to-cart-fields" >
		<a href="<?php echo esc_url( $product_url ); ?>" class="button add-deposit-to-cart-button" ><?php echo esc_html( YITH_WCDP_Labels::get_pay_deposit_label() ); ?></a>
	</div>
</div>

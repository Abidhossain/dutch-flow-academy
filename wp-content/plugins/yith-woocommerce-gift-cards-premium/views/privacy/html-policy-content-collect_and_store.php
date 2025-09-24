<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * Policy content view.
 *
 * @package YITH\GiftCards\Views
 */

?>
<p><?php echo wp_kses_post( _x( 'While you visit our site, we collect information about you during the checkout process on our store. We\'ll track:', 'Privacy Policy Content', 'yith-woocommerce-gift-cards' ) ); ?></p>
<ul>
	<li><?php echo wp_kses_post( _x( 'Sender\'s name, recipient\'s name and email address:  we\'ll use this to create gift card products', 'Privacy Policy Content', 'yith-woocommerce-gift-cards' ) ); ?></li>
</ul>

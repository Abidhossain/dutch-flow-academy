<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * Policy content view.
 *
 * @package YITH\GiftCards\Views
 */

?>
<p><?php echo wp_kses_post( _x( 'Members of our team have access to the information you provide us. For example, both Administrators and Shop Managers can access:', 'Privacy Policy Content', 'yith-woocommerce-gift-cards' ) ); ?></p>
<ul>
	<li><?php echo wp_kses_post( _x( 'Sender name, recipient name, recipient email address, delivery date, message, amount and balance of the gift card you have purchased', 'Privacy Policy Content', 'yith-woocommerce-gift-cards' ) ); ?></li>
</ul>

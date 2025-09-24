<?php
/**
 * My gift cards
 *
 * @package YITH\GiftCards\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$text_input = array(
	'type' => 'text',
	'id'   => 'ywgc-link-code',
	'name' => 'ywgc-link-code',
);

$user = wp_get_current_user();

?>
<script type="text/template" id="tmpl-add-new-gift-card-template">
	<div id="ywgc-add-new-gift-card clearfix">
		<h3 class="ywgc-add-new-gift-card-modal-title"><?php echo esc_html_x( 'Link a gift card to your account', 'Modal title in my account', 'yith-woocommerce-gift-cards' ); ?></h3>
		<div class="ywgc-add-new-gift-card-form">
			<div class="yith-add-new-gc-my-account-notice-message not_valid ywgc-hidden" style="font-weight: bolder; color: red;"><?php esc_html_e( 'The code added is not associated to any existing gift card.', 'yith-woocommerce-gift-cards' ); ?></div>
			<div class="yith-add-new-gc-my-account-notice-message valid ywgc-hidden" style="font-weight: bolder; color: green;"><?php esc_html_e( 'The gift card code is now linked to your account.', 'yith-woocommerce-gift-cards' ); ?></div>
			<span class="ywgc-add-new-gift-card-label"><?php esc_html_e( 'Gift Card code', 'yith-woocommerce-gift-cards' ); ?></span>
			<?php yith_plugin_fw_get_field( $text_input, true ); ?>
			<button class="ywgc-link-gift-card-submit-button" data-current-user-id="<?php echo esc_attr( $user->ID ); ?>"><?php esc_html_e( 'Add it', 'yith-woocommerce-gift-cards' ); ?></button>
		</div>
	</div>
</script>

<?php
/**
 * Create gift card code modal footer
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\GiftCards\Views
 * @since   4.0.0
 */

?>
<div class="yith-gift-card-generator-modal-footer-wrapper">
	<a id="yith-gift-card-generator-modal-button" class="yith-gift-card-generator-modal-button yith-plugin-fw__button--primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=gift_card' ) ); ?>"><?php echo esc_html__( 'Next', 'yith-woocommerce-gift-cards' ); ?></a>
</div>

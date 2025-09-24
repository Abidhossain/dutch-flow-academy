<?php
/**
 * Announcement create/edit modal list table
 *
 * @since   4.0.0
 * @author  YITH
 * @package YITH\MultiVendor
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>

<script type="text/template" id="tmpl-yith-wcmv-modal-announcement">
	<form method="POST" class="announcement-modal-form">
		<table class="form-table">
			<?php YITH_Vendors_Announcements()->admin->print_announcement_fields(); ?>
		</table>
		<input type="hidden" name="announcement_id" id="announcement_id" value="{{data.announcement_id}}"/>
		<input type="hidden" name="modal_action" id="modal_action" value="yith_wcmv_announcement_save"/>
		<?php wp_nonce_field( 'yith_wcmv_announcement_save' ); ?>
	</form>
</script>
<script type="text/template" id="tmpl-yith-wcmv-modal-announcement-buttons">
	<div class="announcement-modal-buttons">
		<button class="yith-plugin-fw__button--secondary yith-plugin-fw__button--xl close-announcement-modal"><?php echo esc_html_x( 'Cancel', '[Admin]Modal button label', 'yith-woocommerce-product-vendors' ); ?></button>
		<button class="yith-plugin-fw__button--primary yith-plugin-fw__button--xl save-announcement-modal"><?php echo esc_html_x( 'Save', '[Admin]Modal button label', 'yith-woocommerce-product-vendors' ); ?></button>
	</div>
</script>
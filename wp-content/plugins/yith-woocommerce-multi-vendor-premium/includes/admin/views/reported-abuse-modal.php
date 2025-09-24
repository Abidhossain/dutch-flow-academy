<?php
/**
 * Reported abuse detail modal list table
 *
 * @since   4.0.0
 * @author  YITH
 * @package YITH\MultiVendor
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>

<script type="text/template" id="tmpl-yith-wcmv-modal-reported-abuse">
	<div class="reported-abuse-message">
		{{{data.message}}}
	</div>
</script>
<script type="text/template" id="tmpl-yith-wcmv-modal-reported-abuse-buttons">
	<div class="reported-abuse-modal-buttons">
		<a href="mailto:{{data.email}}" class="yith-plugin-fw__button--primary yith-plugin-fw__button--xl save-announcement-modal">
			<?php echo esc_html_x( 'Reply', '[Admin]Modal button label', 'yith-woocommerce-product-vendors' ); ?>
		</a>
	</div>
</script>
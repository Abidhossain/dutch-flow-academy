<?php
/**
 * Report Abuse link template
 *
 * @since 4.0.0
 * @package YITH\MultiVendor
 * @var YITH_Vendor $vendor The current vendor if any.
 * @var string $abuse_text The report abuse link text.
 * @var string $button_class The report abuse modal button class.
 * @var string $submit_label modal_title
 * @var array $current_user Current user data.
 * @var string $title The report abuse modal title.
 * @var integer $vendor_id Current vendor ID if any, otherwise 0.
 * @var integer $product_id Current product ID if any, otherwise 0.
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>

<div id="yith-wpv-abuse-report-link">
	<a href="javascript:void(0)" id="yith-wpv-abuse">
		<?php echo esc_html( $abuse_text ); ?>
	</a>
</div>

<div id="yith-wpv-abuse-report" style="display: none;"></div>
<script type="text/template" id="tmpl-yith-wcmv-abuse-report-content">
	<h3 class="yith-wpv-abuse-report-title">
		<?php
		echo esc_html( $title );
		if ( ! empty( $subtitle ) ) :
			?>
			<span class="yith-wpv-abuse-report-subtitle"><?php echo esc_html( $subtitle ); ?></span>
		<?php endif; ?>
	</h3>
	<form action="#" method="post" id="report-abuse" class="report-abuse-form">
		<label for="report_abuse_name">
			<span><?php esc_html_e( 'Your name', 'yith-woocommerce-product-vendors' ); ?></span>
			<input type="text" class="input-text " id="report_abuse_name" name="report_abuse[name]" value="<?php echo esc_attr( $current_user['display_name'] ); ?>" required/>
		</label>
		<label for="report_abuse_email">
			<span><?php esc_html_e( 'Your email address', 'yith-woocommerce-product-vendors' ); ?></span>
			<input type="email" class="input-text " id="report_abuse_email" name="report_abuse[email]" value="<?php echo esc_attr( $current_user['user_email'] ); ?>" required/>
		</label>
		<label for="report_abuse_message" class="wide-field">
			<span><?php esc_html_e( 'Explain the issue to us. We will contact you as soon as possible.', 'yith-woocommerce-product-vendors' ); ?></span>
			<textarea id="report_abuse_message" name="report_abuse[message]" rows="5" required></textarea>
		</label>

		<input type="hidden" name="report_abuse[spam]" value="" class="report_abuse_anti_spam"/>
		<input type="hidden" name="report_abuse[vendor_id]" value="<?php echo absint( $vendor_id ); ?>" />
		<input type="hidden" name="report_abuse[product_id]" value="<?php echo absint( $product_id ); ?>" />
		<input type="hidden" name="action" value="send_report_abuse" />
		<input type="submit" class="submit-report-abuse <?php echo esc_attr( $button_class ); ?>" name="report_abuse[submit]" value="<?php echo esc_html( $submit_label ); ?>" />
	</form>
</script>
<script type="text/template" id="tmpl-yith-wcmv-abuse-report-sent">
	<div class="yith-wpv-abuse-report-sent">
		<img src="<?php echo esc_url( YITH_WPV_ASSETS_URL ); ?>icons/message-sent.svg" alt="" width="100px">
		<div class="yith-wpv-abuse-report-sent-message">
			<?php esc_html_e( 'Thank you!', 'yith-woocommerce-product-vendors' ); ?>
			<span><?php esc_html_e( 'We will get in touch with you as soon as possible.', 'yith-woocommerce-product-vendors' ); ?></span>
		</div>
	</div>
</script>

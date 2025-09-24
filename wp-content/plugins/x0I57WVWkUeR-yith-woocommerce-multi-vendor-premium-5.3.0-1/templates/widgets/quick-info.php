<?php
/**
 * Quick Info widget template
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 1.0.0
 * @var boolean      $is_singular  True if is singular product, false otherwise.
 * @var WC_Product   $product      Product instance.
 * @var YITH_Vendor  $vendor       Vendor instance.
 * @var WP_User      $current_user Current user instance.
 * @var string       $title        Widget title.
 * @var string       $description  Widget description.
 * @var string       $subject      Form subject value.
 * @var string       $submit_label Form submit button label.
 * @var array        $message      The form message array data.
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

$button_class         = apply_filters( 'yith_wpv_quick_info_button_class', 'submit' );
$textarea_placeholder = apply_filters( 'yith_wcmv_quick_info_placeholder', __( 'Message', 'yith-woocommerce-product-vendors' ) );
?>

<div class="clearfix widget yith-wpv-quick-info">
	<h3 class="widget-title"><?php echo esc_html( $title ); ?></h3>
	<div class="yith-wpv-quick-info-wrapper">
		<?php if ( ! empty( $message ) ) : ?>
			<div class="woocommerce-<?php echo esc_attr( $message['class'] ?? '' ); ?>"><?php echo esc_html( $message['message'] ?? '' ); ?></div>
		<?php else : ?>
			<p><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>

		<form action="" method="post" id="respond">
			<input type="text" class="input-text " name="quick_info[name]" value="<?php echo esc_attr( $current_user->display_name ); ?>" placeholder="<?php esc_html_e( 'Name', 'yith-woocommerce-product-vendors' ); ?>" required/>
			<input type="text" class="input-text " name="quick_info[subject]" value="<?php echo esc_attr( $subject ); ?>" placeholder="<?php esc_html_e( 'Subject', 'yith-woocommerce-product-vendors' ); ?>" required/>
			<input type="email" class="input-text " name="quick_info[email]" value="<?php echo esc_attr( $current_user->user_email ); ?>" placeholder="<?php esc_html_e( 'Email', 'yith-woocommerce-product-vendors' ); ?>" required/>
			<textarea name="quick_info[message]" rows="5" placeholder="<?php echo esc_html( $textarea_placeholder ); ?>" required></textarea>
			<input type="submit" class="<?php echo esc_attr( $button_class ); ?>" id="submit" name="quick_info[submit]" value="<?php echo esc_attr( $submit_label ); ?>"/>
			<input type="hidden" name="quick_info[spam]" value=""/>
			<input type="hidden" name="quick_info[vendor_id]" value="<?php echo absint( $vendor->get_id() ); ?>"/>
			<?php if ( $is_singular ) : ?>
				<input type="hidden" name="quick_info[product_id]" value="<?php echo absint( $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id() ); ?>"/>
			<?php endif; ?>
			<?php wp_nonce_field( 'yith_vendor_quick_info_submitted', 'yith_vendor_quick_info_submitted' ); ?>
		</form>
	</div>
</div>
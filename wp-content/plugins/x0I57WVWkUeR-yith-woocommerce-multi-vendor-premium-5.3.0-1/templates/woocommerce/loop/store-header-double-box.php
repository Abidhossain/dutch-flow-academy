<?php
/**
 * Vendor store header template. DOUBLE BOX style
 *
 * @since 1.0.0
 * @package YITH\MultiVendor
 * @var YITH_Vendor $vendor $vendor instance
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

<div id="yith-wcmv-store-header-<?php echo esc_attr( $vendor->get_id() ); ?>" class="store-header-wrapper double-box">
	<!-- Header Image -->
	<?php if ( ! empty( $header_image ) ) : ?>
		<div class="store-header-image">
			<?php echo wp_kses_post( $header_image ); ?>
		</div>
	<?php endif; ?>

	<!-- Store Information -->
	<div class="store-info-wrapper">
		<div class="store-avatar-name">
			<?php if ( ! empty( $avatar ) ) : ?>
				<span class="avatar">
					<?php echo wp_kses_post( $avatar ); ?>
				</span>
			<?php endif; ?>
			<span class="store-name">
					<?php echo esc_html( $vendor->get_name() ); ?>
				</span>
		</div>

		<?php
		/**
		 * The yith_wcmv_vendor_header_store_info hook. Print store info data list.
		 *
		 * @since 4.0.0
		 */
		do_action( 'yith_wcmv_vendor_header_store_info', $vendor );
		?>
	</div>
	<?php
	/**
	 * The yith_wcmv_vendor_header_store_socials hook. Print store socials link.
	 *
	 * @since 4.0.0
	 */
	do_action( 'yith_wcmv_vendor_header_store_socials', $vendor );
	?>
</div>

<?php
/**
 * The yith_wcmv_vendor_header_store_description hook. Print store description.
 *
 * @since 4.0.0
 */
do_action( 'yith_wcmv_vendor_header_store_description', $vendor );

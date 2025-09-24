<?php
/**
 * Vendor store header template. AVATAR BOX style.
 *
 * @since   1.0.0
 * @package YITH\MultiVendor
 * @var YITH_Vendor $vendor $vendor instance
 * @var string $header_image Header image url.
 * @var integer $header_height Header height value.
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

$style = '';
if ( ! empty( $header_image ) ) {
	$style = "background: url({$header_image}) top center;background-size: cover;height:{$header_height}px";
}

?>

<div id="yith-wcmv-store-header-<?php echo esc_attr( $vendor->get_id() ); ?>" class="store-header-wrapper avatar-box">

	<div class="store-avatar-wrapper" style="<?php echo esc_attr( $style ); ?>">
		<div class="avatar">
			<?php echo wp_kses_post( $avatar ); ?>
		</div>
	</div>

	<div class="store-name-wrapper">
	<span class="store-name">
		<?php echo esc_html( $vendor->get_name() ); ?>
	</span>

		<?php
		/**
		 * The yith_wcmv_vendor_header_store_socials hook. Print store socials link.
		 *
		 * @since 4.0.0
		 */
		do_action( 'yith_wcmv_vendor_header_store_socials', $vendor );
		?>

	</div>

	<!-- Store Information -->
	<div class="store-info-wrapper">
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
	 * The yith_wcmv_vendor_header_store_description hook. Print store description.
	 *
	 * @since 4.0.0
	 */
	do_action( 'yith_wcmv_vendor_header_store_description', $vendor );
	?>
</div>

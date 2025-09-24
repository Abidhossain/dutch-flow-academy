<?php
/**
 * My account custom dashboard content.
 *
 * @since      Version 1.0.0
 * @author     YITH
 * @package    YITH\MultiVendor
 * @var YITH_Vendor $vendor  The current vendor.
 * @var string      $title   The vendor status box title.
 * @var string      $message The vendor status box message.
 * @var array       $cta     The vendor status box CTA.
 */

/*
 * This file belongs to the YIT Framework.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>

<div id="yith-vendor-dashboard" class="status-<?php echo esc_attr( $vendor->get_status() ); ?>">
	<span class="yith-vendor-dashboard-icon">
		<img src="<?php echo esc_url( YITH_WPV_ASSETS_URL ); ?>images/vendor-<?php echo esc_attr( $vendor->get_status() ); ?>.svg" width="120" alt=""/>
	</span>
	<h4 class="yith-vendor-dashboard-title"><?php echo esc_html( $title ); ?></h4>
	<p class="yith-vendor-dashboard-message"><?php echo wp_kses_post( $message ); ?></p>
	<?php if ( ! empty( $cta ) ) : ?>
		<a href="<?php echo esc_url( $cta['url'] ?? '#' ); ?>" class="yith-vendor-dashboard-cta" title=""><?php echo esc_html( $cta['label'] ); ?></a>
	<?php endif; ?>
</div>

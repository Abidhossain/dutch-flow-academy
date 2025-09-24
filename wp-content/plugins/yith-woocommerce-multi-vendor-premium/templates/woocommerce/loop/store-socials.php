<?php
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

<!-- Store Socials -->
<div class="store-socials">
		<span class="socials-container">
			<?php foreach ( $socials as $social ) : ?>
				<a class="vendor-social-uri" href="<?php echo esc_url( $social['uri'] ); ?>" title="<?php echo esc_attr( $social['label'] ); ?>" target="_blank">
						<i class="<?php echo esc_attr( $social['icon'] ); ?>"></i>
				</a>
			<?php endforeach; ?>
		</span>
</div>
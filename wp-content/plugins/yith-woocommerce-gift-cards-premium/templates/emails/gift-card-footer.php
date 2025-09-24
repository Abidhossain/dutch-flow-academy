<?php
/**
 * Add a footer for the gift card email
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\GiftCards\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="ywgc-footer">
	<a target="_blank" class="center-email" href="<?php echo esc_url( $shop_link ); ?>"><?php echo wp_kses_post( $shop_name ); ?></a>
</div>

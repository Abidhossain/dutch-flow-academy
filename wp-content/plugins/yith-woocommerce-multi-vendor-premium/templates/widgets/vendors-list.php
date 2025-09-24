<?php
/**
 * YITH_Vendors_List_Widget template
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @var bool $hide_empty True of hide empty vendor, false otherwise.
 * @var bool $show_product_number True to show product number, false otherwise.
 * @var YITH_Vendor[] $vendors An array of vendors.
 * @var string $title THe widget title.
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

<div class="clearfix widget vendors-list">
	<h3 class="widget-title"><?php echo esc_html( $title ); ?></h3>
	<?php
	if ( ! empty( $vendors ) ) :
		?>
		<ul>
			<?php
			foreach ( $vendors as $vendor ) :
				$product_number = count( $vendor->get_products() );
				if ( ! empty( $hide_empty ) && empty( $product_number ) || empty( $vendor->get_owner() ) ) {
					continue;
				}
				?>
				<li>
					<a class="vendor-store-url" href="<?php echo esc_url( $vendor->get_url() ); ?>">
						<?php echo esc_html( $vendor->get_name() ); ?>
					</a>
					<?php
					if ( isset( $show_product_number ) && ! empty( $show_product_number ) ) {
						echo " ({$product_number}) "; // phpcs:ignore
					}
					?>
				</li>
				<?php
			endforeach;
			?>
		</ul>
	<?php endif; ?>
</div>

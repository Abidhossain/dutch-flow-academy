<?php
/**
 * Admin vendors list table
 *
 * @since   4.0.0
 * @author  YITH
 * @package YITH\MultiVendor
 * @var YITH_Vendors_Vendors_List_Table $vendors_table YITH_Vendors_Vendors_List_Table class instance.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.


?>
<div class="wrap custom-list-table vendors-list-table-wrapper yith-plugin-ui--wp-list-auto-h-scroll">
	<div class="yith-plugin-fw__panel__secondary-notices"></div>

	<?php if ( ! $vendors_table->has_items() ) : ?>
		<div class="yith-plugin-fw__list-table-blank-state">
			<img class="yith-plugin-fw__list-table-blank-state__icon" src="<?php echo esc_url( YITH_WPV_ASSETS_URL ); ?>icons/store-alt.svg" width="65" alt=""/>
			<div class="yith-plugin-fw__list-table-blank-state__message"><?php echo esc_html_x( 'No vendor store created yet.', '[Admin]Vendor table empty message', 'yith-woocommerce-product-vendors' ); ?></div>
		</div>
	<?php else : ?>

		<?php $vendors_table->views(); ?>

		<form id="vendors-list-table" method="GET">
			<input type="hidden" name="page" value="<?php echo ! empty( $_GET['page'] ) ? esc_attr( wp_unslash( $_GET['page'] ) ) : ''; ?>"/>
			<input type="hidden" name="tab" value="<?php echo ! empty( $_GET['tab'] ) ? esc_attr( wp_unslash( $_GET['tab'] ) ) : ''; ?>"/>

			<?php
			$vendors_table->add_search_box( sprintf( __( 'Search vendors', 'yith-woocommerce-product-vendors' ) ), 's' );
			?>
			<?php $vendors_table->display(); ?>
		</form>
	<?php endif; ?>

</div>

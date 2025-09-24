<?php
/**
 * Admin commissions list table
 *
 * @since   4.0.0
 * @author  YITH
 * @package YITH\MultiVendor
 * @var YITH_Vendors_Commissions_List_Table $commissions_table YITH_Vendors_Commissions_List_Table class instance.
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>
<div class="custom-list-table">
	<div class="yith-plugin-fw__panel__secondary-notices"></div>

	<?php if ( ! $commissions_table->has_items() ) : ?>
		<div class="yith-plugin-fw__list-table-blank-state">
			<img class="yith-plugin-fw__list-table-blank-state__icon" src="<?php echo esc_url( YITH_WPV_ASSETS_URL ); ?>icons/commission.svg" width="65" alt=""/>
			<div class="yith-plugin-fw__list-table-blank-state__message"><?php echo esc_html_x( 'No commissions recorded yet.', '[Admin]Commissions table empty message', 'yith-woocommerce-product-vendors' ); ?></div>
		</div>
	<?php else : ?>
		<?php $commissions_table->views(); ?>

		<form id="commissions-list-table" method="GET">
			<input type="hidden" name="page" value="<?php echo ! empty( $_REQUEST['page'] ) ? esc_attr( wp_unslash( $_REQUEST['page'] ) ) : ''; ?>"/>
			<input type="hidden" name="tab" value="<?php echo ! empty( $_REQUEST['tab'] ) ? esc_attr( wp_unslash( $_REQUEST['tab'] ) ) : ''; ?>"/>
            <input type="hidden" name="status" value="<?php echo ! empty( $_REQUEST['commission_status'] ) ? esc_attr( wp_unslash( $_REQUEST['commission_status'] ) ) : ''; ?>"/>
			<?php $commissions_table->add_search_box( __( 'Search commissions', 'yith-woocommerce-product-vendors' ), 's' ); ?>
			<?php $commissions_table->display(); ?>
		</form>
	<?php endif; ?>
</div>

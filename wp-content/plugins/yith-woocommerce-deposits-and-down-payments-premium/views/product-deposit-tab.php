<?php
/**
 * Product deposit option tab
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Views
 * @version 1.0.0
 */

/**
 * Variable templates:
 *
 * @var $product WC_Product
 * @var $fields  array
 * @var $loop    int
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly
?>

<?php
$loop   = isset( $loop ) ? $loop : false;
$tab_id = 'yith_wcdp_deposit_tab' . ( is_int( $loop ) ? "_$loop" : '' );
?>

<div id="<?php echo esc_attr( $tab_id ); ?>" class="panel woocommerce_options_panel yith-plugin-ui deposit-options">

	<h3><?php esc_html_e( 'Deposit & balance options', 'yith-woocommerce-deposits-and-down-payments' ); ?></h3>

	<?php foreach ( $fields as $field_key => $field ) : ?>

		<div class="form-row">
			<?php
			$field_label = isset( $field['title'] ) ? $field['title'] : $field_key;
			?>

			<label for="<?php echo esc_attr( $field['id'] ); ?>">
				<?php echo esc_html( $field_label ); ?>
			</label>
			<?php yith_plugin_fw_get_field( $field, true ); ?>

			<?php if ( ! empty( $field['desc'] ) ) : ?>
				<p class="description">
					<?php echo wp_kses_post( $field['desc'] ); ?>
				</p>
			<?php endif; ?>
		</div>

	<?php endforeach; ?>

</div>

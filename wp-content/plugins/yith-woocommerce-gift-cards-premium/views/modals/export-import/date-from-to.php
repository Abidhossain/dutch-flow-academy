<?php
/**
 * View to select the dates in the export
 *
 * @package YITH\GiftCards\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$radio_args  = array(
	'type'    => 'radio',
	'id'      => 'ywgc_export_option_date',
	'name'    => 'ywgc_export_option_date',
	'options' => array(
		'all'     => __( 'All gift cards', 'yith-woocommerce-gift-cards' ),
		'by_date' => __( 'Only gift cards in a specific date range', 'yith-woocommerce-gift-cards' ),
	),
	'value'   => 'all',
);
$date_format = get_option( 'ywgc_plugin_date_format_option', 'yy-mm-dd' );

?>
<div id="ywbc-date-from-to-container" class="yith-plugin-fw-metabox-field-row">
	<label class="yith-date-radio-selector">
		<?php yith_plugin_fw_get_field( $radio_args, true ); ?>
	</label>
	<div class="yith-plugin-fw-field-schedule ywgc-date-from-to-date-selectors hidden">
		<span class="from_datepicker">
			<span class="ywgc_export_option_date_from field-label" style="line-height: 35px;"><?php esc_html_e( 'From', 'yith-woocommerce-gift-cards' ); ?></span>
			<input type="text" id="ywgc_export_option_date_from" autocomplete="off" class="yith-plugin-fw-text-input" value="" name="ywgc_export_option_date_from" placeholder="<?php echo esc_attr( $date_format ); ?>"/>
		</span>
		<span class="to_datepicker">
			<span class="ywgc_export_option_date_to field-label" style="line-height: 35px;"><?php esc_html_e( 'To', 'yith-woocommerce-gift-cards' ); ?></span>
			<input type="text" id="ywgc_export_option_date_to" autocomplete="off" class="yith-plugin-fw-text-input" value="" name="ywgc_export_option_date_to" placeholder="<?php echo esc_attr( $date_format ); ?>"/>
		</span>
	</div>
</div>

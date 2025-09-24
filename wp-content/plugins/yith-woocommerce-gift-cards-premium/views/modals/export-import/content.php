<?php
/**
 * View to manage the import/export
 *
 * @package YITH\GiftCards\Views
 */

$radio_field     = array(
	'id'      => 'gift-card-export-import-radio-modal',
	'name'    => 'gift-card-export-import-radio-modal',
	'type'    => 'radio',
	'options' => array(
		'export' => esc_html_x( 'Export gift card codes', 'Option from a radio button', 'yith-woocommerce-gift-cards' ),
		'import' => esc_html_x( 'Import gift card codes', 'Option from a radio button', 'yith-woocommerce-gift-cards' ),
	),
	'value'   => 'export',
);
$delimiter_field = array(
	'label' => esc_html_x( 'CSV Delimiter', 'input title', 'yith-woocommerce-gift-cards' ),
	'desc'  => esc_html_x( 'Set character to use as delimiter for the CSV file', 'input description', 'yith-woocommerce-gift-cards' ),
	'type'  => 'text',
	'id'    => 'ywgc_csv_delimiter',
	'name'  => 'ywgc_csv_delimiter',
	'value' => ',',
);
$import_field    = array(
	'label'            => esc_html_x( 'Choose a CSV file', 'Title of the CSV upload input', 'yith-woocommerce-gift-cards' ),
	'desc'             => esc_html_x( 'Import gift cards in CSV format, following the structure from', 'import field description, followed with a link with a sample file', 'yith-woocommerce-gift-cards' ) . ' <a href="' . YITH_YWGC_ASSETS_URL . '/csv_samples/yith-gift-card-import-sample.csv" download>' . esc_html_x( 'this sample', 'link with a CSV file sample for importing gift cards', 'yith-woocommerce-gift-cards' ) . '</a>.',
	'type'             => 'media',
	'id'               => 'ywgc_file_import_csv',
	'name'             => 'ywgc_file_import_csv',
	'allow_custom_url' => false,
);

wp_enqueue_media();

$date_fields      = ywgc_get_date_fields_in_export();
$fields_to_export = ywgc_get_fields_to_export();
$counter          = 0;

?>
<div class="yith-plugin-fw__modal__title yith-gift-card-export-import-modal-title choose-action"><?php echo esc_html_x( 'Choose action', 'export & import modal title', 'yith-woocommerce-gift-cards' ); ?></div>
<div class="yith-plugin-fw__modal__title yith-gift-card-export-import-modal-title export hidden"><?php echo esc_html_x( 'Export gift card codes', 'export & import modal title', 'yith-woocommerce-gift-cards' ); ?></div>
<div class="yith-plugin-fw__modal__title yith-gift-card-export-import-modal-title import hidden"><?php echo esc_html_x( 'Import gift card codes', 'export & import modal title', 'yith-woocommerce-gift-cards' ); ?></div>
<div class="yith-gift-card-export-import-modal-content yith-plugin-ui">
	<div id="ywgc-gift-card-export-import-container" class="yith-plugin-fw-metabox-field-row">
		<div class="steps-content">
			<form id="yith-ywgc-export-import-form" enctype="multipart/form-data" method="POST">
				<!-- Step 1  -->
				<div id="step-upload" data-step="1" class="single-step active">
					<?php yith_plugin_fw_get_field( $radio_field, true ); ?>
					<footer>
						<button class="yith-plugin-fw__button--primary yith-gift-card-export-import-modal-button move-step" data-step-to="2"><?php echo esc_html_x( 'Continue', 'Link to continue to the next step in the export/import modal', 'yith-woocommerce-gift-cards' ); ?></button>
						<small class="alert-notice"></small>
					</footer>
				</div>
				<!-- Step 2  -->
				<div id="step-configuration" data-step="2" class="single-step configuration">
					<div class="export-configuration">
						<div class="export-configuration-date-fields"><?php echo $date_fields; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					</div>
					<div class="import-configuration">
						<span class="field-label"><?php echo esc_html( $import_field['label'] ); ?></span>
						<?php yith_plugin_fw_get_field( $import_field, true ); ?>
						<p class="field-description"><?php echo wp_kses_post( $import_field['desc'] ); ?></p>
						<div class="import-configuration-delimiter-field">
							<label for="ywgc_csv_delimiter" class="ywgc_csv_delimiter">
								<span class="field-label"><?php echo esc_html( $delimiter_field['label'] ); ?></span>
								<?php yith_plugin_fw_get_field( $delimiter_field, true ); ?>
							</label>
						</div>
					</div>
					<div class="wrap-buttons">
						<a href="#" class="yith-gift-card-export-import-modal-button move-step previous" data-step-to="1">< <?php echo esc_html_x( 'Back', 'Link to move to previous step', 'yith-woocommerce-gift-cards' ); ?></a>
						<button class="button yith-plugin-fw__button--primary yith-gift-card-export-import-modal-button move-step export ywgc-export-button" data-step-to="3"><?php echo esc_html_x( 'Next', 'Link to move to next step', 'yith-woocommerce-gift-cards' ); ?></button>
						<button class="button yith-plugin-fw__button--primary move-step import ywgc-import-button" id="ywgc_import_gift_cards" data-step-to="4"><?php echo esc_html_x( 'Import', 'Start the import button', 'yith-woocommerce-gift-cards' ); ?></button>
					</div>
				</div>
				<!-- Step 3  -->
				<div id="step-configuration-data" data-step="3" class="single-step configuration-data">
					<div class="export-configuration">
						<div class="export-configuration-settings-group">
							<p><?php echo esc_html_x( 'Select the information to be exported:', 'Title in the export modal, to select the info to be exported in the CSV', 'yith-woocommerce-gift-cards' ); ?></p>
							<?php
							foreach ( $fields_to_export as $field ) {
								$column_class = $counter < 8 ? ' right' : ' left';

								?>
								<label for="<?php echo esc_attr( $field['id'] ); ?>" class="<?php echo esc_attr( $field['id'] . $column_class ); ?>">
									<span class="field-label"><?php echo esc_html( $field['label'] ); ?></span>
									<?php yith_plugin_fw_get_field( $field, true ); ?>
								</label>
								<?php
								++$counter;
							}
							?>
						</div>
						<div class="export-configuration-delimiter-field">
							<label for="ywgc_csv_delimiter" class="ywgc_csv_delimiter">
								<span class="field-label"><?php echo esc_html( $delimiter_field['label'] ); ?></span>
								<?php yith_plugin_fw_get_field( $delimiter_field, true ); ?>
							</label>
						</div>
					</div>
					<div class="wrap-buttons">
						<a href="#" class="yith-gift-card-export-import-modal-button move-step previous" data-step-to="2">< <?php echo esc_html_x( 'Back', 'link to go back to a previous step', 'yith-woocommerce-gift-cards' ); ?></a>
						<button class="button yith-plugin-fw__button--primary move-step export ywgc-export-button" data-step-to="4"><?php echo esc_html_x( 'Export', 'Button to start the export process', 'yith-woocommerce-gift-cards' ); ?></button>
					</div>
				</div>
				<!-- Step 4 -->
				<div id="step-completed" data-step="4" class="single-step completed">
					<img src="<?php echo esc_url( YITH_YWGC_ASSETS_URL . '/images/import-done.svg' ); ?>">
					<p class="ywgc-export-done"><?php echo esc_html_x( 'Export done', 'message that appear after the export is done', 'yith-woocommerce-gift-cards' ); ?></p>
					<p class="ywgc-import-done"><?php echo esc_html_x( 'Import done', 'message that appear after the import is done', 'yith-woocommerce-gift-cards' ); ?></p>
					<button class="yith-plugin-fw__button--close" id="close-modal"><?php echo esc_html_x( 'Close', 'button to close the import/export modal', 'yith-woocommerce-gift-cards' ); ?></button>
					<button class="yith-plugin-fw__button try-again" type="button" data-step-to="2"><?php echo esc_html_x( 'Try again', 'button to try again a failed CSV import', 'yith-woocommerce-gift-cards' ); ?></button>
				</div>
				<?php wp_nonce_field( 'yith-ywgc-import' ); ?>
			</form>
		</div>
	</div>
</div>

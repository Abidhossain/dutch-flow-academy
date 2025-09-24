<?php
/**
 * Template for displaying the text field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

list ( $field_id, $name, $value, $default, $preview_width ) = yith_plugin_fw_extract( $field, 'id', 'name', 'value', 'default', 'preview_width' );

$default_image = wp_get_attachment_url( $default );
if ( ! empty( $value ) && wp_attachment_is_image( $value ) ) {
	$image = wp_get_attachment_url( $value );
} else {
	$value = $default;
	$image = $default_image;
}

$preview_width = ! empty( $preview_width ) ? absint( $preview_width ) : 300;

?>
<div class="yith-wcmv-attachment-upload-container">
	<div class="yith-wcmv-attachment-upload-preview" style="margin-bottom:10px;">
		<?php if ( $image ) : ?>
			<img src="<?php echo esc_url( $image ); ?>" style="max-width:<?php echo esc_attr( $preview_width ); ?>px; height: auto;" alt=""/>
		<?php endif ?>
	</div>
	<input type="hidden" id="<?php echo esc_attr( $field_id ); ?>" class="yith-wcmv-attachment-upload-value" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
	<button id="<?php echo esc_attr( $field_id ); ?>-button" class="yith-wcmv-attachment-upload yith-plugin-fw__button--upload"><?php esc_html_e( 'Upload', 'yith-plugin-fw' ); ?></button>
	<button
		id="<?php echo esc_attr( $field_id ); ?>-button-reset"
		class="yith-wcmv-attachment-reset button-secondary"
		<?php if ( ! empty( $default_image ) ) : ?>
			data-id="<?php echo esc_attr( $default ); ?>"
			data-src="<?php echo esc_attr( $default_image ); ?>"
		<?php endif; ?>
	>
		<?php esc_html_e( 'Reset', 'yith-plugin-fw' ); ?>
	</button>
</div>

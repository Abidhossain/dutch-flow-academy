<?php
/**
 * Template for displaying the File field
 *
 * @var array $field The field.
 * @package YITH\PluginFramework\Templates\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

$message        = $message ?? esc_html_x( 'Drag or upload a file', 'Text showed in the Drag & Drop upload section for the custom gift card images, client side', 'yith-woocommerce-gift-cards' );
$classes        = array(
	'yith-plugin-fw-file',
	$class,
);
$suggested_size = get_option( 'ywgc_custom_design_suggested_size', '1200x1600 px' );
$classes        = implode( ' ', array_filter( $classes ) );

?>
<div class="<?php echo esc_attr( $classes ); ?>">
	<div class="yith-ywgc-drag-drop-icon-modal">
		<img src="<?php echo esc_url( YITH_YWGC_ASSETS_IMAGES_URL . 'drag-drop.svg' ); ?>">
	</div>
	<input type="file" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $name ); ?>" class="yith-plugin-fw-file__field" <?php yith_plugin_fw_html_attributes_to_string( $custom_attributes, true ); ?>/>
	<div class="yith-plugin-fw-file__message">
		<p class="yith-plugin-fw-file__message">
			<?php echo wp_kses_post( $message ); ?>
		</p>
		<p class="yith-plugin-fw-file__suggested_size">
			<?php echo esc_html_x( 'Suggested size: ', 'Message for the suggested size in the drag and drop upload in the modal', 'yith-woocommerce-gift-cards' ) . wp_kses_post( $suggested_size ); ?>
		</p>
	</div>
	<div class="yith-plugin-fw-file__preview">
		<div class="yith-ywgc-preview-image"></div>
		<div class="yith-ywgc-preview-buttons">
			<button type="button" id="decline-image" class="image-upload-cancel"><?php esc_html_e( 'Cancel', 'yith-woocommerce-gift-cards' ); ?></button>
			<button type="button" id="accept-image" class="image-upload-accept" ><?php esc_html_e( 'Use it!', 'yith-woocommerce-gift-cards' ); ?></button>
		</div>
		<span class="yith-plugin-fw-file__preview__name"></span>
	</div>
</div>

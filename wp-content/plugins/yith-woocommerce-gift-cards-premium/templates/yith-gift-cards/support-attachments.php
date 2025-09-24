<?php
/**
 * Custom image upload template
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\GiftCards\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="ywgc-custom-upload-container-modal ywgc-hidden">
	<h2><?php echo esc_html__( 'Upload your image', 'yith-woocommerce-gift-cards' ); ?></h2>
	<p><?php echo esc_html__( 'Upload your custom image here, to make your gift card even more special!', 'yith-woocommerce-gift-cards' ); ?></p>
	<div class="ywgc-custom-design-modal-wrapper">
		<?php
		wc_get_template(
			'fields/file-upload.php',
			array(
				'field_id'          => 'ywgc-upload-picture-modal',
				'class'             => 'yith-ywgc-upload-file-field',
				'name'              => 'ywgc-upload-picture-modal',
				'custom_attributes' => ' accept="image/*"',
			),
			'',
			YITH_YWGC_TEMPLATES_DIR
		);
		?>
	</div>
</div>

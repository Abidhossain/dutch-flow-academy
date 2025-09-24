<?php
/**
 * Gift Card design gallery
 *
 * @author YITH
 * @package YITH\GiftCards\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cnt                              = 0;
$allow_templates                  = get_option( 'ywgc_template_design', 'yes' );
$allow_customer_images            = get_option( 'ywgc_custom_design', 'no' );
$desings_to_show                  = get_option( 'ywgc_template_design_number_to_show', '3' );
$selected_categories_unserialized = array(
	'0' => '0',
);

if ( 'yes' === $allow_templates ) {
	$display = '';
} else {
	$display = 'display: none';
}

if ( 'yes' === $allow_templates || 'yes' === $allow_customer_images ) :
	if ( 'yes' === $allow_templates && ! empty( $item_categories ) || 'yes' === $allow_customer_images ) : ?>
		<h3 class="ywgc_choose_design_title" ><?php echo wp_kses_post( get_option( 'ywgc_choose_design_title', __( 'Choose your image', 'yith-woocommerce-gift-cards' ) ) ); ?></h3>
		<?php
	endif;

	/**
	 * DO_ACTION: yith_ywgc_before_choose_design_section
	 *
	 * Allow actions before the designs section.
	 */
	do_action( 'yith_ywgc_before_choose_design_section' );
	?>

	<div class="gift-card-content-editor step-appearance">
		<div id="ywgc-choose-design-preview" class="ywgc-choose-design-preview" style="<?php echo esc_attr( $display ); ?>" >
			<div class="ywgc-design-list">
				<ul>
					<!-- Default product image -->
					<?php
					if ( $product instanceof WC_Product_Gift_Card ) :
						$default_image_url = YITH_WooCommerce_Gift_Cards_Premium::get_instance()->get_default_header_image();
						$default_image_id  = ywgc_get_attachment_id_from_url( $default_image_url );

						if ( ! $default_image_id ){
							$default_image_id = $product->get_image_id();
						}

						?>
						<!-- Let the user to upload a file to be used as gift card main image -->
						<?php if ( 'yes' === $allow_customer_images ) : ?>
							<li class="upload-image-li">
								<div class="ywgc-preset-image ywgc-custom-picture">
									<img src="<?php echo esc_url( YITH_YWGC_ASSETS_IMAGES_URL . 'plus-small.svg' ); ?>" class="ywgc_upload_plus_icon">
									<?php
									wc_get_template(
										'fields/file-upload.php',
										array(
											'field_id' => 'ywgc-upload-picture',
											'class'    => 'yith-ywgc-upload-file-field',
											'name'     => 'ywgc-upload-picture',
											'custom_attributes' => ' accept="image/*"',
										),
										'',
										YITH_YWGC_TEMPLATES_DIR
									);
									?>
									<p class="ywgc-custom-upload-label"><?php echo esc_html_x( 'Your image', 'Text included inside the upload section on the gift card product page', 'yith-woocommerce-gift-cards' ); ?></p>
								</div>
							</li>
						<?php endif; ?>

						<li class="default-image-li" style="<?php echo empty( $item_categories ) || 'yes' === $allow_customer_images ? 'display: none' : '' ?>">
							<div class="ywgc-preset-image ywgc-default-product-image selected_image_parent" data-design-id="<?php echo esc_attr( $default_image_id ); ?>" data-design-url="<?php echo esc_url( $default_image_url ); ?>" >
								<?php echo wp_get_attachment_image( intval( $default_image_id ), apply_filters( 'yith_ywgc_preset_image_size', 'full' ) ); ?>
							</div>
						</li>

						<?php if ( empty( $item_categories ) ) : ?>
							<li class="ywgc-custom-upload-image-li" style="display: none;">
								<div class="ywgc-preset-image" data-design-id="" data-design-url="" >
								</div>
							</li>
						<?php endif; ?>
					<?php endif; ?>

					<?php
					if ( ! empty( $item_categories ) ) {
						foreach ( $item_categories as $item_id => $categories ) :
							$category_id = str_replace( 'ywgc-category-', '', $categories );
							$category_id = explode( ' ', $category_id );

							foreach ( $category_id as $key => $value ) {
								$category_id = intval( $value );
							}

							if ( apply_filters( 'ywgc_show_all_categories_in_gift_this_product_gallery', true ) ) :
								$item_url = yith_get_attachment_image_url( intval( $item_id ), 'full' );

								if ( empty( $item_url ) ) {
									continue;
								}

								$li_class = 0 === $cnt ? 'first-design-image-li' : '';

								?>
								<li class="<?php echo esc_attr( $li_class ); ?>">
									<?php if ( $cnt <= ( $desings_to_show - 2 ) ) { ?>
										<div class="ywgc-preset-image" data-design-id="<?php echo esc_attr( $item_id ); ?>" data-design-url="<?php echo esc_url( $item_url ); ?>">
											<?php echo wp_get_attachment_image( intval( $item_id ), apply_filters( 'yith_ywgc_preset_image_size', 'full' ) ); ?>
										</div>
									<?php } else { ?>
										<div class="ywgc-preset-image ywgc-preset-image-view-all">
											<input type="button" class="ywgc-choose-image ywgc-choose-template" href="#ywgc-choose-design" value="<?php echo esc_attr( get_option( 'ywgc_template_design_view_all_button', __( 'VIEW ALL', 'yith-woocommerce-gift-cards' ) ) ); ?>"/>
										</div>
										<?php
									}

									++$cnt;

									if ( intval( $desings_to_show ) === $cnt ) {
										break;
									}
									?>
								</li>
								<?php
							endif;
						endforeach;
					} else {
						// Empty li for show custom image.
						?>
						<li>
							<div class="ywgc-preset-image " data-design-id="" data-design-url="" ></div>
						</li>
						<?php
					}
					?>
				</ul>
			</div>
		</div>

		<?php
		/**
		 * DO_ACTION: yith_ywgc_after_choose_design_section
		 *
		 * Allow actions after the designs section.
		 */
		do_action( 'yith_ywgc_after_choose_design_section' );
		?>
	</div>
	<?php
endif;

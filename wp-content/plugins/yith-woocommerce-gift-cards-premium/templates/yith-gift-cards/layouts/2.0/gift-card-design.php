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

global $post;

$cnt      = 0;
$category = '';

if ( isset( $selected_categories_unserialized[1] ) ){
	$category = get_term( $selected_categories_unserialized[1] )->slug;
}

$design_section_display = '';

if ( '' === $category || 'none' === $category && 'no' === $allow_customer_images ){
	$design_section_display = 'display: none';
}

if ( 'yes' === $allow_templates && ! empty( $item_categories ) || 'yes' === $allow_customer_images  ) {
	$display = '';
} else {
	$display = 'display: none';
}

if ( 'yes' === $allow_templates || 'yes' === $allow_customer_images ) : ?>
	<h3 class="ywgc_choose_design_title" style="<?php echo esc_attr( $design_section_display ); ?>"><?php echo wp_kses_post( get_option( 'ywgc_choose_design_title', __( 'Choose your image', 'yith-woocommerce-gift-cards' ) ) ); ?></h3>

	<?php
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
					<?php if ( $product instanceof WC_Product_Gift_Card ) :
						$default_image_url = YITH_WooCommerce_Gift_Cards_Premium::get_instance()->get_default_header_image();
						$default_image_id  = ywgc_get_attachment_id_from_url( $default_image_url );

						$post_thumbnail_id  = ! empty( get_post_thumbnail_id( $post->ID ) ) ? get_post_thumbnail_id( $post->ID ) : $default_image_id;
						$post_thumbnail_url = ! empty( yith_get_attachment_image_url( intval( get_post_thumbnail_id( $post->ID ) ) ) ) ? yith_get_attachment_image_url( intval( get_post_thumbnail_id( $post->ID ) ), 'full' ) : $default_image_url;

						?>
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

						<li class="default-image-li" style="<?php echo esc_attr( $design_section_display ); ?>">
							<div class="ywgc-preset-image ywgc-default-product-image selected_image_parent" data-design-id="<?php echo esc_attr( $post_thumbnail_id ); ?>" data-design-url="<?php echo esc_url( $post_thumbnail_url ); ?>" >
								<?php echo wp_get_attachment_image( intval( $post_thumbnail_id ), apply_filters( 'yith_ywgc_preset_image_size', 'full' ) ); ?>
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
					if ( ! empty( $item_categories ) && 'yes' === $allow_templates ) {
						foreach ( $item_categories as $item_id => $categories ) :
							$category_id = str_replace( 'ywgc-category-', '', $categories );
							$category_id = explode( ' ', $category_id );

							foreach ( $category_id as $key => $value ) {
								$category_id = intval( $value );
							}

							$term_slug_array                  = array();
							$selected_categories_unserialized = is_array( $selected_categories_unserialized ) ? $selected_categories_unserialized : array( '0' => '0' );

							foreach ( $selected_categories_unserialized as $selected_categories ) {
								if ( 0 !== $selected_categories ) {
									$term_slug_array[] = get_term( $selected_categories )->slug;
								}
							}

							if ( in_array( 'none', $term_slug_array, true ) ) {
								continue;
							}

							if ( in_array( $category_id, $selected_categories_unserialized, true ) && $item_id !== $post->ID || in_array( 'all', $term_slug_array, true ) || count( $selected_categories_unserialized ) === 1 ) :
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
									<?php } ?>

									<?php
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

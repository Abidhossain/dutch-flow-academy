<?php
/**
 * Gift Card product add to cart
 *
 * @author YITH
 * @package YITH\GiftCards\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product = wc_get_product( get_option( YWGC_PRODUCT_PLACEHOLDER ) );

?>
<div id="product-<?php echo esc_attr( $product->get_id() ); ?>" <?php wc_product_class( '', $product ); ?>>
	<?php
	$columns           = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
	$post_thumbnail_id = $product->get_image_id();
	$wrapper_classes   = apply_filters(
		'woocommerce_single_product_image_gallery_classes',
		array(
			'woocommerce-product-gallery',
			'woocommerce-product-gallery--' . ( $post_thumbnail_id ? 'with-images' : 'without-images' ),
			'woocommerce-product-gallery--columns-' . absint( $columns ),
			'images',
		)
	);

	?>
	<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>" data-columns="<?php echo esc_attr( $columns ); ?>" style="opacity: 0; transition: opacity .25s ease-in-out;">
		<div class="woocommerce-product-gallery__wrapper">
			<?php
			wc_get_template(
				'gift-this-product/form-preview.php',
				array(
					'product' => $product,
				),
				'',
				trailingslashit( YITH_YWGC_TEMPLATES_DIR )
			);
			?>
		</div>
	</div>
	<div class="summary entry-summary">
		<?php
		wc_get_template(
			'gift-this-product/product-template/default-gift-card-template.php',
			array(
				'product' => $product,
			),
			'',
			trailingslashit( YITH_YWGC_TEMPLATES_DIR )
		);
		?>
	</div>
</div>

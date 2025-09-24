<?php
/**
 * Vendor list shortcode [yith_wcmv_list] template
 *
 * @since      4.0.0
 * @author     YITH
 * @package YITH\MultiVendor
 * @var array  $vendors An array of vendors to show
 * @var string $vendor_image The vendor image type to show.
 */

/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

extract( $sc_args ); // phpcs:ignore

$show_description = ! is_bool( $show_description ) ? 'true' === $show_description : $show_description;
$store_image      = '';
$icons            = yith_wcmv_get_font_awesome_icons();

if ( empty( $vendors ) ) {
	return;
}
?>

	<?php do_action( 'yith_wcmv_before_vendors_list' ); ?>

	<ul class="shortcodes vendors-list">
		<?php
		foreach ( $vendors as $vendor ) :
			/**
			 * @var YITH_Vendor $vendor Current vendor object.
			 */
			if ( empty( $vendor->get_product_count() ) && ! empty( $hide_no_products_vendor ) && 'true' === $hide_no_products_vendor ) {
				continue;
			}

			$store_image = '';
			if ( 'store' === $vendor_image && ! empty( $vendor->get_header_image_id() ) ) {
				$store_image = wp_get_attachment_image( $vendor->get_header_image_id(), apply_filters( 'yith_wcmv_avatar_image_size', 'thumbnail' ), false, array( 'class' => 'store-image' ) );
			} elseif ( 'gravatar' === $vendor_image || 'logo' === $vendor_image ) {
				$store_image = $vendor->get_avatar();
			}

			?>
			<li class="vendor-item <?php echo esc_attr( $vendor->get_slug() ); ?>">
				<h3>
					<a href="<?php echo esc_url( $vendor->get_url() ); ?>" title="<?php esc_html_e( 'Store page', 'yith-woocommerce-product-vendors' ); ?>" class="store-name">
						<?php echo esc_html( $vendor->get_name() ); ?>
					</a>
				</h3>
				<div class="vendor-info-wrapper">
					<a href="<?php echo esc_url( $vendor->get_url() ); ?>" title="<?php esc_html_e( 'Store page', 'yith-woocommerce-product-vendors' ); ?>" class="store-image">
						<?php
						if ( ! empty( $store_image ) ) :
							echo $store_image; // phpcs:ignore
						endif;
						?>
					</a>
					<ul class="vendor-info<?php echo $show_description ? ' has-description' : ''; ?>">
						<?php if ( ! empty( $vendor->get_meta( 'location' ) ) ) : ?>
							<li class="location">
								<?php if ( isset( $icons['location'] ) ) : ?>
									<i class="<?php echo esc_attr( $icons['location'] ); ?>"></i>
								<?php endif; ?>
								<?php echo esc_html( $vendor->get_formatted_address() ); ?>
							</li>
						<?php endif; ?>

						<?php
						$store_email = $vendor->get_meta( 'store_email' );
						if ( ! empty( $store_email ) ) :
							?>
							<li class="store-email">
								<?php if ( isset( $icons['store_email'] ) ) : ?>
									<i class="<?php echo esc_attr( $icons['store_email'] ); ?>"></i>
								<?php endif; ?>
								<a href="mailto:<?php echo esc_attr( $store_email ); ?>"><?php echo esc_html( $store_email ); ?></a>
							</li>
						<?php endif; ?>

						<?php
						$store_telephone = $vendor->get_meta( 'telephone' );
						if ( ! empty( $store_telephone ) ) :
							?>
							<li class="telephone">
								<?php if ( isset( $icons['telephone'] ) ) : ?>
									<i class="<?php echo esc_attr( $icons['telephone'] ); ?>"></i>
								<?php endif; ?>
								<?php echo esc_html( $store_telephone ); ?>
							</li>
						<?php endif; ?>

						<?php
						$vendor_reviews = $vendor->get_reviews_average_and_product();
						if ( ! empty( $vendor_reviews['reviews_product_count'] ) || apply_filters( 'yith_wcmv_show_empty_vendor_rating', false ) ) :
							?>
							<li class="store-rating">
								<?php if ( isset( $icons['rating'] ) ) : ?>
									<i class="<?php echo esc_attr( $icons['rating'] ); ?>"></i>
								<?php endif; ?>
								<?php
								// translators: %1$s stand for the average rating value, %2$d stand for the number of reviews.
								echo esc_html( sprintf( _n( '%1$s average rating from %2$d review', '%1$s average rating from %2$d reviews', $vendor_reviews['reviews_product_count'], 'yith-woocommerce-product-vendors' ), $vendor_reviews['average_rating'], $vendor_reviews['reviews_product_count'] ) );
								?>
							</li>
						<?php endif; ?>

						<?php if ( $show_total_sales ) : ?>
							<li class="store-sales">
								<?php if ( isset( $icons['sales'] ) ) : ?>
									<i class="<?php echo esc_attr( $icons['sales'] ); ?>"></i>
								<?php endif; ?>
								<?php
								// translators: %d stand for number of total sales.
								echo esc_html( sprintf( __( 'Total sales: %d', 'yith-woocommerce-product-vendors' ), count( $vendor->get_orders() ) ) );
								?>
							</li>
						<?php endif; ?>

						<?php
						$socials = $vendor->get_socials();
						if ( ! empty( $socials ) ) :
							?>
							<li class="store-socials">
							<span class="socials-container">
								<?php foreach ( $socials as $social => $uri ) : ?>
									<?php if ( isset( $socials_list['social_fields'][ $social ] ) ) : ?>
										<a class="vendor-social-uri" href="<?php echo esc_url( $uri ); ?>" title="<?php echo esc_attr( $socials_list['social_fields'][ $social ]['label'] ); ?>" target="_blank">
											<i class="<?php echo esc_attr( $socials_list['social_fields'][ $social ]['icon'] ); ?>"></i>
										</a>
									<?php endif; ?>
								<?php endforeach; ?>
							</span>
							</li>
						<?php endif; ?>
						<?php if ( $show_description && ! empty( $vendor->get_description() ) ) : ?>
							<li class="store-description">
								<?php echo wp_kses_post( wp_trim_words( $vendor->get_description(), $description_lenght, false ) ); ?>
							</li>
						<?php endif; ?>
					</ul>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php do_action( 'yith_wcmv_after_vendors_list' ); ?>

<?php

echo paginate_links( $paginate );

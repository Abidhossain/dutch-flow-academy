<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

?>

<div class="store-info">
	<?php if ( ! empty( $location ) ) : ?>
		<span class="store-location">
			<i class="<?php echo esc_attr( $icons['location'] ); ?>"></i>
			<span><?php echo wp_kses_post( $location ); ?></span>
		</span>
	<?php endif; ?>
	<?php if ( ! empty( $telephone ) ) : ?>
		<span class="store-telephone">
			<i class="<?php echo esc_attr( $icons['telephone'] ); ?>"></i>
			<span><?php echo esc_html( $telephone ); ?></span>
		</span>
	<?php endif; ?>
	<?php if ( ! empty( $store_email ) ) : ?>
		<span class="store-email">
			<i class="<?php echo esc_attr( $icons['store_email'] ); ?>"></i>
			<a class="store-email-link" href="mailto:<?php echo esc_attr( $store_email ); ?>">
				<?php echo esc_html( $store_email ); ?>
			</a>
		</span>
	<?php endif; ?>
	<?php if ( ! empty( $vat ) ) : ?>
		<?php $vat_ssn_string = get_option( 'yith_wpv_vat_label', __( 'VAT/SSN', 'yith-woocommerce-product-vendors' ) ); ?>
		<span class="store-vat">
			<i class="<?php echo esc_attr( $icons['vat'] ); ?>"></i>
			<span><?php echo esc_html( sprintf( '%s: %s', $vat_ssn_string, $vat ) ); ?></span>
		</span>
	<?php endif; ?>
	<?php if ( ! empty( $website ) ) : ?>
		<span class="store-website">
			<i class="<?php echo esc_attr( $icons['website'] ); ?>"></i>
			<a href="<?php echo esc_url( $website['url'] ); ?>" target="<?php echo esc_attr( apply_filters( 'yith_wcmv_website_target', '_blank' ) ); ?>">
				<?php echo esc_html( $website['label'] ); ?>
			</a>
		</span>
	<?php endif; ?>
	<?php if ( ! empty( $vendor_reviews ) ) : ?>
		<span class="store-rating">
			<i class="<?php echo esc_attr( $icons['rating'] ); ?>"></i>
			<span>
			<?php
			// translators: %1$s stand for the average rating value, %2$d stand for the number of reviews.
			echo esc_html( sprintf( _n( '%1$s average rating (%2$d review)', '%1$s average rating (%2$d reviews)', $vendor_reviews['reviews_product_count'], 'yith-woocommerce-product-vendors' ), $vendor_reviews['average_rating'], $vendor_reviews['reviews_product_count'] ) );
			?>
				</span>
		</span>
	<?php endif; ?>
	<?php if ( isset( $total_sales ) ) : ?>
		<span class="store-sales">
			<i class="<?php echo esc_attr( $icons['sales'] ); ?>"></i>
			<span>
				<?php
				// translators: %d stand for number of total sales.
				echo esc_html( sprintf( apply_filters( 'yith_wcmv_total_sales_title', __( 'Total sales: %d', 'yith-woocommerce-product-vendors' ), $total_sales ), $total_sales ) );
				?>
			</span>
		</span>
	<?php endif; ?>
	<?php if ( ! empty( $legal_notes ) ) : ?>
		<span class="store-legal">
			<i class="<?php echo esc_attr( $icons['legal_notes'] ); ?>"></i>
			<span><?php echo esc_html( $legal_notes ); ?></span>
		</span>
	<?php endif; ?>
</div>

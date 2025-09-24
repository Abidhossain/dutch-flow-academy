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

use Automattic\WooCommerce\Blocks\Utils\BlockTemplateUtils;

if ( ! class_exists( 'YITH_Vendors_WC_Blocks_Support' ) ) {
	/**
	 * Handle compatibility with WooCommerce blocks
	 */
	class YITH_Vendors_WC_Blocks_Support {
		use YITH_Vendors_Singleton_Trait;

		/**
		 * Constructor
		 *
		 * @since  4.0.0
		 * @return void
		 */
		private function __construct() {
			// Customize WooCommerce product block.
			add_filter( 'woocommerce_blocks_product_grid_item_html', array( $this, 'customize_blocks_product_html' ), 0, 3 );

			if ( $this->support_block_templates() ) {
				add_action( 'template_redirect', array( $this, 'render_block_template' ) );
				add_filter( 'get_block_templates', array( $this, 'add_block_template' ), 10, 3 );

				add_action( 'template_redirect', array( $this, 'customize_single_product' ) );
				add_action( 'template_redirect', array( $this, 'customize_archive' ) );
			}
		}

		/**
		 * Check if theme support templates.
		 *
		 * @param string $template The template to check.
		 * @return bool
		 */
		protected function support_block_templates( $template = '' ) {
			$use_blocks = class_exists( 'Automattic\WooCommerce\Blocks\Utils\BlockTemplateUtils' ) && BlockTemplateUtils::supports_block_templates();
			if ( $use_blocks && $template ) {
				try {
					$container  = \Automattic\WooCommerce\Blocks\Package::container();
					$controller = $container->get( \Automattic\WooCommerce\Blocks\BlockTemplatesController::class );
					$use_blocks = $controller->block_template_is_available( $template );
					if ( $use_blocks ) {
						$templates  = get_block_templates( array( 'slug__in' => array( $template ) ) );
						$use_blocks = ! ( isset( $templates[0] ) && BlockTemplateUtils::template_has_legacy_template_block( $templates[0] ) );
					}
				} catch ( Exception $e ) {
					return false;
				}
			}

			return $use_blocks;
		}

		/**
		 * Add the block template objects to be used for vendor taxonomy.
		 *
		 * @param array  $query_result  Array of template objects.
		 * @param array  $query         Optional. Arguments to retrieve templates.
		 * @param string $template_type wp_template or wp_template_part.
		 * @return array
		 */
		public function add_block_template( $query_result, $query, $template_type ) {

			$slug  = 'taxonomy-' . YITH_Vendors_Taxonomy::TAXONOMY_NAME;
			$slugs = isset( $query['slug__in'] ) ? $query['slug__in'] : array();
			if ( ! in_array( $slug, $slugs, true ) ) {
				return $query_result;
			}

			$template_file = YITH_WPV_TEMPLATE_PATH . "templates/{$slug}.html";
			if ( ! file_exists( $template_file ) ) {
				return $query_result;
			}

			$template       = BlockTemplateUtils::create_new_block_template_object( $template_file, 'wp_template', $slug );
			$query_result[] = BlockTemplateUtils::build_template_result_from_file( $template, 'wp_template' );

			return $query_result;
		}

		/**
		 * Renders the default block template from Woo Blocks if no theme templates exist.
		 */
		public function render_block_template() {
			if ( is_embed() || ! yith_wcmv_is_vendor_page() ) {
				return;
			}

			// make sure template is loaded.
			get_block_templates( array( 'slug__in' => array( 'taxonomy-' . YITH_Vendors_Taxonomy::TAXONOMY_NAME ) ) );
			if ( ! BlockTemplateUtils::theme_has_template( 'taxonomy-' . YITH_Vendors_Taxonomy::TAXONOMY_NAME ) ) {
				add_filter( 'woocommerce_has_block_template', '__return_true', 10, 0 );
			}
		}

		/**
		 * Customize single product page
		 *
		 * @return void
		 */
		public function customize_single_product() {
			if ( ! is_product() || ! $this->support_block_templates( 'single-product' ) ) {
				return;
			}

			remove_action( 'woocommerce_single_product_summary', array( YITH_Vendors()->frontend, 'single_product_vendor_name' ), 5 );
			add_filter( 'render_block_core/post-title', array( $this, 'render_single_product_vendor_name' ), 10, 2 );
		}

		/**
		 * Customize products archive.
		 *
		 * @return void
		 */
		public function customize_archive() {
			if ( ( ! is_shop() && ! is_product_taxonomy() ) || ! $this->support_block_templates( 'archive-product' ) ) {
				return;
			}

			remove_action( 'woocommerce_after_shop_loop_item', array( YITH_Vendors()->frontend, 'shop_loop_item_vendor_name' ), 6 );
			add_filter( 'render_block_woocommerce/product-button', array( $this, 'render_loop_vendor_name' ), 10, 2 );
		}

		/**
		 * Filters the HTML for products in the grid.
		 *
		 * @since  4.0.0
		 * @param string     $html    Product grid item HTML.
		 * @param object     $data    Product data passed to the template.
		 * @param WC_Product $product Product object.
		 * @return string Updated product grid item HTML.
		 */
		public function customize_blocks_product_html( $html, $data, $product ) {
			if ( 'yes' !== get_option( 'yith_wpv_vendor_name_in_loop', 'yes' ) || ! apply_filters( 'yith_wcmv_show_vendor_name_template', true ) ) {
				return $html;
			}

			// Set vendor if any.
			$vendor = yith_wcmv_get_vendor( $product, 'product' );
			if ( $vendor && $vendor->is_valid() ) {
				ob_start();
				yith_wcmv_get_template( 'vendor-name', array( 'vendor' => $vendor ), 'woocommerce/loop' );
				$name_html = ob_get_clean();

				$data->vendor_name = sprintf( '<div class="wc-block-grid__product-vendor-name">%s</div>', $name_html );
				$html              = str_replace( $data->price, $data->price . $data->vendor_name, $html );
			}

			// Remove add to cart for vendor in vacation.
			if ( function_exists( 'YITH_Vendors_Vacation' ) && YITH_Vendors_Vacation()->vendor_is_on_vacation( $vendor ) ) {
				$html = str_replace( $data->button, '', $html );
			}

			return $html;
		}

		/**
		 * Render single product vendor name
		 *
		 * @param string $block_content The block content.
		 * @param array  $block         The full block, including name and attributes.
		 */
		public function render_single_product_vendor_name( $block_content, $block ) {
			global $product, $post;

			if ( isset( $block['attrs']['__woocommerceNamespace'] ) && 'woocommerce/product-query/product-title' === $block['attrs']['__woocommerceNamespace'] ) {
				$product instanceof WC_Product || $product = wc_get_product( $post->ID );

				ob_start();
				YITH_Vendors()->frontend->single_product_vendor_name();
				$block_content .= ob_get_clean();
			}

			return $block_content;
		}

		/**
		 * Render loop product vendor name
		 *
		 * @param string $block_content The block content.
		 * @param array  $block         The full block, including name and attributes.
		 */
		public function render_loop_vendor_name( $block_content, $block ) {
			ob_start();
			YITH_Vendors()->frontend->shop_loop_item_vendor_name();
			$vendor_name = ob_get_clean();

			return $vendor_name . $block_content;
		}
	}
}

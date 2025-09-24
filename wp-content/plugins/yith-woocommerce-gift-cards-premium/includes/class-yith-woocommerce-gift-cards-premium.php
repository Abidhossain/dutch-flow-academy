<?php
/**
 * YITH_WooCommerce_Gift_Cards_Premium class
 *
 * @package YITH\GiftCards\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YITH_WooCommerce_Gift_Cards_Premium' ) ) {
	/**
	 * YITH_WooCommerce_Gift_Cards class
	 *
	 * @since   1.0.0
	 * @author  YITH <plugins@yithemes.com>
	 */
	class YITH_WooCommerce_Gift_Cards_Premium extends YITH_WooCommerce_Gift_Cards_Extended {

		/**
		 * The default product of type gift card
		 *
		 * @var int
		 */
		public $default_gift_card_id = - 1;

		/**
		 * Single instance of the class
		 *
		 * @var YITH_WooCommerce_Gift_Cards_Premium
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Includes
		 *
		 * @return void
		 */
		public function includes() {
			/**
			 * Include third-party integration classes
			 */

			// Elementor Widgets integration.
			if ( defined( 'ELEMENTOR_VERSION' ) ) {
				require_once YITH_YWGC_DIR . 'includes/compatibilities/elementor/class-ywgc-elementor.php';
			}

			// YITH Dynamic Pricing.
			defined( 'YITH_YWDPD_VERSION' ) && require_once YITH_YWGC_DIR . 'includes/compatibilities/class-ywgc-dynamic-pricing.php';

			// YITH Points and Rewards.
			defined( 'YITH_YWPAR_VERSION' ) && require_once YITH_YWGC_DIR . 'includes/compatibilities/class-ywgc-points-and-rewards.php';

			// YITH Multi Vendor.
			defined( 'YITH_WPV_PREMIUM' ) && require_once YITH_YWGC_DIR . 'includes/compatibilities/class-ywgc-multi-vendor-module.php';

			if ( 'yes' === get_option( 'ywgc_aelia_integration_option', 'yes' ) ) {
				// Aelia Currency Switcher.
				class_exists( 'WC_Aelia_CurrencySwitcher' ) && require_once YITH_YWGC_DIR . 'includes/compatibilities/class-ywgc-AeliaCS-module.php';
			}

			// YITH Quick View.
			defined( 'YITH_WCQV_PREMIUM' ) && require_once YITH_YWGC_DIR . 'includes/compatibilities/class-ywgc-general-integrations.php';

			if ( 'yes' === get_option( 'ywgc_wpml_integration_option' ) ) {
				// WPML.
				global $woocommerce_wpml;

				if ( $woocommerce_wpml ) {
					require_once YITH_YWGC_DIR . 'includes/compatibilities/class-ywgc-wpml.php';
				}
			}

			if ( defined( 'WOOCOMMERCE_MULTICURRENCY_VERSION' ) ) {
				require_once YITH_YWGC_DIR . 'includes/compatibilities/class-ywgc-WCMultiCurrency-module.php';
			}

			// Flatsome Theme compatibility.
			$wp_theme = wp_get_theme();

			if ( $wp_theme instanceof WP_Theme ) {
				$parent_theme = $wp_theme->parent();

				if ( 'flatsome' === $wp_theme->get( 'TextDomain' ) ) {
					require_once YITH_YWGC_DIR . 'includes/compatibilities/themes/class-ywgc-flatsome-theme.php';
				} elseif ( ! empty( $parent_theme ) ) {
					if ( 'flatsome' === $wp_theme->parent()->get( 'TextDomain' ) ) {
						require_once YITH_YWGC_DIR . 'includes/compatibilities/themes/class-ywgc-flatsome-theme.php';
					}
				}
			}

			// YITH Multi Currency Switcher integration.
			if ( defined( 'YITH_WCMCS_INIT' ) ) {
				require_once YITH_YWGC_DIR . 'includes/compatibilities/class-ywgc-YITH-Multi-Currency-Switcher-module.php';
			}
		}

		/**
		 * Init_hooks
		 *
		 * @return void
		 */
		public function init_hooks() {
			parent::init_hooks();

			/**
			 * Add attachments to the email sent of the gif card
			 */
			add_filter( 'woocommerce_email_attachments', array( $this, 'attach_documents_to_email' ), 99, 3 );

			/**
			 * Saving disable gift this product
			 */
			add_action( 'woocommerce_process_product_meta', array( $this, 'disable_gift_this_product_woocommerce_process_product_meta' ) );

			/**
			 * When the default gift card image is changed from the plugin setting, update the product image
			 * of the default gift card
			 */
			add_action( 'yit_panel_wc_after_update', array( $this, 'update_default_gift_card' ) );

			/**
			 * Create the _ywgc_delivery_send_date post meta when the gift card is created manually
			 */
			add_action( 'save_post', array( $this, 'create_send_date_on_save' ), 10, 3 );

			/**
			 * Set the manual amount status for gift cards that are linked to the global value
			 * */
			add_filter( 'yith_gift_cards_is_manual_amount_enabled', array( $this, 'is_manual_amount_enabled' ), 10, 2 );

			/**
			 * Hide the default gift card product for gift this product on the admin products list
			 * */
			add_action( 'pre_get_posts', array( $this, 'ywcg_pre_get_posts_hide_default_gift_card' ) );

			add_filter( 'wp_count_posts', array( $this, 'ywgc_wp_count_posts_hide_default_gift_card' ), 10, 3 );

			if ( $this->allow_product_as_present() ) {
				/**
				 * Display in the admin product page the option "Disable Gift this product"
				 */
				add_action( 'add_meta_boxes', array( $this, 'ywgc_add_meta_boxes_disable_gift_this_product' ), 10, 2 );
			}

		}

		/**
		 * Start
		 *
		 * @return void
		 */
		public function start() { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
			parent::start();
		}

		/**
		 *  Execute all the operation need when the plugin init
		 */
		public function on_plugin_init() {
			parent::on_plugin_init();

			if ( ! get_option( YWGC_PRODUCT_PLACEHOLDER ) ) {
				add_option( YWGC_PRODUCT_PLACEHOLDER, -1 );
			}

			if ( class_exists( 'YITH_YWGC_Modules' ) ) {
				$modules_class = YITH_YWGC_Modules::get_instance();
				$module        = 'gift-this-product';

				if ( $modules_class->is_module_active( $module ) ) {
					$this->initialize_products();
				}
			}

			$this->remove_legacy_gift_this_product_option();
		}

		/**
		 * Init an array of plugin emails
		 *
		 * @since  1.5.0
		 */
		public function init_plugin_emails_array() {
			/**
			 * APPLY_FILTERS: yith_ywgc_plugin_emails_array
			 *
			 * Filters list of allowed email types managed by the plugin
			 *
			 * @param array $emails List of email types
			 *
			 * @return array
			 */
			$this->emails = apply_filters(
				'yith_ywgc_plugin_emails_array',
				array(
					'ywgc-email-send-gift-card',
					'ywgc-email-delivered-gift-card',
					'ywgc-email-notify-customer',
				)
			);
		}

		/**
		 *  Remove the legacy option for the gift this product and use the modules instead.
		 */
		public function remove_legacy_gift_this_product_option() {
			if ( 'yes' === get_option( 'ywgc_permit_its_a_present', 'no' ) && class_exists( 'YITH_YWGC_Modules' ) ) {
				$modules_class = YITH_YWGC_Modules::get_instance();
				$module        = 'gift-this-product';

				if ( ! $modules_class->is_module_active( $module ) ) {
					$modules_class->activate_module( $module );
				}
			}

			delete_option( 'ywgc_permit_its_a_present' );
		}

		/**
		 * Add option to the admin product page to disable the gift the product
		 *
		 * @param string  $post_type Post type.
		 * @param WP_Post $post      Post object.
		 */
		public function ywgc_add_meta_boxes_disable_gift_this_product( $post_type, $post ) {
			if ( 'product' !== get_post_type( $post ) ) {
				return;
			}

			$product = wc_get_product( $post->ID );

			/**
			 * APPLY_FILTERS: yith_gift_card_display_disable_gift_this_product_option
			 *
			 * Filter the condition to display the "disable gift this product" option in the products edit page.
			 *
			 * @param bool true to display the option, false to not display it
			 * @param string $post_type the post type
			 * @param object $post the post object
			 *
			 * @return bool
			 */
			if ( ! $product instanceof WC_Product_Gift_Card && 'product' === $post_type && apply_filters( 'yith_gift_card_display_disable_gift_this_product_option', true, $post_type, $post ) ) {
				add_filter( 'product_type_options', array( $this, 'disable_gift_this_product_product_type_options' ), 100, 1 );
			}
		}

		/**
		 * Avoid to show the default gift card product
		 *
		 * @param int    $counts Count of items.
		 * @param string $type Item type.
		 * @param string $perm Permission.
		 *
		 * @return array
		 * @since  2.0.1
		 */
		public function ywgc_wp_count_posts_hide_default_gift_card( $counts, $type, $perm ) {
			if ( 'product' === $type ) {
				global $pagenow;

				$default_gift_product = wc_get_product( get_option( YWGC_PRODUCT_PLACEHOLDER ) );

				if ( $default_gift_product ) {
					$status = $default_gift_product->get_status();

					/**
					 * APPLY_FILTERS: ywgc_wp_count_posts_hide_default_gift_card_filter
					 *
					 * Filter the condition to count or not the default gift card product created by the plugin in the products count.
					 *
					 * @param bool true to not count it, false to count it
					 *
					 * @return bool
					 */
					if ( isset( $counts->$status ) && is_admin() && 'edit.php' === $pagenow && isset( $_GET['post_type'] ) && 'product' === $_GET['post_type'] && apply_filters( 'ywgc_wp_count_posts_hide_default_gift_card_filter', true, $counts, $type, $perm ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						$counts->$status = $counts->$status - 1;
					}
				}
			}

			return $counts;
		}

		/**
		 * Avoid to show the default gift card product
		 *
		 * @param array $query Query.
		 *
		 * @since  2.0.1
		 */
		public function ywcg_pre_get_posts_hide_default_gift_card( $query ) {
			global $pagenow;

			/**
			 * APPLY_FILTERS: ywcg_pre_get_posts_hide_default_gift_card_filter
			 *
			 * Filter the condition to hide or not the default gift card product created by the plugin in the products table.
			 *
			 * @param bool true to hide it, false show it
			 * @param object $query the query object
			 *
			 * @return bool
			 */
			if ( $query->is_admin && 'edit.php' === $pagenow && isset( $_GET['post_type'] ) && 'product' === $_GET['post_type'] && apply_filters( 'ywcg_pre_get_posts_hide_default_gift_card_filter', true, $query ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$query->set( 'post__not_in', array( get_option( YWGC_PRODUCT_PLACEHOLDER ) ) );
			}
		}

		/**
		 * Create gift card pdf file
		 *
		 * @param mixed $object Gift card object.
		 *
		 * @return array
		 * @since  2.0.3
		 */
		public function create_gift_card_pdf_file( $object ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.objectFound
			require_once YITH_YWGC_DIR . 'lib/vendor/autoload.php';

			/**
			 * APPLY_FILTERS: yith_ywgc_mpdf_args
			 *
			 * Filter the arguments for the MPDF library.
			 *
			 * @param array arguments to pass to the MPDF library
			 *
			 * @return array
			 */
			$mpdf_args = apply_filters(
				'yith_ywgc_mpdf_args',
				array(
					'autoScriptToLang' => true,
					'autoLangToFont'   => true,
					'tempDir'          => YITH_YWGC_SAVE_TEMP_DIR,
				)
			);

			if ( is_array( $mpdf_args ) ) {
				$mpdf = new \Mpdf\Mpdf( $mpdf_args );
			} else {
				$mpdf = new \Mpdf\Mpdf();
			}

			ob_start();

			if ( is_rtl() ) {
				wc_get_template(
					'yith-gift-cards/pdf-style-rtl.css',
					null,
					'',
					YITH_YWGC_TEMPLATES_DIR
				);
			} else {
				wc_get_template(
					'yith-gift-cards/pdf-style.css',
					null,
					'',
					YITH_YWGC_TEMPLATES_DIR
				);
			}

			$style = ob_get_clean();
			$style = apply_filters( 'yith_ywgc_gift_card_pdf_styles', $style, $object );

			ob_start();

			$this->preview_digital_gift_cards( $object, 'pdf' );
			$html = ob_get_clean();

			/**
			 * APPLY_FILTERS: yith_ywgc_before_rendering_gift_card_html
			 *
			 * Filter the HTML content passed to the PDF before rendering it.
			 *
			 * @param string $html the HTML content
			 *
			 * @return string
			 */
			$html = apply_filters( 'yith_ywgc_before_rendering_gift_card_html', $html );

			$direction = is_rtl() ? 'rtl' : 'ltr';

			/**
			 * APPLY_FILTERS: yith_ywgc_mpdf_directionality
			 *
			 * Filter the PDF directionality.
			 *
			 * @param string $direction ltr or rtl
			 *
			 * @return string
			 */
			$mpdf->directionality = apply_filters( 'yith_ywgc_mpdf_directionality', $direction );

			/**
			 * DO_ACTION: yith_ywgc_after_write_mpdf_html_template_pdf
			 *
			 * The MPDF object after write the HTML in the PDF template.
			 *
			 * @param object $mpdf the MPDF object
			 */
			do_action( 'yith_ywgc_after_write_mpdf_html_template_pdf', $mpdf );

			$mpdf->WriteHTML( $style, 1 );
			$mpdf->WriteHTML( $html, 2 );

			/**
			 * DO_ACTION: yith_ywgc_after_generate_template_pdf
			 *
			 * The MPDF object after generate the PDF template.
			 *
			 * @param object $mpdf the MPDF object
			 */
			do_action( 'yith_ywgc_after_generate_template_pdf', $mpdf );

			$pdf = $mpdf->Output( 'document', 'S' );

			$old_file = get_post_meta( $object->ID, 'ywgc_pdf_file', true );

			if ( file_exists( $old_file ) ) {
				wp_delete_file( $old_file );
				delete_post_meta( $object->ID, 'ywgc_pdf_file' );
			}

			$pdf_filename = get_option( 'ywgc_pdf_file_name', 'yith-gift-card-[giftcardid]-[uniqid]' );

			/**
			 * APPLY_FILTERS: yith_ywgc_formatted_pdf_filename
			 *
			 * Filter to format the PDF filename.
			 *
			 * @param string the formatted PDF filename
			 * @param object $object the gift card object
			 * @param string $pdf_filename the PDF filename
			 *
			 * @return string
			 */
			$formatted_pdf_filename = apply_filters(
				'yith_ywgc_formatted_pdf_filename',
				str_replace(
					array( '[giftcardid]', '[uniqid]' ),
					array( $object->ID, uniqid() ),
					$pdf_filename
				),
				$object,
				$pdf_filename
			);

			/**
			 * APPLY_FILTERS: yith_ywgc_pdf_new_file_path
			 *
			 * Filter the gift card PDF file path.
			 *
			 * @param string the PDF file path
			 * @param int the gift card ID
			 *
			 * @return string
			 */
			$new_file = apply_filters( 'yith_ywgc_pdf_new_file_path', YITH_YWGC_SAVE_DIR . $formatted_pdf_filename . '.pdf', $object->ID );

			file_put_contents( $new_file, $pdf ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

			update_post_meta( $object->ID, 'ywgc_pdf_file', $new_file );

			return $new_file;
		}

		/**
		 * Output a gift cards template filled with real data or with sample data to start editing it
		 * on product page
		 *
		 * @param WC_Product|YWGC_Gift_Card_Premium $object  Product object or gift card object.
		 * @param string                            $context Context.
		 * @param string                            $case    Case.
		 */
		public function preview_digital_gift_cards( $object, $context = 'shop', $case = 'recipient' ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.objectFound, Universal.NamingConventions.NoReservedKeywordParameterNames.caseFound
			if ( $object instanceof YWGC_Gift_Card_Premium ) {
				$header_image_url = $this->get_header_image( $object );
				$amount           = $object->total_amount;

				/**
				 * APPLY_FILTERS: yith_ywgc_gift_card_template_amount
				 *
				 * Filter the gift card formatted price in the product page preview.
				 *
				 * @param string selected amount formatted as price
				 * @param object $object gift card product object
				 * @param string $amount selected amount
				 *
				 * @return string
				 */
				$formatted_price = apply_filters( 'yith_ywgc_gift_card_template_amount', wc_price( $amount ), $object, $amount );
				$gift_card_code  = $object->gift_card_number;
				$message         = $object->message;
				$expiration_date = ! is_numeric( $object->expiration ) ? strtotime( $object->expiration ) : $object->expiration;

				/**
				 * APPLY_FILTERS: yith_ywgc_shop_page_url_qr
				 *
				 * Filter the URL to add in the QR Code generated in the gift card to automatically apply the code.
				 *
				 * @param string URL to redirect the customer, default: the shop page
				 *
				 * @return string
				 */
				$shop_page_url = apply_filters( 'yith_ywgc_shop_page_url_qr', get_permalink( wc_get_page_id( 'shop' ) ) ? get_permalink( wc_get_page_id( 'shop' ) ) : site_url(), $object );

				$args = array();

				if ( $object->product_as_present ) {
					if ( $object->present_variation_id ) {
						$product = wc_get_product( $object->present_variation_id );
					} else {
						$product = wc_get_product( $object->present_product_id );
					}

					if ( get_option( 'ywgc_gift_this_product_apply_gift_card', 'yes' ) === 'yes' ) {
						$args = array(
							YWGC_ACTION_ADD_DISCOUNT_TO_CART => $gift_card_code,
							YWGC_ACTION_VERIFY_CODE       => YITH_YWGC()->hash_gift_card( $object ),
							YWGC_ACTION_PRODUCT_ID        => is_object( $product ) ? $product->get_id() : '',
							YWGC_ACTION_GIFT_THIS_PRODUCT => 'yes',
						);
					} else {
						$args = array(
							YWGC_ACTION_PRODUCT_ID        => is_object( $product ) ? $product->get_id() : '',
							YWGC_ACTION_GIFT_THIS_PRODUCT => 'yes',
						);
					}

					if ( 'to_customize_page' === get_option( 'ywgc_gift_this_product_button_redirect', 'to_product_page' ) ) {
						$shop_page_url = add_query_arg( $args, get_page_link( get_option( 'ywgc_gift_this_product_redirected_page' ) ) );
					} else {
						$shop_page_url = add_query_arg( $args, get_permalink( $product->get_id() ) );
					}
				} else {
					$args = array(
						YWGC_ACTION_ADD_DISCOUNT_TO_CART => $gift_card_code,
						YWGC_ACTION_VERIFY_CODE          => YITH_YWGC()->hash_gift_card( $object ),
					);
				}

				$apply_discount_url = add_query_arg( $args, $shop_page_url );
			}

			if ( isset( $header_image_url ) ) {
				if ( strpos( $header_image_url, '-yith_wc_gift_card_premium_separator_ywgc_template_design-' ) !== false ) {
					$array_header_image_url = explode( '-yith_wc_gift_card_premium_separator_ywgc_template_design-', $header_image_url );
					$header_image_url       = $array_header_image_url['1'];
				}
			}

			$product_id = isset( $object->product_id ) ? $object->product_id : '';

			/**
			 * APPLY_FILTERS: yith_wcgc_date_format
			 *
			 * Filter the date format used in the plugin.
			 *
			 * @param string the date format
			 *
			 * @return string
			 */
			$args = array(
				'company_logo_url'         => ( 'yes' === get_option( 'ywgc_shop_logo_on_gift_card', 'no' ) ) ? get_option( 'ywgc_shop_logo_url', YITH_YWGC_ASSETS_IMAGES_URL . 'default-giftcard-main-image.png' ) : '',
				'header_image_url'         => $header_image_url,
				'default_header_image_url' => $this->get_default_header_image(),
				'formatted_price'          => $formatted_price,
				'gift_card_code'           => $gift_card_code,
				'message'                  => $message,
				'context'                  => $context,
				'object'                   => $object,
				'product_id'               => $product_id,
				'case'                     => $case,
				'shop_page_url'            => $shop_page_url,
				'apply_discount_url'       => $apply_discount_url,
				'date_format'              => apply_filters( 'yith_wcgc_date_format', 'Y-m-d' ),
				'expiration_date'          => $expiration_date,
			);

			wc_get_template( 'yith-gift-cards/ywgc-gift-card-template.php', $args, '', trailingslashit( YITH_YWGC_TEMPLATES_DIR ) );
		}

		/**
		 * Attach the documents to the email
		 *
		 * @param array  $attachments Email attachments.
		 * @param string $status      Email id.
		 * @param mixed  $object      Object.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function attach_documents_to_email( $attachments, $status, $object ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.objectFound
			if ( 'yes' !== get_option( 'ywgc_attach_pdf_to_gift_card_code_email', 'no' ) ) {
				return $attachments;
			}

			if ( ! $object instanceof YWGC_Gift_Card_Premium ) {
				return $attachments;
			}

			if ( 'ywgc-email-send-gift-card' !== $status ) {
				return $attachments;
			}

			$attachments[] = $this->create_gift_card_pdf_file( $object );

			return $attachments;
		}

		/**
		 * Add option to the admin product page to disable gift the product
		 *
		 * @param array $options Options.
		 *
		 * @return array
		 */
		public function disable_gift_this_product_product_type_options( $options ) {
			$options['yith_wcgc_disable_gift_this_product'] = array(
				'id'            => '_yith_wcgc_disable_gift_this_product',
				'wrapper_class' => 'show_if_simple show_if_variable show_if_yith_bundle show_if_booking',
				'label'         => esc_html__( 'Disable gift this product', 'yith-woocommerce-gift-cards' ),
				'description'   => esc_html__( 'Check this option if you want to disable the option "gift this product" on this product.', 'yith-woocommerce-gift-cards' ),
				'default'       => 'no',
			);

			return $options;
		}

		/**
		 * Saving dsiable gift this product
		 *
		 * @param int $product_id Product id.
		 */
		public function disable_gift_this_product_woocommerce_process_product_meta( $product_id ) {
			if ( ! $product_id ) {
				return;
			}

			$product = wc_get_product( $product_id );

			if ( $product->is_type( array( 'simple', 'variable', 'yith_bundle', 'booking' ) )  ){
				$disable_gift_this_product = isset( $_POST['_yith_wcgc_disable_gift_this_product'] ) ? 'yes' : 'no'; // phpcs:ignore WordPress.Security.NonceVerification.Missing

				$product->update_meta_data( '_yith_wcgc_disable_gift_this_product', $disable_gift_this_product );
				$product->save_meta_data();
			}


		}

		/**
		 * When the default gift card image is changed from the plugin setting, update the product image
		 * of the default gift card
		 */
		public function update_default_gift_card() {
			if ( isset( $_POST['ywgc_gift_card_header_url-yith-attachment-id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$product = wc_get_product( $this->default_gift_card_id );

				$product->update_meta_data( '_thumbnail_id', sanitize_text_field( wp_unslash( $_POST['ywgc_gift_card_header_url-yith-attachment-id'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$product->save_meta_data();
			}
		}

		/**
		 * Create a product of type gift card to be used as placeholder. Should not be visible on shop page.
		 */
		public function initialize_products() {
			global $wpdb;

			// Search for a product with meta YWGC_PRODUCT_PLACEHOLDER.
			$this->default_gift_card_id = get_option( YWGC_PRODUCT_PLACEHOLDER, -1 );

			// Force the addition of the option with a value, to be able to update it if necessary.
			if ( ! get_option( YWGC_PRODUCT_PLACEHOLDER, -1 ) ) {
				add_option( YWGC_PRODUCT_PLACEHOLDER, -1 );
			}

			$product = wc_get_product( $this->default_gift_card_id );

			// reset the gift card product manually.
			if ( isset( $_GET['ywgc-reset'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				update_option( YWGC_PRODUCT_PLACEHOLDER, -1 );
				wp_delete_post( $this->default_gift_card_id, true );

				// Get all the gift card products and delete the ones created by default by the plugin. This will work only since version 4.2.0.
				$args        = array(
					'limit'  => -1,
					'status' => array( 'draft', 'pending', 'private', 'publish', 'trash' ),
					'type'   => 'gift-card',
					'return' => 'ids',
				);
				$product_ids = wc_get_products( $args );

				foreach ( $product_ids as $product_id ) {
					$product              = wc_get_product( $product_id );
					$is_gift_this_product = $product->get_meta( '_ywgc_is_default_gift_this_product' );

					if ( 'yes' === $is_gift_this_product ) {
						wp_delete_post( $product_id, true );
					}
				}

				$redirect_url = remove_query_arg( 'ywgc-reset' );
				wp_safe_redirect( $redirect_url );
				exit;
			}

			// If there is no default product.
			if ( ! is_object( $product ) ) {
				// Create a default gift card product.
				$args = array(
					'post_title'     => esc_html__( 'Gift this product', 'yith-woocommerce-gift-cards' ),
					'post_name'      => 'default_gift_this_product',
					'post_content'   => esc_html__( 'This product has been automatically created by the plugin YITH Gift Cards.You must not edit it, or the plugin could not work properly. The main functionality of this product is to be used for the feature "Gift this product"', 'yith-woocommerce-gift-cards' ),
					'post_status'    => 'publish',
					'post_date'      => gmdate( 'Y-m-d H:i:s' ),
					'post_author'    => 0,
					'post_type'      => 'product',
					'comment_status' => 'closed',
				);

				$this->default_gift_card_id = wp_insert_post( $args );
				update_option( YWGC_PRODUCT_PLACEHOLDER, $this->default_gift_card_id );

				// Create a taxonomy for products of type YWGC_GIFT_CARD_PRODUCT_TYPE and set the product created to the new taxonomy
				// Create product type.
				$term    = wp_insert_term( YWGC_GIFT_CARD_PRODUCT_TYPE, 'product_type' );
				$term_id = - 1;

				if ( $term instanceof WP_Error ) {
					$error_code = $term->get_error_code();

					if ( 'term_exists' === $error_code ) {
						$term_id = $term->get_error_data( $error_code );
					}
				} else {
					$term_id = $term['term_id'];
				}

				if ( -1 !== $term_id ) {
					wp_set_object_terms( $this->default_gift_card_id, $term_id, 'product_type' );
				} else {
					wp_die( esc_html__( 'An error occurred, you cannot use the plugin', 'yith-woocommerce-gift-cards' ) );
				}

				// set this default gift card product as virtual.
				$product = wc_get_product( $this->default_gift_card_id );

				if ( $product && is_object( $product ) ) {
					$product->set_virtual( true );
					$product->set_catalog_visibility( 'hidden' );
					$product->update_meta_data( '_ywgc_is_default_gift_this_product', 'yes' );
					$product->save();
				}
			} else {
				$product = wc_get_product( $this->default_gift_card_id );

				if ( ! is_object( $product ) ) {
					return;
				}

				if ( $product && 'gift-card' !== $product->get_type() ) {
					wp_set_object_terms( $product->get_id(), YWGC_GIFT_CARD_PRODUCT_TYPE, 'product_type' );
				}

				if ( ! $product->is_virtual() ) {
					$product->set_virtual( true );
					$product->save();
				}

				if ( ! $product->get_image_id() ){
					$default_image_id = get_option( 'ywgc_default_image_id' );
					$product->set_image_id( $default_image_id );
					$product->save();
				}

				update_option( YWGC_PRODUCT_PLACEHOLDER, $product->get_id() );
			}
		}

		/**
		 * Execute update on data used by the plugin that has been changed passing
		 * from a DB version to another
		 */
		public function update_database() {
			/**
			 * Init DB version if not exists
			 */
			$db_version = get_option( self::YWGC_DB_VERSION_OPTION );

			if ( ! $db_version ) {
				// Update from previous version where the DB option was not set.
				global $wpdb;

				// Update metakey from YITH Gift Cards 1.0.0.
				$query = "Update {$wpdb->prefix}woocommerce_order_itemmeta
                        set meta_key = '" . YWGC_META_GIFT_CARD_POST_ID . "'
                        where meta_key = 'gift_card_post_id'";
				$wpdb->query( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

				$db_version = '1.0.0';
			}

			/**
			 * Start the database update step by step
			 */
			if ( version_compare( $db_version, '1.0.0', '<=' ) ) {
				// Set gift card placeholder with catalog visibility equal to "hidden".
				$product = wc_get_product( get_option( YWGC_PRODUCT_PLACEHOLDER ) );

				$product->update_meta_data( '_visibility', 'hidden' );
				$product->save_meta_data();

				$db_version = '1.0.1';
			}

			if ( version_compare( $db_version, '1.0.1', '<=' ) ) {
				// extract the user_id from the order where a gift card is applied and register it so the gift card will be shown on my-account.
				$args = array(
					'numberposts' => - 1,
					'meta_key'    => YWGC_META_GIFT_CARD_ORDERS, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'post_type'   => YWGC_CUSTOM_POST_TYPE_NAME,
					'post_status' => 'any',
				);

				// Retrieve the gift cards matching the criteria.
				$posts = get_posts( $args );

				foreach ( $posts as $post ) {
					$gift_card = new YWGC_Gift_Card_Premium( array( 'ID' => $post->ID ) );

					if ( ! $gift_card->exists() ) {
						continue;
					}

					/**
					 * Order object
					 *
					 * @var WC_Order $order
					 */
					$orders = $gift_card->get_registered_orders();

					foreach ( $orders as $order_id ) {
						$order = wc_get_order( $order_id );

						if ( $order ) {
							$gift_card->register_user( $order->get_user_id() );
						}
					}
				}

				$db_version = '1.0.2';  // Continue to next step.
			}

			if ( version_compare( $db_version, '1.0.2', '<=' ) ) {
				flush_rewrite_rules();
				$db_version = '1.0.3';  // Continue to next step.
			}

			// Update the current DB version.
			update_option( self::YWGC_DB_VERSION_OPTION, YITH_YWGC_DB_CURRENT_VERSION );
		}

		/**
		 * Getter option allow manual amount
		 *
		 * @return bool
		 * @since  2.2.6
		 */
		public function allow_manual_amount() {
			return get_option( 'ywgc_permit_free_amount', 'no' );
		}

		/**
		 * Getter option allow product as present
		 *
		 * @return bool
		 * @since  2.2.6
		 */
		public function allow_product_as_present() {
			return yith_ywgc_is_module_active( 'gift-this-product' );
		}

		/**
		 * Getter option allow multiple recipients
		 *
		 * @return bool
		 * @since  2.2.6
		 */
		public function allow_multiple_recipients() {
			return ( 'yes' === get_option( 'ywgc_allow_multi_recipients', 'no' ) );
		}

		/**
		 * Getter option order cancelled action
		 *
		 * @return bool
		 * @since  2.2.6
		 */
		public function order_cancelled_action() {
			return get_option( 'ywgc_order_cancelled_action', 'nothing' );
		}

		/**
		 * Getter option order refunded action
		 *
		 * @return bool
		 * @since  2.2.6
		 */
		public function order_refunded_action() {
			return get_option( 'ywgc_order_refunded_action', 'nothing' );
		}

		/**
		 * Getter option gift this product label
		 *
		 * @return bool
		 * @since  2.2.6
		 */
		public function ywgc_gift_this_product_label() {
			$get_option_ywgc_gift_this_product_label = get_option( 'ywgc_gift_this_product_label', esc_html__( 'Gift this product', 'yith-woocommerce-gift-cards' ) );

			return ( empty( $get_option_ywgc_gift_this_product_label ) ? 'Gift this product' : $get_option_ywgc_gift_this_product_label );
		}

		/**
		 * Retrieve a gift card product instance from the gift card code
		 *
		 * @param string $code the card code to search for.
		 *
		 * @return mixed
		 */
		public function get_gift_card_by_code( $code ) {
			$gift_card_object = new YWGC_Gift_Card_Premium( array( 'gift_card_number' => $code ) );

			if ( is_object( $gift_card_object ) && 0 !== $gift_card_object->ID ) {
				return $gift_card_object;
			}

			return false;
		}

		/**
		 * Set the manual amount status for gift cards that are linked to the global value
		 *
		 * @param bool   $enabled Is manual amount enabled.
		 * @param string $status  Manual amount status.
		 *
		 * @return bool
		 */
		public function is_manual_amount_enabled( $enabled, $status ) {
			if ( 'global' === $status ) {
				$enabled = $this->allow_manual_amount();
			}

			return $enabled;
		}

		/**
		 * Create the gift card send date on save
		 *
		 * @param int     $post_id Post id.
		 * @param WP_Post $post    Post object.
		 * @param bool    $update  Update post or not.
		 */
		public function create_send_date_on_save( $post_id, $post, $update ) {
			if ( 'gift_card' === $post->post_type ) {
				$delivery_send_date = get_post_meta( $post_id, '_ywgc_delivery_send_date', true );

				if ( '' === $delivery_send_date ) {
					update_post_meta( $post_id, '_ywgc_delivery_send_date', '' );
				}
			}
		}

		/**
		 * Retrieve the count of gift cards where the user is the recipient
		 *
		 * @param int $user_id User id.
		 */
		public function ywgc_count_user_gift_cards( $user_id ) {
			$user = get_user_by( 'id', $user_id );

			/**
			 * APPLY_FILTERS: yith_ywgc_woocommerce_my_account_my_orders_query
			 *
			 * Filter the query arguments which get all the gift cards assigned to an user.
			 *
			 * @param array arguments to generate the get_posts query
			 *
			 * @return array
			 */
			$gift_cards_args = apply_filters(
				'yith_ywgc_woocommerce_my_account_my_orders_query',
				array(
					'numberposts' => - 1,
					'fields'      => 'ids',
					'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						'relation' => 'OR',
						array(
							'key'   => YWGC_META_GIFT_CARD_CUSTOMER_USER,
							'value' => $user_id,
						),
						array(
							'key'   => '_ywgc_recipient',
							'value' => $user->user_email,
						),
					),
					'post_type'   => YWGC_CUSTOM_POST_TYPE_NAME,
					'post_status' => 'any',
				)
			);

			$ids = get_posts( $gift_cards_args );

			return count( $ids );
		}

		/**
		 * Define bulk actions.
		 *
		 * @param array $actions Existing actions.
		 *
		 * @return array
		 */
		public function define_bulk_actions( $actions ) {
			unset( $actions['edit'] );
			unset( $actions['trash'] );

			/**
			 * APPLY_FILTERS: yith_ywgc_gift_cards_bulk_actions
			 *
			 * Filter the defined bulk actions in the gift card dashboard.
			 *
			 * @param array bulks actions defined in the gift card dashboard
			 *
			 * @return array
			 */
			$custom = apply_filters(
				'yith_ywgc_gift_cards_bulk_actions',
				array(
					'send_gift_cards'   => __( 'Send gift cards', 'yith-woocommerce-gift-cards' ),
					'delete_gift_cards' => __( 'Delete gift cards permanently', 'yith-woocommerce-gift-cards' ),
				)
			);

			$actions = array_reverse( array_merge( $actions, $custom ) );

			return $actions;
		}
	}
}

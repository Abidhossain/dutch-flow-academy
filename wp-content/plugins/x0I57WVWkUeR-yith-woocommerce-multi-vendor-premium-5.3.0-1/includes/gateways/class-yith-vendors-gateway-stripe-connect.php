<?php
/**
 * YITH Gateway Stripe Connect
 * Define methods and properties for class that manages payments via Stripe Connect
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Vendors_Gateway_Stripe_Connect' ) ) {

	class YITH_Vendors_Gateway_Stripe_Connect extends YITH_Vendors_Gateway {

		/**
		 * The gateway slug
		 *
		 * @var string
		 */
		protected $id = 'stripe-connect';

		/**
		 * The gateway name
		 *
		 * @var string
		 */
		protected $method_title = 'Stripe Connect';

		/**
		 * YITH_Vendors_Gateway_Stripe_Connect constructor.
		 *
		 * @return void
		 * @since 4.0.0
		 */
		public function __construct() {

			$this->set_is_external( true );
			$this->set_is_available_on_checkout( true );

			$this->set_external_args(
				array(
					'check_method'   => 'function_exists',
					'check_for'      => 'YITH_Stripe_Connect',
					'plugin_url'     => '//yithemes.com/themes/plugins/yith-woocommerce-stripe-connect/',
					'plugin_name'    => 'YITH Stripe Connect for WooCommerce',
					'min_version'    => '1.0.4',
					'plugin_version' => defined( 'YITH_WCSC_VERSION' ) ? YITH_WCSC_VERSION : 0,
				)
			);

			parent::__construct();
		}

		/**
		 * Init gateway hooks and filters
		 *
		 * @return void
		 * @since  4.0.0
		 */
		protected function init() {
			parent::init();

			// Prevent generate commissions for suborders.
			add_filter( 'yith_wcsc_process_order_commissions', array( $this, 'process_order_commissions' ), 10, 2 );
			add_filter( 'yith_wcsc_process_product_commissions', array( $this, 'process_product_commissions' ), 10, 2 );
			add_filter(
				'yith_wcsc_process_cart_item_commissions',
				array(
					$this,
					'process_product_commissions',
				),
				10,
				2
			);

			if ( $this->is_external_plugin_enabled() ) {
				// Enqueue Scripts.
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 20 );
				if ( ! $this->is_enabled() ) {
					// Special actions: stripe connect enabled but integration disabled.
					add_action(
						'admin_notices',
						array(
							$this,
							'print_wc_stripe_connect_enable_gateway_for_vendors_message',
						)
					);
					add_action( 'wp_ajax_enable_gateway_for_vendors', array( $this, 'save_enable_gateway_done' ) );
				}
			}

			if ( ! $this->is_enabled() ) { // Do not go further.
				return;
			}

			// Admin Panel.
			add_filter( 'yith_wcsc_general_settings', array( $this, 'add_commissions_options' ), 10, 1 );
			add_filter( 'yith_wcsc_prepared_commission_args', 'YITH_Vendors_Gateway_Stripe_Connect::prepared_commission_args', 10, 2 );
			add_action( 'admin_notices', array( $this, 'print_connect_vendor_to_stripe_message' ) );
			add_action(
				'yith_wcmv_vendor_dashboard_payments_after_fields',
				array(
					$this,
					'stripe_connect_account_page',
				)
			);

			if ( current_user_can( 'manage_woocommerce' ) ) {
				// Admin Message if the gateway is enabled for vendors.
				add_action( 'admin_notices', array( $this, 'print_wc_stripe_connect_redirect_uri_message' ) );
				add_action( 'wp_ajax_redirect_uri_done_for_vendors', array( $this, 'save_redirect_uri_done' ) );

			}

			if ( current_user_can( YITH_Vendors_Capabilities::ROLE_NAME ) && ! empty( YITH_Stripe_Connect()->admin ) ) {
				remove_action(
					'admin_notices',
					array(
						YITH_Stripe_Connect()->admin,
						'print_wc_stripe_connect_uri_webhook_message',
					)
				);
			}

			// Checkout Payment.
			if ( $this->is_enabled_for_checkout() ) {
				add_action( 'woocommerce_order_status_changed', array( $this, 'create_vendors_transfer' ), 30, 4 );
			}
		}

		/**
		 * Filter the standard commission args to allow Stripe Connect To manage Shipping FEE
		 *
		 * @param array $args Prepared commission args.
		 * @param array $commission_args Commission args.
		 * @return array
		 */
		public static function prepared_commission_args( $args, $commission_args ) {
			$integration_note = isset( $commission_args['integration_item'] ) ? maybe_unserialize( $commission_args['integration_item'] ) : '';

			if ( ! empty( $integration_note['plugin_integration'] ) && YITH_WPV_SLUG === $integration_note['plugin_integration'] ) {
				$commission_id = ! empty( $integration_note['vendor_commission_id'] ) ? $integration_note['vendor_commission_id'] : 0;
				$commission    = yith_wcmv_get_commission( $commission_id );
				if ( $commission ) {
					$shipping_fee         = _x( 'Shipping fee', '[admin]: commission type', 'yith-woocommerce-product-vendors' );
					$args['product_info'] = empty( $args['product_info'] ) ? sprintf( '%s %s', YITH_Vendors_Taxonomy::get_taxonomy_labels( 'singular_name' ), $shipping_fee ) : $args['product_info'];
				}
			}

			return $args;
		}

		/**
		 * Register gateway options array
		 *
		 * @return void
		 * @since  4.0.0
		 */
		public function register_gateway_options() {
			$title           = __( 'Send commissions to vendors on payment completed', 'yith-woocommerce-product-vendors' );
			$p_text          = _x( 'When creating charges on your platform and separately creating a transfer, the platform can earn money by allocating less of the charge amount to the destination Stripe account, as in the following example. Assuming that represents a delivery service transaction, with a charge to the customer of $100, a transfer of $20 to the delivery person, and a transfer of $70 to the restaurant:', '[Admin]: Option description', 'yith-woocommerce-product-vendors' );
			$li_1            = _x( '1. The charge amount less the Stripe fees is added to the platform account’s pending balance.', '[Admin]: Option description', 'yith-woocommerce-product-vendors' );
			$li_2            = _x( '2. When the platform’s available balance is sufficient (at least $90), the transfers can be processed, reducing the platform’s available balance by the specified amounts and increasing both connected account’s available balances by that same amount.', '[Admin]: Option description', 'yith-woocommerce-product-vendors' );
			$li_3            = _x( '3. The platform retains an additional $6.80 ($100.00 - $70.00 - $20.00 - $3.20, assuming standard U.S. Stripe fees).', '[Admin]: Option description', 'yith-woocommerce-product-vendors' );
			$img_path        = YITH_WPV_ASSETS_URL . 'images/stripe-charges-transfers.png';
			$doc_url         = '//stripe.com/docs/connect/charges-transfers#collecting-fees';
			$source          = sprintf( "%s: <a href='%s' target='_blank'>%s</a>", _x( 'Source', '[part of] Source Stripe Documentation', 'yith-woocommerce-product-vendors' ), $doc_url, __( 'Stripe Documentation', 'yith-woocommerce-product-vendors' ) );
			$collecting_fees = sprintf( "<div id='stripe-connect-description-wrapper'><h4>%s</h4><p>%s<br/><ul id='stripe_collect_fees'><li>%s</li><li>%s</li><li>%s</li></ul><img class='stripe-img' src='%s'><small>%s</small></p></div>", $title, $p_text, $li_1, $li_2, $li_3, $img_path, $source );

			$this->options = array(
				'stripe_connect_enable_commissions_log' => array(
					'id'      => 'yith_wcmv_enable_stripe-connect_commissions_log',
					'type'    => 'checkbox',
					'title'   => __( 'Enable/Disable', 'yith-woocommerce-product-vendors' ),
					'desc'    => sprintf(
						"%s <a href='%s'>%s</a>",
						__( "Add vendors' commissions to", 'yith-woocommerce-product-vendors' ),
						add_query_arg(
							array(
								'page' => 'yith_wcsc_panel',
								'tab'  => 'commissions',
							),
							admin_url( 'admin.php' )
						),
						__( 'Stripe Connect Commission Report', 'yith-woocommerce-product-vendors' )
					),
					'default' => 'no',
				),

				'stripe_connect_checkout_description'   => array(
					'type' => 'html',
					'html' => $collecting_fees,
				),
			);
		}

		/**
		 * Add "Connect to Stripe" button in vendor panel
		 *
		 * @return void
		 * @since  2.6.0
		 */
		public function stripe_connect_account_page() {
			if ( ! class_exists( 'YITH_Stripe_Connect_Frontend' ) ) {
				require_once YITH_WCSC_PATH . 'includes/class.yith-stripe-connect-frontend.php';
			}

			add_filter(
				'yith_wcsc_connect_account_template_args',
				array(
					$this,
					'stripe_connect_account_template_args',
				)
			);
			add_filter( 'yith_wcsc_account_page_script_data', array( $this, 'stripe_connect_account_template_args' ) );

			$stripe_connect_frontend = new YITH_Stripe_Connect_Frontend(); // phpcs:ignore

			$option_description = apply_filters( 'yith_wcmv_stripe_connect_option_description', _x( 'In order to use the Stripe Connect service, you need to link your Stripe account with the website application.', '[Admin]: Option description', 'yith-woocommerce-product-vendors' ) );
			ob_start();
			?>

			<h3><?php echo esc_html( $this->get_method_title() ); ?></h3>
			<div class="form-field">
				<?php $stripe_connect_frontend->print_account_page(); ?>
				<p class="description"><?php echo esc_html( $option_description ); ?></p>
			</div>

			<?php
			echo ob_get_clean(); // phpcs:ignore
		}

		/**
		 * Filter the account connection template args
		 *
		 * @param array $args Current filter value.
		 * @return array template arguments
		 * @since  2.6.0
		 */
		public function stripe_connect_account_template_args( $args ) {
			$oauth_link         = apply_filters( 'yith_wcmv_stripe_connect_oauth_link', add_query_arg( array( 'redirect_uri' => $this->get_redirection_uri( true ) ), $args['oauth_link'] ), $args );

            $args['oauth_link'] = $oauth_link;

			$vendor = yith_wcmv_get_vendor( 'current', 'user' );
			if ( $vendor && $vendor->is_valid() && $vendor->has_limited_access() ) {
				$args['count_commissions'] = 0;
			}

			return $args;
		}

		/**
		 * Vendor panel redirection uri
		 *
		 * @param boolean $urlencode True if you want the url encoded, false otherwise.
		 * @return string
		 * @since  2.6.0
		 */
		public function get_redirection_uri( $urlencode = false ) {
			$vendor_admin_panel = add_query_arg(
				array(
					'page' => YITH_Vendors_Admin::PANEL_PAGE,
					'tab'  => 'payments',
				),
				admin_url( 'admin.php' )
			);

			return $urlencode ? rawurlencode( $vendor_admin_panel ) : $vendor_admin_panel;
		}

		/**
		 * Add redirect URI message for vendors
		 *
		 * @return void
		 * @since  2.6.0
		 */
		public function print_wc_stripe_connect_redirect_uri_message() {
			// phpcs:disable WordPress.Security.NonceVerification
			$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
			$section      = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '';

			if ( 'yes' !== get_option( 'yith_wcmv_redirected_uri_for_vendors', 'no' ) && ( 'yith_wcsc_panel' === $current_page || 'yith-stripe-connect' === $section || 'yith_wpv_panel' === $current_page ) ) {
				?>
				<div class="notice notice-warning yith_wcsc_message yith_wcsc_message_redirect_uri_for_vendors"
					data-action="redirect_uri_done_for_vendors">
					<p>
						<?php
						// translators: %1$s stand for the redirection URI, %2$s stand for the Stripe Dashboard url.
						echo wp_kses_post( sprintf( __( '<b>YITH Stripe Connect for WooCommerce (Multi Vendor Integration) -</b> Define the following <b>Redirect URI</b> %1$s in your <b>Redirect URIs</b> section at the following path <a href="%2$s" target="_blank">Stripe Dashboard > Connect > Settings</a>.', 'yith-stripe-connect-for-woocommerce' ), '<code>' . $this->get_redirection_uri() . '</code>', 'https://dashboard.stripe.com/account/applications/settings' ) );
						?>
					</p>
					<p>
						<a href="#"
							class="button-primary"><?php esc_html_e( 'Done', 'yith-woocommerce-product-vendors' ); ?></a>
					</p>
				</div>
				<?php
			}
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Add Enable Stripe Connect For Vendors message
		 *
		 * @return void
		 * @since  2.6.0
		 */
		public function print_connect_vendor_to_stripe_message() {
			$vendor            = yith_wcmv_get_vendor( 'current', 'user' );
			$stripe_connect_id = get_user_meta( $vendor->get_owner(), 'stripe_user_id', true );
			if ( empty( $stripe_connect_id ) && $vendor && $vendor->is_valid() && $vendor->has_limited_access() && $vendor->get_owner() === get_current_user_id() ) {
				$stripe_connect_vendor_uri = add_query_arg(
					array(
						'page' => YITH_Vendors_Admin::PANEL_PAGE,
						'tab'  => 'payments',
					),
					admin_url( 'admin.php' )
				);
				?>
				<div class="notice notice-warning yith_wcsc_message yith_wcsc_message_connect_to_stripe_gateway_for_vendors"
					data-action="enable_gateway_for_vendors">
					<p>
						<?php
						// translators: %s stand for the plugin panel url.
						echo wp_kses_post( sprintf( __( '<b>Stripe Connect Enabled - </b>You can use your Stripe account to receive the commissions. Please go to the <a href="%s" target="_blank">Vendor Profile> Payments</a> section and click on <b>Connect from Stripe</b>. If you don\'t have a Stripe account you can create a new one after click on connect button. Thanks', 'yith-stripe-connect-for-woocommerce' ), $stripe_connect_vendor_uri ) );
						?>
					</p>
				</div>
				<?php
			}
		}

		/**
		 * Add Enable Stripe Connect For Vendors message
		 *
		 * @return void
		 * @since  2.6.0
		 */
		public function print_wc_stripe_connect_enable_gateway_for_vendors_message() {
			// phpcs:disable WordPress.Security.NonceVerification
			$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
			$section      = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '';

			if ( 'yes' !== get_option( 'yith_wcmv_enable_gateway_for_vendors', 'no' ) && ( 'yith_wcsc_panel' === $current_page || 'yith-stripe-connect' === $section || yith_wcmv_is_plugin_panel() ) ) {
				$stripe_connect_uri = yith_wcmv_get_admin_panel_url(
					array(
						'tab'     => 'commissions',
						'sub_tab' => 'commissions-gateways',
					)
				);
				?>
				<div class="notice notice-warning yith_wcsc_message yith_wcsc_message_enable_gateway_for_vendors"
					data-action="enable_gateway_for_vendors">
					<p>
						<?php
						// translators: %s stand for the plugin panel url.
						echo wp_kses_post( sprintf( __( '<b>YITH Stripe Connect for WooCommerce (Multi Vendor Integration) - </b>Please, enable the <b>Multi Vendor Integration</b> for Stripe Connect in <a href="%s" target="_blank">YITH Plugins > Multi Vendor > Gateways ></a> <b>Stripe Connect</b> section.', 'yith-stripe-connect-for-woocommerce' ), $stripe_connect_uri ) );
						?>
					</p>
					<p>
						<a href="#"
							class="button-primary"><?php esc_html_e( 'Done', 'yith-woocommerce-product-vendors' ); ?></a>
					</p>
				</div>
				<?php
			}
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Save redirect uri option
		 *
		 * @return void
		 * @since  2.6.0
		 */
		public function save_redirect_uri_done() {
			$value = update_option( 'yith_wcmv_redirected_uri_for_vendors', 'yes' );
			wp_send_json_success( $value );
		}

		/**
		 * Save redirect uri option
		 *
		 * @return void
		 * @since  2.6.0
		 */
		public function save_enable_gateway_done() {
			$value = update_option( 'yith_wcmv_enable_gateway_for_vendors', 'yes' );
			wp_send_json_success( $value );
		}

		/**
		 * Enqueue Scripts
		 *
		 * @return void
		 * @since  2.6.0
		 */
		public function enqueue_scripts() {
			if ( yith_wcmv_is_plugin_panel() ) {
				wp_enqueue_script( 'yith-wcsc-admin' );
			}
		}

		/* === PAYMENT METHODS === */

		/**
		 * Pay method, used to process payment requests
		 *
		 * @param array $pay_data Array of parameters for the single requests.
		 * @return array
		 * @since  1.0
		 */
		public function pay( $pay_data ) {

			// Collect commission ids where payment fail.
			$commissions_failed = array();

			$not_connected_message              = _x( "The vendor hasn't connected the profile to Stripe.", '[Admin Error Message]', 'yith-woocommerce-product-vendors' );
			$commission_log_enabled_for_vendors = 'yes' === get_option( 'yith_wcmv_enable_stripe-connect_commissions_log', 'no' );
			// Gateway Requirements.
			$api_handler               = YITH_Stripe_Connect_API_Handler::instance();
			$stripe_connect_gateway    = YITH_Stripe_Connect()->get_gateway();
			$stripe_connect_commission = $commission_log_enabled_for_vendors ? YITH_Stripe_Connect_Commissions::instance() : null;
			// Build Payments Data For API.
			$transfer = null;

			foreach ( $pay_data as $vendor_id => $vendor_pay_data ) {
				$user_id           = $vendor_pay_data['user_id'];
				$stripe_connect_id = get_user_meta( $user_id, 'stripe_user_id', true );
				$amounts           = $vendor_pay_data['amount'];
				foreach ( $amounts as $currency => $amount ) {

					$args = array(
						'amount'      => yith_wcsc_get_amount( $amount, $currency ),
						'currency'    => $currency,
						'destination' => $stripe_connect_id,
					);

					$args = apply_filters( 'yith_wcmv_stripe_connect_transfer_args', $args, $pay_data, $vendor_pay_data, $vendor_id );

					if ( ! empty( $vendor_pay_data['transfer_group'] ) ) {
						$args['transfer_group'] = $vendor_pay_data['transfer_group'];
					}

					if ( ! empty( $vendor_pay_data['source_transaction'] ) ) {
						$args['source_transaction'] = $vendor_pay_data['source_transaction'];
					}

					$commission_ids = $vendor_pay_data['commission_ids'][ $currency ];

					// Create entry in Payments table.
					$payment_id = $this->register_payment(
						array(
							'payment'        => array(
								'vendor_id'        => $vendor_id,
								'user_id'          => $user_id,
								'amount'           => $amount,
								'currency'         => $currency,
								'payment_date'     => $vendor_pay_data['payment_date'],
								'payment_date_gmt' => $vendor_pay_data['payment_date_gmt'],
							),
							'commission_ids' => $commission_ids,
						)
					);

					if ( ! empty( $stripe_connect_id ) ) {
						// The vendor have a valid Stripe account.
						$transfer = $api_handler->create_transfer( $args );

						if ( isset( $transfer['error_transfer'] ) ) {
							// Display messages on order note and log file.
							$message = sprintf( '%s', $transfer['error_transfer'] );
							$stripe_connect_gateway->log( 'info', sprintf( 'Payment ID: %s', $payment_id ) );
							$stripe_connect_gateway->log( 'info', sprintf( 'Destination ID: %s', $stripe_connect_id ) );
							$stripe_connect_gateway->log( 'info', sprintf( 'User ID: %s', $user_id ) );
							$stripe_connect_gateway->log( 'error', sprintf( 'Stripe Error: %s', $message ) );
							YITH_Vendors()->payments->add_note( $payment_id, $message );

							$result = array(
								'status'   => false,
								'messages' => rawurlencode( $message ),
							);
						} elseif ( $transfer instanceof \Stripe\Transfer ) {
							// Lucky Day For Vendors! Money transfer complete.
							$message = __( 'Payment correctly issued to the gateway', 'yith-woocommerce-product-vendors' );
							YITH_Vendors()->payments->add_note( $payment_id, urldecode( $message ) );
							$stripe_connect_gateway->log( 'info', sprintf( 'Stripe Success: %s', $message ) );

							$result = array(
								'status'   => true,
								'messages' => rawurlencode( $message ),
							);
						}
					} else {
						// Not Connected to Stripe.
						$vendor  = yith_wcmv_get_vendor( $vendor_id, 'vendor' );
						$message = sprintf(
							'%s (%s: <a href="%s">#%s - %s</a>)',
							$not_connected_message,
							YITH_Vendors_Taxonomy::get_taxonomy_labels( 'singular_name' ),
							$vendor->get_url( 'admin' ),
							$vendor_id,
							$vendor->get_name()
						);

						YITH_Vendors()->payments->add_note( $payment_id, urldecode( $message ) );

						$message = sprintf( __( 'Payment failed: %s', 'yith-woocommerce-product-vendors' ), $not_connected_message );
						$this->add_note_commissions( $commission_ids, $message );

						$stripe_connect_gateway->log( 'info', sprintf( 'Payment ID: %s', $payment_id ) );
						$stripe_connect_gateway->log( 'info', sprintf( 'Destination ID: %s', $stripe_connect_id ) );
						$stripe_connect_gateway->log( 'info', sprintf( 'User ID: %s', $user_id ) );
						$stripe_connect_gateway->log( 'error', sprintf( 'Stripe Error: %s', $message ) );

						$result = array(
							'status'   => false,
							'messages' => rawurlencode( $message ),
						);
					}

					if ( isset( $result['status'] ) ) {
						$payment_status = true === $result['status'] ? 'paid' : 'failed';
					} else {
						$payment_status = 'failed';
					}

					$this->update_payment_status( $payment_id, $payment_status );

					foreach ( $commission_ids as $commission_id ) {
						$commission = yith_wcmv_get_commission( $commission_id );
						if ( $commission ) {
							$order = $commission->get_order();
							if ( 'paid' === $payment_status ) {
								$commission->update_status( $payment_status, '', true );
								$this->set_payment_post_meta( $commission );
								$gateway_payment_message = sprintf( '%s. %s %s', $result['messages'], _x( 'Paid via', '[Note]: Payed By Gateway', 'yith-woocommerce-product-vendors' ), $this->get_method_title() );
								$commission->add_note( urldecode( $gateway_payment_message ) );
							} else {
								$commissions_failed[] = $commission_id;
							}

							if ( $commission_log_enabled_for_vendors ) {
								$item    = $commission->get_item();
								$product = $commission->get_product();
								$vendor  = $commission->get_vendor();
								$item_id = $item instanceof WC_Order_Item ? $item->get_id() : 0;
								$notes   = array();
								if ( $transfer instanceof \Stripe\Transfer ) {
									$extra_info = array(
										'generated_by'    => array(
											'label' => __( 'Generated by', 'yith-woocommerce-product-vendors' ),
											'note'  => 'YITH WooCommerce Multi Vendor',
										),

										'vendor_information' => array(
											'label' => __( 'Vendor', 'yith-woocommerce-product-vendors' ),
											'note'  => sprintf( '<a href="%s" target="_blank">%s</a>', $vendor->get_url( 'admin' ), $vendor->get_name() ),
										),

										'commission_type' => array(
											'label' => __( 'Commission type', 'yith-woocommerce-product-vendors' ),
											'note'  => ucfirst( $commission->type ),
										),

										'commission_url'  => array(
											'label' => __( 'Commission ID', 'yith-woocommerce-product-vendors' ),
											'note'  => sprintf( '<a href="%s" target="_blank">#%s</a>', $commission->get_view_url( 'admin' ), $vendor->get_id() ),
										),
									);

									$notes = array(
										'transfer_id' => $transfer->id,
										'destination_payment' => $transfer->destination,
										'extra_info'  => apply_filters( 'yith_wcmv_extra_info_for_stripe_connect_commission', $extra_info ),
									);
								}

								$integration_item = array(
									'plugin_integration'   => YITH_WPV_SLUG,
									'payment_id'           => $payment_id,
									'vendor_commission_id' => $commission->get_id(),
								);

								$stripe_connect_commission->insert(
									array(
										'user_id'          => $user_id,
										'order_id'         => $order->get_id(),
										'order_item_id'    => $item_id,
										'product_id'       => $product instanceof WC_Product ? $product->get_id() : 0,
										'commission'       => $commission->get_amount(),
										'commission_status' => 'paid' === $payment_status ? 'sc_transfer_success' : 'sc_transfer_error',
										'commission_type'  => 'percentage',
										'commission_rate'  => ( $commission->get_rate() * 100 ),
										'payment_retarded' => 0,
										'purchased_date'   => $commission->get_date( 'mysql' ),
										'note'             => maybe_serialize( $notes ),
										'integration_item' => maybe_serialize( $integration_item ),
									)
								);
							}
						}
					}
				}
			}

			return array(
				'status'             => empty( $commissions_failed ),
				'commissions_failed' => $commissions_failed,
				'message'            => '',
			);
		}

		/**
		 * Create transfers for vendor's commissions
		 *
		 * @param integer       $order_id The order ID.
		 * @param string        $old_status The old order status.
		 * @param string        $new_status The new order status.
		 * @param WC_Order|null $order Order object or null.
		 * @since  1.0
		 */
		public function create_vendors_transfer( $order_id, $old_status, $new_status, $order = null ) {
			$allowed_status = $this->get_order_status_allowed();
			if ( empty( $order ) || empty( $order->get_parent_id() ) || ! $order->has_status( $allowed_status ) ) { // Skip main order.
				return false;
			}

			$commission_ids = yith_wcmv_get_commissions(
				array(
					'order_id' => $order_id,
					'status'   => 'all',
				)
			);

			$extra_args    = array(
				// translators: %d is the vendor's order ID.
				'transfer_group' => sprintf( __( 'Transfer to pay the vendor\'s order: #%d', 'yith-woocommerce-product-vendors' ), $order_id ),
			);
			$pay_data_args = array(
				'commission_ids' => $commission_ids,
				'extra_args'     => $extra_args,
			);
			$pay_data      = $this->get_pay_data( $pay_data_args );

			$this->pay( $pay_data );
		}

		/**
		 * Add note commissions
		 *
		 * @param array  $commission_ids An array of commissions ids.
		 * @param string $message Commissions message.
		 * @return void
		 */
		public function add_note_commissions( $commission_ids, $message ) {
			// Add Note to commissions.
			foreach ( $commission_ids as $commission_id ) {
				$commission = yith_wcmv_get_commission( $commission_id );
				$commission && $commission->add_note( $message );
			}
		}

		/**
		 * Check if current order is a vendor suborder and skip it to commissions creation process
		 *
		 * @param boolean $process If process or not the order.
		 * @param integer $order_id The order ID.
		 * @return bool True if Stripe Connect can process this order, false otherwise
		 * @since  3.0.1
		 */
		public function process_order_commissions( $process, $order_id ) {
			$order = wc_get_order( $order_id );
			if ( $order && 'yith_wcmv_vendor_suborder' === $order->get_created_via() ) {
				$process = false;
			}

			return $process;
		}

		/**
		 * Check if current product is from vendor or not
		 *
		 * @param boolean $process If process or not the product.
		 * @param integer $product_id The product ID.
		 * @return bool TRUE if Stripe Connect can process this order, false otherwise
		 * @since  3.0.1
		 */
		public function process_product_commissions( $process, $product_id ) {
			if ( function_exists( 'YITH_Stripe_Connect_Commissions' ) && 'yes' === YITH_Stripe_Connect_Commissions()->stripe_connect_gateway->get_option( 'vendor-product-commissions', 'yes' ) ) {
				$product = wc_get_product( $product_id );
				if ( $product instanceof WC_Product ) {
					$parent_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
					$vendor    = yith_wcmv_get_vendor( $parent_id, 'product' );
					$process   = ( $vendor && $vendor->is_valid() ) ? false : $process;
				}
			}

			return $process;
		}

		/**
		 * Add  Stripe Connect General options for commissions
		 *
		 * @param array $options Array of commissions options.
		 * @return array Stripe Connect option array
		 */
		public function add_commissions_options( $options ) {
			$vendors_option = array(
				'vendor-product-commissions' => array(
					'title'   => __( 'Exclude vendors\' products from commissions', 'yith-woocommerce-product-vendors' ),
					'type'    => 'checkbox',
					'label'   => __( 'If enabled, the receivers will not earn any commissions on vendors products', 'yith-woocommerce-product-vendors' ),
					'default' => 'yes',
					'id'      => 'vendor_product_commissions',
				),
			);

			$offset  = array_search( 'commissions-exceeded', array_keys( $options ), true );
			$first   = array_slice( $options, 0, $offset + 1 );
			$last    = array_slice( $options, $offset + 1, count( $options ) );
			$options = array_merge( $first, $vendors_option, $last );

			return $options;
		}
	}
}

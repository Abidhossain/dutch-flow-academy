<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Advanced_Shipment_Tracking_Admin_Notice {

	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;
	
	/**
	 * Initialize the main plugin function
	*/
	public function __construct() {
		$this->init();
	}
	
	/**
	 * Get the class instance
	 *
	 * @return WC_Advanced_Shipment_Tracking_Admin_Notice
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	/*
	* init from parent mail class
	*/
	public function init() {
		
		add_action( 'admin_init', array( $this, 'ast_pro_notice_ignore_cb' ) );

		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';

		if ( 'woocommerce-advanced-shipment-tracking' != $page ) {
			// Zorem Return For WooCommerce PRO Notice
			add_action( 'admin_notices', array( $this, 'zorem_return_admin_notice' ) );

		}

		// AST PRO Notice
		add_shortcode( 'ast_settings_admin_notice', array( $this, 'ast_settings_admin_notice' ) );
	}



	/**
	 * Display admin notice for missing shipping integration
	 */
	public function ast_settings_admin_notice() {
		ob_start();

		include 'views/admin_message_panel.php';
		
		return ob_get_clean();
	}

	/*
	* Dismiss admin notice for trackship
	*/
	public function ast_pro_notice_ignore_cb() {
		if ( isset( $_GET['zorem-return-update-notice'] ) ) {
			if (isset($_GET['nonce'])) {
				$nonce = sanitize_text_field($_GET['nonce']);
				if (wp_verify_nonce($nonce, 'zorem_return_dismiss_notice')) {
					update_option('zorem_return_update_ignore_385', 'true');
				}
			}
		}
	}

	/*
	* Display admin notice on plugin install or update
	*/
	public function zorem_return_admin_notice() {
		
		if ( get_option('zorem_return_update_ignore_385') ) {
			return;
		}
		
		$nonce = wp_create_nonce('zorem_return_dismiss_notice');
		$dismissable_url = esc_url(add_query_arg(['zorem-return-update-notice' => 'true', 'nonce' => $nonce]));

		?>
		<style>		
		.wp-core-ui .notice.zorem-return-dismissable-notice{
			position: relative;
			padding-right: 38px;
			border-left-color: #3b64d3;
		}
		.wp-core-ui .notice.zorem-return-dismissable-notice h3{
			margin-bottom: 5px;
		} 
		.wp-core-ui .notice.zorem-return-dismissable-notice a.notice-dismiss{
			padding: 9px;
			text-decoration: none;
		} 
		.wp-core-ui .button-primary.zorem_return_notice_btn {
			background: #3b64d3;
			color: #fff;
			border-color: #3b64d3;
			text-transform: uppercase;
			padding: 0 11px;
			font-size: 12px;
			height: 30px;
			line-height: 28px;
			margin: 5px 0 10px;
		}
		.zorem-return-dismissable-notice strong{
			font-weight:bold;
		}
		</style>
		<div class="notice updated notice-success zorem-return-dismissable-notice">
			<a href="<?php esc_html_e( $dismissable_url ); ?>" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></a>
			<h2>🔁 Simplify Returns with Zorem Returns for WooCommerce!</h2>
			<p>Let your customers easily request returns or exchanges directly from their account. With features like return approvals, customizable return reasons, and automated status updates, the Zorem Returns plugin helps you manage post-purchase experiences more efficiently.</p>
			
			<p><strong>🎁 Special Offer:</strong> Get <strong>20% OFF</strong> with coupon code <strong>RETURNS20!</strong></p>
			<a class="button-primary zorem_return_notice_btn" target="blank" href="https://www.zorem.com/product/zorem-returns/">👉 Learn More & Get Zorem Returns</a>
			<a class="button-primary zorem_return_notice_btn" href="<?php esc_html_e( $dismissable_url ); ?>">Dismiss</a>
		</div>
		<?php
	}
}

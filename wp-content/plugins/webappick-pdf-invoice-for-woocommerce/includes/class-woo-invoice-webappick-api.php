<?php //phpcs:ignore
/**
 * WooCommerce Invoice Free Plugin Uses Tracker
 * Uses Webappick Insights for tracking
 *
 * @package    Woo_Invoice_ProWebAppickAPI
 * @since   1.2.2
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'Woo_Invoice_WebAppickAPI' ) ) {
	/**
	 * Class Woo_Invoice_WebAppickAPI
	 */
	final class Woo_Invoice_WebAppickAPI {

		/**
		 * Singleton instance
		 *
		 * @var Woo_Invoice_WebAppickAPI
		 */
		protected static $instance;

		/**
		 * Singleton Client
		 *
		 * @var Challan\AppServices\Client
		 */
		protected $client = null;

		/**
		 * Singleton Insights
		 *
		 * @var Challan\AppServices\Insights
		 */
		protected $insights = null;

		/**
		 * Promotions Class Instance
		 *
		 * @var Challan\AppServices\Promotions
		 */
		public $promotion = null;

		/**
		 * Plugin License Manager
		 *
		 * @var Challan\AppServices\License
		 */
		protected $license = null;

		/**
		 * Plugin Updater
		 *
		 * @var Challan\AppServices\Updater
		 */
		protected $updater = null;

		/**
		 * Initialize
		 *
		 * @return Woo_Invoice_WebAppickAPI
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Class constructor
		 *
		 * @return void
		 * @since  1.0.0
		 */
		private function __construct() {
			/**
			 * Get info noinspection PhpIncludeInspection
			 *
			 * @noinspection PhpIncludeInspection
			 */
			if ( ! class_exists( 'WebAppick\Free\AppServices\Client' ) ) {
				include_once CHALLAN_FREE_LIBS_PATH . 'WebAppick/AppServices/Client.php';
			}
			$this->client = new Challan\AppServices\Client( '28261c3c-793e-4c74-bf7f-2837b8c6ddb4', 'Woo Invoice Free', CHALLAN_FREE_ROOT_FILE );
			// Load.
			$this->insights  = $this->client->insights(); // Plugin Insights.
			$this->promotion = $this->client->promotions(); // Promo offers.

			// Setup.
			$this->promotion->set_source( 'https://api.bitbucket.org/2.0/snippets/woofeed/RLbyop/files/woo-feed-notice.json' );

			// Initialize.
			$this->insight_init();
			$this->promotion->init();
		}

		/**
		 * Cloning is forbidden.
		 *
		 * @since 1.0.2
		 */
		public function __clone() {
			 _doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning is forbidden.', 'webappick-pdf-invoice-for-woocommerce' ), '1.0.2' );
		}

		/**
		 * Initialize Insights
		 *
		 * @return void
		 */
		private function insight_init() {
			$this->insights->add_extra(
				array(
					'products'   => $this->insights->get_post_count( 'product' ),
					'variations' => $this->insights->get_post_count( 'product_variation' ),
					'orders'     => $this->insights->get_post_count( 'order' ),
				)
			);
			$project_slug = $this->client->getSlug();
			add_filter( $project_slug . '_what_tracked', array( $this, 'data_we_collect' ), 10, 1 );
			add_filter(
				"WebAppick_{$project_slug}_Support_Ticket_Recipient_Email",
				function () {
					return 'support@webappick.com';
				},
				10
			);
			add_filter( "WebAppick_{$project_slug}_Support_Ticket_Email_Template", array( $this, 'support_ticket_template' ), 10 );
			add_filter( "WebAppick_{$project_slug}_Support_Request_Ajax_Success_Response", array( $this, 'support_response' ), 10 );
			add_filter( "WebAppick_{$project_slug}_Support_Request_Ajax_Error_Response", array( $this, 'supportErrorResponse' ), 10 );
			add_filter(
				"WebAppick_{$project_slug}_Support_Page_URL",
				function () {
					return 'https://webappick.com/support/';
				},
				10
			);
			$this->insights->init();
		}

		/**
		 * Generate Support Ticket Email Template.
		 *
		 * @return string
		 */
		public function support_ticket_template() {
			// dynamic variable format __INPUT_NAME__.
			/**
			 * Get info noinspection HtmlUnknownTarget
			 *
			 * @noinspection HtmlUnknownTarget
			 */
			$template  = '<div style="margin: 10px auto;"><p>Website : <a href="__WEBSITE__">__WEBSITE__</a><br>Plugin : %s (v.%s)</p></div>';
			$template  = sprintf( $template, $this->client->getName(), $this->client->getProjectVersion() );
			$template .= '<div style="margin: 10px auto;"><hr></div>';
			$template .= '<div style="margin: 10px auto;"><h3>__SUBJECT__</h3></div>';
			$template .= '<div style="margin: 10px auto;">__MESSAGE__</div>';
			$template .= '<div style="margin: 10px auto;"><hr></div>';
			$template .= sprintf(
				'<div style="margin: 50px auto 10px auto;"><p style="font-size: 12px;color: #009688">%s</p></div>',
				'Message Processed With WebAppick Service Library (v.' . $this->client->getClientVersion() . ')'
			);
			return $template;
		}

		/**
		 * Generate Support Ticket Ajax Response
		 *
		 * @return string
		 */
		public function support_response() {
		    $response        = '';
			$response        .= sprintf( '<h3>%s</h3>', esc_html__( 'Thank you -- Support Ticket Submitted.', 'webappick-pdf-invoice-for-woocommerce' ) );
			$ticket_submitted = esc_html__( 'Your ticket has been successfully submitted.', 'webappick-pdf-invoice-for-woocommerce' );
			$twenty4_hours    = sprintf( '<strong>%s</strong>', esc_html__( '24 hours', 'webappick-pdf-invoice-for-woocommerce' ) );
			$notification    = sprintf( esc_html__( 'You will receive an email notification from "support@webappick.com" in your inbox within %s.', 'webappick-pdf-invoice-for-woocommerce' ), $twenty4_hours ); //phpcs:ignore
			$follow_up        = esc_html__( 'Please Follow the email and WebAppick Support Team will get back with you shortly.', 'webappick-pdf-invoice-for-woocommerce' );
			$response        .= sprintf( '<p>%s %s %s</p>', $ticket_submitted, $notification, $follow_up );
			$doc_link         = sprintf( '<a class="button button-primary" href="https://webappick.helpscoutdocs.com/" target="_blank"><span class="dashicons dashicons-media-document" aria-hidden="true"></span> %s</a>', esc_html__( 'Documentation', 'webappick-pdf-invoice-for-woocommerce' ) );
			$vid_link         = sprintf( '<a class="button button-primary" href="https://www.youtube.com/c/WebAppick/videos" target="_blank"><span class="dashicons dashicons-video-alt3" aria-hidden="true"></span> %s</a>', esc_html__( 'Video Tutorials', 'webappick-pdf-invoice-for-woocommerce' ) );
			$response        .= sprintf( '<p>%s %s</p>', $doc_link, $vid_link );
			$response        .= '<br><br><br>';
			$toc              = sprintf( '<a href="https://webappick.com/terms-and-conditions/" target="_blank">%s</a>', esc_html__( 'Terms & Conditions', 'webappick-pdf-invoice-for-woocommerce' ) );
			$pp               = sprintf( '<a href="https://webappick.com/privacy-policy/" target="_blank">%s</a>', esc_html__( 'Privacy Policy', 'webappick-pdf-invoice-for-woocommerce' ) );
			$policy          = sprintf( esc_html__( 'Please read our %1$s and %2$s', 'webappick-pdf-invoice-for-woocommerce' ), $toc, $pp ); //phpcs:ignore
			$response        .= sprintf( '<p style="font-size: 12px;">%s</p>', $policy );
			return $response;
		}

		/**
		 * Set Error Response Message For Support Ticket Request
		 *
		 * @return string
		 */
		public function supportErrorResponse() { //phpcs:ignore
			return sprintf(
				'<div class="mui-error"><p>%s</p><p>%s</p><br><br><p style="font-size: 12px;">%s</p></div>',
				esc_html__( 'Something Went Wrong. Please Try The Support Ticket Form On Our Website.', 'webappick-pdf-invoice-for-woocommerce' ),
				sprintf( '<a class="button button-primary" href="https://webappick.com/support/" target="_blank">%s</a>', esc_html__( 'Get Support', 'webappick-pdf-invoice-for-woocommerce' ) ),
				esc_html__( 'Support Ticket form will open in new tab in 5 seconds.', 'webappick-pdf-invoice-for-woocommerce' )
			);
		}

		/**
		 * Set Data Collection description for the tracker
		 *
		 * @param data $data get data information.
		 *
		 * @return array
		 */
		public function data_we_collect( $data ) {
			$data = array_merge(
				$data,
				array(
					esc_html__( 'Number of products in your site.', 'webappick-pdf-invoice-for-woocommerce' ),
					esc_html__( 'Number of Orders in your site.', 'webappick-pdf-invoice-for-woocommerce' ),
					esc_html__( 'Site name, language and url.', 'webappick-pdf-invoice-for-woocommerce' ),
					esc_html__( 'Number of active and inactive plugins.', 'webappick-pdf-invoice-for-woocommerce' ),
					esc_html__( 'Your name and email address.', 'webappick-pdf-invoice-for-woocommerce' ),
				)
			);

			return $data;
		}

		/**
		 *
		 * Get data collection description
		 *
		 * @return array
		 */
		public function get_data_collection_description() {
			 return $this->insights->get_data_collection_description();
		}

		/**
		 * Update Tracker OptIn
		 *
		 * @param  bool $override optional. ignore last send datetime settings if true.
		 * @return void
		 * @see    Insights::send_tracking_data()
		 */
		public function tracker_opt_in( $override = false ) {
			 $this->insights->optIn( $override );
		}

		/**
		 * Update Tracker OptOut
		 *
		 * @return void
		 */
		public function tracker_opt_out() {
			$this->insights->optOut();
		}

		/**
		 * Check if tracking is enable
		 *
		 * @return bool
		 */
		public function is_tracking_allowed() {
			 return $this->insights->is_tracking_allowed();
		}
	}
}

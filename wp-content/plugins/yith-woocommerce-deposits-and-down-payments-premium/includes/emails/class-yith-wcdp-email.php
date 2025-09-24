<?php
/**
 * General WCDP email class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Classes\Emails
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCDP_Email' ) ) {
	/**
	 * General plugin email class (used only as base for extension)
	 *
	 * @since 1.0.0
	 */
	class YITH_WCDP_Email extends WC_Email {

		/**
		 * HTML content for the email
		 *
		 * @var string
		 */
		protected $content_html = '';

		/**
		 * Plain content for the email
		 *
		 * @var string
		 */
		protected $content_text = '';

		/**
		 * Order customer object
		 *
		 * @var \WP_User
		 * @since 1.0.0
		 */
		public $customer;

		/**
		 * Order related full amount suborders (ids array)
		 *
		 * @var mixed
		 * @since 1.0.0
		 */
		public $suborders;

		/**
		 * Set replaces for current emails
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function set_replaces() {
			if ( ! $this->customer && $this->object ) {
				$this->customer = $this->object->get_user();
			}

			$placeholders = array(
				'{order_date}'           => date_i18n( wc_date_format(), strtotime( $this->object->get_date_completed() ) ),
				'{order_number}'         => $this->object ? $this->object->get_order_number() : '',
				'{order_id}'             => $this->object ? $this->object->get_id() : '',
				'{order_status}'         => $this->object ? $this->object->get_status() : '',
				'{order_state}'          => $this->object ? $this->object->get_status() : '',
				'{customer_name}'        => $this->customer ? $this->customer->display_name : '',
				'{customer_login}'       => $this->customer ? $this->customer->user_login : '',
				'{customer_email}'       => $this->customer ? $this->customer->user_email : '',
				'{deposit_table}'        => $this->get_deposit_table(),
				'{deposit_table_plain}'  => $this->get_deposit_table( true ),
				'{suborder_table}'       => $this->get_deposit_table(),
				'{suborder_table_plain}' => $this->get_deposit_table( true ),
				'{deposit_list}'         => $this->get_deposit_list(),
				'{deposit_list_plain}'   => $this->get_deposit_list( true ),
				'{suborder_list}'        => $this->get_deposit_list(),
				'{suborder_list_plain}'  => $this->get_deposit_list( true ),
			);

			$this->placeholders = array_merge(
				$this->placeholders,
				$placeholders
			);
		}

		/**
		 * Returns deposit table template (plain/html)
		 *
		 * @param bool  $plain Whether to use plain template or HTML one.
		 * @param array $args  Additional arguments for the template.
		 * @return string Deposit table template
		 * @since 1.0.0
		 */
		public function get_deposit_table( $plain = false, $args = array() ) {
			ob_start();

			$template = 'deposit-table.php';
			$template = 'emails/' . ( $plain ? 'plain/' : '' ) . $template;

			$args = array_merge(
				array(
					'parent_order' => $this->object,
				),
				$args
			);

			yith_wcdp_get_template( $template, $args );

			return $this->format_string( ob_get_clean() );
		}

		/**
		 * Returns deposit list template (plain/html)
		 *
		 * @param bool  $plain Whether to use plain template or HTML one.
		 * @param array $args  Additional arguments for the template.
		 * @return string Deposit list template
		 * @since 1.0.0
		 */
		public function get_deposit_list( $plain = false, $args = array() ) {
			ob_start();

			$template = 'deposit-list.php';
			$template = 'emails/' . ( $plain ? 'plain/' : '' ) . $template;

			$args = array_merge(
				array(
					'email'        => $this,
					'parent_order' => $this->object,
					'suborder'     => isset( $this->suborder_id ) ? wc_get_order( $this->suborder_id ) : false,
				),
				$args
			);

			yith_wcdp_get_template(
				$template,
				$args
			);

			return $this->format_string( ob_get_clean() );
		}

		/**
		 * Get HTML content for the mail
		 *
		 * @return string HTML content of the mail
		 * @since 1.0.0
		 */
		public function get_content_html() {
			ob_start();

			yith_wcdp_get_template(
				$this->template_html,
				array(
					'parent_order'  => $this->object,
					'child_orders'  => $this->suborders,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => true,
					'plain_text'    => false,
					'email'         => $this,
				)
			);

			return $this->format_string( ob_get_clean() );
		}

		/**
		 * Get plain text content of the mail
		 *
		 * @return string Plain text content of the mail
		 * @since 1.0.0
		 */
		public function get_content_plain() {
			ob_start();

			yith_wcdp_get_template(
				$this->template_plain,
				array(
					'parent_order'  => $this->object,
					'child_orders'  => $this->suborders,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => true,
					'plain_text'    => true,
					'email'         => $this,
				)
			);

			return $this->format_string( ob_get_clean() );
		}

		/**
		 * Returns style to be used for buttons inside deposit emails.
		 *
		 * @return string HTML style attribute content (inline CSS).
		 */
		public function get_button_inline_style() {
			$base      = get_option( 'woocommerce_email_base_color' );
			$base_text = wc_light_or_dark( $base, '#202020', '#ffffff' );

			$style = "
				display: inline-block;
				background-color: $base;
				color: $base_text;
				white-space: nowrap;
				padding: 10px 15px;
				text-decoration: none;
			";

			return apply_filters( 'yith_wcdp_email_button_style', $style, $this );
		}

		/**
		 * Generate custom fields by using YITH framework fields.
		 *
		 * @param string $key The key of the field.
		 * @param array  $data The attributes of the field as an associative array.
		 *
		 * @return string
		 */
		public function generate_yith_wcdp_field_html( $key, $data ) {
			$field_key = $this->get_field_key( $key );
			$value     = $this->get_option( $key );
			$defaults  = array(
				'title'                => '',
				'label'                => '',
				'yith_wcdp_field_type' => 'text',
				'description'          => '',
				'desc_tip'             => false,
			);

			wp_enqueue_script( 'yith-plugin-fw-fields' );
			wp_enqueue_style( 'yith-plugin-fw-fields' );

			$data = wp_parse_args( $data, $defaults );

			$field          = $data;
			$field['type']  = $data['yith_wcdp_field_type'];
			$field['name']  = $field_key;
			$field['value'] = $value;
			$private_keys   = array( 'label', 'title', 'description', 'yith_wcdp_field_type', 'desc_tip' );

			foreach ( $private_keys as $private_key ) {
				unset( $field[ $private_key ] );
			}

			ob_start();
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?><?php echo wp_kses_post( $this->get_tooltip_html( $data ) ); ?></label>
				</th>
				<td class="forminp yith-plugin-ui">
					<fieldset>
						<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
						<?php yith_plugin_fw_get_field( $field, true, true ); ?>
						<?php echo wp_kses_post( $this->get_description_html( $data ) ); ?>
					</fieldset>
				</td>
			</tr>
			<?php

			return ob_get_clean();
		}
	}
}

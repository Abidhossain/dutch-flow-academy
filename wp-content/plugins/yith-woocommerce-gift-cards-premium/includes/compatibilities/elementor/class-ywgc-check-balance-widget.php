<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Class to manage the widget to check the gift card balance
 *
 * @package YITH\GiftCards\Classes\Compatibilities\Elementor
 */

use Elementor\Controls_Manager;
use Elementor\Widget_Button;
use ElementorPro\Modules\QueryControl\Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YWGC_Elementor_Check_Balance_Widget' ) ) {
	/**
	 * YWGC_Elementor_Check_Balance_Widget
	 */
	class YWGC_Elementor_Check_Balance_Widget extends \Elementor\Widget_Base {

		/**
		 * Get widget name.
		 */
		public function get_name() {
			return 'ywgc-check-balance-widget';
		}

		/**
		 * Get widget title.
		 */
		public function get_title() {
			return esc_html__( 'YITH Gift Card Check Balance Form', 'yith-woocommerce-gift-cards' );
		}

		/**
		 * Get widget icon.
		 */
		public function get_icon() {
			return 'fas fa-money-check-alt';
		}

		/**
		 * Get widget categories.
		 */
		public function get_categories() {
			return array( 'yith' );
		}

		/**
		 * Get widget style dependencies.
		 */
		public function get_style_depends() {
			return array( 'ywgc-shortcodes' );
		}

		/**
		 * Register widget controls.
		 */
		protected function register_controls() {
			$this->start_controls_section(
				'content_section',
				array(
					'label' => esc_html__( 'Content', 'yith-woocommerce-gift-cards' ),
					'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
				)
			);

			$this->add_control(
				'section-description',
				array(
					'label'       => esc_html__( 'Form description', 'yith-woocommerce-gift-cards' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'input_type'  => 'text',
					'placeholder' => esc_html__( 'Write a description here', 'yith-woocommerce-gift-cards' ),
				)
			);

			$this->end_controls_section();
		}

		/**
		 * Render widget output on the frontend.
		 */
		protected function render() {
			$settings = $this->get_settings_for_display();

			$html = wp_oembed_get( $settings['section-description'] );

			echo '<div class="ywgc-check-balance-widget-elementor-widget">';

			echo wp_kses_post( ( $html ) ? $html : $settings['section-description'] );

			echo do_shortcode( '[yith_gift_card_check_balance_form]' );

			echo '</div>';
		}
	}
}

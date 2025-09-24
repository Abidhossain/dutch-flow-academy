<?php
/**
 * Class to load the Elementor widgets
 *
 * @package YITH\GiftCards\Classes\Compatibilities\Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YWGC_Elementor' ) ) {
	/**
	 * Implements the YWGC_Elementor class.
	 *
	 * @since   1.2.2
	 * @author  YITH
	 */
	class YWGC_Elementor {

		/**
		 * Single instance of the class
		 *
		 * @var YWGC_Elementor
		 */
		protected static $instance;

		/**
		 * Store the order to use in widget previews.
		 *
		 * @var int
		 */
		public $order_to_test = 0;

		/**
		 * Returns single instance of the class
		 *
		 * @return YWGC_Elementor
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * YWGC_Elementor constructor.
		 */
		public function __construct() {
			if ( did_action( 'elementor/loaded' ) ) {
				add_action( 'elementor/elements/categories_registered', array( $this, 'add_elementor_yith_widget_category' ) );

				$register_widget_hook = version_compare( ELEMENTOR_VERSION, '3.5.0', '>=' ) ? 'elementor/widgets/register' : 'elementor/widgets/widgets_registered';

				add_action( $register_widget_hook, array( $this, 'elementor_init_widgets' ) );

				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			}
		}

		/**
		 * Add_elementor_yith_widget_category
		 *
		 * @param  mixed $elements_manager elements_manager.
		 * @return void
		 */
		public function add_elementor_yith_widget_category( $elements_manager ) {
			$elements_manager->add_category(
				'yith',
				array(
					'title' => 'YITH',
					'icon'  => 'fa fa-plug',
				)
			);
		}

		/**
		 * Elementor_init_widgets
		 *
		 * @return void
		 */
		public function elementor_init_widgets() {
			// Include Widget files.
			require_once YITH_YWGC_DIR . 'includes/compatibilities/elementor/class-ywgc-form-widget.php';
			require_once YITH_YWGC_DIR . 'includes/compatibilities/elementor/class-ywgc-check-balance-widget.php';
			require_once YITH_YWGC_DIR . 'includes/compatibilities/elementor/class-ywgc-redeem-widget.php';
			require_once YITH_YWGC_DIR . 'includes/compatibilities/elementor/class-ywgc-user-table-widget.php';

			// Register widget.
			$widgets_manager = \Elementor\Plugin::instance()->widgets_manager;

			if ( is_callable( array( $widgets_manager, 'register' ) ) ) {
				$widgets_manager->register( new \YWGC_Elementor_Form_Widget() );
				$widgets_manager->register( new \YWGC_Elementor_Check_Balance_Widget() );
				$widgets_manager->register( new \YWGC_Elementor_Redeem_Widget() );
				$widgets_manager->register( new \YWGC_Elementor_User_Table_Widget() );
			} else {
				$widgets_manager->register_widget_type( new \YWGC_Elementor_Form_Widget() );
				$widgets_manager->register_widget_type( new \YWGC_Elementor_Check_Balance_Widget() );
				$widgets_manager->register_widget_type( new \YWGC_Elementor_Redeem_Widget() );
				$widgets_manager->register_widget_type( new \YWGC_Elementor_User_Table_Widget() );
			}
		}

		/**
		 * Register shortcodes styles and scripts
		 */
		public function enqueue_scripts() {
			wp_register_style(
				'ywgc-shortcodes',
				YITH_YWGC_ASSETS_URL . '/css/ywgc-shortcodes.css',
				array(),
				YITH_YWGC_ENQUEUE_VERSION
			);
		}
	}
}

/**
 * Unique access to instance of YWGC_Elementor class
 *
 * @return YWGC_Elementor
 */
function YWGC_Elementor() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid, Universal.Files.SeparateFunctionsFromOO
	return YWGC_Elementor::get_instance();
}

YWGC_Elementor();

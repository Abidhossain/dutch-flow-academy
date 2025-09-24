<?php
/**
 * Enqueue Scripts & Styles
 *
 * @package LifterLMS_Assignments/Classes
 *
 * @since 1.0.0-beta.1
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue Scripts & Styles
 *
 * @since 1.0.0-beta.1
 */
class LLMS_Assignments_Assets {

	/**
	 * Constructor
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.5 Unknown.
	 * @since 1.1.8 Enqueue block editor assets.
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'register' ) );
		add_action( 'wp', array( $this, 'init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor' ) );

	}

	/**
	 * Register, enqueue, & localize frontend scripts
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.4 Unknown.
	 * @since 2.0.0 Drop `LLMS_ASSETS_SUFFIX` from file names.
	 *
	 * @return void
	 */
	public function enqueue() {

		if ( is_singular( 'llms_assignment' ) ) {

			$script_deps = array( 'jquery', 'llms-iziModal' );

			global $post;
			$assignment = llms_get_post( $post );
			if ( 'essay' === $assignment->get( 'assignment_type' ) ) {

				LLMS_Admin_Assets::register_quill( array( 'wordcount' ) );
				if ( ! wp_style_is( 'llms-quill-snow-theme', 'registered' ) ) {
					wp_register_style( 'llms-quill-snow-theme', plugins_url( '/assets/vendor/quill/quill.snow.css', LLMS_ASSIGNMENTS_PLUGIN_FILE ), array(), '1.3.5', 'screen' );
				}

				wp_enqueue_style( 'llms-quill-snow-theme' );
				array_push( $script_deps, 'llms-quill' );
				array_push( $script_deps, 'llms-quill-wordcount' );

			}

			wp_enqueue_style( 'llms-iziModal' );
			wp_enqueue_style( 'llms-assignments' );

			wp_register_script( 'llms-assignments', plugins_url( 'assets/js/llms-assignments.js', LLMS_ASSIGNMENTS_PLUGIN_FILE ), $script_deps, LLMS_ASSIGNMENTS_VERSION, true );
			wp_enqueue_script( 'llms-assignments' );

		}

	}

	/**
	 * Register, enqueue, & localize admin scripts.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.5 Unknown.
	 * @since 1.2.1 Fixed race condition between the core `llms-builder` and `llms-assignments-builder` scripts.
	 * @since 2.0.0 Drop `LLMS_ASSETS_SUFFIX` from file names.
	 *
	 * @link https://github.com/gocodebox/lifterlms-assignments/issues/60
	 *
	 * @return void
	 */
	public function enqueue_admin() {

		$screen = get_current_screen();

		if ( 'admin_page_llms-course-builder' === $screen->id ) {

			wp_register_style( 'llms-assignments-builder', plugins_url( 'assets/css/llms-assignments-builder.css', LLMS_ASSIGNMENTS_PLUGIN_FILE ), array(), LLMS_ASSIGNMENTS_VERSION );
			wp_enqueue_style( 'llms-assignments-builder' );

			wp_style_add_data( 'llms-assignments-builder', 'rtl', 'replace' );

			$llms_builder         = wp_scripts()->query( 'llms-builder' );
			$llms_builder->deps[] = 'llms-assignments-builder';
			wp_register_script( 'llms-assignments-builder', plugins_url( 'assets/js/llms-assignments-builder.js', LLMS_ASSIGNMENTS_PLUGIN_FILE ), array( 'wp-hooks' ), LLMS_ASSIGNMENTS_VERSION, true );

		} elseif ( 'lifterlms_page_llms-reporting' === $screen->id ) {

			wp_register_style( 'llms-assignments-reporting', plugins_url( 'assets/css/llms-assignments-reporting.css', LLMS_ASSIGNMENTS_PLUGIN_FILE ), array(), LLMS_ASSIGNMENTS_VERSION );
			wp_enqueue_style( 'llms-assignments-reporting' );

			wp_style_add_data( 'llms-assignments-reporting', 'rtl', 'replace' );

			if ( isset( $_GET['submission_id'] ) ) {

				wp_register_script( 'llms-assignments-submission-review', plugins_url( 'assets/js/llms-assignments-submission-review.js', LLMS_ASSIGNMENTS_PLUGIN_FILE ), array( 'jquery' ), LLMS_ASSIGNMENTS_VERSION, true );
				wp_enqueue_script( 'llms-assignments-submission-review' );

				wp_register_style( 'llms-assignments-submission-review', plugins_url( 'assets/css/llms-assignments-submission-review.css', LLMS_ASSIGNMENTS_PLUGIN_FILE ), array(), LLMS_ASSIGNMENTS_VERSION );
				wp_enqueue_style( 'llms-assignments-submission-review' );

				wp_style_add_data( 'llms-assignments-submission-review', 'rtl', 'replace' );

				wp_enqueue_style( 'llms-assignments' );

			}
		}

	}

	/**
	 * Register and enqueue block editor assets.
	 *
	 * @since 1.1.8
	 * @since 2.0.0 Drop `LLMS_ASSETS_SUFFIX` from file names.
	 *
	 * @return void
	 */
	public function enqueue_block_editor() {

		global $post;
		if ( 'lesson' === get_post_type( $post ) ) {
			wp_register_script(
				'llms-assignments-blocks',
				plugins_url( 'assets/js/llms-assignments-blocks.js', LLMS_ASSIGNMENTS_PLUGIN_FILE ),
				array( 'llms-blocks-editor', 'react', 'react-dom', 'wp-components', 'wp-data', 'wp-element', 'wp-hooks', 'wp-i18n' ),
				LLMS_ASSIGNMENTS_VERSION,
				true
			);
			wp_set_script_translations( 'llms-assignments-blocks', 'lifterlms-assignments', LLMS_ASSIGNMENTS_PLUGIN_DIR . 'i18n' );
			wp_enqueue_script( 'llms-assignments-blocks' );

		}

	}

	/**
	 * Get started
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function init() {

		if ( is_admin() ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );

	}

	/**
	 * Register scripts & styles used on front and backend
	 *
	 * @since 1.0.0-beta.2
	 * @since 1.0.0-beta.6 Unknown.
	 * @since 2.0.0 Drop `LLMS_ASSETS_SUFFIX` from file names.
	 *
	 * @return void
	 */
	public function register() {

		wp_register_style( 'llms-assignments', plugins_url( 'assets/css/llms-assignments.css', LLMS_ASSIGNMENTS_PLUGIN_FILE ), array(), LLMS_ASSIGNMENTS_VERSION );
		wp_style_add_data( 'llms-assignments', 'rtl', 'replace' );

	}

}
return new LLMS_Assignments_Assets();

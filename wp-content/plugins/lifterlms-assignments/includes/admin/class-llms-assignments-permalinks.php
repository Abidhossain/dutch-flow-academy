<?php
/**
 * LLMS_Admin_Header class file
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 2.2.0
 * @version 2.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Permalink settings class
 */
class LLMS_Assignments_Permalinks {

	/**
	 * Permalink settings.
	 *
	 * @var array
	 */
	private $permalinks = array();

	/**
	 * Constructor.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'current_screen', array( $this, 'load_on_permalinks_screen' ) );

		add_action( 'llms_permalink_setting_fields', array( $this, 'output_permalink_fields' ) );
	}

	/**
	 * Initialize the permalink settings on the permalinks screen.
	 *
	 * @return void
	 */
	public function load_on_permalinks_screen() {
		$screen = get_current_screen();

		if ( 'options-permalink' === $screen->id ) {
			$this->settings_init();
			$this->settings_save();
		}
	}

	/**
	 * Initialize the permalinks
	 *
	 * @since 2.2.0
	 * @return void
	 */
	public function settings_init() {
		if ( function_exists( 'llms_get_assignments_permalink_structure' ) ) {
			$this->permalinks = llms_get_assignments_permalink_structure();
		}
	}

	/**
	 * Add the assignment base field to the LifterLMS permalink settings section
	 *
	 * @since 2.2.0
	 * @return void
	 */
	public function output_permalink_fields() {
		?>
		<tr>
			<th>
				<label for="assignment_base">
					<?php esc_html_e( 'Assignment Post Type', 'lifterlms-assignments' ); ?>
				</label>
			</th>
			<td>
				<input name="llms_assignment_base" id="assignment_base" type="text" value="<?php echo esc_attr( $this->permalinks['assignment_base'] ); ?>" class="regular-text code" required>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save the permalink settings
	 *
	 * @since 2.2.0
	 */
	public function settings_save() {
		if ( ! is_admin() ) {
			return;
		}

		if ( isset( $_POST['llms-permalinks-nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['llms-permalinks-nonce'] ), 'llms-permalinks' ) ) {
			if ( function_exists( 'llms_switch_to_site_locale' ) ) {
				llms_switch_to_site_locale( 'lifterlms-assignments', LLMS_ASSIGNMENTS_PLUGIN_DIR, 'i18n' );
			}

			$permalinks = llms_get_assignments_permalink_structure();

			$permalinks['assignment_base'] = isset( $_POST['llms_assignment_base'] ) ? sanitize_text_field( wp_unslash( $_POST['llms_assignment_base'] ) ) : $permalinks['assignment_base'];

			llms_set_assignments_permalink_structure( $permalinks );

			if ( function_exists( 'llms_restore_locale' ) ) {
				llms_restore_locale( 'lifterlms-assignments', LLMS_ASSIGNMENTS_PLUGIN_DIR, 'i18n' );
			}
		}
	}
}

return new LLMS_Assignments_Permalinks();

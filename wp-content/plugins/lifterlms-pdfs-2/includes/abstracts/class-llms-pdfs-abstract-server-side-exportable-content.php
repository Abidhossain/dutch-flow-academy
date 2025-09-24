<?php
/**
 * LLMS_PDFS_Abstract_Server_Side_Exportable_Content abstract class file.
 *
 * @package LifterLMS/Abstracts
 *
 * @since 2.0.0
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Base class for server-side exportable content.
 *
 * @since 2.0.0
 */
abstract class LLMS_PDFS_Abstract_Server_Side_Exportable_Content extends LLMS_PDFS_Abstract_Exportable_Content {

	/**
	 * List of hooks used to output the button.
	 *
	 * @var array[] {
	 *     An array of arrays describing the hook.
	 *
	 *     @type string   $0 The action hook name.
	 *     @type int      $1 Optional. The hook's callback priority. Defaults to `10`.
	 *     @type callable $2 Optional. The callback function. Defaults to {@see LLMS_PDFS_Abstract_Exportable_Content::output_button()}.
	 * }
	 */
	protected $button_hooks = array();

	/**
	 * Nonce field / key name.
	 *
	 * @var string
	 */
	protected $nonce_key = '';

	/**
	 * Retrieves the ID of the current object.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed ...$args Arguments from the hook.
	 * @return int
	 */
	abstract protected function get_current_object_id( ...$args );

	/**
	 * Retrieves the filename for the exported PDF.
	 *
	 * @since 2.0.0
	 *
	 * @param array $object_data Object data array from {@see LLMS_PDFS_Abstract_Server_Side_Exportable_Content::parse_object_data()}.
	 * @return string
	 */
	abstract protected function get_filename( $object_data );


	/**
	 * Retrieves the HTML to write to the PDF.
	 *
	 * @since 2.0.0
	 *
	 * @param array $object_data Object data array from {@see LLMS_PDFS_Abstract_Server_Side_Exportable_Content::parse_object_data()}.
	 * @return string
	 */
	abstract protected function get_html( $object_data );

	/**
	 * Constructor.
	 *
	 * Configures class variables, and adds an init action.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->nonce_key = "_llms_pdfs_dl_{$this->id}_nonce";
		parent::__construct();

	}

	/**
	 * Parses button hook data and adds the action.
	 *
	 * @since 2.0.0
	 *
	 * @param array $hook_data {
	 *     Array of hook data.
	 *
	 *     @type string        $0 The action hook name.
	 *     @type null|integer  $1 The action priority. If `null` defaults to `10`.
	 *     @type null|callable $2 The callback function. If `null` defaults to {@see LLMS_PDFS_Abstract_Server_Side_Exportable_Content::output_button}.
	 *     @type integer       $3 Number of arguments passed to the callback. Defaults to `1`.
	 * }
	 * @return void
	 */
	protected function add_button_hook( $hook_data ) {

		$hook     = $hook_data[0];
		$priority = $hook_data[1] ?? null;
		$callback = $hook_data[2] ?? null;
		$num_args = $hook_data[3] ?? 1;

		$priority = is_numeric( $priority ) ? $priority : 10;
		$callback = is_callable( $callback ) ? $callback : array( $this, 'output_button' );

		add_action( $hook, $callback, $priority, $num_args );

	}

	/**
	 * Formats the HTML before writing it to the PDF.
	 *
	 * By default, this method converts the default PDF stylesheet into inline
	 * styles via the CSS inliner utility.
	 *
	 * This method can be overridden to add additional styles or HTML formatting as needed.
	 *
	 * @since 2.0.0
	 *
	 * @param string $html        The HTML to be formatted.
	 * @param array  $object_data Object data array from {@see LLMS_PDFS_Abstract_Server_Side_Exportable_Content::parse_object_data()}.
	 * @return string
	 */
	protected function format_html( $html, $object_data = array() ) {

		$css = file_get_contents( LLMS_PDFS_PLUGIN_DIR . 'includes/views/styles.css' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- not a remote file.
		return Pelago\Emogrifier\CssInliner::fromHtml( $html )->inlineCss( $css )->render();

	}

	/**
	 * Retrieves the inner HTML used to generate the download button.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_button_inner_html() {
		return sprintf(
			// Translators: %s = Download icon HTML.
			__( 'Download %s', 'lifterlms' ),
			'<i class="fa fa-cloud-download" aria-hidden="true"></i>'
		);
	}

	/**
	 * Retrieves the settings used to output the download button.
	 *
	 * @since 2.0.0
	 *
	 * @return array Array of settings suitable to pass to {@see llms_form_field()}.
	 */
	protected function get_button_settings() {

		return array(
			'columns'     => 12,
			'classes'     => 'llms-button-secondary',
			'id'          => "llms-dl-{$this->id}",
			'name'        => "llms_dl_{$this->id}",
			'value'       => $this->get_button_inner_html(),
			'last_column' => true,
			'type'        => 'submit',
		);

	}

	/**
	 * Initialize the content type.
	 *
	 * If the content type should load, registers assets and add hooks.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean Returns `truen` if the export type is enabled and should load and `false` otherwise.
	 */
	public function init() {

		$should_load = parent::init();

		if ( $should_load ) {

			add_action( 'init', array( $this, 'handle_form_submit' ), 25 );
			foreach ( $this->button_hooks as $hook_data ) {
				$this->add_button_hook( $hook_data );
			}
		}

		return $should_load;

	}

	/**
	 * Generates and outputs the PDF.
	 *
	 * @since 2.0.0
	 *
	 * @param array $object_data Object data array from {@see LLMS_PDFS_Abstract_Server_Side_Exportable_Content::parse_object_data()}.
	 * @return string
	 */
	protected function generate_pdf( $object_data ) {

		$pdf = LLMS_PDFS_Generator_Server_Side::instance()->get(
			$this->get_pdf_args( $object_data ),
			$this->id
		);

		$pdf = $this->write_pdf( $pdf, $object_data );

		/**
		 * Filters the PDF output destination.
		 *
		 * By default, the PDF is output to the browser and the user is prompted to save / download the result.
		 *
		 * @since 2.0.0
		 *
		 * @param string $dest Output destination, {@see TCPDF::output()}.
		 */
		$dest = apply_filters( 'llms_pdfs_server_side_pdf_output_destination', 'D' );
		return $pdf->Output( $this->get_filename( $object_data ) . '.pdf', $dest );

	}

	/**
	 * Retrieves the HTML for the export form & button.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed ...$args Arguments from the hook.
	 * @return string
	 */
	protected function get_button_html( ...$args ) {

		$id = "llms-pdfs-dl-{$this->id}-form";

		/**
		 * FIlters the nonce field used in the submission form.
		 *
		 * @since 2.0.0
		 *
		 * @param string $nonce_field The nonce field HTML from `wp_nonce_field()`.
		 * @param string $id          The content type ID.
		 * @param string $nonce_key   The content type's unique nonce key.
		 */
		$nonce_field = apply_filters(
			'llms_pdfs_server_side_nonce_field',
			wp_nonce_field( $this->id, $this->nonce_key, false, false ),
			$this->id,
			$this->nonce_key
		);

		ob_start();
		?>
		<form class="llms-pdfs-dl-form" id="<?php echo esc_attr( $id ); ?>" method="POST" action="">
			<?php llms_form_field( $this->get_button_settings() ); ?>
			<input type="hidden" name="object_id" value="<?php echo $this->get_current_object_id( ...$args ); ?>">
			<?php echo $nonce_field; ?>
		</form>
		<?php
		return ob_get_clean();

	}

	/**
	 * Retrieves PDF initialization arguments passed to {@see LLMS_PDFS_Generator_Server_Side::get()}.
	 *
	 * @since 2.0.0
	 *
	 * @param array $object_data Object data array from {@see LLMS_PDFS_Abstract_Server_Side_Exportable_Content::parse_object_data()}.
	 * @return array Array of arguments passed to {@see LLMS_PDFS_Generator_Server_Side::get()}.
	 */
	protected function get_pdf_args( $object_data ) {
		return array(
			'title'       => '',
			'subject'     => '',
			'orientation' => $this->orientation,
		);
	}

	/**
	 * Handles form submission to parse object data and generate the desired PDF.
	 *
	 * @since 2.0.0
	 *
	 * @return void|integer Returns `-1` when the form isn't submitted or there was a nonce verification error,
	 *                      returns `0` when the posted object data cannot be parsed and exits with status code `0`
	 *                      on success.
	 */
	public function handle_form_submit() {

		if ( ! llms_verify_nonce( $this->nonce_key, $this->id ) ) {
			return -1;
		}

		$object_data = $this->parse_object_data();
		if ( ! $object_data ) {
			return 0;
		}

		$this->generate_pdf( $object_data );
		llms_exit( 0 );

	}

	/**
	 * Retrieves object data from form submission.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function parse_object_data() {

		$data = array();

		$id = llms_filter_input( INPUT_POST, 'object_id' );
		if ( $id && is_numeric( $id ) ) {
			$data['id'] = absint( $id );
		} elseif ( ! empty( $id ) && is_string( $id ) && false !== strpos( $id, '|' ) ) {
			$data = array_map( 'absint', explode( '|', $id ) );
		}

		return $data;

	}

	/**
	 * Outputs and returns the HTML of download button.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed ...$args Arguments from the hook.
	 * @return void
	 */
	public function output_button( ...$args ) {
		echo $this->get_button_html( ...$args );
	}

	/**
	 * Writes the export HTML to the PDF.
	 *
	 * @since 2.0.0
	 *
	 * @param TCPDF $pdf         PDF object instance.
	 * @param array $object_data Object data array from {@see LLMS_PDFS_Abstract_Server_Side_Exportable_Content::parse_object_data()}.
	 * @return TCPDF
	 */
	protected function write_pdf( $pdf, $object_data ) {

		$html = $this->format_html(
			$this->get_html( $object_data ),
			$object_data
		);

		$pdf->writeHTML( trim( $html ) );

		return $pdf;

	}

}

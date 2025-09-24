<?php
/**
 * LLMS_PDFS_Generator_Server_Side class file.
 *
 * @package LifterLMS_PDFs/Classes
 *
 * @since 2.0.0
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main class for the PHP server-side generator.
 *
 * @since 2.0.0
 */
class LLMS_PDFS_Generator_Server_Side {

	use LLMS_Trait_Singleton;

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	private function __construct() {

		if ( ! class_exists( 'TCPDF' ) ) {
			require_once LLMS_PDFS_PLUGIN_DIR . 'vendor/tecnickcom/tcpdf/tcpdf.php';
		}

	}

	/**
	 * Retrieves a configured TCPDF instance.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $args {
	 *     Configuration arguments.
	 *
	 *     @type string  $title       The PDF's metadata title.
	 *     @type string  $subject     The PDF's metadata subject/description.
	 *     @type boolean $header      Whether or not to include a preformatted header on the PDF. The header uses integration
	 *                                title and description settings on the first page of export files.
	 *     @type boolean $footer      Whether or not to include a preformatted footer on the PDF. The footer includes
	 *                                pagination information on the bottom of each page of export files.
	 *     @type string  $orientation The PDF's orientation. Either "portrait" or "landscape".
	 *     @type string  $unit        The PDF's unit. Accepts: "mm" or "in". This is used primarily when setting internal margins.
	 *     @type string  $format      The PDF's paper size.
	 * }
	 * @param string $context Context string, passed to filters to enable customization depending on the context.
	 * @return TCPDF
	 */
	public function get( $args = array(), $context = 'default' ) {

		$args = wp_parse_args(
			$args,
			array(
				'title'       => '',
				'subject'     => '',
				'header'      => true,
				'footer'      => true,
				'orientation' => 'portrait',
				'unit'        => 'mm',
				'format'      => llms_pdfs()->get_integration()->get_option( 'paper_size', 'letter' ),
			)
		);

		/**
		 * Filters the pdf configuration arguments.
		 *
		 * @since 2.0.0
		 *
		 * @param array  $arg     Configuration arguments. See {@see LLMS_PDFS_Generator_Server_Side::get()} $args parameter.
		 * @param string $context The PDF's Context/ID.
		 */
		$args = apply_filters( 'llms_pdfs_server_side_pdf_args', $args, $context );

		$pdf = new TCPDF( $args['orientation'], $args['unit'], strtoupper( $args['format'] ) );
		return $this->configure( $pdf, $args );

	}

	/**
	 * Configures a PDF instance with the given arguments.
	 *
	 * @since 2.0.0
	 *
	 * @param TCPDF $pdf  The TCPDF instance.
	 * @param array $args Configuration arguments. See {@see LLMS_PDFS_Generator_Server_Side::get()} $args parameter.
	 * @return TCPDF
	 */
	private function configure( $pdf, $args ) {

		$integration = llms_pdfs()->get_integration();
		$h_title     = $integration->get_option( 'header_title' );
		$h_desc      = $integration->get_option( 'header_description' );

		// Set document / file metadata.
		$pdf->SetAuthor( $h_title );
		$pdf->SetCreator( sprintf( 'LifterLMS PDFs/%s (https://lifterlms.com/product/lifterlms-pdfs)', LLMS_PDFS_VERSION ) );
		if ( $args['title'] ) {
			$pdf->SetTitle( $args['title'] );
		}
		if ( $args['subject'] ) {
			$pdf->SetSubject( $args['subject'] );
		}

		$top_margin   = 5;
		$print_header = false;
		if ( $args['header'] && ( $h_title || $h_desc ) ) {
			$top_margin   = $h_title && $h_desc ? 20 : 12;
			$print_header = true;
			$pdf->SetHeaderData( '', 0, $h_title, $h_desc );
			$pdf->SetHeaderMargin( 5 );
		}

		$pdf->setPrintHeader( $print_header );
		$pdf->setPrintFooter( $args['footer'] );

		$pdf->SetMargins( 5, $top_margin, 5 );
		$pdf->SetFooterMargin( 10 );
		$pdf->setAutoPageBreak( true, 15 );

		$pdf->AddPage();

		// There's a gap coming from a source I can't determine. This removes it.
		$pdf->setY( $pdf->getY() - 5 );

		return $pdf;

	}

}

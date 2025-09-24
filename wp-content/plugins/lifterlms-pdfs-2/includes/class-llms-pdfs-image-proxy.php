<?php
/**
 * LLMS_PDFS_Generator_Server_Side class file
 *
 * @package LifterLMS_PDFs/Classes
 *
 * @since 2.1.0
 * @version 2.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adds an image proxy "endpoint" for use by client-side PDF generators to use
 * images served from a foreign origin (for example from a CDN).
 *
 * @since 2.1.0
 */
class LLMS_PDFS_Image_Proxy {

	/**
	 * Nonce action.
	 *
	 * @var string
	 */
	const NONCE = 'llms-pdfs-img-proxy';

	/**
	 * Proxy success exit code.
	 *
	 * @var int
	 */
	const PROXY_SUCCESS = 0;

	/**
	 * Proxy error exit code when request is missing a URL.
	 *
	 * Note: 1 is skipped to be reserved as a general exit code for unknown errors
	 * should we require that in the future.
	 *
	 * @var int
	 */
	const PROXY_ERR_MISSING_URL = 2;

	/**
	 * Proxy error exit code when the requested URL is disallowed.
	 *
	 * @var int
	 */
	const PROXY_ERR_DISALLOWED_URL = 3;

	/**
	 * Proxy error exit code when an error is encountered requesting the external image.
	 *
	 * @var int
	 */
	const PROXY_ERR_REMOTE_GET = 4;

	/**
	 * Query string variable key.
	 *
	 * @var string
	 */
	const QUERY_STRING_VAR = 'img_proxy';



	/**
	 * List of allowed origins.
	 *
	 * @var string[]
	 */
	private $allowed_origins = array();

	/**
	 * Constructor.
	 *
	 * @since 2.1.0
	 *
	 * @return void
	 */
	public function __construct() {

		if ( self::is_enabled() ) {
			add_action( 'init', array( $this, 'process_request' ) );
		}

	}

	/**
	 * Retrieves the allowed origins option, normalizes and trims results, and removes empty elements.
	 *
	 * @since 2.1.0
	 *
	 * @return string[]
	 */
	private function get_allowed_origins() {
		if ( empty( $this->allowed_origins ) ) {
			/**
			 * Don't rely on PHP_EOL as stored in DB in case of a migration between different OSs.
			 *
			 * @link https://github.com/gocodebox/lifterlms-pdfs/pull/23/files#r830266007
			 */
			$origins = str_replace( "\r\n", "\n", llms_pdfs()->get_integration()->get_option( 'proxy_allowed_origins' ) );
			$this->allowed_origins = array_values(
				array_filter(
					array_map(
						'trim',
						explode( "\n", $origins )
					)
				)
			);
		}
		return $this->allowed_origins;
	}

	/**
	 * Retrieves the proxy URL.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public static function get_url() {
		return wp_nonce_url( get_site_url(), self::NONCE, self::QUERY_STRING_VAR );
	}

	/**
	 * Determines if the image proxy is enabled.
	 *
	 * @since 2.1.0
	 *
	 * @return boolean
	 */
	public static function is_enabled() {
		return llms_parse_bool( llms_pdfs()->get_integration()->get_option( 'proxy_enabled' ) );
	}

	/**
	 * Determines if the specified URL is allowed based on the proxy_allowed_origins setting.
	 *
	 * @since 2.1.0
	 *
	 * @param string $url The requested image URL.
	 * @return boolean
	 */
	private function is_url_allowed( $url ) {

		$origins = $this->get_allowed_origins();

		// If a wildcard is found in the array, then allow any URL.
		if ( in_array( '*', $origins, true ) ) {
			return true;
		}

		foreach ( $origins as $origin ) {
			if ( false !== stripos( $url, $origin ) ) {
				return true;
			}
		}

		return false;

	}


	/**
	 * Processes proxy requests.
	 *
	 * @since 2.1.0
	 *
	 * @return null|void Returns `null` when the nonce can't be verified or the URL isn't requested.
	 *                   Exits with a `PROXY_ERR_*` code on error.
	 *                   On success, outputs the image according to the requested response type
	 *                   and exits with the `PROXY_SUCCESS` code.
	 */
	public function process_request() {

		if ( ! llms_verify_nonce( self::QUERY_STRING_VAR, self::NONCE, 'GET' ) ) {
			return null;
		}

		// URL not supplied, can't proceed.
		$img_url = llms_filter_input( INPUT_GET, 'url', FILTER_SANITIZE_URL );
		if ( empty( $img_url ) ) {
			llms_exit( self::PROXY_ERR_MISSING_URL );
		}

		$res_type        = llms_filter_input( INPUT_GET, 'responseType', FILTER_SANITIZE_URL ) ?? 'text';
		$allowed_origins = $this->get_allowed_origins();

		/**
		 * Filters whether or not the proxy can be used for the specified image url.
		 *
		 * @since 2.1.0
		 *
		 * @param boolean  $is_allowed      Whether or not the image is allowed.
		 * @param string   $img_url         The image URL.
		 * @param string[] $allowed_origins Array of allowed origin URLs.
		 */
		$is_url_allowed = apply_filters(
			'llms_pdfs_image_proxy_is_url_allowed',
			$this->is_url_allowed( $img_url ),
			$img_url,
			$allowed_origins
		);

		// URL is disallowed.
		if ( ! $is_url_allowed ) {
			$log = sprintf( 'Disallowed: %s', $img_url );
			llms_log( $log, 'pdfs-img-proxy' );
			llms_exit( self::PROXY_ERR_DISALLOWED_URL );
		}

		$req      = wp_remote_get( $img_url );
		$res_code = wp_remote_retrieve_response_code( $req );
		if ( absint( $res_code ) >= 400 ) {
			$log = sprintf( 'Error [%1$d %2$s]: %3$s', $res_code, wp_remote_retrieve_response_message( $req ), $img_url );
			llms_log( $log, 'pdfs-img-proxy' );
			llms_exit( self::PROXY_ERR_REMOTE_GET );
		}

		echo $this->output_image(
			wp_remote_retrieve_body( $req ),
			wp_remote_retrieve_headers( $req ),
			$res_type
		);
		llms_exit( self::PROXY_SUCCESS );

	}

	/**
	 * Returns a proxied image based on the requested response type.
	 *
	 * Note: I've been unable to find any real explicit documentation on the response types but the example node.js proxy
	 * allows for 'blob' and 'text' (the default). I can't figure out exactly what circumstances text would be requested under
	 * as it appears the default is to request the blob. They use `typeof new XMLHttpRequest().responseType === string` to
	 * determine the response type so I think some browsers may not support blob based on this criteria. In any event, this
	 * function returns base64 data uri for the image as the node proxy example does. See link below.
	 *
	 * @since 2.1.0
	 *
	 * @link https://github.com/niklasvh/html2canvas-proxy-nodejs/blob/5d4cf02de2cb983747b5ea56a4e320ef21d329c5/server.js#L24-L34
	 *
	 * @param string   $body     Image request response body.
	 * @param string[] $headers  Image request response headers.
	 * @param string   $res_type Requested response type, either "blob" or "text".
	 * @return string
	 */
	private function output_image( $body, $headers, $res_type ) {

		if ( 'blob' === $res_type ) {
			return $body;
		}

		$content_type = $headers['content-type'];
		$encoded      = base64_encode( $body ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Not used for obfuscation purposes.

		return "data:{$content_type};base64,{$encoded}";

	}

}

return new LLMS_PDFS_Image_Proxy();

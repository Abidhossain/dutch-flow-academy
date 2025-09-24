<?php
/**
 * This class provide API interface to download fonts for MPDF
 *
 * @since      3.3.25
 * @package    Challan_Free
 * @subpackage Challan_Free/api
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists("Challan_Font_Downloader_API") ) {

    /**
     * REST API endpoints for Font downloader
     */
    class Challan_Font_Downloader_API extends WP_REST_Controller
    {

        /**
         * A Unique identifier
         *
         * @var string
         */
        protected $namespace;

        /**
         * REST API endpoint resource base url
         * @var string
         */
        protected $rest_base;

        /**
         * Class default constructor
         * @return self
         */
        public function __construct() {
            $this->namespace = 'challan/v1';
            $this->rest_base = '/font-downloader';
        }

        /**
         * Register API endpoint
         * @return void
         */
        public function register_routes() {

            // Register font downloader route
            register_rest_route(
                $this->namespace,
                $this->rest_base . '/download-font',
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'download_font' ],
                    'permission_callback' => [ $this, 'get_route_access' ],
                ]
            );

            register_rest_route(
                $this->namespace,
                $this->rest_base . '/maybe-download-font',
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'maybe_download_font' ],
                    'permission_callback' => [ $this, 'get_route_access' ],
                ]
            );
        }

        /**
         * Download font for MPDF
         *
         * @param object $request
         * @return  object  WP_REST_Response
         */
        public function background_download_font( $request ) {

//        $retrieved_nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
//        if ( ! wp_verify_nonce( $retrieved_nonce, 'wp_rest' ) ) {
//            die( 'Failed security check' );
//        }

            //check font directory is writeable or not
            if ( ( ! is_dir(CHALLAN_FREE_FONT_DIR) || ! file_exists(CHALLAN_FREE_FONT_DIR)) || ! is_writable(CHALLAN_FREE_FONT_DIR) ) {
                //font download impossible
                return false;
            }

            //load font downloader if already is not loaded
            if ( ! class_exists("Challan_DropBoxFontDownloader") ) {
                require_once CHALLAN_FREE_ROOT_FILE_PATH . "includes/classes/class-dropbox-font-downloader.php";
            }
            $fontConfig = Challan_DropBoxFontDownloader::prepareConfigForDownloadFont();
            $downloaded = $fontConfig["downloaded"];
            $download = $fontConfig["download"];


            if ( count($download) > 0 ) {
                $path = array_shift($download);
                $baseUrl = implode("", $fontConfig['base-url']);
                $source = "{$baseUrl}{$path}";
                $fileSep = explode('/', $source);
                $destination = CHALLAN_FREE_FONT_DIR . $fileSep[ count($fileSep) - 1 ];
                if ( ! file_exists(rtrim($destination, ".zip")) ) {
                    Challan_DropBoxFontDownloader::dispatchDownload("{$baseUrl}{$path}");
                }
                $downloaded[] = $path;

                $response = [];
                $response['status'] = true;
                $response["font_downloaded"] = count($downloaded);
                $response["font_remaining"] = count($download);
                if ( 0 === $response["font_remaining"] ) {
                    update_option('challan_mpdf_all_font_downloading_done', true, true);
                }
                $response["font_total"] = count($fontConfig["others"]);
                return $response;
            }else {
            }



        }

        /**
         * Download font for MPDF
         *
         * @param object $request
         * @return  object  WP_REST_Response
         */
        public function download_font( $request ) {

//        $retrieved_nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
//        if ( ! wp_verify_nonce( $retrieved_nonce, 'wp_rest' ) ) {
//            die( 'Failed security check' );
//        }

            //check font directory is writeable or not
            if ( ( ! is_dir(CHALLAN_FREE_FONT_DIR) || ! file_exists(CHALLAN_FREE_FONT_DIR)) || ! is_writable(CHALLAN_FREE_FONT_DIR) ) {
                //font download impossible
                $response = [];
                $response['status'] = true;
                $response["font_downloaded"] = 0;
                $response["font_remaining"] = 0;
                $response["font_remaining"] = 0;
                $response["font_total"] = 0;
                return new WP_REST_Response([
                    'status'        => 200,
                    'response'      => "Font download impossible because maybe font directory is not exist or maybe font directory is not writeable.",
                    'response_body' => $response,
                ]);
            }

            //load font downloader if already is not loaded
            if ( ! class_exists("Challan_DropBoxFontDownloader") ) {
                require_once CHALLAN_FREE_ROOT_FILE_PATH . "includes/classes/class-dropbox-font-downloader.php";
            }
            $fontConfig = Challan_DropBoxFontDownloader::prepareConfigForDownloadFont();
            $downloaded = $fontConfig["downloaded"];
            $download = $fontConfig["download"];


            if ( count($download) > 0 ) {
                $path = array_shift($download);
                $baseUrl = implode("", $fontConfig['base-url']);
                $source = "{$baseUrl}{$path}";
                $fileSep = explode('/', $source);
                $destination = CHALLAN_FREE_FONT_DIR . $fileSep[ count($fileSep) - 1 ];
                if ( ! file_exists(rtrim($destination, ".zip")) ) {
                    Challan_DropBoxFontDownloader::dispatchDownload("{$baseUrl}{$path}");
                }
                $downloaded[] = $path;
            }

            $response = [];
            $response['status'] = true;
            $response["font_downloaded"] = count($downloaded);
            $response["font_remaining"] = count($download);
			if ( 0 === $response["font_remaining"] ) {
				update_option('challan_mpdf_all_font_downloading_done', true, true);
			}
            $response["font_total"] = count($fontConfig["others"]);
            return new WP_REST_Response([
                'status'        => 200,
                'response'      => "response success",
                'response_body' => $response,
            ]);

        }

        /**
         * Check font should be downloaded or not
         *
         * @param   $request
         * @return  WP_REST_Response
         * @since   3.3.25
         */
        public function maybe_download_font( $request ) {
            //load font downloader if already is not loaded
            if ( ! class_exists("Challan_DropBoxFontDownloader") ) {
                require_once CHALLAN_FREE_ROOT_FILE_PATH . "includes/classes/class-dropbox-font-downloader.php";
            }
            $fontConfig = Challan_DropBoxFontDownloader::prepareConfigForDownloadFont();
            $downloaded = $fontConfig["downloaded"];
            $download = $fontConfig["download"];
            $response = [];
            $response['status'] = true;
            $response["font_downloaded"] = count($downloaded);
            $response["font_remaining"] = count($download);
            $response["font_total"] = count($fontConfig["others"]);
            return new WP_REST_Response([
                'status'        => 200,
                'response'      => "response success",
                'response_body' => $response,
            ]);

        }

        public function get_route_access() {
            return true;
//        if ( current_user_can('manage_options') ) {
//			return true;
//		}
        }

	    /**
	     * Handle ajax request for downloading font REST API.
	     *
	     * @since   3.3.27
	     * @return void
	     */
		public function ajax_download_font(){
			$response = $this->download_font(new WP_REST_Request(\WP_REST_Server::READABLE, $this->rest_base . '/download-font' ));
			wp_send_json($response->get_data(), 200);
			exit;
			wp_die();
		}
    } // end class definition

    add_action('rest_api_init', function () {
		if ( ! headers_sent() ) {
			header('Access-Control-Allow-Headers: Authorization, X-WP-Nonce,Content-Type, X-Requested-With');
			header('Access-Control-Allow-Origin: *');
			header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
			header('Access-Control-Allow-Credentials: true');
		}
        $apiFontDownloader = new Challan_Font_Downloader_API();
        $apiFontDownloader->register_routes();
    });


	add_action( 'wp_ajax_prepare_fonts', function () {
		if ( ! headers_sent() ) {
			header('Access-Control-Allow-Headers: Authorization, X-WP-Nonce,Content-Type, X-Requested-With');
			header('Access-Control-Allow-Origin: *');
			header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
			header('Access-Control-Allow-Credentials: true');
			header('Content-Type: application/json; charset=utf-8');
			http_response_code(200);
		}

		$apiFontDownloader = new Challan_Font_Downloader_API();
		$apiFontDownloader->ajax_download_font();
	} );
}

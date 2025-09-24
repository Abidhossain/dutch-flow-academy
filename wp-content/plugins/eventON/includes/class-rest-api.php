<?php
/**
 * REST API for event access -- in progress
 * @version 4.5.2
 */

class EVO_Rest_API{
	private $nonce = 'invalid';
	static $version = 'v1';

	public function __construct(){
		add_action('wp_loaded', array($this, 'nonce_gen'));
		add_action( 'rest_api_init', array($this, 'rest_routes'));
	}
	function nonce_gen(){
		$this->nonce = wp_create_nonce( 'rest_eventon' );
	}
	// get rest api url
	public static function get_rest_api( $request = ''){
		return esc_url_raw( add_query_arg('evo-ajax', $request, get_rest_url(null,'eventon/'. self::$version . '/data') ));
	}
	function rest_routes(){
		register_rest_route( 
			'eventon/'. self::$version ,'/data', 
			array(
				'methods' => 'POST',
				'callback' => array($this,'rest_returns'),					
				'permission_callback' => function (WP_REST_Request $request) {
                	return true;
            	}
			) 
		);

		// Main endpoint: /wp-json/eventon/v1/events?evo-ajax=eventon_get_events
        register_rest_route(
            'eventon/' . self::$version,
            '/events',
            array(
                'methods' => WP_REST_Server::READABLE, // GET requests
                'callback' => array($this, 'rest_returns'),
                'permission_callback' => function (WP_REST_Request $request) {
                	return true;
                    // Verify nonce for security
                    $nonce = $request->get_header('X-WP-Nonce') ?: $request->get_param('_wpnonce');
                    return wp_verify_nonce($nonce, 'rest_eventon');
                },
                'args' => array(
                    'evo-ajax' => array(
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            )
        );

		register_rest_route( 
			'evo-admin' ,
			'data', 
			array(
				'methods'   => WP_REST_Server::READABLE,
				'callback' => array($this,'rest_admin'),					
				'permission_callback' => function (WP_REST_Request $request) {
                	return true;
            	}
			) 
		);
	}

	function rest_admin(){
		return new WP_REST_Response('Howdy!!');
	}

	function rest_returns( WP_REST_Request $request){

		$params = $request->get_params();
        $action = isset($params['evo-ajax']) ? sanitize_text_field($params['evo-ajax']) : '';

        if( $action == 'eventon_get_events'){
        	return new WP_REST_Response(
                array(
                    'status' => 'success',
                ),
                200
            );
        }

		$params = $request->get_params();
		$data = array();
		if(isset($params['evo-ajax'])  ){			
			$nonce = wp_create_nonce( 'rest_'.EVO()->version );


			$action = $params['evo-ajax'];
			$action = sanitize_text_field( $action );

			

			return apply_filters('evo_ajax_rest_'. $action, array('html'=>'test'), $params);


		}

		return new WP_Error(
            'eventon_error',
            'Error processing events',
            array('status' => 500)
        );
	}
}
<?php
/**
 * Template Loader
 *
 * @class 		EVO_Template_Loader 
 * @version		4.9.10
 * @package		Eventon/Classes
 * @category	Class
 * @author 		AJDE
 */



class EVO_Template_Loader {

	public $template_directory,$theme_support, $template_blocks;

	const PLUGIN_SLUG = 'eventon';

	public function __construct() {

		$this->template_directory = EVO()->plugin_path() . '/templates';
		$this->theme_support = evo_current_theme_is_fse_theme();


		$this->template_blocks = new EVO_Temp_Blocks();

		// block
		//add_action('init', array($this, 'register_block_templates'));
		add_filter( 'get_block_templates', array( $this, 'add_evo_block_templates' ), 10, 3 );	
		add_filter( 'template_include', array( $this, 'template_loader' ) , 99);

		//add_filter('rest_pre_dispatch', array($this, 'fix_rest_template_request'), 10, 3);


		add_action('init', function () {
    $template = (new EVO_Temp_Blocks())->get_single_event_template('single-ajde_events');
    if ($template && function_exists('wp_register_block_template')) {
        wp_register_block_template('eventon//single-ajde_events', [
            'title' => $template->title,
            'content' => $template->content,
            'description' => $template->description,
            'source' => 'plugin',
            'type' => 'wp_template',
            'post_types' => ['ajde_events'],
        ]);
    }
});
		//add_filter( 'default_template_types', array( $this, 'block_template_types' ), 10, 1 );
		
	}

	// Fix REST API template request
    public function fix_rest_template_request($result, $server, $request) {
        $route = $request->get_route();
	    if ($route === '/wp/v2/templates/eventon//single-ajde_events' && $request->get_param('context') === 'edit') {
	        $template = $this->template_blocks->get_single_event_template('single-ajde_events');
	        if ($template) {
	            return new WP_REST_Response(array(
	                'id' => 'eventon//single-ajde_events',
	                'slug' => 'single-ajde_events',
	                'source' => 'plugin',
	                'content' => array('raw' => $template->content),
	                'title' => array('raw' => $template->title),
	                'type' => 'wp_template',
	                'status' => 'publish',
	                'theme' => 'EventON',
	            ), 200);
	        }
	    }
	    return $result;
    }

	public function register_block_templates() {
        if (function_exists('register_block_template')) {
            register_block_template('eventon//single-ajde_events', array(
                'title' => esc_html__('Event Page', 'eventon'),
                'description' => __('Template used to display event pages.', 'eventon'),
                'content' => file_get_contents(EVO()->plugin_path() . '/templates/blocks/single-ajde_events.html'),
                'source' => 'plugin',
                'type' => 'wp_template',
                'post_types' => array('ajde_events'),
            ));
        } else {
            // Fallback for older WP versions
            add_filter('get_block_templates', array($this, 'add_evo_block_templates'), 10, 3);
        }
    }


	// Load a template
	public function template_loader( $template ) {
		if ( is_embed() ) {	return $template;	}

		$default_file = $this->get_template_loader_default_file();
		//EVO_Debug($default_file);

		if ( $default_file ) {
			// Filter hook to choose which files to find before eventon does it's own logic.
			$search_files = $this->get_template_loader_files( $default_file );
			$template     = locate_template( $search_files );

			// if locate failed, check for existence of tiles
			if ( ! $template ) {
				foreach( $search_files as $file){
					if( file_exists( $file)){
						$template =  $file;
						break;
					}
				}
			}
			
			// If no theme template is found, load from the plugin
			if ( ! $template ) {

				$plugin_template = EVO()->plugin_path() . '/templates/' . $default_file;

				if ( file_exists( $plugin_template ) ) {
	                $template = $plugin_template;
	            }				
			}
		}

		// Fallback to PHP template in editor if block template fails
	    if (is_admin() && isset($_GET['post']) && get_post_type($_GET['post']) === 'ajde_events') {
	        $php_template = EVO()->plugin_path() . '/templates/single-ajde_events.php';
	        if (file_exists($php_template)) {
	            $template = $php_template;
	        }
	    }

	    //EVO_Debug($template);

		return apply_filters('evo_template_loader_overtake', $template , $default_file);
	}

	// get default filename for template except block template with same name
	// @since 4.1.2
	private function get_template_loader_default_file(){

		$default_file = '';
		$evo_template = false;

		$proceed = apply_filters('evo_default_temp_file_before', true);
		if( $proceed === false ) return '';
		if( $proceed !== true ) return $proceed;
		
		if (	is_singular( 'ajde_events' ) ) 	{

			wp_enqueue_style( 'evo_single_event');	
			$default_file = ( $this->has_block_template( 'single-ajde_events' ) && $this->theme_support ) ?
				'':'single-ajde_events.php';

			// if single event page is only for loggedin users
				if( EVO()->cal->check_yn('evosm_loggedin','evcal_1') && !is_user_logged_in()){
					wp_redirect( evo_login_url() );
				}

			// if single event template is disabled
				if( EVO()->cal->check_yn('evo_ditch_sin_template','evcal_1')) $default_file = '';
				$evo_template = true;

		
		} elseif (is_post_type_archive( 'ajde_events' ) ){
			$default_file = 'archive-ajde_events.php';
			$evo_template = true;

		} elseif (  is_tax(array('event_location')) ){
			$default_file 	= 'taxonomy-event_location.php';
			$evo_template = true;
		} elseif (  is_tax(array('event_organizer')) ){

			$default_file 	= 'taxonomy-event_organizer.php';
			$evo_template = true;

		} elseif( is_tax(apply_filters('evo_tempload_et_types', array('event_type', 'event_type_2', 'event_type_3', 'event_type_4','event_type_5','event_type_6',
			'event_type_7','event_type_8','event_type_9','event_type_10'))) ){

			$default_file 	= 'taxonomy-event_type.php'; 
			$evo_template = true;
			
		} elseif ( is_event_taxonomy() ) {
			$object = get_queried_object();
			$evo_template = true;

			if ( $this->has_block_template( 'taxonomy-' . $object->taxonomy ) ) {
				$default_file = '';
			} else {
				if ( is_tax( 'event_type' ) || is_tax( 'post_tag' ) ) {
					$default_file = 'taxonomy-' . $object->taxonomy . '.php';
				} elseif ( ! $this->has_block_template( 'archive-ajde_events' ) ) {
					$default_file = 'archive-ajde_events.php';
				} else {
					$default_file = '';
				}
			}
		} elseif ( is_tag() ) {
			

		} elseif (
			( is_post_type_archive( 'ajde_events' )  ) &&
			! $this->has_block_template( 'archive-ajde_events' )
		) {
			$default_file = $this->$theme_support ? 'archive-ajde_events.php' : '';
		} else {
			$default_file = '';
		}

		$default_file = apply_filters('evo_template_loadr_default_file', $default_file, $this);


		// General Block check
		if( !empty($default_file) && $evo_template ){	

			$block_name = str_replace('.php', '', $default_file);
			if( $this->has_block_template( $block_name ) && $this->theme_support ) return '';
		}


		return $default_file;
	}

	// Get an array of filenames to search for a given template
	// @since 4.1.2
	public function get_template_loader_files( $default_file  ) {
		
		$templates = apply_filters( 'evo_template_loader_files', array(), $default_file );
		//print_r($templates);

		if ( is_page_template() ) {
			$page_template = get_page_template_slug();

			if ( $page_template ) {
				$validated_file = validate_file( $page_template );
				if ( 0 === $validated_file ) {
					$templates[] = $page_template;
				} else {
					error_log( "EventON: Unable to validate template path: \"$page_template\". Error Code: $validated_file." ); 
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				}
			}
		}

		// single event page
		if( is_singular('ajde_events')){
			$templates[] = 'single-ajde_events.php';
		}

		if( is_event_taxonomy()){
			$object = get_queried_object();

			if( false !== strpos( $object->taxonomy ,'event_type')){
				$templates[] = 'taxonomy-event_type.php';
			}else{
				$templates[] = 'taxonomy-' . $object->taxonomy .'.php';
			}

		}

		$templates[] = EVO()->template_path() .'/' . $default_file;
		$templates[] = EVO()->template_path() .'/templates/' . $default_file;

		return array_unique( $templates );
	}

	// language value for the archive pages
	function pass_lang(){
		if( isset($_GET['l'])) EVO()->lang = sanitize_text_field( $_GET['l'] );
	}

	
	
	

// Render Block Templates
	function add_evo_block_templates($query_result, $query, $template_type){
		$slugs = isset($query['slug__in']) ? $query['slug__in'] : array();
		//EVO_Debug($slugs);

	    foreach ($slugs as $slug) {
	        if (!in_array($slug, apply_filters('evo_block_templates', array(
	            'single-ajde_events',
	            'archive-ajde_events',
	            'taxonomy-event_type',
	            'taxonomy-event_organizer',
	            'taxonomy-event_location'
	        ), $slug))) {
	            continue;
	        }

	        $template = $this->template_blocks->get_single_event_template($slug);
	        $template->id = 'eventon//' . $slug; // Ensure consistent ID format
	        $query_result[] = $template;
	    }

	    $query_result = $this->template_blocks->remove_theme_templates_with_custom_alternative($query_result);
	    return $query_result;		
	}



	// Checks whether a block template with that name exists.
	public function has_block_template( $template_name ) {
		if ( ! $template_name ) {	return false;	}

		$proceed = apply_filters('evo_has_block_temp_before', true, $template_name);
		if( !$proceed) return false;

		$has_template            = false;
		$template_filename       = $template_name . '.html';
		$template_filename_2       = $template_name . '.php';

		// Since Gutenberg 12.1.0, the conventions for block templates directories have changed,
		// we should check both these possible directories for backwards-compatibility.
		$possible_templates_dirs = array( 'templates', 'block-templates' );

		// Combine the possible root directory names with either the template directory
		// or the stylesheet directory for child themes, getting all possible block templates
		// locations combinations.
		$filepath        = DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $template_filename;
		$legacy_filepath = DIRECTORY_SEPARATOR . 'block-templates' . DIRECTORY_SEPARATOR . $template_filename;
		$possible_paths  = apply_filters('evo_block_templates_dir', array(
			get_stylesheet_directory() . $filepath,
			get_stylesheet_directory() . $legacy_filepath,
			get_template_directory() . $filepath,
			get_template_directory() . $legacy_filepath,
			EVO()->plugin_path() .'/templates/blocks/'  . $template_filename,
			EVO()->plugin_path() .'/templates/blocks/'  . $template_filename_2,
		));


		// Check the first matching one.
		foreach ( $possible_paths as $path ) {
			if ( is_readable( $path ) ) {
				$has_template = true;	break;
			}
		}

		// Filters the value of the result of the block template check
		return (bool) apply_filters( 'evo_has_block_template', $has_template, $template_name );
	}

	function get_plugin_block_template_types($template_slug, $type){
		$all_data = array(
			'single-ajde_events' => array(
				'title'=> _X('Single Event', 'Template name', 'eventon'),
				'description'=> __('Template used to display single event.', 'eventon')
			)
		);
		return $all_data[ $template_slug][ $type];
	}

	function block_template_types($template_types){
		//print_r($template_types);
		$template_types['single-ajde_events']=  array(
				'title'=> _X('Single Event', 'Template name', 'eventon'),
				'description'=> __('Template used to display single event.', 'eventon')
			);
		return $template_types;
	}
// SUPPORTIVE
	// get the first matching template part within theme directories
	public static function get_theme_template_path( $template_slug, $template_type = 'wp_template' ) {
		$template_filename      = $template_slug . '.php';
		$possible_templates_dir = 'wp_template' === $template_type ? 
			array('templates') : array( 'parts');

		// Combine the possible root directory names with either the template directory
		// or the stylesheet directory for child themes.
		$possible_paths = array_reduce(
			$possible_templates_dir,
			function( $carry, $item ) use ( $template_filename ) {
				$filepath = DIRECTORY_SEPARATOR . $item . DIRECTORY_SEPARATOR . $template_filename;

				$carry[] = get_stylesheet_directory() . $filepath;
				$carry[] = get_template_directory() . $filepath;
				$carry[] = EVO()->plugin_path() . '/templates/blocks/'. $template_filename;

				return $carry;
			},
			array()
		);

		// Return the first matching.
		foreach ( $possible_paths as $path ) {
			if ( is_readable( $path ) ) {
				return $path;
			}
		}

		return null;
	}
	
}
new EVO_Template_Loader();
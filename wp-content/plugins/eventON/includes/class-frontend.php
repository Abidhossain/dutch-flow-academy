<?php
/**
 * evo_frontend class for front and backend.
 *
 * @class 		evo_frontend
 * @version		4.9
 * @package		EventON/Classes
 * @category	Class
 * @author 		AJDE
 */

class evo_frontend {

	private $content;
	public $evo_options, $evo_lang_opt, $evopt1;

	public $load_only_on_evo_pages = false;
	public $evo_on_page = false;

	public function __construct(){


		//$DD = new DateTime();
		//$DDtz = new DateTimeZone('Argentina/Buenos_Aires');
		//echo $DD->format('Y-m-d H:i');
		
		// eventon related wp options access on frontend
		$this->evo_options = $this->evopt1 = EVO()->evo_generator->evopt1;
		$this->evo_lang_opt = EVO()->evo_generator->evopt2;
	
		// register styles and scripts
			add_action( 'init', array( $this, 'debug_init' ), 10 );
			add_action( 'init', array( $this, 'register_scripts' ), 10 );

			$this->load_only_on_evo_pages = apply_filters('evo_load_scripts_topage', EVO()->cal->check_yn('evo_load_scripts_only_onevo','evcal_1') );

			// load eventon scripts/styles on all pages
			if(!$this->load_only_on_evo_pages){
				add_action( 'wp_enqueue_scripts', array( $this, 'load_evo_scripts_styles' ), 10 );
				add_action( 'wp_enqueue_scripts', array( $this, 'inline_styles_scripts' ), 10 );
				add_action( 'wp_enqueue_scripts', array( $this, 'load_dynamic_evo_styles' ), 10 );
			}else{
				// if load eventON scripts/styles to only eventon pages
				//add_action( 'wp_enqueue_scripts', array( $this, 'denqueue_scripts' ), 90 );
			}


			// load eventon scripts/styles only on event pages and load to page header
			if($this->load_only_on_evo_pages && EVO()->cal->check_yn('evo_load_all_styles_onpages','evcal_1') ){
				add_action( 'wp_enqueue_scripts', array( $this, 'load_default_evo_styles' ), 10 );
				add_action( 'wp_enqueue_scripts', array( $this, 'load_dynamic_evo_styles' ), 10 );
			}

		
		// Other page meta
			add_filter('body_class', array($this, 'body_classes'), 10,1);

			if( EVO()->cal->check_yn('evo_header_meta_data')){
				add_action( 'wp_head', array( $this, 'generator' ) );
			}

		// SINGLE Events related
			add_action( 'wp_head', array( $this, 'event_headers' ) );	
			$this->register_se_sidebar();

		// schedule deleting past events
			add_action('evo_cron_daily_actions', array($this, 'evo_cron_daily_actions'));	
	
		// Rewrite URL
			add_filter('query_vars',array($this, 'query_vars'));
			add_action('init', array($this, 'add_endpoints'));
			add_action('template_redirect', array($this,'handle_requests'));
				
		// heartbeat
			add_filter(	'heartbeat_received', array($this, 'heartbeat'),10,2);
			add_filter(	'heartbeat_nopriv_received', array($this, 'heartbeat_nopriv'),10,2);
			add_filter( 'heartbeat_settings', array($this,'wp_heartbeat_settings') );

		EVO()->cal->set_cur('evcal_1');

	}

	// debug on init
		public function debug_init(){}

	// heartbeat
		public function heartbeat_nopriv($response, $data){
			return apply_filters('evo_heartbeat_received_nopriv',$response, $data);
		}
		public function heartbeat($response, $data){
			return apply_filters('evo_heartbeat_received',$response, $data);
		}
		public function wp_heartbeat_settings($settings) {
	        if (EVO()->cal->check_yn('evo_realtime_vir_update', 'evcal_1')) {
	            $settings['interval'] = EVO()->cal->get_prop('_vir_hrrate', 'evcal_1') ?: 15;
	        }
	        return apply_filters('evo_heartbeat_settings', $settings);
	    }

	// end points
		function add_endpoints(){
			add_rewrite_endpoint('var', EP_PERMALINK);

			$url_slug = EVO()->cal->get_ics_url_slug();
			$events_base = 'events';

			// Rule for exporting all events
		    add_rewrite_rule('^'. $url_slug .'/all/?$', 'index.php?export_events=all', 'top');

		    // Rule for exporting a specific event and repeat index
		    add_rewrite_rule('^'. $url_slug .'/([^/]+)_([^/]+)/?$', 'index.php?export_events=single&event_id=$matches[1]&repeat_interval=$matches[2]', 'top');

		    // New endpoint: /events/one-event/{event_id}/{repeat_interval}
	        add_rewrite_rule(
	            "^$events_base/one-event/([^/]+)/([^/]+)/?$",
	            'index.php?post_type=ajde_events&one_event=$matches[1]_$matches[2]',
	            'top'
	        );
		}
	// Pass custom end points to single event URL
		function query_vars($vars){

			return array_merge($vars, [
	            'var',
	            'export_events',
	            'event_id',
	            'repeat_interval',
	            'one_event'
	        ]);
		}

	// handle template redirect requested
		public function handle_requests() {
		    // download ICS logic
		    if (get_query_var('export_events')) {

		    	$export_type = get_query_var('export_events');
		    	$ics_content = '';

		        // Verify the nonce
		        $nonce = $_GET['nonce'] ?? $_GET['key'] ?? '';

		        // Verify the nonce
		        if( $export_type === 'all'){
					if (!$nonce || !wp_verify_nonce($nonce, 'export_event_nonce')) {
					    wp_die('Invalid nonce.');
					}
				}

		        if ($export_type === 'all') {
		            // Export all events		            
		            $filename = "all_events.ics";

		            $events = EVO()->calendar->get_all_event_data(array(
						'hide_past'=>'yes'
					));

					// EACH EVENT
					foreach($events as $event_id=>$event){

						$EVENT = new EVO_Event( $event_id, ( isset( $event['pmv']) ? $event['pmv']: ''), 0, true, false);
						$ics_content .= $EVENT->get_ics_content();

					}

					if(empty($events)) wp_die('No Events.');


		        } else if ($export_type === 'single') {
		            // Get event_id and repeat_interval
		            $event_id = get_query_var('event_id');
		            $repeat_interval = get_query_var('repeat_interval');

		            // Fetch the specific event data
		            $EVENT = new EVO_Event($event_id,'',$repeat_interval);
					$EVENT->get_event_post();
		            	
		            // validations
						// check post type
						if( 'ajde_events' !== $EVENT->post_type ) wp_die('Not a valid Event!');

						// check event exists
						if( $EVENT->post_status != 'publish' && !is_user_logged_in() ) wp_die('Not a valid Event!');

						// check password protected event
						if( $EVENT->is_password_required() ) wp_die('Password Protected Event!');	

		          
		            // Generate the ICS content for the specific event
		            $ics_content = $EVENT->get_ics_content();
		            $filename = $EVENT->post_name .".ics";

		        } else {
		            wp_die('Invalid export type.');
		        }

		         // Output the ICS file for download
		        header('Content-Type: text/calendar; charset=utf-8');
		        header('Content-Disposition: attachment; filename="' . $filename . '"');

		        echo "BEGIN:VCALENDAR\n";
				echo "VERSION:2.0\n";
				echo "PRODID:-//eventon.com NONSGML v1.0//EN\n";
				echo "CALSCALE:GREGORIAN\n";
				echo "METHOD:PUBLISH\n";

		        echo $ics_content;

		        echo "END:VCALENDAR";

		        exit;
		    }

		    // one event direct opening
			if ($one_event = get_query_var('one_event')) {
				list($event_id, $repeat_interval) = explode('_', $one_event);
	            $event_id = (int) $event_id;
	            $repeat_interval = (int) $repeat_interval;
            	
            	add_filter('body_class', fn($classes) => array_merge($classes, ["one_event_{$event_id}_{$repeat_interval}"]));
			}


		}

	// Body class
		function body_classes($classes){
			if(!empty($this->evo_options['evo_rtl']) && $this->evo_options['evo_rtl']=='yes')
				$classes[] = 'evortl';

			
			return apply_filters('evo_rtl_body_class', $classes); // @~ 2.6.12
		}

	// styles and scripts
		public function register_scripts() {

			$evo_opt= $this->evo_options;	
						
			wp_register_script('evo_handlebars',plugins_url(EVENTON_BASE) . '/assets/js/lib/handlebars.js', array('jquery'), EVO()->version, true ); // 2.6.8
			
			if(!EVO()->cal->check_yn('evo_dis_jitsi')){
				wp_register_script('evo_jitsi','https://meet.jit.si/external_api.js', array('jquery'), EVO()->version, true ); // 4.5.3
			}
						
			wp_register_script('evo_moment',plugins_url(EVENTON_BASE) . '/assets/js/lib/moment.min.js', array('jquery'), EVO()->version, true ); // 2.8
			wp_register_script('evo_moment_tz',plugins_url(EVENTON_BASE) . '/assets/js/lib/moment_timezone_min.js', array('jquery'), EVO()->version, true ); // 4.5.8
			
			wp_register_script('evo_mobile',EVO()->assets_path.'js/lib/jquery.mobile.min.js', array('jquery'), EVO()->version, true ); // 2.2.17
			
			wp_register_script('evcal_easing', EVO()->assets_path. 'js/lib/jquery.easing.1.3.js', array('jquery'),'1.0',true );//2.2.24
			wp_register_script('evo_mouse', EVO()->assets_path. 'js/lib/jquery.mousewheel.min.js', array('jquery'),EVO()->version,true );//2.2.24
			wp_register_script('evcal_functions', EVO()->assets_path. 'js/eventon_functions.js', array('jquery'), EVO()->version ,true );// 2.2.22
			wp_register_script('evcal_ajax_handle', EVO()->assets_path. 'js/eventon_script.js', array('jquery'), EVO()->version ,true );


			// TRUMBO editor
			wp_register_script( 'evo_wyg_editor',EVO()->assets_path.'lib/trumbowyg/trumbowyg.min.js','', EVO()->version, true );
			wp_register_style( 'evo_wyg_editor',EVO()->assets_path.'lib/trumbowyg/ui/trumbowyg.css', '', EVO()->version);

			// removing soon
				wp_localize_script( 
					'evcal_ajax_handle', 
					'the_ajax_script', 
					apply_filters('evo_ajax_script_data_legacy', array( 
						'ajaxurl' => admin_url( 'admin-ajax.php' ) , 
						'rurl'=> get_rest_url(),
						'postnonce' => wp_create_nonce( 'eventon_nonce' ),
						'ajax_method' => 'ajax',
						'evo_v'=> EVO()->version
					))
				);

			// modified @4.4
			wp_localize_script( 
				'evcal_ajax_handle', 
				'evo_general_params', // handle key
				apply_filters('evo_ajax_script_data', array( 
					'ajaxurl' => admin_url( 'admin-ajax.php' ) , 
					'evo_ajax_url' => evo_ajax::get_endpoint('%%endpoint%%'), 
					'ajax_method' => ( EVO()->cal->get_prop('evo_com_method') == 'ajax'? 'ajax':'endpoint'),
					'rest_url'=> EVO_Rest_API::get_rest_api('%%endpoint%%'),
					'n' => wp_create_nonce( 'eventon_nonce' ),					
					'nonce' => wp_create_nonce( 'wp_rest' ),					
					'evo_v'=> EVO()->version,
					'text'=> array(
						'err1'=> evo_lang('This field is required'),
						'err2'=> evo_lang('Invalid email format'),
						'err3'=> evo_lang('Incorrect Answer'),
						'local_time'=> evo_lang('Local Time'),
					),
					'html'=> array( // 4.6
						'preload_general' => EVO()->calendar->helper->get_preload_general_html(),
						'preload_events' => EVO()->calendar->helper->get_preload_events_html(),
						'preload_event_tiles' => EVO()->calendar->helper->get_preload_events_tile_html(),
						'preload_taxlb' => EVO()->calendar->helper->get_preload_taxlb_html(),
						'preload_gmap' => EVO()->elements->get_preload_map(),
					),
					'cal'=> array(
						'lbs'=> EVO()->cal->get_prop('evo_ecard_lbs','evcal_1'),// lightbox scroll style
						'lbnav'=> EVO()->cal->get_prop('evo_ecard_lbnav','evcal_1'),// 4.9 lightbox event navigation
						'is_admin' => is_admin(), // to check if this is admin or not @4.9.3
						'search_openoninit'=> EVO()->cal->check_yn( 'EVOSR_showfield' ,'evcal_1'), // @added 4.9.8
					)
				))
			);

			// os maps
			//wp_register_script('evo_osmap', EVO()->assets_path. 'js/leaflet.js', array('jquery'), EVO()->version ,true );	
			//wp_register_style('evo_osmap',EVO()->assets_path.'css/lib/leaflet.css', array(), EVO()->version);	

			// google maps	
			wp_register_script('eventon_gmaps', EVO()->assets_path. 'js/maps/eventon_gen_maps.js', array('jquery'), EVO()->version ,true );	
			wp_register_script('eventon_gmaps_blank', EVO()->assets_path. 'js/maps/eventon_gen_maps_none.js', array('jquery'), EVO()->version ,true );	
			

			$apikey = !empty($evo_opt['evo_gmap_api_key'])? '?key='.$evo_opt['evo_gmap_api_key'] .'&callback=Function.prototype&loading=async&libraries=marker,places' :'';

			
			wp_register_script( 'evcal_gmaps', 
				apply_filters('eventon_google_map_url', 
					'https://maps.googleapis.com/maps/api/js'.$apikey), 
				array('jquery'),'1.0',true);
			

			// STYLES
			wp_register_style('evo_font_icons',EVO()->assets_path.'fonts/all.css','',EVO()->version);		
			
			// Defaults styles and dynamic styles
			wp_register_style('evcal_cal_default',EVO()->assets_path.'css/eventon_styles.css', array(), EVO()->version);	
			// single event
			wp_register_style('evo_single_event',EVO()->assets_path.'css/evo_event_styles.css',array(),EVO()->version);	

			// addon styles
			if(evo_settings_check_yn($this->evo_options,'evcal_concat_styles'  )){
				$ver = get_option('eventon_addon_styles_version');
				wp_register_style('evo_addon_styles',EVO()->assets_path.'css/eventon_addon_styles.css',array(), (empty($ver)?1.0:$ver) );
			}


			global $is_IE;
			if ( $is_IE ) {
				wp_register_style( 'ieStyle', EVO()->assets_path.'css/lib/ie.css', array(), '1.0' );
				wp_enqueue_style( 'ieStyle' );
			}

			// LOAD custom google fonts for skins	
			if( evo_settings_val( 'evo_googlefonts', $this->evo_options, true)){										
				wp_register_style( 'evcal_google_fonts', $this->google_fonts(), array(), EVO()->version);
			}
			
			
			$this->register_evo_dynamic_styles();

			// pluggable
				do_action('evo_register_other_styles_scripts');


		}

		// google fonts
		public function google_fonts(){
			$google_fonts = apply_filters(
				'evo_google_font_families',
				array(
					'noto-sans' => 'Noto+Sans:400,400italic,700',
					//'open-sans' => 'Open+Sans:400,400italic,700',
					//'Roboto' => 'Roboto:400,700,900',
					//'league-spartan' => 'League+Spartan:400,700',
					//'monsterrat' => 'Montserrat:700,800,900',
					'poppins' => 'Poppins:700,800,900',
					//'figtree' => 'Figtree:700,800,900',
				)
			);

			$query_args = array(
				'family' => implode( '|', $google_fonts ),
				'subset' => rawurlencode( 'latin,latin-ext' ),
			);

			$fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );

			return $fonts_url;
		}
		
		public function register_evo_dynamic_styles(){
			if(  !EVO()->cal->check_yn('evcal_css_head','evcal_1') ){
				if(is_multisite()) {
					$uploads = wp_upload_dir();
					$dynamic_style_url = $uploads['baseurl'] . '/eventon_dynamic_styles.css';					
				} else {
					$dynamic_style_url = EVO()->assets_path. 'css/eventon_dynamic_styles.css';	
				}

				$dynamic_style_url = str_replace(array('http:','https:'), '',$dynamic_style_url);

				wp_register_style('eventon_dynamic_styles', $dynamic_style_url, array(), EVO()->version );
			}
		}
		
		public function load_dynamic_evo_styles(){
			
			// if write dynamic styles into the page
			if( EVO()->cal->check_yn('evcal_css_head','evcal_1')){

				$dynamic_css = get_option('evo_dyn_css');
				if(!empty($dynamic_css)){

					$dynamic_css = wp_kses( $dynamic_css, array("\'", '\"'));

					wp_register_style( 'evo_dynamic_styles', false );
					wp_enqueue_style( 'evo_dynamic_styles' );
					wp_add_inline_style('evo_dynamic_styles', $dynamic_css);
					
					//echo '<style type ="text/css" rel="eventon_dynamic_styles">'.$dynamic_css.'</style>';
				}				
			}else{
				wp_enqueue_style( 'eventon_dynamic_styles');
			}

		}

		// load inline styles and scripts - @since 4.2.3 @u 4.6
		function inline_styles_scripts(){

			$inline_script = "jQuery(document).ready(function($){";
			$inline_script .= apply_filters('evo_inline_script_jq','');
			$inline_script .= "});";
			//echo $inline_script.'ttt';

			wp_register_script( 'evo-inlinescripts-header', '' );
			wp_enqueue_script( 'evo-inlinescripts-header' );
			wp_add_inline_script( 'evo-inlinescripts-header', $inline_script );

			do_action('eventon_page_inline_styles_scripts'); //4.6
		}

		// ENQ all scripts
		public function enqueue_evo_scripts(){
			$this->load_google_maps_scripts();
			$this->load_default_evo_scripts();
		}		

		// load eventon scripts and styles into page
		public function load_evo_scripts_styles(){
			$this->load_google_maps_scripts();
			$this->load_default_evo_scripts();
			$this->load_default_evo_styles();

			// 4.6.9
			add_action( 'wp_footer', array( $this, 'footer_code' ) ,15);

			do_action('evo_loaded_scripts_styles');
		}

		// scripts
		public function load_default_evo_scripts(){
			
			wp_enqueue_script('evcal_functions');
			wp_enqueue_script('evcal_easing');
			wp_enqueue_script('evo_handlebars');
			
			// only for frontend
			if( !is_admin()){				
				if(!EVO()->cal->check_yn('evo_dis_jitsi'))  wp_enqueue_script('evo_jitsi');
			}

			// conditional loading
			if( !EVO()->cal->check_yn('evo_dis_jqmobile') ) wp_enqueue_script('evo_mobile');
			if( !EVO()->cal->check_yn('evo_dis_moment') ){
				wp_enqueue_script('evo_moment');
				wp_enqueue_script('evo_moment_tz');
			} 
			
			wp_enqueue_script('evo_mouse');
			wp_enqueue_script('evcal_ajax_handle');			
			
			//wp_enqueue_script('evo_osmap');
			do_action('eventon_enqueue_scripts');
			
		}

		// styles
		public function load_default_evo_styles(){
			
			if(!EVO()->cal->check_yn('evo_googlefonts')){
				wp_enqueue_style( 'evcal_google_fonts' );
			}

			if( !is_admin() ){
				//wp_enqueue_style( 'evo_osmap');	
				wp_enqueue_style( 'evcal_cal_default');	
				wp_enqueue_style( 'evo_addon_styles');	
			}
			if(!EVO()->cal->check_yn('evo_fontawesome','evcal_1'))
				wp_enqueue_style( 'evo_font_icons' );

			// Addon styles will be loaded to page at this point
			do_action('eventon_enqueue_styles');

			$this->load_dynamic_evo_styles();
		}

		// load google maps API and scripts to page
		function load_google_maps_scripts(){

			// google maps loading conditional statement
			if( EVO()->cal->check_yn('evcal_cal_gmap_api','evcal_1')	){

				// remove completly
				if( EVO()->cal->get_prop('evcal_gmap_disable_section','evcal_1') =='complete'){


					EVO()->calendar->google_maps_load = false;
					wp_enqueue_script('eventon_gmaps_blank');
					wp_dequeue_script( 'evcal_gmaps');
					
				}else{ // remove only gmaps API

					EVO()->calendar->google_maps_load = true;
					
					wp_enqueue_script('eventon_gmaps');
					wp_dequeue_script( 'evcal_gmaps');
				}

			}else { // NOT disabled

				//update_option('evcal_gmap_load',true);
				EVO()->calendar->google_maps_load = true;

				// load map files only to frontend
				if ( !is_admin() ){
					wp_enqueue_script('evcal_gmaps');			
					wp_enqueue_script('eventon_gmaps');
				}
			}
		}
		
		public function evo_styles(){
			add_action('wp_head', array($this, 'load_default_evo_scripts'));
		}

		public function denqueue_scripts(){
			wp_dequeue_script('evcal_gmaps');			
			wp_dequeue_script('eventon_gmaps');
		}

	// check if members only
		function is_member_only($shortcode_args){
			if(!isset($shortcode_args['members_only'])) return true;

			return ( 
			 	($shortcode_args['members_only']=='yes' && is_user_logged_in()) ||
			 	$shortcode_args['members_only']=='no' || empty($shortcode_args['members_only'])
			)? true: false;
		}
		function nonMemberCalendar(){
			return __('You must login first to see calendar','eventon');
		}

	// language
		function lang(
			$evo_options, 
			$field, 
			$default_val, 
			$lang = ''
		){
				
			$evo_options = (!empty($evo_options))? $evo_options: $this->evo_lang_opt;
			
			// check for language preference
			if(!empty($lang)){
				$_lang_variation = $lang;
			}else{
				$shortcode_arg = EVO()->evo_generator->shortcode_args;
				$_lang_variation = (!empty($shortcode_arg['lang']))? $shortcode_arg['lang']:'L1';
			}
			
			$new_lang_val = (!empty($evo_options[$_lang_variation][$field]) )?
				stripslashes($evo_options[$_lang_variation][$field]): $default_val;
				
			return $new_lang_val;
		}

	

	// Event Type Taxonomies
		function get_localized_event_tax_names($lang='', $options='', $options2=''
		){
			$output ='';

			$options = (!empty($options))? $options: $this->evo_options;
			$options2 = (!empty($options2))? $options2: $this->evo_lang_opt;
			$_lang_variation = (!empty($lang))? $lang:'L1';

			
			// foreach event type upto activated event type categories
			foreach(eventon_get_valid_ett() as $key => $val ){
				$_tax_lang_field = 'evcal_lang_et'. ( $key == 1 ? '': $key );
				$ab = ($key==1)? '':$key;

				// check on eventon language values for saved name
				$lang_name = (!empty($options2[$_lang_variation][$_tax_lang_field]))? 
					stripslashes($options2[$_lang_variation][$_tax_lang_field]): null;

				// conditions
				if(!empty($lang_name)){
					$output[$x] = $lang_name;
				}else{
					$output[$x] = (!empty($options['evcal_eventt'.$ab]))? $options['evcal_eventt'.$ab]:'Event Type '.$ab;
				}	
			}
			
			return $output;
		}
		function get_localized_event_tax_names_by_slug($slug, $lang=''){
			$options = $this->evo_options;
			$options2 = $this->evo_lang_opt;
			$_lang_variation = (!empty($lang))? $lang:'L1';

			// initial values
			$x = ($slug=='event_type')?'1': (substr($slug,-1));
			$ab = ($x==1)? '':$x;
			$_tax_lang_field = 'evcal_lang_et'.$x;

			// check on eventon language values for saved name
			$lang_name = (!empty($options2[$_lang_variation][$_tax_lang_field]))? 
				stripslashes($options2[$_lang_variation][$_tax_lang_field]): null;

			// conditions
			if(!empty($lang_name)){
				return $lang_name;
			}else{
				return (!empty($options['evcal_eventt'.$ab]))? $options['evcal_eventt'.$ab]:'Event Type '.$ab;
			}	

		}

	// event header to single events pages
		function event_headers(){
			global $post;
			//print_r($post);

			if($post && property_exists($post, 'post_type') && $post->post_type=='ajde_events'):

				
				// if disable OG tags set via settings
				if( evo_settings_check_yn($this->evo_options,'evosm_disable_ogs') ) return false;

				//$thumbnail = get_the_post_thumbnail($post->ID, 'medium');
				$img_id =get_post_thumbnail_id($post->ID);
				$pmv = get_post_meta($post->ID);
				
				ob_start();
					$excerpt = eventon_get_normal_excerpt( $post->post_content, 20);
					//$excerpt = $post->post_content;
				?>
				<meta name="robots" content="all"/>
				<meta property="description" content="<?php echo $excerpt;?>" />
				<meta property="og:type" content="event" /> 
				<meta property="og:title" content="<?php echo $post->post_title;?>" />
				<meta property="og:url" content="<?php echo get_permalink($post->ID);?>" />
				<meta property="og:description" content="<?php echo $excerpt;?>" />
				<?php if($img_id!=''): 
					$img_src = wp_get_attachment_image_src($img_id,'full');
					if(is_array($img_src)):
				?>
					<meta property="og:image" content="<?php echo $img_src[0];?>" /> 
					<meta property="og:image:width" content="<?php echo $img_src[1];?>" /> 
					<meta property="og:image:height" content="<?php echo $img_src[2];?>" /> 
				<?php endif; endif;?>
				<?php
				// organizer as author
					if(!empty($pmv['evcal_organizer']))
						echo '<meta property="article:author" content="'.$pmv['evcal_organizer'][0].'" />';

				echo apply_filters('evo_facebook_header_metadata', ob_get_clean());


				// twitter
				?>
				<meta name="twitter:card" content="summary_large_image">
				<meta name="twitter:title" content="<?php echo $post->post_title;?>">
				<meta name="twitter:description" content="<?php echo $excerpt;?>">
				<?php if(isset($img_src[0])):?>
					<meta name="twitter:image" content="<?php echo $img_src[0];?>">
				<?php endif;?>
				<?php

			endif;
		}

	// Side bars
		// create a single event sidebar
		function register_se_sidebar(){			
			if(EVO()->cal->check_yn('evosm_1','evcal_1') ){
				register_sidebar(array(
				  'name' => __( 'Single Event Sidebar' ),
				  'id' => 'evose_sidebar',
				  'description' => __( 'Widgets in this area will be shown on the right-hand side of single events page.' ),
				  'before_title' => '<h3 class="widget-title">',
				  'after_title' => '</h3>'
				));
			}
		}

	// Schedule 
	// initiated in install
		function evo_cron_daily_actions(){
			
			evo_process_to_past_events();
		}

	// EMAILING	
		// get email parts
			public function get_email_part($part){
				global $eventon;

				$file_name = 'email_'.$part.'.php';

				$childThemePath = get_stylesheet_directory();	

				$paths = array(
					0=> TEMPLATEPATH.'/'.$eventon->template_url.'templates/email/',
					1=> $childThemePath.'/'.$eventon->template_url.'templates/email/',
					2=> AJDE_EVCAL_PATH.'/templates/email/',
				);

				foreach($paths as $path){				
					if(file_exists($path.$file_name) ){	
						$template = $path.$file_name;	
						break;
					}//echo($path.$file_name.'<br/>');
				}

				ob_start();

				include($template);

				return ob_get_clean();
			}

		// Get email body parts
		// to pull full email templates
			public function get_email_body($part, $def_location, $args='', $paths=''){
				global $eventon;

				ob_start();

				$file_location = EVO()->template_locator(
					$part.'.php', 
					$def_location,
					'templates/email/'
				);
				include($file_location);

				return ob_get_clean();
				
				/*
				$file_name = $part.'.php';
				global $eventon;

				if(empty($paths) && !is_array($paths)){
					$paths = array(
						0=> TEMPLATEPATH.'/'.$eventon->template_url.'templates/email/',
						1=> $def_location,
					);
				}

				foreach($paths as $path){	
					// /echo $path.$file_name.'<br/>';			
					if(file_exists($path.$file_name) ){	
						$template = $path.$file_name;	
						break;
					}
				}

				ob_start();

				if($template)
					include($template);

				return ob_get_clean();
				*/
			}
	// front-end website
		/** Output generator to aid debugging. */
			public function generator() {
				echo "\n\n" . '<!-- EventON Version -->' . "\n" . '<meta name="generator" content="EventON ' . esc_attr( EVO()->version ) . '" />' . "\n\n";
			}

	// CONTENT FILTERING U4.2.3
		function filter_evo_content($str){
			if( !EVO()->cal->get_prop('evo_content_filter','evcal_1') || EVO()->cal->get_prop('evo_content_filter','evcal_1') == 'evo'){	
				global $wp_embed;

				$str = $wp_embed->autoembed($str);
				$str = wptexturize($str);
				$str = convert_smilies($str);
				$str = convert_chars($str);
				$str = wpautop($str);
				$str = shortcode_unautop($str);
				$str = prepend_attachment($str);
				$str = do_shortcode($str);
				return $str;
			}elseif(EVO()->cal->get_prop('evo_content_filter','evcal_1')=='def'){
				return apply_filters('the_content', $str);
				
			}else{// no filter at all
				return $str;
			}
			
		}

	// footer for page with lightbox
		function footer_code(){		
			
			// GLOBAL DATA
			$global = apply_filters('evo_global_data',array(
				'calendars'=> array()
			));
			echo "<div id='evo_global_data' data-d='". json_encode($global)."'></div>";

			do_action('evo_page_footer');

		}
}
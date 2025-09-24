<?php
/*
	Event Tickets Admin init
 * @author 		AJDE
 * @category 	Admin
 * @package 	eventon-tickets/Classes
 * @version     2.4.14
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class evotx_admin{	
	private $addon_data;
	private $urls;
	private $evotx_opt;

	public $evotx_email_vals;

	function __construct(){
		add_action('admin_init', array($this, 'evotx_admin_init'));
		add_action('admin_head', array($this, 'evotx_admin_head'));
		include_once('class-meta_boxes.php');
		include_once('class-admin-evo-tix.php');

		// HOOKs		
		$this->evotx_opt = get_option('evcal_options_evcal_tx');
		
		// actions when event moved to trash that have wc product
		add_action('wp_trash_post', array($this, 'move_to_trash'));

		// duplicating events
		add_action('evo_after_duplicate_event', array($this, 'after_event_duplicate'), 10, 2);
		add_action('eventon_duplicate_event_exclude_meta', array($this, 'exclude_duplicate_field'), 10, 1);

		add_action( 'transition_post_status', array($this,'update_wc_status'), 10, 3 );

		add_action( 'admin_menu', array( $this, 'menu' ),9);
		add_action( 'admin_menu', array( $this, 'order_tix' ),99);	
		add_filter( 'pre_get_posts', array($this,'meta_filter_posts' ));

		// shortcode inclusions
		add_filter('eventon_shortcode_popup',array($this, 'add_shortcode_options'), 10, 1);
		add_filter('evo_sc_keys_assoc',array($this, 'sc_keys'), 10, 1);

		// custom ticket purchases order menu
		add_action( 'woocommerce_order_query_args', array($this, 'filter_wc_orders_table'), 99, 1);

	}


	// Initiate admin for tickets addon
		function evotx_admin_init(){

			$this->register_styles_scripts();

			// set ticket language strings
			$lang = new evotx_lang();
			
			
			global $pagenow, $typenow, $wpdb, $post;

			$__page = !empty($_GET['page']) ? sanitize_text_field( $_GET['page'] ) : false;

			
			if ( $typenow == 'post' && ! empty( $_GET['post'] ) && $post ) {
				$typenow = $post->post_type;
			} elseif ( empty( $typenow ) && ! empty( $_GET['post'] ) ) {
		        $typenow = get_post_type( $_GET['post'] );
		    } elseif (empty( $typenow ) && ! empty( $_GET['post-type'] ) ) {
		    	$typenow = $_GET['post-type'];
		    }

		    // for CPT pages
			if ( $typenow == '' || $typenow == "ajde_events" || $typenow =='evo-tix' || $typenow =='shop_order' || $__page == 'wc-orders') {

				$this->evotx_event_post_styles();	
				wp_enqueue_script('evo_charts');

			}

			// include ticket id in the search
				if($typenow =='' || $typenow == 'evo-tix' && !wp_doing_ajax()){
					// Filter the search page
					add_filter('pre_get_posts', array($this, 'evotx_search_pre_get_posts'));		
				}

				if($pagenow == 'edit.php' && $typenow == 'evo-tix'){
					add_action( 'admin_print_styles-edit.php', array($this, 'evotx_event_post_styles' ));	
				}

			// for only eventon tickets settings page
				if($pagenow == 'admin.php' && !empty($_REQUEST['page']) && $_REQUEST['page']=='eventon' && !empty($_REQUEST['tab']) && $_REQUEST['tab']=='evcal_tx'){
					$this->evotx_admin_styles();
				}

			// settings
			add_filter('eventon_settings_tabs',array($this,'evotx_tab_array') ,10, 1);
			add_action('eventon_settings_tabs_evcal_tx',array($this,'evotx_tab_content') );
		}

		function evotx_admin_head(){
			global $pagenow,$typenow;
			// disable evo-tix post creation button
			if(!empty($typenow) && $typenow =='evo-tix') echo '<style>h1 .page-title-action{display: none !important;}</style>';
		}
	
	// Shortcode
		public function sc_keys($array){

			$array['evotx_btn'] = __('Buy Ticket Button','evotx');
			$array['evotx_attendees'] = __('Show All Attendees','evotx');
			return $array;
		}
		function add_shortcode_options($shortcode_array){
			global $evo_shortcode_box;
			
			$new_shortcode_array = array(
				array(
					'id'=>		's_TX',
					'name'=>	__('Buy Ticket Button','evotx'),
					'code'=>	'evotx_btn',
					'variables'=>array(
						array(
							'name'=>'<i>NOTE: This standalone buy ticket button can be placed anywhere in your website to prompt a lightbox ticket data to quickly add a ticket to cart.</i>',
							'type'=>'note',
						),
						array(
							'name'=>'Button Text',
							'placeholder'=>'eg. Buy Ticket Now',
							'type'=>'text',
							'var'=>'btn_txt','default'=>'0',
						),array(
							'name'=>'Buy ticket box details',
							'placeholder'=>'eg. Purchase tickets right now.',
							'type'=>'text',
							'var'=>'box_details','default'=>'',
						),
						array(
							'name'=>'Show event date/time',
							'type'=>'YN',
							'var'=>'date_time',
							'default'=>'no'
						),array(
							'name'=>'Show event location (if any)',
							'type'=>'YN',
							'var'=>'location',
							'default'=>'no'
						),
						array(
							'name'=>'Event ID',
							'type'=>'select','var'=>'id',
							'placeholder'=>'eg. 234',	
							'options'=>	EVO()->shortcode_gen->get_event_ids(),
							'guide'=> __('These are the events that have tickets enabled','evotx')	
						),array(
							'name'=>'Repeat Interval ID',
							'type'=>'text',
							'var'=>'ri',
							'placeholder'=>'eg. 2',
							'guide'=>__('Enter the repeat interval instance ID of the event you want to show from the repeating events series (the number at the end of the single event URL)  eg. 3. This is only for repeating events','eventon')
						)
					)
				),
				array(
					'id'=>		's_TX2',
					'name'=>	__('Show All Attendees','evotx'),
					'code'=>	'evotx_attendees',
					'variables'=>array(
						array(
							'name'=>'<i>NOTE: This will display all the attendees of the event on frontend.</i>',
							'type'=>'note',
						),						
						array(
							'name'=>'Event ID',
							'type'=>'select','var'=>'id',
							'placeholder'=>'eg. 234',	
							'options'=>	EVO()->shortcode_gen->get_event_ids(),
							'guide'=> __('These are the events that have tickets enabled','evotx')	
						),
						array(
							'name'=>'Repeat Interval ID',
							'type'=>'text',
							'var'=>'ri',
							'placeholder'=>'eg. 2',
							'guide'=>__('Enter the repeat interval instance ID of the event you want to show from the repeating events series (the number at the end of the single event URL)  eg. 3. This is only for repeating events','eventon')
						),array(
							'name'=>'Show event details header',
							'type'=>'YN',
							'var'=>'event_details',
							'default'=>'no'
						)

					)
				)
			);

			return array_merge($shortcode_array, $new_shortcode_array);
		}

		
	// duplication event ticket product @2.4
		public function after_event_duplicate($EVENT, $post){
			
			// if tickets activated for this event
			if(  $EVENT->check_yn('evotx_tix') ){

				$old_wc_product_id = get_post_meta($post->ID,'tx_woocommerce_product_id', true);

				if( !$old_wc_product_id ) return;
				
				$old_product = wc_get_product( $old_wc_product_id );

				$wc_adp = new WC_Admin_Duplicate_Product;

				$new_name = $old_product->get_name() .' (copy)';
				$new_sku = $old_product->get_sku().'_'.( rand(2000,9999));

				$dup_product = $wc_adp->product_duplicate( $old_product );
			    $dup_product = wc_get_product( $dup_product->get_id() ); // recall the WC_Product Object
			    $dup_product->set_sku( $new_sku );
			    $dup_product->set_name( $new_name );
    			$dup_product->set_slug( sanitize_title( $new_name ) ); // slug
    			$dup_product->update_meta_data( '_eventid',  $EVENT->ID ); // event id
    			$dup_product->set_status( 'publish' );

    			$dup_product->save(); // Save

    			
    			// update for new event
    			$EVENT->set_prop('tx_woocommerce_product_id', $dup_product->get_id() );


				// pluggable for event with tickets
				do_action('evotx_after_duplicate_ticket_event', $EVENT, $dup_product->get_id(), $post, $dup_product);
			}
		}
		function exclude_duplicate_field($array){
			$array[] = 'tx_woocommerce_product_id';
			return $array;
		}

	// update associated wc product status along side event post status
		public function update_wc_status( $new_status, $old_status, $post ) {
			if($post->post_type=='ajde_events'){
				$tx_wc_id = get_post_meta($post->ID, 'tx_woocommerce_product_id', true);
				// only events with wc tx product association
				$post_exists = $this->post_exist($tx_wc_id);
				if($tx_wc_id && $post_exists){
					$product = get_post($tx_wc_id, 'ARRAY_A');
					$product['post_status']= $new_status;
					$product['ID']= $tx_wc_id;
					wp_update_post($product);
				}				
			}
		}

	// SUPPORT
		// check if post exist for a ID
			function post_exist($ID){
				global $wpdb;

				$post_id = $ID;
				$post_exists = $wpdb->get_row("SELECT ID FROM $wpdb->posts WHERE id = '" . $post_id . "'", 'ARRAY_A');
				return $post_exists? $post_exists['ID']: false;
			}
		
		
	    // move a event to trash
		    function move_to_trash($post_id){
		    	$post_type = get_post_type( $post_id );
		    	$post_status = get_post_status( $post_id );
		    	if($post_type == 'ajde_events' && in_array($post_status, array('publish','draft','future')) ){
		    		$woo_product_id = get_post_meta($post_id, 'tx_woocommerce_product_id', true);

		    		if(!empty($woo_product_id)){
		    			$__product = array(
		    				'ID'=>$woo_product_id,
		    				'post_status'=>'trash'
		    			);
		    			wp_update_post( $__product );
		    		}	
		    	}
		    }

	// TABS SETTINGS
		function evotx_tab_array($evcal_tabs){
			$evcal_tabs['evcal_tx']='Tickets';		
			return $evcal_tabs;
		}

		function evotx_tab_content(){
			include_once('class-settings.php');
			$settings = new evotx_settings();
			$settings->content();
		}

		

	// GET product type by product ID
		public function get_product_type($id){
			if ( $terms = wp_get_object_terms( $id, 'product_type' ) ) {
				$product_type = sanitize_title( current( $terms )->name );
			} else {
				$product_type = apply_filters( 'default_product_type', 'simple' );
			}
			return $product_type;
		}

	// other hooks
		function evotx_search_pre_get_posts($query){
		    // Verify that we are on the search page that that this came from the event search form
		    if($query->query_vars['s'] != '' && is_search())
		    {
		        // If "s" is a positive integer, assume post id search and change the search variables
		        if(absint($query->query_vars['s']))
		        {
		            // Set the post id value
		            $query->set('p', $query->query_vars['s']);

		            // Reset the search value
		            $query->set('s', '');
		        }
		    }
		}	

		public function register_styles_scripts(){
			wp_register_style( 'evotx_admin_post', EVOTX()->assets_path.'admin_evotx_post.css', '', EVOTX()->version );
			wp_register_style( 'evo_wyg_editor',EVO()->assets_path.'lib/trumbowyg/trumbowyg.css', '', EVO()->version);

			wp_register_script( 'evo_wyg_editor_j',EVO()->assets_path.'lib/trumbowyg/trumbowyg.min.js','', EVO()->version, true );
			wp_register_script( 'evotx_draw_attendees',EVOTX()->assets_path.'tx_draw_attendees.js','',EVOTX()->version);
			wp_enqueue_script( 'evotx_admin_post_script',EVOTX()->assets_path.'tx_admin_post_script.js','',EVOTX()->version);
		}
		function evotx_event_post_styles(){
			
			wp_enqueue_script('evo_handlebars');
			
			// enque trumbo editor			
			wp_enqueue_script( 'evo_wyg_editor_j');			
			wp_enqueue_style('evo_wyg_editor');			

			wp_enqueue_style( 'evotx_admin_post');
			wp_enqueue_script( 'evotx_draw_attendees');
			
			wp_localize_script( 
				'evotx_admin_post_script', 
				'evotx_admin_ajax_script', 
				apply_filters('evotx_admin_localize_data', array( 
					'ajaxurl' => admin_url( 'admin-ajax.php' ) , 
					'postnonce' => wp_create_nonce( 'evotx_nonce' ),
					'text'=> array(
						't1'=> __('Search Attendees ticket id, name, email','evotx'),
						't2'=> __('More Filters','evotx'),
						't3'=> __('Email Preview','evotx'),
					)
				))
			);

		}
		function evotx_admin_styles(){
			global $evotx;
			wp_enqueue_style( 'evotx_admin_css', EVOTX()->assets_path.'tx_admin.css');
			wp_enqueue_script( 'evotx_admin_script', EVOTX()->assets_path.'tx_admin_script.js');
			wp_localize_script( 
				'evotx_admin_script', 
				'evotx_admin_ajax_script', 
				array( 
					'ajaxurl' => admin_url( 'admin-ajax.php' ) , 
					'postnonce' => wp_create_nonce( 'evotx_nonce' )
				)
			);
		}
		

	
	
	// EventON settings menu inclusion
		function menu(){
			add_submenu_page( 'eventon', 'Tickets', __('Tickets','evotx'), 'manage_eventon', 'admin.php?page=eventon&tab=evcal_tx', '' );
		}
	// add submenu for ticket orders only
		function order_tix(){
			// add submenu page
			add_submenu_page('woocommerce','Ticket Orders', 'Ticket Orders', 'manage_eventon','admin.php?page=wc-orders&evofilter=evotix');
		}

		public function filter_wc_orders_table( $query_args ){
			if ( ! is_admin() ) return $query_args;


			if ( is_admin()  && isset($_GET['page']) && $_GET['page'] === 'wc-orders'  && isset( $_GET['evofilter'])  && $_GET['evofilter'] == 'evotix') {
		        $query_args['meta_query'][] = array(
		            'key'     => '_order_type',
		            'value'   => 'evotix',
		        );
		    }
		    return $query_args;

		}

	// add search parameters to get only event ticket orders
		function meta_filter_posts( $query ) {
			if(!is_admin() ) return $query;

			if( !is_search() ) return $query;

			if( isset( $_GET['post_type'] ) && $_GET['post_type']=='shop_order'
				&& !empty($_GET['meta_value']) && $_GET['meta_value']=='evotix'
			){
				$query->set( 'meta_key', '_order_type' );
				$query->set( 'meta_value', 'evotix' );
			}
			return $query;
		}

		function get_format_time($unix){
			$evcal_opt1 = get_option('evcal_options_evcal_1');
			$date_format = eventon_get_timeNdate_format($evcal_opt1);

			$TIME = eventon_get_editevent_kaalaya($unix, $date_format[1], $date_format[2]);

			return $TIME;
		}
	
}

$GLOBALS['evotx_admin'] = new evotx_admin();



	

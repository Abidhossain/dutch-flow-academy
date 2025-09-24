<?php
/**
 * Function ajax for backend
 * @version   4.9.12
 */
class EVO_admin_ajax{
	public $helper, $post_data;
	
	public function __construct(){

		$ajax_events = array(
			'get_shortcode_generator'=>'get_shortcode_generator',	

			'deactivate_product'	=>'deactivate_product',	
			'validate_license'		=>'validate_license',					
			'revalidate_license'	=>'revalidate_license',					
			'export_events'			=>'export_events',					
			'get_addons_list'		=>'get_addons_list',
			'get_addons_list'		=>'get_addons_list',
			'admin_lic_info'		=>'admin_lic_info',
			'lics_code'				=>'lics_code',

			'export_settings'		=>'export_settings',
			'get_import_settings'	=>'get_import_settings',
			'import_settings'		=>'import_settings',

			'admin_test_email'		=>'admin_test_email',
			'admin_get_environment'		=>'admin_get_environment',
			'admin_system_log'		=>'admin_system_log',
			'admin_system_log_flush'		=>'admin_system_log_flush',
			'admin_get_views'		=>'admin_get_views',
			'rel_event_list'		=>'rel_event_list',
			'get_latlng'				=>'get_latlng',

			'generate_custom_repeat_unix' =>'generate_custom_repeat_unix',
			'edit_custom_repeat' =>'edit_custom_repeat',

			'get_secondary_settings'=> 'get_secondary_settings',
			'save_secondary_settings'=> 'save_secondary_settings',

			'config_virtual_event'	=>'config_virtual_event',
			'select_virtual_moderator'	=>'select_virtual_moderator',
			'get_virtual_users'	=>'get_virtual_users',
			'save_virtual_mod_settings'	=>'save_virtual_mod_settings',
			'save_virtual_event_settings'	=>'save_virtual_event_settings',


			'eventedit_onload'	=>'evo_eventedit_onload',
			'eventedit_settings'	=>'evo_eventedit_settings', // 4.6

			// event card designer
			'load_ecard_designer'	=> 'load_ecard_designer', // 4.6
			'save_eventcard_designer'	=> 'save_eventcard_designer', // 4.6

			// save general settings
			'general_settings_save'			=> 'settings_save', // 4.8

		);

		$restricted_actions = [];
		foreach ( $ajax_events as $ajax_event => $class ) {

			$prepend = 'eventon_';
			add_action( 'wp_ajax_'. $prepend . $ajax_event, array( $this, $class ) );

			// for non loggedin user actions
			if( in_array( $ajax_event, $restricted_actions)){
				add_action( 'wp_ajax_nopriv_'. $prepend . $ajax_event, array( $this, 'restrict_unauthenticated' ) );
			}else{
				add_action( 'wp_ajax_nopriv_'. $prepend . $ajax_event, array( $this, $class ) );
			}
		}

		add_action('wp_ajax_eventon-feature-event', array($this, 'eventon_feature_event'));
		add_action('wp_ajax_nopriv_eventon-feature-event', array($this, 'restrict_unauthenticated'));


		$this->post_data = EVO()->helper->sanitize_array( $_POST );
		//$this->post_data =  $_POST ;

		//error_log('d');

		// rest based functions
		//add_filter('evo_ajax_rest_eventon_eventedit_onload', array($this, 'evo_eventedit_onload'),10, 2);
	}

	// Handle unauthenticated requests
    public function restrict_unauthenticated() {
        wp_send_json( array( 'status' => 'bad', 'msg' => __( 'Authentication required', 'eventon' )) );
        wp_die();
    }

	// shortcode generator
		public function get_shortcode_generator(){
			// Allow all roles, with nonce check, authorization check, read capability
        	EVO()->helper->validate_request( 'nn', 'eventon_admin_nonce', 'read', false, true );

			$sc = isset($this->post_data['sc']) ? stripslashes( $this->post_data['sc'] ): 'add_eventon';
			$content = EVO()->shortcode_gen->get_content();	

			wp_send_json(array(
				'status'=>'good',
				'content'=> $content,
				'sc' => sanitize_text_field( $sc ),
	            'type' => isset( $this->post_data['type'] ) ? sanitize_text_field( $this->post_data['type'] ) : '',
	            'other_id' => isset( $this->post_data['other_id'] ) ? sanitize_text_field( $this->post_data['other_id'] ) : '',
			));wp_die();		
		}

	// generate custom repeat instance unix
		public function generate_custom_repeat_unix(){
			// Allow all roles, with nonce check, authorization check
			EVO()->helper->validate_request( 'nn', 'eventon_admin_nonce', false, false, true );
			$msg = '';
			$PD = $this->post_data;


			// required data check
			if( empty($PD['event_new_repeat_start_date_x']) || empty( $PD['event_new_repeat_end_date_x'])){
				$output['msg'] = __('Missing required data!','eventon');
				wp_send_json($output); wp_die();
			}

			// generate unix from passed data
			$timezone = EVO()->calendar->timezone0 ?: new DateTimeZone('UTC');
			$_is_24h = (!empty($PD['_evo_time_format']) && $PD['_evo_time_format']=='24h')? true:false;
			$time_format = $_is_24h ? 'H:i':'g:ia';


			$new_index = (int)$PD['new_index'] +1;
			// if editing interval
			if( !empty($PD['edit_index'])) $new_index = (int)$PD['edit_index'];


			// time strings
			$start_time_string = $PD['_new_repeat_start_hour'].':'.$PD['_new_repeat_start_minute']. ( isset($PD['_new_repeat_start_ampm'])? $PD['_new_repeat_start_ampm']:'');
			$end_time_string = $PD['_new_repeat_end_hour'].':'.$PD['_new_repeat_end_minute']. ( isset($PD['_new_repeat_end_ampm'])? $PD['_new_repeat_end_ampm']:'');

			// generate unix from passed time Y/m/d (H:i / g:ia)
			$start_unix = DateTime::createFromFormat('Y/m/d '. $time_format, $PD["event_new_repeat_start_date_x"].$start_time_string, $timezone );
			$end_unix = DateTime::createFromFormat('Y/m/d '. $time_format, $PD["event_new_repeat_end_date_x"]. $end_time_string, $timezone );

			// check if unix generated
			$start_unix_val = $end_unix_val = null;
			if ($start_unix && $end_unix) {
		       	$start_unix_val = $start_unix->format('U');
		        $end_unix_val = $end_unix->format('U');
		        
		    } else {
		        error_log('Failed to parse interval: ' . print_r($PD, true));
		        $output['msg'] = __('Failed to parse interval','eventon');
				wp_send_json($output); wp_die();
		    }
			

			$start_dt = $PD["event_new_repeat_start_date"] .' '. $start_time_string;
			$end_dt = $PD["event_new_repeat_end_date"] .' '. $end_time_string;

			$_html =  '<li data-cnt="'.$new_index.'" style="display:flex" class="'.($new_index==0?'initial':'').($new_index>3?' over':'').'">'. ($new_index==0? '<dd>'.__('Initial','eventon').'</dd>':'').'<i>'.$new_index.'</i><span>'.__('from','eventon').'</span> '. $start_dt .' <span class="e">End</span> '. $end_dt .
				'<span class="evodfxi evofxdrr evofxaic evoclwi evogap5 evofxjcfe">
					<em class="evo_rep_edit evodfx evofxjcc evofxaic" alt="Edit"><i class="fa fa-pencil"></i></em>
					<em class="evo_rep_del evodfx evofxjcc evofxaic" alt="Delete"><i class="fa fa-times"></i></em>
				</span>'.
				'<input type="hidden" name="repeat_intervals['.$new_index.'][0]" value="'.$start_unix_val.'"/><input type="hidden" name="repeat_intervals['.$new_index.'][1]" value="'.$end_unix_val.'"/>'
						.'</li>';
			$msg = __('Repeat Instance Added','eventon');
			
			wp_send_json(array(
				'status'=> 'good',
				'content'=> $_html,
				'msg'=> $msg
			));
			wp_die();

		}

		public function edit_custom_repeat(){
			EVO()->helper->validate_request( 'nn', 'eventon_admin_nonce', false, false, true );
			$PD = $this->post_data;

			$_is_24h = (!empty($PD['_evo_time_format']) && $PD['_evo_time_format']=='24h')? true:false;
			$date_format = !empty($PD['_evo_date_format']) ? $PD['_evo_date_format'] : 'Y/m/d';
			$time_format = $_is_24h ? 'H:i' : 'g:ia';

			$timezone = new DateTimeZone('UTC');
			$index = key($PD['repeat_intervals']); // Get dynamic index
		    $start_unix = $PD['repeat_intervals'][$index][0];
		    $end_unix = $PD['repeat_intervals'][$index][1];

		    $start_dt = new DateTime("@$start_unix", $timezone);
		    $end_dt = new DateTime("@$end_unix", $timezone);

		    $output = [
		        'start_date' => $start_dt->format($date_format),
		        'start_date_x' => $start_dt->format('Y/m/d'),
		        'start_hour' => $start_dt->format($_is_24h ? 'H' : 'g'),
		        'start_minute' => $start_dt->format('i'),
		        'start_ampm' => $_is_24h ? '' : $start_dt->format('a'),
		        'end_date' => $end_dt->format($date_format),
		        'end_date_x' => $end_dt->format('Y/m/d'),
		        'end_hour' => $end_dt->format($_is_24h ? 'H' : 'g'),
		        'end_minute' => $end_dt->format('i'),
		        'end_ampm' => $_is_24h ? '' : $end_dt->format('a')
		    ];

		    wp_send_json($output);
		    wp_die();


		}

	// on event edit page load
		function evo_eventedit_onload(){

			// Allow all roles, with nonce check, authorization check
			EVO()->helper->validate_request( 'nn', 'eventon_admin_nonce', 'edit_eventons', false, true );


			// passing id to allow loading only certain metabox data
			$id = isset( $this->post_data['id'] ) ? $this->post_data['id'] : false;
			$event_id = isset( $this->post_data['eid'] ) ? $this->post_data['eid'] : null;

			$EVENT = new EVO_Event( $event_id );
			

			// pass the IDs for the data needed
			$all_dom_id_array = apply_filters('evo_eventedit_pageload_dom_ids',array(), $this->post_data, $EVENT, $id);

			// if specific box id is passed
			if( $id && isset( $all_dom_id_array[ $id] )) $all_dom_id_array = array($id => $all_dom_id_array[ $id]);

			$content_array_new = apply_filters('evo_eventedit_pageload_dom_data',array(), $this->post_data, $EVENT, $all_dom_id_array);
			$content_array_old = apply_filters('evo_eventedit_pageload_data',$content_array_new, $this->post_data, $EVENT, $id, $all_dom_id_array);// deprecating 4.9

			

			$new_content_array = array();

			foreach($content_array_old as $key => $val){
				$new_content_array[ $key] = $val;
			}
			foreach($content_array_new as $key => $val){
				if( isset($new_content_array[ $key ]) && !empty( $new_content_array[ $key ])) continue;
				$new_content_array[ $key] = $val;
			}
			

			$response = array(
				'status'=>'good',
				'content_array'=> $new_content_array,
				'dom_ids'=> $all_dom_id_array
			);

			wp_send_json($response); wp_die();
		}

	// open event edit settings as lightbox -- incomplete 4.9
		public function evo_eventedit_settings(){
			$EVENT = new EVO_Event($this->post_data['event_id']);

			ob_start();

			echo "<div class='evodfx evofx_dr_r'>";
			echo "<div class='evodfx evofx_dr_c' style='width:150px;'>
			<p>Subtitle</p>
			<p>Status</p>
			<p>Attendance</p>
			</div>
			<div class='evofx_1_1 evobr15' style='border:1px solid var(--evo_color_1); overflow:hidden;'>";
			include_once('post_types/class-meta_box_all.php');
			echo "</div></div>";

			$response = array(
				'status'=>'good',
				'content'=>  ob_get_clean(),
			);


			wp_send_json($response); wp_die();
		}

	// get secondary lightbox settings
		public function get_secondary_settings(){

			// Validate request
			EVO()->helper->validate_request();

			$post_data = EVO()->helper->sanitize_array( $_POST);
			$settings_file_key = isset($post_data['setitngs_file_key']) ? $post_data['setitngs_file_key'] : '';
			$allowed_files = array(
			    'cmf_settings' => plugin_dir_path(__FILE__) . 'views/cmf_settings.php',
			);
			

			if (array_key_exists($settings_file_key, $allowed_files) && file_exists($allowed_files[$settings_file_key])) {
			    ob_start();
			    include_once($allowed_files[$settings_file_key]);
			    wp_send_json([
			        'status' => 'good',
			        'content' => ob_get_clean()
			    ]);
			} else {
				error_log('Invalid settings file key attempted: ' . $settings_file_key);
			    wp_send_json([
			        'status' => 'bad',
			        'msg' => __('Invalid settings file requested', 'eventon')
			    ]);
			}
			wp_die();
		}
		public function save_secondary_settings(){
			// Validate request
			EVO()->helper->validate_request('evo_noncename','evo_save_secondary_settings');

			$post_data = EVO()->helper->sanitize_array( $_POST);

			// if html fields passed
			$html_fields = false;
			if(  isset( $post_data['html_fields'] ) ){
				$html_fields = json_decode(  stripslashes( $post_data['html_fields']) );

				$html_fields = is_array($html_fields) ? $html_fields : false;
			}


			$EVENT = new EVO_Event( $post_data['event_id']);

			foreach($post_data as $key=>$val){

				// skip fields
				if( in_array( $key, array('evo_noncename','event_id','_wp_http_referer'))) continue;
				
				// html content
				if( $html_fields &&  in_array($key, $html_fields )){
					$val = EVO()->helper->sanitize_html( $_POST[ $key ] );
				}

				

				$EVENT->save_meta($key, $val);
			}

			wp_send_json(array(
				'status'=>'good','msg'=> __('Event Data Saved Successfully','eventon')
			)); wp_die();
		}

	// virtual events
		public function config_virtual_event(){

			// Validate request
			EVO()->helper->validate_request();

			$post_data = EVO()->helper->sanitize_array( $_POST);

			$EVENT = new EVO_Event( $post_data['eid'] );

			ob_start();

			include_once('views/virtual_event_settings.php');

			wp_send_json(array(
				'status'=>'good','content'=> ob_get_clean()
			)); wp_die();
		}
		public function select_virtual_moderator(){
			
			// Validate request
			EVO()->helper->validate_request();

			ob_start();

			$eid = (int) $_POST['eid'];
			$EVENT = new EVO_Event( $eid);			
			$set_user_role = $EVENT->get_prop('_evo_user_role');
			$set_mod = $EVENT->get_prop('_mod');

			global $wp_roles;
			?>
			<div style="padding:20px">
				<form class='evo_vir_select_mod'>
					<input type="hidden" name="action" value='eventon_save_virtual_mod_settings'>
					<input type="hidden" name="eid" value='<?php echo esc_attr($eid);?>'>

					<?php wp_nonce_field( 'evo_save_virtual_mod_settings', 'evo_noncename' );?>
					
					<p class='row'>
						<label><?php _e('Select a user role to find users');?></label>
						<select class='evo_select_more_field evo_virtual_moderator_role' name='_user_role' data-eid='<?php echo $eid;?>'>
							<option value=''> -- </option>
							<?php 
							
							foreach($wp_roles->roles as $role_slug=>$rr){
								$select = $set_user_role == $role_slug ? 'selected="selected"' :'';
								echo "<option value='". $role_slug. "' {$select}>". $rr['name'] .'</option>';
							}

						?></select>
					</p>
					<p class='row evo_select_more_field_2'>
						<label><?php _e('Select a user for above role');?></label>
						<select name='_mod' class='evo_virtual_moderator_users'>
							<?php
							if( $set_user_role ):
								echo $this->get_virtual_users_select_options($set_user_role, $set_mod );
							else:
							?>
								<option value=''>--</option>
							<?php endif;?>
						</select>
					</p>
					<p class='evo_save_changes' ><span class='evo_btn save_virtual_event_mod_config ' data-eid='<?php echo esc_attr($eid);?>' style='margin-right: 10px'><?php _e('Save Changes','eventon');?></span></p>
				</form>
			</div>

			<?php

			wp_send_json(array(
				'status'=>'good','content'=> ob_get_clean()
			));wp_die();
		}
		public function get_virtual_users_select_options($role_slug, $set_user_id=''){
			
			// Validate request
			EVO()->helper->validate_request();

			$users = get_users( array( 
				'role' => $role_slug,
				'fields'=> array('ID','user_email', 'display_name') 
			) );
			$output = false;
			
			if($users){
				foreach($users as $user){
					$select = ( !empty($set_user_id) && $set_user_id == $user->ID) ? "selected='selected'":'';
					$output .= "<option value='{$user->ID}' {$select}>{$user->display_name} ({$user->user_email})</option>";
				}
			}
			return $output;
		}
		public function get_virtual_users(){

			// Validate request
			EVO()->helper->validate_request();

			$user_role = sanitize_text_field( $_POST['_user_role']);

			wp_send_json(array(
				'status'=>'good',
				'content'=> empty($user_role) ? 
					"<option value=''>--</option>" : 
					$this->get_virtual_users_select_options($user_role)
			)); wp_die();
		}

		// @updated 4.5.2
		public function save_virtual_event_settings(){

			// Validate request
			EVO()->helper->validate_request('evo_noncename','evo_save_virtual_event_settings');
			$post_data = EVO()->helper->sanitize_array( $_POST);
			$EVENT = new EVO_Event( $post_data['event_id']);

			foreach($post_data as $key=>$val){

				if( in_array($key, array( '_vir_url'))){
					$val = $post_data[$key];
				}

				// html content
				if( in_array($key, array( '_vir_after_content','_vir_pre_content','_vir_embed'))){
					$val = EVO()->helper->sanitize_html( $_POST[ $key ] );
				}

				$EVENT->save_meta($key, $val);
			}

			wp_send_json(array(
				'status'=>'good','msg'=> __('Virtual Event Data Saved Successfully','eventon')
			)); wp_die();
		}

		public function save_virtual_mod_settings(){

			// Validate request
			EVO()->helper->validate_request('evo_noncename','evo_save_virtual_mod_settings');

			$post_data = EVO()->helper->sanitize_array( $_POST);

			$EVENT = new EVO_Event( (int)$post_data['eid']);

			$EVENT->save_meta('_evo_user_role', $post_data['_user_role']);
			$EVENT->save_meta('_mod', $post_data['_mod']);

			wp_send_json(array(
				'status'=>'good','msg'=> __('Moderator Data Saved Successfully','eventon')
			)); wp_die();
			
		}

	// Related Events @4.5.9
		public function rel_event_list(){

			// Validate request
			EVO()->helper->validate_request();		
			$post_data = EVO()->helper->sanitize_array( $_POST);

			$event_id = (int)$post_data['eventid'];
			$EVs = json_decode( stripslashes($post_data['EVs']), true );

			$wp_args = array(
				'posts_per_page'=>-1,
				'post_type'=>'ajde_events',
				'post__not_in'=> array( $event_id ),
				'post_status'=>'publish'
			);
			$events = new WP_Query($wp_args );

			
			$content = '';

			$content .= "<div class='evo_rel_events_form' data-eventid='{$event_id}'>";

			$ev_count = 0;

			// each event
			if($events->have_posts()){	
				
					
				$events_list = array();

				foreach( $events->posts as $post ) {		

					$event_id = $post->ID;
					$EV = new EVO_Event($event_id);

					$time = $EV->get_formatted_smart_time();
					$is_selected = (is_array($EVs) && array_key_exists($event_id.'-0', $EVs) );

					ob_start();
					?><span class='rel_event<?php echo $is_selected?' select':'';?>' data-id="<?php echo $event_id.'-0';?>" data-n="<?php echo htmlentities($post->post_title, ENT_QUOTES)?>" data-t='<?php echo $time;?>'>
						<i class='rel_event_i evofz24i evomarr10 <?php echo $is_selected? "fa fa-circle-check":"far fa-circle";?>'></i>
						<span class='o'>
							<span class='n evofz14'><?php echo $post->post_title;?></span>
							<span class='t'><?php echo $time;?></span>							
						</span>
					</span><?php

					$events_list[ $EV->get_start_time() . '_' . $event_id ] = ob_get_clean();
					$ev_count++;

					$repeats = $EV->get_repeats_count();
					if($repeats){
						for($x=1; $x<=$repeats; $x++){
							$EV->load_repeat($x);
							$time = $EV->get_formatted_smart_time($x);

							ob_start();

							$is_selected = (is_array($EVs) && array_key_exists($event_id.'-'.$x, $EVs) );
							$select =  $is_selected ? ' select':'';
							
							?><span class='rel_event<?php echo $select;?>' data-id="<?php echo $event_id.'-'.$x;?>" data-n="<?php echo htmlentities($post->post_title, ENT_QUOTES)?>" data-t='<?php echo $time;?>'>
								<i class='rel_event_i evofz24i evomarr10 <?php echo $is_selected? "fa fa-circle-check":"far fa-circle";?>'></i>
								<span class='o'>
									<span class='n evofz14'><?php echo $post->post_title;?></span>
									<span class='t'><?php echo $time;?></span>									
								</span>
							</span><?php

							$events_list[ $EV->get_start_time() . '_' . $x ] = ob_get_clean();
							$ev_count++;
						}
					}
				}

				krsort($events_list);

				$content .= "<div class='evo_rel_search'>
					<span class='evo_rel_ev_count' data-t='".__('Events','eventon')."'>". $ev_count .' '. __('Events','eventon') ."</span>
					<input class='evo_rel_search_input' type='text' name='event' value='' placeholder='" . __('Search events by name','eventon'). " '/>
				</div>
				<div class='evo_rel_events_list'>";


				foreach($events_list as $ed=>$ee){
					$content .= $ee;
				}
				
				$content .= "</div><p style='text-align:center; padding-top:10px;'><span class='evo_btn evo_save_rel_events'>". __('Save Changes','eventon') ."</span></p>";
				
			}else{
				$content .= "<p>". __('You must create events first!','eventon') ."</p>";
			}

			$content .= "</div>";

			wp_send_json(array(
				'status'=>'good',
				'content'=> $content
			)); wp_die();
		}

	// Get Location Cordinates @updated 4.7.2
		public function get_latlng(){
			// Validate request
			EVO()->helper->validate_request('nn','eventon_admin_nonce', 'read', false ,true);	

			if( !isset($_POST['address'])){
				echo json_encode(array(
				'status'=>'bad','m'=> __('Address Missing','eventon'))); exit;
			}

			$address = sanitize_text_field($_POST['address']);
			
			$latlon_results = eventon_get_latlon_from_address( $address );
			

			if( !$latlon_results ){
				wp_send_json(array(
				'status'=>'bad','m'=> __('Could not connect to map API','eventon'))); 
				wp_die();
			}

			if( !is_array($latlon_results)){
				wp_send_json(array(
				'status'=>'bad','m'=> $latlon_results )); 
				wp_die();
			}

			if( !isset( $latlon_results['lat'] ) || !isset( $latlon_results['lng']) ){
				wp_send_json(array(
				'status'=>'bad','m'=> __('Could get results from map API','eventon'))); 
				wp_die();
			}


			wp_send_json(array(
				'status'=>'good',
				'lat' => $latlon_results['lat'],
		        'lng' => $latlon_results['lng'],
			)); 
			wp_die();

			
		}

	// get HTML views
		public function admin_get_views(){

			$post_data = EVO()->helper->sanitize_array( $_POST);

			if(!isset($_POST['type'])){
				echo 'failed'; exit;
			} 

			$type = $_POST['type'];
			$data = isset($_POST['data'])? $_POST['data']: array();

			$views = new EVO_Views();

			wp_send_json(array(
				'status'=>'good',
				'content'=>$views->get_html($type, $data)
			)); wp_die();
		}

	// event card designer 4.6
		public function load_ecard_designer(){
			$designer = new EVO_Desginer();

			wp_send_json(array(
				'status'=>'good','content'=> $designer->get_eventcard_designer()
			)); wp_die();
		}
		public function save_eventcard_designer(){

			// Validate request
			EVO()->helper->validate_request('evo_noncename','evo_evard_save');	
			

			$post_data = EVO()->helper->sanitize_array( $_POST);

			EVO()->cal->set_cur('evcal_1');
			EVO()->cal->set_prop( 'evo_ecl' , $post_data['evo_ecl']);

			wp_send_json(array(
				'status'=>'good','msg'=> __('EventCard Design Saved Successfully')
			)); wp_die();

		}

	// export eventon settings
		public function export_settings(){
			
			// Validate request
			EVO()->helper->validate_request('nonce','evo_export_settings', 'edit_eventons', true ,true);		
			

			header('Content-type: text/plain');
			header("Content-Disposition: attachment; filename=Evo_settings__".date("d-m-y").".json");
			
			$json = array();
			$evo_options = get_option('evcal_options_evcal_1');
			foreach($evo_options as $field=>$option){
				// skip fields
				if(in_array($field, array('option_page','action','_wpnonce','_wp_http_referer'))) continue;
				$json[$field] = $option;
			}

			wp_send_json($json); wp_die();
		}

	// import settings
		public function get_import_settings(){
			
			// Validate request
			EVO()->helper->validate_request('nn','eventon_admin_nonce', 'edit_eventons', true ,true);		

			$output = array('status'=>'bad','msg'=>'');

			ob_start();

			EVO()->elements->print_import_box_html(array(
				'box_id'=>'evo_settings_upload',
				'title'=>__('Upload JSON Settings File Form'),
				'message'=>__('NOTE: You can only upload settings data as .json file'),
				'file_type'=>'.json',
				'type'		=> 'inlinebox'
			));

			$output['status'] = 'good';
			$output['content'] = ob_get_clean();

			wp_send_json($output); wp_die();
			

		}

		// @4.6.9
		public function import_settings(){
			
			// Validate request
			EVO()->helper->validate_request('nonce','eventon_admin_nonce', 'edit_eventons', true ,true);	

			$output = array('status'=>'','msg'=>'');
			$post_data = EVO()->helper->sanitize_array( $_POST);
			$JSON_data = $post_data['jsondata'];

			// check if json array present
			if( $JSON_data && !is_array($JSON_data)){
				$output['msg'] = __('Uploaded file is not a json format!','eventon');
				wp_send_json($output); wp_die();
			} 

			// if all good
			if( empty($output['msg'])){
				
				// remove slashes on json
				foreach( array('evo_ecl','evo_etl') as $key){
					$JSON_data[ $key ] = stripslashes( $JSON_data[ $key ] );
				}

				EVO()->cal->set_cur('evcal_1');
				EVO()->cal->set_option_values( $JSON_data );

				$output['success'] = 'good';
				$output['msg'] = __('Successfully updated settings! This page will refresh with new settings.','eventon');
			}
			
			wp_send_json($output); wp_die();

		}

	// export events as CSV
	// @update 4.7.11
		public function export_events($event_id = ''){

			// Validate request	
			$restrict = EVO()->cal->check_yn('evo_restrict_csv', 'evcal_1');

			EVO()->helper->validate_request(
			    'nonce',
			    'eventon_download_events',
			    $restrict ? 'edit_eventons' : '',
			    $restrict,
			    $restrict,
			    'message'
			);
				
					
			
			$run_process_content = false;
			$wp_args = array();

			// if event ID was passed
				if( isset($_REQUEST['eid']) ){
					$wp_args = array('p' => (int)$_REQUEST['eid']);
				}

			header('Content-Encoding: UTF-8');
        	header('Content-type: text/csv; charset=UTF-8');
			header("Content-Disposition: attachment; filename=Eventon_events_".date("d-m-y").".csv");
			header("Pragma: no-cache");
			header("Expires: 0");
			echo "\xEF\xBB\xBF"; // UTF-8 BOM
				
			EVO()->cal->set_cur('evcal_1');
			$evo_opt = get_option('evcal_options_evcal_1');
			$event_type_count = evo_max_ett_count();
			$cmd_count = evo_calculate_cmd_count($evo_opt);

			$run_iconv = EVO()->cal->check_yn('evo_disable_csv_formatting','evcal_1') ? false : true;

			$fields = $csv_fields = $this->get_event_csv_fields( $event_type_count , $cmd_count );

			// Print out the CSV file header
				$csvHeader = '';
				foreach( $csv_fields as $var=>$val){	
					$csvHeader.= $val.',';	
				}

				// plug for CSV column headers
				$csvHeader = apply_filters('evo_export_events_csv_header',$csvHeader);
				$csvHeader.= "\n";
				
				echo (function_exists('iconv'))? iconv("UTF-8", "ISO-8859-2", $csvHeader): $csvHeader;
 		

			// using calendar function
				$events = EVO()->calendar->get_all_event_data(array(
					'hide_past'=>'no',
					'wp_args'=> $wp_args
				));
				
				if(!empty($events)):

					// allow processing content for html readability
					$process_html_content = true;

					$DD = new DateTime('now', EVO()->calendar->cal_tz);

					// EACH EVENT
					foreach($events as $event_id => $event):

						//print_r($event);

						$event_csv_data = array();

						$pmv = isset($event['pmv'] ) ? $event['pmv'] : '';
						$EVENT = new EVO_Event( $event_id, $pmv, 0, true, false);

						$DD->setTimezone( $EVENT->tz ); // adjust time to event tz
						
						if(empty($pmv ) ) $pmv = $EVENT->get_data();

						// initial values
							$event_csv_data['publish_status'] = $event['post_status'];
							$event_csv_data['event_id'] = $EVENT->ID;
							$event_csv_data['evcal_event_color'] = $EVENT->get_hex();
							$event_csv_data['event_name'] = $EVENT->get_title();

						// event description
							$event_content = ( $event['content'] ?? '');
							$event_content = str_replace('"', "'", $event_content);
							$event_content = str_replace(',', "\,", $event_content);
							if( $run_process_content){
								$event_content = $this->html_process_content( $event_content, $process_html_content);
							}

							$event_csv_data['event_description'] = $event_content;

						// start time
							if( isset($event['start'])){
								$DD->setTimestamp( $event['start'] );
								
								$event_csv_data['event_start_date'] = $DD->format( apply_filters('evo_csv_export_dateformat','m/d/Y') );
								$event_csv_data['event_start_time'] = $DD->format( apply_filters('evo_csv_export_timeformat','h:i:A') );
							}

						// end time
							if( isset($event['end'])){
								$DD->setTimestamp( $event['end'] );
								
								$event_csv_data['event_end_date'] = $DD->format( apply_filters('evo_csv_export_dateformat','m/d/Y') );
								$event_csv_data['event_end_time'] = $DD->format( apply_filters('evo_csv_export_timeformat','h:i:A') );

							}

						// event types
							for($y=1; $y<=$event_type_count;  $y++){
								$_ett_name = ($y==1)? 'event_type': 'event_type_'.$y;
									
								if( isset($event[$_ett_name])){
									$term_ids = $term_names = '';
									
									foreach ( $event[$_ett_name] as $termid=>$termname ) {
										$term_ids .= $termid.',';
										$term_names .= $termname.',';
									}

									$event_csv_data[$_ett_name ] = $term_ids;
									$event_csv_data[$_ett_name .'_slug' ] = $termname;
								}												
							}

						// for event custom meta data
							for($z=1; $z<=$cmd_count;  $z++){
								$cmd_key_name = 'cmd_'. $z;
								$cmd_name = '_evcal_ec_f'.$z.'a1_cus';

								$cmd_val = $EVENT->get_prop( $cmd_name );
								$event_csv_data[ $cmd_name ] = ( $cmd_val ? $this->html_process_content( $cmd_val , $process_html_content ) : null);
								
								
								// for button custom meta field
								if( EVO()->cal->get_prop('evcal_ec_f'. $z.'a2') == 'button'){
									$cmd_val2 = $EVENT->get_prop( $cmd_name.'L' );
									$event_csv_data[ $cmd_name.'L' ] = ($cmd_val2? esc_url( $cmd_val2 ): null );

									// open in new window
									$cmd_val3 = $EVENT->get_prop( '_evcal_ec_f'.$z.'_onw' );
									$event_csv_data[ '_evcal_ec_f'.$z.'_onw' ] = ($cmd_val3? esc_html( $cmd_val3 ) :null);
								}
							}

						// FOR EACH field for not filled values				
						foreach($csv_fields as $var => $val){

							if( isset( $event_csv_data[ $var ]) ) continue;
														
							// yes no values
								if(in_array($val, array('exclude_from_cal','completed','featured','all_day','hide_end_time','event_gmap','evo_year_long','_evo_month_long','repeatevent'))){

									$event_csv_data[ $var ] = $EVENT->check_yn( $var ) ? 'yes':'no';
									continue;
								}
														
									
							// all other fields
								if( !isset($event_csv_data[ $var ])  ){

									$_value = '';
									
									// check if event data has this value
									if( !empty( $event[ $val ])) $_value = $event[ $val ];
									if( $prop_val = $EVENT->get_prop($val) ) $_value = $prop_val;

									if( empty( $_value )) continue;

									$event_csv_data[ $var ] = $this->html_process_content( $_value, $process_html_content );
								}

						}

					
					// creating the csv row data
						$csvRow = '';

						$event_csv_data = apply_filters('evo_export_events_csv_eventdata', $event_csv_data, $EVENT, $csv_fields );

						foreach( $csv_fields as $var => $val){
							if( isset( $event_csv_data[ $var ]) && !empty( $event_csv_data[ $var ] ) ){

								// for location image
								if( $var == 'location_img' && is_array($event_csv_data[ $var ] )){
									$csvRow .= '"' . $event_csv_data[ $var ][1] .'",';
									continue;
								}

								$csvRow .= '"' . ( is_array($event_csv_data[ $var ] ) ? 
									'-': $event_csv_data[ $var ] ) .'",';
							}else{
								$csvRow .= ',';
							}
						}


					// closing @4.6.9
						$csvRow = apply_filters('evo_export_events_csv_row',$csvRow, $event_id, $pmv, $EVENT);
						$csvRow.= "\n";

					// row formating
						if( $run_iconv ){
							//echo (function_exists('iconv'))? iconv("UTF-8", "ISO-8859-2", $csvRow): $csvRow;
							echo $csvRow;
						}else{
							echo $csvRow;							
						}

					endforeach;// each event
				endif;

			
				wp_die();


 			
		}

		// @4.6.1 @updated 4.7.4
		private function get_event_csv_fields( $et_count, $cmd_count){
			// clean name => variable key
			$csv_fields = array(
				'publish_status'=>'publish_status',	
				'event_id'=>'event_id',			
				'evcal_event_color'=>'color',
				'event_name'=>'event_name',				
				'event_description'=>'event_description',
				'event_start_date'=>'event_start_date',
				'event_start_time'=>'event_start_time',
				'event_end_date'=>'event_end_date',
				'event_end_time'=>'event_end_time',

				//'evcal_allday'=>'all_day',
				'_evo_tz'			=>'event_timezone',
				'_time_ext_type'	=>'time_ext_type',
				'evo_hide_endtime'	=>'hide_end_time',
				'evcal_gmap_gen'	=>'event_gmap',
				'evo_year_long'		=>'year_long_event',

				'_featured'			=>'featured',
				'evo_exclude_ev'	=>'exclude_from_cal',
				'_completed'		=>'completed',

				'evo_location_id'=>'location_tax',
				'evcal_location_name'=>'location_name',	// location name		
				'location_address'	=>'location_address',	// address		
				'location_desc'		=>'location_desc',	
				'location_lat'		=>'location_lat',	
				'location_lon'		=>'location_lon',	
				'location_link'		=>'location_link',	
				'location_img'		=>'location_img',	
				
				'evo_organizer_id'	=>'organizer_tax',
				'event_organizer'	=>'organizer_name',
				'organizer_description'	=>'organizer_desc',
				'organizer_email'	=>'organizer_email',
				'organizer_phone'	=>'organizer_phone',
				'organizer_contact'	=>'organizer_contact',
				'organizer_address'	=>'organizer_address',
				'organizer_link'	=>'organizer_link',
				'organizer_img'		=>'organizer_img',

				'evcal_subtitle'=>'evcal_subtitle',
				'evcal_lmlink'=>'learnmore link',
				'image_url'=> 'image_url',

				'evcal_repeat'=>'repeatevent',
				'evcal_rep_freq'=>'repeat_type',
				'evcal_rep_gap'=>'repeat_gap',
				'evcal_rep_num'=>'repeat_count',
				'evp_repeat_rb'=>'repeat_mode',
			);

			// event types
				for($y=1; $y<=$et_count;  $y++){
					$_ett_name = ($y==1)? 'event_type': 'event_type_'.$y;
					$csv_fields[ $_ett_name ] = $_ett_name;
					$csv_fields[ $_ett_name .'_slug' ] = $_ett_name .'_slug';
				}
			// for event custom meta data
				for($z=1; $z<=$cmd_count;  $z++){
					$_cmd_name = 'cmd_'.$z;
					$_cmd_name_val = '_evcal_ec_f'.$z.'a1_cus';
					$csv_fields[ $_cmd_name_val ] = $_cmd_name;
				
					// for button custom meta field
					if( EVO()->cal->get_prop('evcal_ec_f'. $z.'a2') == 'button') {
						$csv_fields[ $_cmd_name_val ."L" ] = $_cmd_name ."_link" ;
						$csv_fields[ '_evcal_ec_f'.$z.'_onw' ] = $_cmd_name ."_onw";
					}
				}

			return apply_filters('evo_csv_export_fields', $csv_fields);

		}
		function html_process_content($content, $process = true){
			//$content = iconv('UTF-8', 'Windows-1252', $content);
			if( is_array( $content )) return $content;
			return ($process)? htmlentities($content, ENT_QUOTES): $content;
		}

	// Validation of eventon products
		public function validate_license(){

			// Validate request
			EVO()->helper->validate_request('nn','eventon_admin_nonce', 'edit_eventons', true ,true );		
			
			$post_data = EVO()->helper->sanitize_array( $_POST);

			$status = 'bad'; 
			$error_code = 11; 
			$error_msg_add = $html = $email = $msg = $validation = '';
			
			// check for required information
				if(empty($post_data['type']) && isset($post_data['key']) && isset($post_data['slug']) ){ 
					wp_send_json(array('status'=>'bad','error_msg'=> EVO_Error()->error_code(14) ));		
					wp_die();
				}

			// Initial values
			$type = $post_data['type'];
			$license_key = $post_data['key'];
			$slug = $post_data['slug'];

			$PROD = new EVO_Product_Lic($slug);
			
			// check for key format validation
			$verifyformat = $PROD->purchase_key_format($license_key );
			if(!$verifyformat) $error_code = '02';	

			// check if email provided for eventon addons
			if( $post_data['slug'] != 'eventon'){
				if(empty($post_data['email'])){
					$status = 'bad';
					$msg = 'Email address not provided!';
					$verifyformat = false;
				}else{
					$email = str_replace(' ','',$post_data['email']);
				}
			}
			
			// if license key format is validated
			if($verifyformat){


				// save eventon data
				if($type=='main') $PROD->save_license_data();

				$status = 'good';
				$msg = ($slug=='eventon')?
					'Excellent! Purchase key verified and saved. Thank you for activating EventON!':
					'Excellent! License key verified and saved. Thank you for activating EventON addon!';

				// passing data for license
				$data_args = array(
					'type'		=>(!empty($post_data['type'])?$post_data['type']:'main'),
					'key'		=> addslashes( str_replace(' ','',$license_key) ),
					'email'		=> $email,
					'envato_username'		=> (!empty($post_data['envato_username'])?$post_data['envato_username']:''),
					'product_id'=>(!empty($post_data['product_id'])?$post_data['product_id']:''),
				);
				$validation = $PROD->remote_validation($data_args);

				// Other update tasks
				if($type=='addon'){	
					// update other addon fields
					foreach(array(
						'email','product_id','instance','key'
					) as $field){
						if(!empty($post_data[$field])){
							$PROD->set_prop( $field, $post_data[$field], false);
						}
					}
					$PROD->save();
				}

				$results = $this->get_remote_validation_results($validation, $PROD, $type);
	
				if(isset($results['error_code'])) $error_code = $results['error_code'];

				$status = $results['status'];
					
				if($error_code != 11){
					$msg = EVO_Error()->error_code( $error_code);
				}

				if($results['status'] == 'bad' && in_array( $error_code, array(11,21,23) )){
					$msg = EVO_Error()->error_code( 120 );
				}

			}else{
				// Invalid license key format
				$status = 'bad';
				if(empty($msg)) $msg = 'License Key format is not a valid format!';
			}

			$return_content = array(
				'status'=>	$status,
				'msg'=> 	$msg,				
				'code'=> 	$error_code,
				'html'=>	$this->get_html_view( $type,$slug),
				'debug'=> 	$validation,
				'slug'=> $slug,
			);

			wp_send_json($return_content);	wp_die();
		}

		// RE-VALIDATE
			public function revalidate_license(){
				// Validate request
				EVO()->helper->validate_request('nn','eventon_admin_nonce', 'edit_eventons', true ,true );		

				$post_data = EVO()->helper->sanitize_array( $_POST);
				$slug = $post_data['slug'];

				$PROD = new EVO_Product_Lic($slug);

				//echo $PROD->get_prop('key');

				if( !$PROD->get_prop('key') || !$PROD->get_prop('email')){
					echo json_encode(array(
						'status'=>'bad',
						'msg'=>'Required fields for remote validation are missing! try deactivating and reactivating again.'
					));		
					exit;
				}else{

					$ERR = new EVO_Error();
					$ERR->record_gen_log('Re-activating', $slug,'','',false);

					$data_args = array(
						'type'		=>(!empty($post_data['type'])?$post_data['type']:'main'),
						'key'		=> $PROD->get_prop('key'),
						'email'		=> $PROD->get_prop('email'),
						'product_id'=>(!empty($post_data['product_id'])?$post_data['product_id']:''),						
						'instance'	=> md5(get_site_url()),
					);
					$validation = $PROD->remote_validation($data_args);
					
					$results = $this->get_remote_validation_results( $validation, $PROD , $post_data['type']);
					$output_error_code = isset($results['error_code'])? (int) $results['error_code']: false;

					if($results['status'] == 'bad'){
						$ERR->record_gen_log('Re-activating failed', $slug, $results['error_code'],'',false);
					}
					
					$ERR->save();

					// Message intepretation
					$msg = ($results['status']=='bad'? EVO_Error()->error_code(15): EVO_Error()->error_code(16));
					if($results['status'] == 'bad' && $output_error_code && in_array( $output_error_code, array(11,21,23) )){
						$msg = EVO_Error()->error_code( 121 );
					}

					if($output_error_code && in_array( $output_error_code, array(100,101,102,103)) ){
						$msg = EVO_Error()->error_code( $output_error_code );

						if( $output_error_code == 103){
							$msg = EVO_Error()->error_code( '103r' );
							EVO_Error()->record_deactivation_loc($slug);
							$PROD->deactivate();
						}
					}

					wp_send_json(array(
						'status'=> $results['status'],
						'msg'=> $msg,
						//'error_msg'=> EVO_Error()->error_code( $results['error_code']),
						'html'=> $this->get_html_view( 'addon',$slug),	
						'slug'=> $slug				
					));	 wp_die();
				}
			}

	// REMOTE RESULTS
		private function get_remote_validation_results($validation, $PROD, $type){
			// validation contain // status, error_remote_msg, error_code, api_url
			// invalid remote validation
			$output = array();
			$error_code = false;
			if($validation['status'] =='good'){
				$output['status'] = 'good';
				EVO_Prods()->get_remote_prods_data();
				$PROD->evo_kriyathmaka_karanna();
				EVO_Error()->record_activation_rem();

			}else{
				$output['status'] = 'bad';	
				if(!empty($validation['error_code'])) $error_code =  (int)$validation['error_code'];
				
				$output['error_code'] = $error_code;
				$output['error_msg'] = isset($validation['error_remote_msg'])? $validation['error_remote_msg']: '';

				// local kriyathmaka karala nehe
				if(!$PROD->kriyathmaka_localda() && $error_code && in_array( $error_code, array(11,21,23) ) ){
					$PROD->evo_kriyathmaka_karanna_athulen();
					EVO_Error()->record_activation_loc($error_code);
				}
			}

			return $output;
		}

	// Deactivate EventON Products
		public function deactivate_product(){
			// Validate request
			EVO()->helper->validate_request('nn','eventon_admin_nonce', 'edit_eventons', true ,true);		

			$post_data = EVO()->helper->sanitize_array( $_POST);
			$error_msg = $status = $html = '';
			$error_code = '00';
			
			if($post_data['type'] == 'main' && (!isset($post_data['key']) || strpos($post_data['key'], 'EVO') !== 0)){
				$PROD = new EVO_Product_Lic('eventon');
				$status = $PROD->deactivate();

				$slug = 'eventon';

				// not able to deactivate
				if(!$status){
				 	$error_code = '07';	
				}else{ // deactivated
					EVO_Error()->record_deactivation_loc($slug);
					$html = $this->get_html_view('main',$slug);
					$error_code = 32;
				}
				
			}else{// for addons

				if(!isset($post_data['slug'])){
					wp_send_json(array(
						'status'=>'bad',
						'error_msg'=> EVO_Error()->error_code(14)
					)); wp_die();
				}

				$PROD = new EVO_Product_Lic($post_data['slug']);
			
				// passing data
					$remote_data = array(
						'key'		=> addslashes( str_replace(' ','',$post_data['key']) ),
						'email'		=>(!empty($post_data['email'])? $post_data['email']: null),
						'product_id'=>(!empty($post_data['product_id'])? $post_data['product_id']: null),
					);

				// deactivate addon from remote server
					$deactive_remotely = $PROD->remote_deactivate($remote_data);

					$returned_error_code = isset($deactive_remotely['error_code'])? (int)$deactive_remotely['error_code']:false;

					if($returned_error_code && in_array( $returned_error_code, array(30,31) ) ){
						
						EVO_Error()->record_deactivation_fail($returned_error_code);
						EVO_Error()->record_deactivation_loc($post_data['slug']);
						$PROD->deactivate();
						$error_code = 32;
					}else{
						$error_code = 33;
						EVO_Error()->record_deactivation_rem();
						$PROD->deactivate();
					}


					// get updated HTML
					$html = ( $post_data['type'] == 'main' ) ? 
						$this->get_html_view('main','eventon'):
						$this->get_html_view('addon',$post_data['slug']);
					$status = 'success';
			}

			$return_content = array(
				'status'=> ($status?'success':'bad'),
				'msg'=>EVO_Error()->error_code($error_code),
				'html'=> $html,					
			);
			
			wp_send_json($return_content);	wp_die();
		}

		private function get_html_view($type,$slug){
			$views = new EVO_Views();
			$var = ($type=='main')? 'evo_box': 'evo_addon_box';
			return $views->get_html(	$var,array('slug'	=>$slug) );
		}
		
	// get all addon details @version 4.7
		public function get_addons_list(){

			// Validate request
			EVO()->helper->validate_request('nn','eventon_admin_nonce', 'edit_eventons', true ,true);		

			$active_plugins = get_option( 'active_plugins' );
			$views = new EVO_Views();

			ob_start();


			// eventon main plugin 
				echo $views->get_html('evo_box');

			// installed addons	
				$addons_list = new EVO_Addons_List();

				$count=1;
				// EACH ADDON
				foreach($addons_list->get_list() as $slug=>$product){

					if($slug=='eventon') continue; // skip for eventon
					
					echo $views->get_html(
						'evo_addon_box',
						array(
							'slug'				=>$slug,
							'product'			=>$product,
							'active_plugins'	=>$active_plugins
						)
					);
					
					$count++;
				} //endforeach

			$content = ob_get_clean();

			$return_content = array(
				'content'=> $content,
				'status'=>true
			);			
			wp_send_json($return_content);	wp_die();
		}

	// get eventon product license info
		public function admin_lic_info(){

			// Validate request
			EVO()->helper->validate_request('nn','eventon_admin_nonce', 'edit_eventons', true ,true);		

			$post_data = EVO()->helper->sanitize_array( $_POST);
			$slug = $post_data['slug'];
			
			ob_start();

			$product = new EVO_Product_Lic($slug);

			EVO()->elements->start_table_header('evolic_info',array(
				'field'=>'Field',
				'value'=>'Value'
			));

			$lic_key = $product->get_license();
			
			$rows = array(
				array(
					'field'=> 'License Status',
					'value'=> $product->get_license_status()
				),
				array(
					'field'=> 'Remote Validity',
					'value'=> $product->get_remote_validity()
				),
				array(
					'field'=>'License Key',
					'value'=> $product->get_license() ."<i class='far fa-copy evo_copycontent evocurp evohoop7 evomarl10 evotooltipfree' data-d='".__('Copy License key','eventon')."' data-val='{$lic_key}'></i>"
				)
			);

			if( $slug == 'eventon'){
				$rows[]= array('field'=>'Purchaser Username','value'=> $product->get_prop('envato_username'));
				$rows[]= array('field'=>'Number of Sites Allowed','value'=> '1');
			}else{
				$rows[]= array('field'=>'Purchaser Email','value'=> $product->get_prop('email'));
				$rows[]= array('field'=>'Addon ID','value'=> $product->get_prop('product_id'));
				$rows[]= array('field'=>'Addon Instance','value'=> $product->get_prop('instance'));
			}

			EVO()->elements->table_rows($rows);
			EVO()->elements->table_footer(

			);

			wp_send_json(array(
				'status'=>'good', 'content'=> ob_get_clean(),
			)); wp_die();

		}

		public function lics_code(){
			// Validate request
			EVO()->helper->validate_request('nn','eventon_admin_nonce', 'edit_eventons', true ,true);		

			$evo_products = new EVO_Product_Lic('all');
		}

	// saving general settings -- @added 4.8 @updated 4.8.1		
		
		// loadin new language
		public function settings_load_new_lang(){

		}

		// save language settings
		public function settings_save(){

			// Validate request
			EVO()->helper->validate_request('evoajax','eventon_settings_save_nonce', 'edit_eventons', true ,true);		

			// Decode JSON data
		    $form_data = json_decode(stripslashes($_POST['formData']), true);
		    if (json_last_error() !== JSON_ERROR_NONE) {
		        wp_send_json_error(array('message' => 'Invalid JSON data'));
		        wp_die();
		    }

		    // get current tab
		    $current_tab = (!empty($_POST['tab']))? sanitize_text_field($_POST['tab']): 'evcal_1';

		    $help = new evo_helper();
		    $new_settings = array();

		    // load existing settings values
		    	EVO()->cal->set_cur( $current_tab );
		    	$saved_settings = EVO()->cal->get_op( $current_tab );
				$saved_settings = !empty($saved_settings) && is_array($saved_settings)? $saved_settings : array();


		    // for language settings
			    if( $current_tab == 'evcal_2'):
					$_lang_version = (!empty($_POST['lang']))? sanitize_text_field($_POST['lang']): 'L1';
					$sanitized_form_data = array();

					// Process duplicates and sanitize each value
				    foreach ($form_data as $itemkey => $itemvalue ) {

				    	// skip saving unnecessary text
				    	if( in_array($itemkey, array('action','option_page','_wp_http_referer','_wpnonce','evcal_noncename','evo_current_lang','evo_translated_text'))) continue;

				        if( !empty($itemvalue )) {
				            $key = sanitize_text_field( $itemkey );
				            $value = sanitize_textarea_field( $itemvalue );

				            if (strpos($key, '_v_') !== false) {
				                $key = str_replace('_v_', '', $key);
				            }

				            $sanitized_form_data[$key] = $value;
				        }
				    }


					$lang_opt = get_option('evcal_options_evcal_2');
					if(!empty($lang_opt) ){
						$new_settings[$_lang_version] = $sanitized_form_data;
						$new_settings = array_merge($lang_opt, $new_settings);
					}else{
						$new_settings[$_lang_version] = $sanitized_form_data;
					}

					// Update the option with sanitized data
    				update_option('evcal_options_evcal_2', $new_settings);

    		// all other settings
				else:
					

					// fields to skip sanitization @u 4.6
					$none_san_fields = apply_filters('evo_settings_non_san_fields', array('evo_etl','evcal_top_fields','evcal_sort_options'), $current_tab);
					
					// field keys with html
					$html_fields = apply_filters( 'evo_settings_html_fields', array());

					//$new_settings = array();
					$new_settings = $saved_settings;

					// process all form data
					foreach($form_data as $settings_field => $settings_value ){

						// strip [] from feild name
						if( strpos($settings_field, '[]') !== false ){
							$settings_field = str_replace('[]','', $settings_field);							
						}

						// skip fields
						if(in_array($settings_field, array( 
							'option_page', 'action','_wpnonce','_wp_http_referer','evcal_noncename','qm-theme','qm-editor-select'
						))){	continue;	}


						// HTML fields 4.6
						if( in_array( $settings_field, $html_fields )){
							$new_settings[ $settings_field ] = $help->sanitize_html( $settings_value );
							continue;
						}

						// none sanitize fields
						if( in_array($settings_field, $none_san_fields) ){
							$new_settings[ $settings_field ] = $settings_value;

						// If value contains 'http' or 'https', treat it as a URL
					    } elseif (is_string($settings_value) && (strpos($settings_value, 'http') !== false)) {
					        $new_settings[$settings_field] = esc_url($settings_value);

					    // Sanitize normal text fields
					    } else {
					        $new_settings[$settings_field] = !is_array($settings_value) ? sanitize_text_field($settings_value) : $settings_value;
					    }	
						
					}

					// check isolatedly saved setting values and include them
						foreach( array('evo_ecl','evowhs') as $_iso_field){
							if( array_key_exists( $_iso_field, $saved_settings)){

								$new_settings[ $_iso_field ] = $saved_settings[ $_iso_field ];
							}
						}

					// for general settings evcal_1
						if( $current_tab == 'evcal_1'){
							// update custom meta fields count into settings
							$new_settings['cmd_count'] = evo_calculate_cmd_count();
						}


					// plug
						do_action('evo_before_settings_saved', $current_tab, '',  $new_settings);


					// save settings
						EVO()->cal->set_cur( $current_tab );

						$new_settings = apply_filters('evo_save_settings_optionvals', $new_settings, $current_tab, $form_data);

						EVO()->cal->set_option_values( $new_settings );
						//EVO_Debug($new_settings);


					// save custom styles and php code
						if( isset($new_settings['evcal_styles']) ){
							$styles = urldecode($new_settings['evcal_styles']); // Decode the encoded CSS
						    $sanitized_styles = eventon_sanitize_css($styles);
						   
						   	if (!empty($sanitized_styles)) {
						        update_option('evcal_styles', $sanitized_styles); // Save non-empty styles
						    } else {
						        delete_option('evcal_styles'); // Remove the option if styles are empty
						    }

						}

						if( isset($new_settings['evcal_php']) )	
							update_option('evcal_php', strip_tags(stripslashes($new_settings['evcal_php'])) );

					// update dynamic styles after settings are saved to options field
						if( $current_tab == 'evcal_1' || $current_tab == 'evcal_3'){

							// add dynamic styles to options
							EVO()->evo_admin->update_dynamic_styles();

							// update the dynamic styles .css file or write to headr
							EVO()->evo_admin->generate_dynamic_styles_file();
						}


					// update global settings values
					$GLOBALS['EVO_Settings'][ 'evcal_options_' .$current_tab] = $new_settings;

				endif;
			
			

			$return_content = array(
				'form_data'=> $form_data,
				'new_settings'=> $new_settings,
				'content'=> '',
				'msg'=> __('Saved Successfully','eventon'),
				'status'=>'good'
			);			
			wp_send_json($return_content);	wp_die();
		}


	/** Feature an event from admin */
		public function eventon_feature_event() {

			// Validate request
			EVO()->helper->validate_request('_wpnonce','eventon-feature-event', 'edit_eventons', true ,true,'message');		

			$post_id = isset( $_GET['eventID'] ) && (int) $_GET['eventID'] ? (int) $_GET['eventID'] : '';

			if (!$post_id) wp_die( __( 'Event id is missing!', 'eventon' ) );

			$post = get_post($post_id);

			if(!$post) wp_die( __( 'Event post doesnt exists!'),'eventon');
			if( $post->post_type !== 'ajde_events' ) wp_die( __('Post type is not an event', 'eventon' ) );

			$featured = get_post_meta( $post->ID, '_featured', true );

			wp_safe_redirect( remove_query_arg( array('trashed', 'untrashed', 'deleted', 'ids'), wp_get_referer() ) );
			
			if( $featured == 'yes' )
				update_post_meta($post->ID, '_featured', 'no');
			else
				update_post_meta($post->ID, '_featured', 'yes'); 

			wp_safe_redirect( remove_query_arg( array('trashed', 'untrashed', 'deleted', 'ids'), wp_get_referer() ) );
			exit;
		}
	
	// Diagnose
		// send test email
		public function admin_test_email(){

			// Validate request
			EVO()->helper->validate_request('nonce','eventon_admin_nonce', 'edit_eventons', true ,true );		

			
			$post_data = EVO()->helper->sanitize_array( $_POST);
			$email_address = $post_data['email'];

			$result = wp_mail($email_address, 'This is a Test Email', 'Test Email Body', array('Content-Type: text/html; charset=UTF-8') );
			
			$ts_mail_errors = array();
			if(!$result){
				global $ts_mail_errors;
				global $phpmailer;

				if (!isset($ts_mail_errors)) $ts_mail_errors = array();

				if (isset($phpmailer)) {
					$ts_mail_errors[] = $phpmailer->ErrorInfo;
				}
			}

			wp_send_json(array(
				'status'=> $result ? 'good':'bad',
				'msg'=> ($result?'Email Sent': 'Email was not sent'),
				'error'=>$ts_mail_errors
			));		
			wp_die();
		}

		// system log
		public function admin_system_log(){

			// Validate request
			EVO()->helper->validate_request('nn','eventon_admin_nonce', 'edit_eventons', true ,true );		
			
			$html = '';
			ob_start();

			echo EVO_Error()->_get_html_log_view();

			echo "<div class='evopadt20'>";

				EVO()->elements->print_trigger_element(array(
					'extra_classes'=>'',
					'title'=>__('Flush Log','eventon'),
					'dom_element'=> 'span',
					'uid'=>'evo_admin_flush_log',
					'lb_class' =>'evoadmin_system_log',
					'lb_load_new_content'=> true,	
					'ajax_data' =>array('action'=>'eventon_admin_system_log_flush'),
				), 'trig_ajax');

			echo "</div>";


			$html = ob_get_clean();

			wp_send_json(array(
				'status'=>'good',
				'content'=> $html
			));
			wp_die();
		}
		public function admin_system_log_flush(){
			// Validate request
			EVO()->helper->validate_request('nn','eventon_admin_nonce', 'edit_eventons', true ,true );		

			EVO_Error()->_flush_all_logs();

			$html = EVO_Error()->_get_html_log_view();
			
			wp_send_json(array(
				'status'=>'good',
				'msg'=> __('All system logs flushed'),
				'content'=> $html
			));
			wp_die();
		}

		// environment @u 4.5.5
		public function admin_get_environment(){

			// Validate request
			EVO()->helper->validate_request('nn','eventon_admin_nonce', 'edit_eventons', true ,true );		


			require_once(AJDE_EVCAL_PATH.'/includes/admin/ajax/class-ajax-environment.php');

			$ajax = new EVO_Admin_Ajax_Environment();
						
			$html = ''; 
			$data = $ajax->get_data();

			$html = '<div class="evo_environment">';
			foreach($data as $D=>$V){

				if( strpos($D, 'shead') !==  false ){ 
					$html .= "<p class='shead'>". $V ."</p>"; continue;
				}

				$D = str_replace('_', ' ', $D);
				$html .= "<p><span>".$D."</span><span class='data'>". $V ."</span></p>";
			}


			$html .= "</div>";
				
			wp_send_json(array(
				'status'=>'good',
				'content'=> $html,
			)); wp_die();
		}

		
}
new EVO_admin_ajax();
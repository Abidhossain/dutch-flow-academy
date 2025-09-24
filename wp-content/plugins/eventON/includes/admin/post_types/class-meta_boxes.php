<?php
/**
 * Meta boxes for ajde_events
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	EventON/Admin/ajde_events
 * @version     4.9.10
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class evo_event_metaboxes{
	public $EVENT = false;
	public $event_data = array();
	private $helper;

	public function __construct(){
		add_action( 'add_meta_boxes', array($this,'metabox_init') );
		add_action( 'save_post', array($this,'eventon_save_meta_data'), 1, 2 );
		//add_action( 'post_submitbox_misc_actions', array($this,'ajde_events_settings_per_post' ));

		add_filter('evo_eventedit_pageload_dom_data',array($this, 'eventedit_pageload_data'), 10, 4);
		add_filter('evo_eventedit_pageload_dom_ids', array($this, 'eventedit_domids'), 12,3);
	}

	// INIT meta boxes
		function metabox_init(){

			global $post;

			// get post type
			$postType = !empty($_GET['post_type'])? sanitize_text_field($_GET['post_type']): false;	   
	   		if(!$postType && !empty($_GET['post']))   	$postType = get_post_type( sanitize_text_field($_GET['post']));

	   		if( !$postType) return false;
	   		if( $postType != 'ajde_events' ) return false;

	   		// Custom editor // 2.8.5
	   		wp_enqueue_style('evo_wyg_editor');
	   		wp_enqueue_script('evo_wyg_editor');
			
			// initiate a event object
	   		$this->EVENT = $this->EVENT ? $this->EVENT: new EVO_Event($post->ID);
	   		$this->event_data = $this->EVENT->get_data();

	   		$GLOBALS['EVO_Event'] = $this->EVENT;

			$evcal_opt1= get_option('evcal_options_evcal_1');

			// ajde_events meta boxes
			add_meta_box('ajdeevcal_mb1', __('Main Event Details','eventon'), array($this,'ajde_evcal_show_box'),'ajde_events', 'normal', 'high');

			add_meta_box('ajdeevcal_mb1_cmf', __('Event Custom Meta Fields','eventon'), array($this,'ajde_evcal_show_box_cmf'),'ajde_events', 'normal', 'high');	

			add_meta_box('ajdeevcal_mb3jd',__('Options','eventon'), 
				array($this,'meta_box_event_options'),'ajde_events', 'side', 'low');
			
			add_meta_box('ajdeevcal_mb2',__('Colors','eventon'), 
				array($this,'meta_box_event_color'),'ajde_events', 'side', 'core');
			
			add_meta_box('ajdeevcal_mb_ei',__('Images +','eventon'), 
				array($this,'metabox_event_extra_images'),'ajde_events', 'side', 'low');
			
			
			// if third party is enabled
			if( EVO()->cal->check_yn('evcal_paypal_pay','evcal_1')){
				add_meta_box('ajdeevcal_mb3',__('Third Party Settings','eventon'), array($this,'ajde_evcal_show_box_3'),'ajde_events', 'normal', 'high');
			}


			// @updated 2.6.7 to pass event object
			do_action('eventon_add_meta_boxes', $this->EVENT);
		}

	// event edit ajax load
		function eventedit_domids($array){
			$array['evo']= 'evo_pageload_data';
			$array['evo_color']= 'evo_mb_color';
			$array['evo_options']= 'evo_mb_options';
			return $array;
		}
		function eventedit_pageload_data($array, $postdata, $EVENT, $dom_id_array){


			// load event edit main content
			if( array_key_exists( 'evo', $dom_id_array )){
				ob_start();
				include_once 'class-meta_box_all.php';
				$items = ob_get_clean();

				$array['evo'] = $items;	
			}

			// event colors
			if( array_key_exists( 'evo_color', $dom_id_array )){
				ob_start();
				include_once 'class-meta_boxes-color.php';
				$items_color = ob_get_clean();

				$array['evo_color'] = $items_color;
			}

			// event options
			if( array_key_exists( 'evo_options', $dom_id_array )){
				ob_start();
				include_once 'class-meta_boxes-options.php';
				$items_color = ob_get_clean();

				$array['evo_options'] = $items_color;
			}
			
			return $array;
		}

	// extra event images
		function metabox_event_extra_images(){
			include_once 'class-meta_boxes-extraimages.php';			
		}

	// EXTRA event settings for the page
		function meta_box_event_options(){
			EVO()->admin_elements->print_loading_metabox_skeleton(
				array(
					'id'=> 'evo_mb_options',
					'class'=>'evo_mb_options',
					'nonce_key'=> '',
				)
			);
		}
	
	// Event Color Meta Box	
		public function meta_box_event_color(){

			EVO()->admin_elements->print_loading_metabox_skeleton(
				array(
					'id'=> 'evo_mb_color',
					'class'=>'evo_mb_color',
					'nonce_key'=> '',
				)
			);
			
		}

	// MAIN META BOX CONTENT
		public function ajde_evcal_show_box(){
			/*
			lightbox based event edit settings - coming soon
			<div class=''>
				<?php 
				EVO()->elements->print_trigger_element(array(
					'title'=> __('Open Event Settings'),
					'lb_class'=>'evo_event_edit_box',
					'lb_title'=> __('Edit Event Settings'),
					'uid'=>'evo_edit_event_open',
					'ajax_data'=> array(
						'action'=>'eventon_eventedit_settings',
						'event_id'=> $p_id
					)
				),'trig_lb');

				?>
			</div>
			*/

			$p_id = get_the_ID();
			$closedmeta = eventon_get_collapse_metaboxes($p_id);
			$closedmeta = is_array($closedmeta)? implode(',', $closedmeta):'';

			EVO()->admin_elements->print_loading_metabox_skeleton(
				array(
					'id'=> 'evo_mb',
					'post_id'=> $p_id,
					'loader_content_id'=> 'evo_pageload_data',
					'class'=>'eventon_mb',
					'nonce_action'=> 'eventon_save_event_post',
					'nonce_name'=> 'evo_noncename',
					'hidden_fields'=> array(
						'evo_event_id'=> $p_id,
						'evo_collapse_meta_boxes'=> $closedmeta
					)
				)
			);

  
		}

		// for custom meta boxes
		function ajde_evcal_show_box_cmf(){
			?>
			<div id='evo_mb' class='eventon_mb'>
				<?php include_once( 'class-meta_boxes-cmf.php');?>
			</div>
			<?php
		}

	// THIRD PARTY event related settings 
		function ajde_evcal_show_box_3(){	
			
			
			$evcal_opt1= get_option('evcal_options_evcal_1');
				$evcal_opt2= get_option('evcal_options_evcal_2');
				
				// Use nonce for verification
				//wp_nonce_field( plugin_basename( __FILE__ ), 'evo_noncename_mb3' );
				
				// The actual fields for data entry
				$ev_vals = $this->event_data;
			
			?>
			<table id="meta_tb" class="form-table meta_tb evoThirdparty_meta" >
				<?php
					// (---) hook for addons
					if(has_action('eventon_post_settings_metabox_table'))
						do_action('eventon_post_settings_metabox_table');
				
					if(has_action('eventon_post_time_settings'))
						do_action('eventon_post_time_settings');

				// PAYPAL
					if($evcal_opt1['evcal_paypal_pay']=='yes'):
					?>
					<tr>
						<td colspan='2' class='evo_thirdparty_table_td'>
							<div class='evo3rdp_header'>
								<span class='evo3rdp_icon'><i class='fab fa-paypal'></i></span>
								<p><?php _e('Paypal "BUY NOW" button','eventon');?></p>
							</div>	
							<div class='evo_3rdp_inside'>
								<p class='evo_thirdparty'>
									<label for='evcal_paypal_text'><?php _e('Text to show above buy now button','eventon')?></label><br/>			
									<input type='text' id='evcal_paypal_text' name='evcal_paypal_text' value='<?php echo (!empty($ev_vals["evcal_paypal_text"]) )? $ev_vals["evcal_paypal_text"][0]:null?>' style='width:100%'/>
								</p>
								<p class='evo_thirdparty'><label for='evcal_paypal_item_price'><?php _e('Enter the price for paypal buy now button <i>eg. 23.99</i> (WITHOUT currency symbol)')?><?php EVO()->elements->tooltips(__('Type the price without currency symbol to create a buy now button for this event. This will show on front-end calendar for this event','eventon'),'',true);?></label><br/>			
									<input placeholder='eg. 29.99' type='text' id='evcal_paypal_item_price' name='evcal_paypal_item_price' value='<?php echo (!empty($ev_vals["evcal_paypal_item_price"]) )? $ev_vals["evcal_paypal_item_price"][0]:null?>' style='width:100%'/>
								</p>
								<p class='evo_thirdparty'>
									<label for='evcal_paypal_email'><?php _e('Custom Email address to receive payments','eventon')?><?php EVO()->elements->tooltips('This email address will override the email saved under eventON settings for paypal to accept payments to this email instead of paypal email saved in eventon settings.','',true);?></label><br/>			
									<input type='text' id='evcal_paypal_email' name='evcal_paypal_email' value='<?php echo (!empty($ev_vals["evcal_paypal_email"]) )? $ev_vals["evcal_paypal_email"][0]:null?>' style='width:100%'/>
								</p>
							</div>		
						</td>			
					</tr>
					<?php endif; ?>
				</table>
			<?php
		}
		
	// Save the Event data meta box
		public function get_event_cpt_meta_keys($event_id){
			return apply_filters('eventon_event_metafields', array(
				'evcal_event_color','evcal_event_color_n',
				'evcal_event_color2','evcal_event_color_n2',
				'evcal_exlink','evcal_lmlink','evcal_subtitle',
				'evcal_hide_locname','evcal_gmap_gen','evcal_name_over_img', 'evo_access_control_location',
				'evcal_mu_id','evcal_paypal_item_price','evcal_paypal_text','evcal_paypal_email',
				'evcal_repeat','_evcal_rep_series','_evcal_rep_endt','_evcal_rep_series_clickable','evcal_rep_freq','evcal_rep_gap','evcal_rep_num',
				'evp_repeat_rb','evo_repeat_wom','evo_rep_WK','evp_repeat_rb_wk','evo_rep_WKwk',
				'evcal_lmlink_target','_evcal_exlink_target','_evcal_exlink_option',
				'evo_hide_endtime','evo_span_hidden_end','_time_ext_type',
				'evo_evcrd_field_org','evo_event_org_as_perf','evo_event_timezone',
				'_evo_virtual_endtime',

				'evo_exclude_ev',				
				'ev_releated',				
			), $event_id);
		}

		public function event_save_datetime( $EVENT){

			$proper_time = 	evoadmin_get_unix_time_fromt_post($EVENT->ID);

			// if Repeating event save repeating intervals
				if( eventon_is_good_repeat_data()  ){

					if(!empty($proper_time['unix_start'])){

						$unix_E = $end_range = (!empty($proper_time['unix_end']))? $proper_time['unix_end']: $proper_time['unix_start'];
						$repeat_intervals = eventon_get_repeat_intervals($proper_time['unix_start'], $unix_E);

						// save repeat interval array as post meta
						if ( !empty($repeat_intervals) ){

							$E = end($repeat_intervals);
							$end_range = $E[1];

							$EVENT->set_meta( 'repeat_intervals', $repeat_intervals);
						}else{
							$EVENT->del_prop( 'repeat_intervals');
						}
					}
				}
			// full time converted to unix time stamp
				if ( !empty($proper_time['unix_start']) )
					$EVENT->set_meta( 'evcal_srow', $proper_time['unix_start']);
				
				if ( !empty($proper_time['unix_end']) )
					$EVENT->set_meta( 'evcal_erow', $proper_time['unix_end']);


			// save virtual end time
				if( isset($proper_time['unix_vir_end']) && !empty($proper_time['unix_vir_end'])){
					$EVENT->set_meta( '_evo_virtual_erow', $proper_time['unix_vir_end']);
				}

			// save adjusted event times
				foreach( array( 'unix_start_ev', 'unix_end_ev', 'unix_vend_ev') as $f){
					if ( !empty($proper_time[ $f ]) ) 
						$EVENT->set_meta(  '_'.$f , $proper_time[ $f ]);
				}
		}

		public function eventon_save_meta_data($post_id, $post){

			// Verifications
				if($post->post_type!='ajde_events')	return;					

				
				// Stop WP from clearing custom fields on autosave
				if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
					return;

				// Prevent quick edit from clearing custom fields
				if (defined('DOING_AJAX') && DOING_AJAX)
					return;
				
				
				// verify this came from the our screen and with proper authorization,
				// because save_post can be triggered at other times
				if( empty($_POST['evo_noncename']) || !wp_verify_nonce( $_POST['evo_noncename'], 'eventon_save_event_post' ) ){
					EVO_Debug('Nonce verification failed for saving event post');
					return;			
				}


				// Check permissions
				if ( !current_user_can( 'edit_post', $post_id ) ){
					EVO_Debug('Current user do not have permission to save event');
					return;	
				}


			global $pagenow;
			$_allowed = array( 'post-new.php', 'post.php' );
			if(!in_array($pagenow, $_allowed)) return;

			// initiate the event 
			$this->EVENT = $EVENT = new EVO_Event($post_id, '', 0, true, $post );

			$HELP = new evo_helper();
			$post_data = $HELP->recursive_sanitize_array_fields( $_POST );
			$disable_fusion_save = EVO()->cal->check_yn('evo_fusion_off','evcal_1');
						
			// $_POST FIELDS array
				$fields_ar = $this->get_event_cpt_meta_keys( $EVENT->ID );

			
			// array of post meta fields that should be deleted from event post meta
				foreach(array(
					'evo_location_tax_id','evo_organizer_tax_id','_cancel'
				) as $ff){
					delete_post_meta($post_id, $ff);
				}

			// Backward compatible cancel event u4.2.3
				if(!isset($post_data['_status']) && isset($post_data['_cancel']) && $post_data['_cancel'] == 'yes'){
					$post_data['_status'] = 'cancelled';
				}

			// Add post_data keys with _ to the array
				foreach($post_data as $F=>$V){	if(substr($F, 0,1) === '_')		$fields_ar[] = $F;	}

			// remove duplicate field keys
				$fields_ar = array_unique($fields_ar);

			// process event date and time pieces into unix values @4.9	
				$this->event_save_datetime( $EVENT);

			// save previous start date for reschedule events
				if( isset($post_data['_status']) && $post_data['_status'] == 'rescheduled' && isset($post_data['event_prev_date_x'])
				){
					$date = $post_data['event_prev_date_x'];
				}

			// event images processing
				if(!empty($post_data['_evo_images'])){
					$imgs = explode(',', $post_data['_evo_images']);
					$imgs = array_filter($imgs); 
					$str = ''; $x = 1;
					foreach($imgs as $IM){		
						//if( $x > apply_filters('evo_event_images_max',3)) continue;				
						$str .= $IM .','; $x++;
					}
					$EVENT->set_prop( '_evo_images',$str);
				}else{
					$EVENT->del_prop( '_evo_images');
				}
				

			// run through all the event meta fields @u4.5.5
				foreach($fields_ar as $f_val){
					
					// make sure values are not empty at $_POST level
					if(!empty($_POST[$f_val])){

						$post_value = ( $post_data[$f_val]);

						// urls passed on @4.7.4
						if( $f_val == 'evcal_exlink' && $post_data['_evcal_exlink_option']=='2'
							|| in_array($f_val, array('evcal_lmlink'))
						){
							$EVENT->set_prop( $f_val , esc_url_raw( $_POST[ $f_val ] )  );
							continue;
						}

						// fields to skip saving @4.9
						if( in_array($f_val, apply_filters('evo_save_event_meta_skips', array()))){
							continue;
						}

						// skip fusion/avada data if enabled
						if( $disable_fusion_save && (stripos($f_val, 'fusion') !== false) ) continue;

						// for fields with HTML content @since 4.3.3
						if( in_array($f_val, apply_filters('evo_event_metafields_htmlcontent',
							array('evcal_subtitle')
						) ) ){

							$EVENT->set_prop($f_val, $HELP->sanitize_html( $_POST[ $f_val ] ));
							continue;
						}

						$EVENT->set_prop( $f_val , $post_value);

						// ux val for single events linking to event page	
						if($f_val=='evcal_exlink' && $post_data['_evcal_exlink_option']=='4'){
							$EVENT->set_prop( 'evcal_exlink' , get_permalink($post_id) );
						}

					}else{
						$EVENT->del_prop( $f_val);
					}					
				}


			// Save all event data values
				if( isset($post_data['_edata']) ){
					$EVENT->set_prop('_edata', $post_data['_edata']);
				}							
			
			// Other data							
				//set event color code to 1 for none select colors
					if ( !isset( $post_data['evcal_event_color_n'] ) )	$EVENT->set_prop( 'evcal_event_color_n',1);
									
				// save featured event data default value no
					if(empty( $post_data['_featured']) )	$EVENT->set_prop( '_featured','no');

				// language corresponding
					if(empty($post_data['_evo_lang']))	$EVENT->set_prop( '_evo_lang','L1');
			
						
			// (---) hook for addons
			do_action('eventon_save_meta', $fields_ar, $post_id, $this->EVENT, $post_data);

			// save user closed meta field boxes
			if(!empty($post_data['evo_collapse_meta_boxes']))
				eventon_save_collapse_metaboxes($post_id, $post_data['evo_collapse_meta_boxes'],true );
				
		}

	// Process metabox content
	// @since 4.2.3
		function process_content($array){
			$output = '';
			
			ob_start();

			foreach($array as $mBOX):

				if( empty($mBOX['content'])) continue;

				$closed = isset($mBOX['close']) && $mBOX['close'] ? 'closed' : '';					
				
				?>
				<div class='evomb_section evo_borderb' id='<?php echo $mBOX['id'];?>'>			
					<?php 
					$this->event_metabox_parts_header( $mBOX );		
					?>
					<div class='evomb_body <?php echo $closed;?>' box_id='<?php echo $mBOX['id'];?>'>
						<?php	 echo $mBOX['content'];?>
					</div>
				</div>
			<?php 
			endforeach;

			return ob_get_clean();
		}

		public function event_metabox_parts_header(  $data_array ){

			extract( array_merge(array(
				'variation' => '',
				'iconURL'=> '',
				'guide'=> '',
				'hiddenVal'=> '',
				'visibility_type'=> '',
				'iconPOS'=> '',
				'close'=> '',
				'name'=> '',
			),$data_array));


			$closed = !empty($close) && $close ? 'closed' : '';
			$icon_class = (!empty($iconPOS))? 'evIcons':'evII';
			$icon_style = (!empty($iconURL))?
						'background-image:url('.$iconURL.')'
						:'background-position:'.$iconPOS;
			$hiddenVal = (!empty($hiddenVal))?	'<span class="hiddenVal">'.$hiddenVal.'</span>':null;
			$guide = (!empty($guide))? 	EVO()->elements->tooltips($guide):null;

			// visibility type ONLY for custom meta fields
			$visibility_types = array('all'=>__('Everyone','eventon'),'admin'=>__('Admin Only','eventon'),'loggedin'=>__('Loggedin Users Only','eventon'));
			$visibility_type = (!empty($visibility_type))? "<span class='visibility_type'>".__('Visibility Type:','eventon').' '.$visibility_types[$visibility_type] .'</span>': false;
					

			?>
			<div class='evomb_header evopad5 evoposr evoboxcb evodfx evofx_dr_r evofx_ai_c evogap10<?php echo $closed;?>'>
				<?php // custom field with icons
					if(!empty($variation) && $variation	=='customfield'):?>	
					<span class='evomb_icon evofz18 <?php echo $icon_class;?>'><i class='fa <?php echo !empty($iconURL)? $iconURL: ''; ?>'></i></span>
					
				<?php else:	?>
					<span class='evomb_icon evofz18 <?php echo $icon_class;?>' style='<?php echo $icon_style?>'></span>
				<?php endif; ?>
				<p class='evomb_header_label evomar0i'><?php echo !empty($name)? $name: '';?><?php echo $hiddenVal;?><?php echo $guide;?><?php echo $visibility_type;?></p>
			</div>
			<?php 
		}
}

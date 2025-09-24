<?php
/**
* Calendar single event's html structure 
* @version 4.8
*/

class EVO_Cal_Event_Structure extends EVO_Cal_Event_Structure_Schema{
	public $EVENT;
	public $timezone = '';
	public $ev_tz = '';
	public $timezone_data = array();

	public $OO = array();
	public $OO2 = array();

	public $helper, $help;
	
	public function __construct($EVENT=''){

		$this->timezone_data = array(
			'__f'=>'YYYY-MM-DD h:mm:a',
			'__df'=> 'YYYY-MM-DD',
			'__tf'=> 'h:mm:a',
			'__t'=> evo_lang('View in my time')
		);

		if(!empty($EVENT)) $this->EVENT = $EVENT;

		$this->timezone = get_option('gmt_offset', 0);
		$this->ev_tz = $EVENT->get_timezone_key();

		$this->helper = $this->help = new evo_helper();
	}


// HTML EventTop
	function get_eventtop_tags( $EVENT, $object ){
		$eventtop_tags = array();
		extract( $object );

		// status
		if( $_status && $_status != 'scheduled'){
			$eventtop_tags['status'] = array(
				$EVENT->get_event_status_lang(),
				$_status
			);
		}

		// featured
		if(!empty($featured) && $featured){
			$eventtop_tags['featured'] = array(evo_lang('Featured')	);
		}

		// completed
		if(!empty($completed) && $completed){
			$eventtop_tags['completed'] = array(evo_lang('Completed')	);
		}

		// repeating event tag
		if( $EVENT && $EVENT->is_repeating_event()){
			$eventtop_tags['repeating'] = array(evo_lang('Repeating Event')	);
		}

		// virtual
		if( $EVENT && $EVENT->get_attendance_mode() != 'offline' ){
			if( $EVENT->is_mixed_attendance()){
				$eventtop_tags['virtual_physical'] = 
					array(evo_lang('Virtual/ Physical Event'), 'vir'	);
			}else{
				$eventtop_tags['virtual'] = array(evo_lang('Virtual Event'), 'vir'	);
			}							
		}

		return $eventtop_tags;
	}
	

	// HTML for various event top blocks
		function get_eventtop_types($tax_field, $object, $EVENT){
			$OT = '';

			if( empty($object->$tax_field)) return $OT;

			$tax_data = $object->$tax_field;
			if( !isset( $tax_data['terms'] )) return $OT;

			$OT .="<span class='evoet_eventtypes level_4 evcal_event_types ett{$tax_data['tax_index']}'><em><i>{$tax_data['tax_name']}</i></em>";

			foreach($tax_data['terms'] as $term_id=>$TD){
				$OT .="<em data-filter='{$TD['s']}' data-v='{$TD['tn']}' data-id='{$term_id}' class='evoetet_val evoet_dataval'>".$TD['i']. $TD['tn'] . $TD['add'] ."</em>";
			}
			$OT .="</span>";

			
			return $OT;
		}

		function get_eventtop_cmf_html($v, $EVENT){
			$OT = $icon_string = '';
			if( empty($v['value'])) return $OT;

			// user loggedin visibility restriction
			if( !empty($v['login_needed_message']) ) return $OT;


			// user role restriction validation
			if( ($v['visibility_type'] =='admin' && !current_user_can( 'manage_options' ) ) ||
				($v['visibility_type'] =='loggedin' && !is_user_logged_in() && empty($v['login_needed_message']))
			) return $OT;
			
			// custom icon
			if( !empty($v['imgurl']) && EVO()->cal->check_yn('evo_eventtop_customfield_icons','evcal_1') ){
				$icon_string ='<i class="fa '. $v['imgurl'] .'"></i>'; 
			}

			$cmf_value = $EVENT->process_dynamic_tags( $v['value'] );


			
			if( $v['type'] == 'button'){									
				$OT.= "<span class='evoet_cmf'><em class='evcal_cmd evocmd_button' data-href='". ($v['valueL'] ). "' data-target='". ($v['_target']). "'>" . $icon_string . $cmf_value ."</em></span>";
			
			}elseif( $v['type'] == 'textarea_basic' || $v['type'] == 'textarea' || $v['type'] == 'textarea_trumbowig'){

				$_x = $v['x'];
				
				$cmf_data = $EVENT->get_custom_data($v['x']);
				
				// remove breakable html elements from custom meta value @since 4.3.3
				$cmf_value_pro = $this->helper->sanitize_html_for_eventtop( $cmf_value );

				$OT.= "<span class='evoet_cmf'><em class='evcal_cmd marr10'>". $icon_string . "<i>".  $v['field_name'].'</i></em><em>'.  $cmf_value_pro  ."</em>
					</span>";		

			}else{	
				$OT.= "<span class='evoet_cmf'><em class='evcal_cmd marr10'>". $icon_string . "<i>".  $v['field_name'].'</i></em><em>'. $cmf_value ."</em>
					</span>";									
			}

			return $OT;
		}

	// return html eventtop with filled in dynamic data
		function get_dynamic_eventtop($eventdata, $layout){

		}

	function get_event_top($EventData, $eventtop_fields){
			
		$EVENT = $this->EVENT;
		$SC = EVO()->calendar->shortcode_args;
		$OT = '';		
		
		$evOPT = EVO()->calendar->evopt1;
		$evOPT2 = EVO()->calendar->evopt2;

		extract($EventData);

		EVO()->cal->set_cur('evcal_1');

		// open for pluggability 
			$eventtop_fields = apply_filters('evoet_data_structure', $this->custom_eventtop_layout( $eventtop_fields, $SC) , $EventData, $EVENT);

			$layout = $eventtop_fields['layout'];
			//print_r($layout);

		// for each column in eventtop
			for($x = 0; $x<5; $x++){
				if(!isset( $layout['c'.$x] )) continue;

				$additional_class = '';
				if( $x == '3'){
					$show_widget_eventtops = (!empty($evOPT['evo_widget_eventtop']) && $evOPT['evo_widget_eventtop']=='yes')? '':' hide_eventtopdata ';
					$additional_class .= 'evcal_desc'. $show_widget_eventtops;
				} 

				$inner_content = '';
				
				if( is_array( $layout['c'.$x] ) && isset( $layout['c'.$x]) ){

					foreach( $layout['c'.$x] as $field){
						// eventtop bubbles
						//if( $SC['eventtop_style'] == '3' && $field['f'] != 'day_block') continue;

						$inner_content .= $this->get_eventtop_item_html( $field['f'], $EventData, $eventtop_fields);
					}
				}

				if( empty($inner_content)) continue;

				$OT.= "<span class='evoet_c{$x} evoet_cx {$additional_class}'>";
				$OT .= $inner_content;

				$OT .= "</span>";
			}	

			// include event top data
				$OT .= $this->get_eventtop_item_html( 'data', $EventData, $eventtop_fields);	

		return $OT;
	}

	// @since 4.5
	function custom_eventtop_layout( $eventtop_fields, $SC){
		return $eventtop_fields;
	}

// EvnetCard HTML
	function get_event_card($array, $EventData, $evOPT, $evoOPT2, $ep_fields = ''){
		// INIT
			$EVENT = $this->EVENT;
			$ED = $EventData;
			$evoOPT2 = (!empty($evoOPT2))? $evoOPT2: '';
			$this->OO = $evOPT;
			$this->OO2 = $evoOPT2;
			
			$OT ='';
			$count = 1;
			$items = count($array);	

			//print_r($array);

			extract($EventData);

			$ep_fields = !empty($ep_fields)? explode(',', $ep_fields): false;
			
			// close button
			$close = "<div class='evcal_evdata_row evcal_close' title='".eventon_get_custom_language($evoOPT2, 'evcal_lang_close','Close')."'></div>";

			// additional fields array 
			$array = apply_filters('evo_eventcard_adds' , $array);

			//print_r($array);
			//print_r($EventData);

		ob_start();


		// get event card designer fields
		$eventcard_fields = EVO()->calendar->helper->get_eventcard_fields_array();

		//print_r($eventcard_fields);

		//if( !is_array($eventcard_fields)) return ob_get_clean();

		$processed_fields = array();		
		
		$rows = is_array($eventcard_fields) ? count($eventcard_fields) :0;
		$i = 1;
		foreach( $eventcard_fields as $R=>$boxes){
			
			$CC = '';
			$box_count = 0;
			
			$opened = false;

			foreach( $boxes as $B=>$box){

				if( !isset($box['n'])) continue;
				$NN = $box['n'];
				if( isset($box['h']) && $box['h'] =='y' ) continue;

				// get box data
					if( !array_key_exists($NN, $array)) continue;
					
					$BD = $array[ $NN ];
					// convert to an object
					$BDO = new stdClass();
					foreach ($BD as $key => $value){
						$BDO->$key = $value;
					}

				// if only specific fields set
					if( $ep_fields && !in_array($NN, $ep_fields) ) continue;

				// if already processed
					if( in_array( $NN, $processed_fields)) continue;
					$processed_fields[] = $NN;


				// box content
				$BCC = $this->get_eventcard_box_content( $NN, $BDO, $EVENT , $EventData);

				if( empty($BCC)) continue;

				// @since 4.2.3
				$BCC = apply_filters('evo_eventcard_box_html', $BCC, $box, $EVENT, $EventData);

				$color = isset($box['c']) ? $box['c']:'';

				if( $B == 'L1' || $B == 'R1'){
					$CC .= "<div class='evocard_box_h'>";
					$opened = true;
				}

				$CC .= "<div id='event_{$NN}' class='evocard_box {$NN}' data-c='". $color ."' 
					style='". (!empty($color) ? "background-color:#{$color}":'') ."'>". $BCC . "</div>";

				// stacked boxes close container
				if( $opened){
					if( (count($boxes) == 3 && ($B == 'L2' || $B == 'R2') ) ||
						( count($boxes) == 4 && ($B == 'L3' || $B == 'R3') ) ){
						$CC .= "</div>"; $opened = false;
					}
				}
				$box_count++;

			}

			if( $opened ) $CC .= "</div>";
			if( empty($CC)) continue;

			$row_class = array('evocard_row');
			if($box_count>1) $row_class[] ='bx'.$box_count;
			if($box_count>1) $row_class[] ='bx';
			if( array_key_exists('L1', $boxes)) $row_class[] = 'L';
			if($i == $rows)  $row_class[] = 'lastrow';

			echo "<div class='". implode(' ', $row_class) ."'>";
			echo $CC;
			echo "</div>";
			$i++;
		}

		echo "<button class='evo_card_row_end evcal_close' title='".eventon_get_custom_language($evoOPT2, 'evcal_lang_close','Close')."'></button>";

		return ob_get_clean();		
	}	

// return box HTML content using box field name
	function get_eventcard_box_content($box_name, $box_data, $EVENT, $EventData){

		$OT = '';
		$evOPT = $this->OO;
		$evoOPT2 = $this->OO2;
		$object = $box_data;
		$end_row_class = $end = '';
		$ED = $EventData;

		//print_r($EventData);

		extract($EventData);

		//echo $box_name.' ';

		// each eventcard type
			switch($box_name){

				// addition
					case has_filter("eventon_eventCard_{$box_name}"):
					
						$helpers = array(
							'evOPT'=> $evOPT,
							'evoOPT2'=>$evoOPT2,
							'end_row_class'=> '','end'=>'',
						);

						$OT.= apply_filters("eventon_eventCard_{$box_name}", $object, $helpers, $EVENT);
						
					break;
					
				// Event Details
					case 'eventdetails':	
						
						ob_start();
						include('views/html-eventcard-details.php');
						return ob_get_clean();
									
					break;

				// TIME
					case 'time':
						ob_start();
						include('views/html-eventcard-time.php');
						return ob_get_clean();
						
					break;

				// location
					case 'location':
						ob_start();
						include('views/html-eventcard-location.php');
						return ob_get_clean();
					break;
				

				// Location Image
					case 'locImg':

						if(empty($location_img_id)) break;
						
						ob_start();
						include('views/html-eventcard-locimg.php');
						return ob_get_clean();

					break;

				// GOOGLE map
					case 'gmap':	

						ob_start();
						include('views/html-eventcard-gmap.php');
						return ob_get_clean();
						
					break;

				// REPEAT SERIES
					case 'repeats':
						ob_start();
						include('views/html-eventcard-repeat.php');
						return ob_get_clean();
					break;
				
				// Featured image
					case 'ftimage':
						
						ob_start();
						include('views/html-eventcard-ftimage.php');
						return ob_get_clean();
												
					break;
				
				// event organizer
					case 'organizer':					
						

						if(empty($ED['event_organizer'])) break;
						
						ob_start();
						include('views/html-eventcard-organizer.php');
						return ob_get_clean();
						
						
					break;
				
				// get directions
					case 'getdirection':
						
						$_from_address = false;
						if(!empty($location_address)) $_from_address = $location_address;
						if(!empty($location_getdir_latlng) && $location_getdir_latlng =='yes' && !empty($location_latlng)){
							$_from_address = $location_latlng;
						}

						if(!$_from_address) break;
						
						ob_start();
						include('views/html-eventcard-direction.php');
						return ob_get_clean();
						
					break;

				// learn more link
					case "learnmore":
						// learn more link with pluggability
						$learnmore_link = !empty($EVENT->get_prop('evcal_lmlink'))? apply_filters('evo_learnmore_link', $EVENT->get_prop('evcal_lmlink'), $object): false;
						$learnmore_target = ($EVENT->get_prop('evcal_lmlink_target')  && $EVENT->get_prop('evcal_lmlink_target')=='yes')? 'target="_blank"':null;

						if(!$learnmore_link) break;
						
						$OT.= "<div class='evo_metarow_learnM evo_metarow_learnmore evorow'>
							<a class='evcal_evdata_row evo_clik_row ' href='".$learnmore_link."' ".$learnmore_target.">
								<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__fai_006', 'fa-link',$evOPT )."'></i></span>
								<h3 class='evo_h3'>".eventon_get_custom_language($evoOPT2, 'evcal_evcard_learnmore2','Learn More')."</h3>
							</a>
							</div>";
					break;

					case "addtocal":

						ob_start();
						include('views/html-eventcard-addtocal.php');
						return ob_get_clean();
						
					break;
						
			
				// Related Events @2.8 u4.5.9 
					case 'relatedEvents':
						$events = $EVENT->get_prop('ev_releated');
						if( !$events ) break;

						$events = json_decode($events, true);

						if( !is_array( $events )) break;


						ob_start();
						include('views/html-eventcard-related.php');
						return ob_get_clean();

					break;
				
				// Virtual Event
					case 'virtual':

						if($EVENT->is_virtual() && !$EVENT->is_cancelled()):
							ob_start();

							$vir = new EVO_Event_Virtual($EVENT);
							echo $vir->get_eventcard_cell_html();
							
							$OT.= ob_get_clean();
						endif;
					break;

				// health guidance
					case 'health':

						if( !$EVENT->check_yn('_health')) break;

						ob_start();
						include('views/html-eventcard-health.php');
						return ob_get_clean();


					break;

				// paypal link
						case 'paypal':
							$ev_txt = $EVENT->get_prop('evcal_paypal_text');
							$text = ($ev_txt)? $ev_txt: evo_lang_get('evcal_evcard_tix1','Buy ticket via Paypal');

							$currency = !empty($evOPT['evcal_pp_cur'])? $evOPT['evcal_pp_cur']: false;
							$email = ($EVENT->get_prop('evcal_paypal_email')? $EVENT->get_prop('evcal_paypal_email'): $evOPT['evcal_pp_email']);

							if($currency && $email):
								$_event_time = $EVENT->get_formatted_smart_time();							
								
								ob_start();
							?>
							

							<div class='evo_metarow_paypal evorow evcal_evdata_row evo_paypal'>
								<span class='evcal_evdata_icons'><i class='fa <?php echo get_eventON_icon('evcal__fai_007', 'fa-ticket',$evOPT );?>'></i></span>
								<div class='evcal_evdata_cell'>
									<span class='evcal_evdata_cell_title evodb evofz16 evoff_1' style='padding-bottom:15px;'><?php echo $text;?></span>
									<form target="_blank" name="_xclick" action="https://www.paypal.com/us/cgi-bin/webscr" method="post">
										<input type="hidden" name="cmd" value="_xclick">
										<input type="hidden" name="business" value="<?php echo $email;?>">
										<input type="hidden" name="currency_code" value="<?php echo $currency;?>">
										<input type="hidden" name="item_name" value="<?php echo $EVENT->post_title.' '.$_event_time;?>">
										<input type="hidden" name="amount" value="<?php echo $EVENT->get_prop('evcal_paypal_item_price');?>">
										<input type='submit' class='evcal_btn' value='<?php echo evo_lang_get('evcal_evcard_btn1','Buy Now');?>'/>
									</form>										
								</div></div>							
							<?php $OT.= ob_get_clean();
							endif;

						break;

				// social share u4.5.7
					case 'evosocial':
						ob_start();
						include('views/html-eventcard-social.php');
						return ob_get_clean();
					break;
				
			}// end switch

			// for custom meta data fields
			if(!empty($object->x) && $box_name == 'customfield'.$object->x){
				ob_start();
				include('views/html-eventcard-cmf.php');
				return ob_get_clean();
			}

		return $OT;
	}

}

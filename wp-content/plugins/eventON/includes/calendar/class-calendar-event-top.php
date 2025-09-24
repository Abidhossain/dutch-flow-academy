<?php 
/**
 * Event Top Content
 * @version 4.9
 */

class EVO_Cal_Event_Structure_Top{

	function get_eventtop_item_html($field, $_object, $eventtop_fields = ''){
		extract($_object);
		$object = (object) $_object;
		$SC = EVO()->calendar->shortcode_args;
		$EVENT = $this->EVENT;

		$eventtop_used_fields = $eventtop_fields['used'];

		
		$OT = '';
		switch($field){
			case 'data':

				// organizer data
				$orgs = array();
				if( !empty($event_organizer) ){
					foreach($event_organizer as $id=>$dd){
						$orgs[$id] = $dd->name;
					}
				}

				// event tag data
					$tags = array();
					if( isset($SC['hide_et_tags']) && $SC['hide_et_tags'] == 'yes'){}else{

						// get set to hide event top tags
							$eventtop_hidden_tags = EVO()->cal->get_prop('evo_etop_tags') ?: array();

						// print the event top tags
						foreach( apply_filters('eventon_eventtop_abovetitle_tags', $this->get_eventtop_tags( $EVENT, $_object) ) as $ff=>$vv){
							if(in_array($ff, $eventtop_hidden_tags)) continue;

							$ff = isset($vv[1]) ? $vv[1] : $ff;
							$tags[ $ff ] = $vv[0];
						}
					}

				// build data array
				$data = array(
					'd'=> array(
						'loc.n'=> (!empty($location_name) ? $location_name: ''),
						'orgs'=> $orgs,
						'tags'=> $tags
					),
					'bgc'=> $color,
					'bggrad'=> ( !empty($bggrad)? $bggrad:'')
				);
				$OT .= "<span class='evoet_data' ". $this->helper->array_to_html_data($data)."></span>";

			break;
			case 'ft_img':
				if( empty($object->img_url_med)) return $OT; 

				$url = apply_filters('eventon_eventtop_image_url', $object->img_url_med);

				$time_vals = ( $object->show_time) ? '<span class="evo_img_time"></span>':'';

				$OT.= "<span class='evoet_cy ev_ftImg' data-img='".(!empty($object->img_src)? $object->img_src: '')."' data-thumb='".$url."' style='background-image:url(\"".$url."\")' >{$time_vals}</span>";
			break;

			case 'day_block':

				if(!empty($eventtop_day_block) && !$eventtop_day_block) break;
				if(!is_array( $object->start_date_data )) break;

				// if event hide_et_dn
				if( isset($SC['hide_et_dn']) && $SC['hide_et_dn'] == 'yes') break;

				// day block data adjustments
				$day_block_data = isset($eventtop_fields['day_block']) && is_array($eventtop_fields['day_block']) ? $eventtop_fields['day_block']: array();

				$show_start_year = ( in_array('eventyear',$day_block_data) || 
					(isset($SC['eventtop_style']) && $SC['eventtop_style'] == 3 && $object->year_long) 
					?'yes':'no');
				$show_end_year = in_array('eventendyear',$day_block_data) ?'yes':'no';

				// bubbles event title passing
				$data_add = ( $SC['eventtop_style'] == '3') ? apply_filters('eventon_eventtop_maintitle',$this->EVENT->get_title() ) : '';
				
				$OT.="<span class='evoet_dayblock evcal_cblock ".( $object->year_long?'yrl ':null).( $object->month_long?'mnl ':null)."' data-bgcolor='".$color."' data-smon='".$object->start_date_data['F']."' data-syr='".$object->start_date_data['Y']."' data-bub='{$data_add}'>";
				
				// include dayname if allowed via settings
				$daynameS = $daynameE = '';
				if( is_array($day_block_data) && in_array('dayname', $day_block_data)){
					$daynameS = (!empty($event_date_html['start']['day'])? $event_date_html['start']['day']:'');
					$daynameE = (!empty($event_date_html['end']['day'])? $event_date_html['end']['day']:'');
				}

				$time_data = apply_filters('evo_eventtop_dates', array(
					'start'=>array(
						'year'=> 	($show_start_year=='yes'? $event_date_html['start']['year']:''),	
						'day'=>		$daynameS,
						'date'=> 	(!empty($event_date_html['start']['date'])?$event_date_html['start']['date']:''),
						'month'=>  	(!empty($event_date_html['start']['month'])?$event_date_html['start']['month']:''),
						'time'=>  	(!empty($event_date_html['start']['time'])?$event_date_html['start']['time']:''),
					),
					'end'=>array(
						'year'=> 	(($show_end_year=='yes' && !empty($event_date_html['end']['year']) )? $event_date_html['end']['year']:''),	
						'day'=>		$daynameE,
						'date'=> 	(!empty($event_date_html['end']['date'])? $event_date_html['end']['date']:''),
						'month'=> 	(!empty($event_date_html['end']['month'])? $event_date_html['end']['month']:''),
						'time'=> 	(!empty($event_date_html['end']['time'])? $event_date_html['end']['time']:''),
					),
				), $show_start_year, $object );


				$class_add = '';
				// Check if it's the same day
				$is_same_day = (
				    !empty($time_data['start']['year']) && 
				    !empty($time_data['end']['year']) &&
				    $time_data['start']['year'] === $time_data['end']['year'] &&
				    $time_data['start']['month'] === $time_data['end']['month'] &&
				    $time_data['start']['date'] === $time_data['end']['date']
				);

				
				foreach($time_data as $type=>$data){					
					$end_content = '';
					

					foreach($data as $field=>$value){
						if(empty($value)) continue;
						$end_content .= "<em class='{$field}'>{$value}</em>";
					}

					if($type == 'end' && empty($data['year']) && empty($data['month']) && empty($data['date']) && !empty($data['time'])){
						$class_add = 'only_time';
					}
					if(empty($end_content)) continue;
					$OT .= "<span class='evo_{$type} {$class_add} evofxdrc'>";
					$OT .= $end_content;
					$OT .= "</span>";
				}

				// crystal clear style
				if( $SC['eventtop_style'] == '5'){
					
					$OT .= "<span class='evo_ett_break'></span><span class='evo_eventcolor_circle'><i style='background-color:{$object->color}'></i></span>";
				}
							
				$OT .= "</span>";

			break;

			case 'tags':
				// above title inserts
				if( isset($SC['hide_et_tags']) && $SC['hide_et_tags'] == 'yes') return $OT;

				$OT.= "<span class='evoet_tags evo_above_title'>";
					
					// any live now events 4.6
					if($EVENT &&  !$EVENT->is_cancelled() && $EVENT->is_event_live_now() && !EVO()->cal->check_yn('evo_hide_live') ){
						$OT.= "<span class='evo_live_now' title='".( evo_lang('Live Now')  )."'>". EVO()->elements->get_icon('live') ."</span>";
					}

					// get set to hide event top tags
						$eventtop_hidden_tags = EVO()->cal->get_prop('evo_etop_tags') ?: array();

					$OT .= apply_filters("eventon_eventtop_abovetitle", '', $object, $EVENT, $eventtop_hidden_tags);
					
					// print the event top tags
					foreach( apply_filters('eventon_eventtop_abovetitle_tags', $this->get_eventtop_tags( $EVENT, $_object) ) as $ff=>$vv){
						if(in_array($ff, $eventtop_hidden_tags)) continue;

						$v1 = isset($vv[1]) ? ' '.$vv[1]:'';
						$OT.= "<span class='evo_event_headers {$ff}{$v1}'>". $vv[0] . "</span>";
					}



				$OT.="</span>";
			break;

			case 'title':
				// event edit button
					$editBTN = '';
					// settings enabled - 4.0
					if( EVO()->cal->check_yn('evo_showeditevent','evcal_1')){
						$__go = false;
						// if user is admin of the site
						if( current_user_can('manage_options') ) $__go = true;

						if( $__go){
							$editBTN = apply_filters('eventon_event_title_editbtn',
								"<i href='".get_edit_post_link($this->EVENT->ID)."' class='editEventBtnET fa fa-pencil'></i>", $this->EVENT);
						}
					}
				$OT.= "<span class='evoet_title evcal_desc2 evcal_event_title ". ( EVO()->cal->check_yn('evo_dis_all_caps') ? 'evottui':'') ."' itemprop='name'>". apply_filters('eventon_eventtop_maintitle',$this->EVENT->get_title() ) . $editBTN."</span>";

				// location attributes
					$event_location_variables = '';
					if(!empty($location_name) && (!empty($location_address) || !empty($location_latlng))){
						$LL = !empty($location_latlng)? $location_latlng:false;

						if(!empty($location_address)) $event_location_variables .= ' data-location_address="'.$location_address.'" ';
						$event_location_variables .= ($LL)? 'data-location_type="lonlat"': 'data-location_type="address"';
						$event_location_variables .= ' data-location_name="'.$location_name.'"';
						if(isset($location_url))	$event_location_variables .= ' data-location_url="'.$location_url.'"';
						$event_location_variables .= ' data-location_status="true"';
						$event_location_variables .= ' data-locid="'. $location_term_id . '"';

						if( $LL){
							$event_location_variables .= ' data-latlng="'.$LL.'"';
						}

						$OT.= "<span class='event_location_attrs' {$event_location_variables}></span>";
					}

			break;
			case 'subtitle':				
				// below title inserts
				$OT.= "<span class='evoet_cy evoet_subtitle evo_below_title' >";
					if($ST = $this->EVENT->get_subtitle()){
						$OT.= "<span class='evcal_event_subtitle ". ( EVO()->cal->check_yn('evo_dis_all_caps') ? 'evottui':'' ) ."' >" .apply_filters('eventon_eventtop_subtitle' , $ST)  ."</span>";
					}

				$OT.="</span>";
			break;

			case 'status_reason':

				// event status reason 
					if( $reason = $this->EVENT->get_status_reason()){
						$OT.= '<span class="evoet_cy evoet_status_reason"><span class="evoet_sr_text">'. $reason .'</span></span>';
					}

			break;
			case 'time':

				if( isset($SC['hide_et_tl']) && $SC['hide_et_tl'] == 'yes') return $OT;

				$OT.= "<span class='evoet_cy evoet_time_expand level_3'>";
				
				// time
				$timezone_text = (!empty($object->timezone)? ' <em class="evo_etop_timezone">'.$object->timezone. '</em>':null);

				$tzo = $tzo_box = '';

				// custom timezone text
				if( !EVO()->cal->check_yn('evo_gmt_hide','evcal_1') && !empty($EVENT->gmt) ){
					$timezone_text .= "<span class='evo_tz marl5'>(". $EVENT->gmt .")</span>";
				}
					
				// event time
				$OT.= "<em class='evcal_time evo_tz_time'><i class='fa fa-clock-o'></i>". 
					apply_filters('evoeventtop_belowtitle_datetime', $object->event_date_html['html_fromto'], $object->event_date_html, $object) . 
					$timezone_text ."</em> ";

				// view in my time - local time
				if( !empty($this->ev_tz) && EVO()->cal->check_yn('evo_show_localtime','evcal_1') ){

					$_def_local_time = EVO()->cal->check_yn('evo_show_loct_on');
					extract( $this->timezone_data );
		
					$data = array(
						'__df'=> $__df,
						'__tf'=> $__tf,
						'__f'=> $__f,
						'times'=>  $EVENT->start_unix . '-' . $EVENT->end_unix,
						'tzo' => $this->help->_get_tz_offset_seconds( $this->ev_tz,   $EVENT->start_unix)
					);

					// show local time by default
					if( $_def_local_time){
						$OT.= "<em class='evcal_tz_time evo_mytime evo_loct_inprocess evobr5' title='". evo_lang('My Time') ."'  ". $this->help->array_to_html_data($data).">...</em>";	
					}else{
						$OT.= "<em class='evcal_tz_time evo_mytime tzo_trig evo_hover_op6' title='". evo_lang('My Time') ."'  ". $this->help->array_to_html_data($data).">{$__t}</em>";	
					}
						
				}

				// manual timezone text
				if( empty($this->ev_tz) ) $OT.= "<em class='evcal_local_time' data-s='{$event_start_unix}' data-e='{$event_end_unix}' data-tz='". $EVENT->get_prop('_evo_tz') ."'></em>";
			
				$OT.= "</span>";

			break;

			case 'location':

				// hide via shortcode
				if( isset($SC['hide_et_tl']) && $SC['hide_et_tl'] == 'yes') return $OT;

				$eventtop_location_data = $eventtop_fields['location'];
				

				// location name				
				$LOCname = ( ('locationame' == $eventtop_location_data || $eventtop_location_data == 'both') && !empty($location_name) )? $location_name: false;

				// location address
				$LOCadd = ( ( 'location' == $eventtop_location_data || $eventtop_location_data =='both') && !empty($location_address))? stripslashes($location_address): false;

				// check if location address and name the same
					if( $LOCname == $LOCadd ) $LOCadd = '';


				if($LOCname || $LOCadd){
					$OT.= "<span class='evoet_location level_3'>";
					$OT.= '<em class="evcal_location" '.( !empty($location_latlng)? ' data-latlng="'.$location_latlng.'"':null ).' data-add_str="'.$LOCadd.'" data-n="'. $LOCname .'"><i class="fa fa-location-pin"></i>'.($LOCname? '<em class="event_location_name">'.$LOCname.'</em>':'').
						( ($LOCname && $LOCadd)?', ':'').
						$LOCadd.'</em>';
					$OT.= "</span>";
				}				

			break;
			case 'organizer':
				if( in_array('organizer',$eventtop_used_fields) && !empty($event_organizer) ){

					
					$OT.="<span class='evcal_oganizer level_4'>
						<em><i>".( eventon_get_custom_language( '','evcal_evcard_org', 'Event Organized By')  ).'</i></em>';

						$org_link_type = EVO()->cal->get_prop('evo_eventtop_org_link','evcal_1');

						foreach($event_organizer as $EO_id=>$EO){

							if( empty( $EO->name)) continue;
							
							// open as lightbox
							if( $org_link_type == 0 || !$org_link_type){
								$btn_data = array(
									'lbvals'=> array(
										'lbc'=>'evo_organizer_lb_'.$EO->term_id,
										't'=>	$EO->name,
										'ajax'=>'yes',
										'ajax_type'=>'endpoint',
										'ajax_action'=>'eventon_get_tax_card_content',
										'end'=>'client',
										'd'=> array(					
											'eventid'=> $EVENT->ID,
											'ri'=> $EVENT->ri,
											'term_id'=> $EO->term_id,
											'tax'=>'event_organizer',
											'load_lbcontent'=>true
										)
									)
								);

								$OT.='<em class="evoet_dataval evolb_trigger" '. $this->help->array_to_html_data($btn_data) .'>'.$EO->name."</em>";
							}

							// open as archive page
							if( $org_link_type == 1){
								$OT.='<em class="evoet_dataval evo_org_clk_link evo_hover_op7 evo_curp" data-link="'. $EO->link .'">'.$EO->name."</em>";
							}
							// open as organizer link if available page
							if( $org_link_type == 2){
								$link = !empty($EO->organizer_link) ? $EO->organizer_link : false;
								$OT.='<em class="evoet_dataval '. ( $link ? 'evo_org_clk_link evo_hover_op7 evo_curp':'') .'" data-link="'. $link .'">'.$EO->name."</em>";
							}

							// do nothing
							if( $org_link_type == 'x'){
								$OT.='<em class="evoet_dataval">'.$EO->name."</em>";
							}
						}
						
					$OT.="</span>";
				}

			break;
			// @updated 4.7.2
			case 'eventtags':
				// event tags
				
					$event_tags = wp_get_post_tags($this->EVENT->ID);
					if(!$event_tags) return $OT;

					$OT.="<span class='evo_event_tags level_4'>
						<em><i>".eventon_get_custom_language( '','evo_lang_eventtags', 'Event Tags')."</i></em>";

					$count = count($event_tags);
					$i = 1;
					foreach($event_tags as $tag){

						$tag_name = evo_lang_get('evolang_event_tag_'. $tag->term_id, $tag->name);

						$OT.="<em class='evoet_dataval' data-tagid='{$tag->term_id}'>". $tag_name . ( ($count==$i)?'':',')."</em>";
						$i++;
					}
					$OT.="</span>";
			break;
			
			case 'progress_bar':
				
				// event progress bar
				if( !EVO()->cal->check_yn('evo_eventtop_progress_hide','evcal_1')  && $EVENT->is_event_live_now() && !$EVENT->is_cancelled()
					&& !$EVENT->echeck_yn('hide_progress')
					&& $EVENT->get_event_status() != 'postponed'
				){
					

					$livenow_bar_sc = isset($SC['livenow_bar']) ? $SC['livenow_bar'] : 'yes';
					
					// check if shortcode livenow_bar is set to hide live bar
					if($livenow_bar_sc != 'yes') return $OT;

					$OT.= "<span class='evoet_progress_bar evo_event_progress ' >";

					//$OT.= "<span class='evo_ep_pre'>". evo_lang('Live Now') ."</span>";

					$end_utc = $EVENT->get_end_time( true); // end time in utc0

					// deprecating values
						$now =  EVO()->calendar->utc_time;
						$duration = $EVENT->duration;					
						$gap = $end_utc - $now; // how far event has progressed

					$perc = $duration == 0? 0: ($duration - $gap) / $duration;
					$perc = (int)( $perc*100);
					if( $perc > 100) $perc = 100;

					// action on expire							
					$exp_act = $nonce = '';
					if( isset($SC['cal_now']) && $SC['cal_now'] == 'yes'){
						$exp_act = 'runajax_refresh_now_cal';
						$nonce = wp_create_nonce('evo_calendar_now');
					}

					
					$OT.= "<span class='evo_epbar_o'><span class='evo_ep_bar'><b style='width:{$perc}%'></b></span></span>";
					$OT.= "<span class='evo_ep_time evo_countdowner' data-endutc='{$end_utc}' data-gap='{$gap}' data-dur='{$duration}' data-exp_act='". $exp_act ."' data-n='{$nonce}' data-ds='".evo_lang('Days')."' data-d='".evo_lang('Day')."' data-t='". evo_lang('Time Left')."'></span>";

					$OT.= "</span>";

				}
			
			case has_filter("eventon_eventtop_{$field}"):
				//echo $field;
				$helpers = array(
					'evOPT'=>	EVO()->calendar->evopt1,
					'evoOPT2'=> EVO()->calendar->evopt2,
				);

				$OT.= apply_filters("eventon_eventtop_{$field}", '', $object, $EVENT, $helpers);	
			break;
		}


		// custom meta fields
			if( strpos($field, 'cmd') !== false){
				if(!empty($object->cmf_data) && is_array($object->cmf_data) && count($object->cmf_data)>0){

					if( !isset($object->cmf_data[ $field ])) return $OT;
					$OT = $this->get_eventtop_cmf_html( $object->cmf_data[ $field ] , $EVENT);
				}
			}

		// event type taxonomy
			if(strpos($field, 'eventtype') !== false){
				$OT .= $this->get_eventtop_types($field, $object, $EVENT);
			}

		return $OT;
	}
}

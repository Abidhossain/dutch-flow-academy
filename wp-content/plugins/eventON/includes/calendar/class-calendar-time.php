<?php
/**
 * Calendar Time class.
 *
 * @class 		EVO_Cal_Time
 * @version		4.9.2
 * @package		EventON/Classes
 * @category	Class
 * @author 		AJDE
 */

class EVO_Cal_Time {

// GENERATE TIME for event @4.5.7
	public function generate_time_( $EVENT, $focus_month_beg_range='', 	$FOCUS_month_int='', $cal_hide_end_time = false ){
		
		$data_array = array(
			'start'=> array(
				'year'=>'','month'=>'','date'=>''
			), 
			'end'=> array(
				'year'=>'','month'=>'','date'=>''
			)
		);
		$_event_date_HTML = array();

		EVO()->cal->set_cur('evcal_1');

		// get date and time format
			extract( EVO()->calendar->get_date_time_format() );
			$DT_format = 'YFj'. $date_format . $time_format;


		// INITIAL variables
			$evcal_lang_allday = $__txt_allday = $this->lang( 'evcal_lang_allday', 'All Day');
			$SC = $this->shortcode_args;

			
			// start and end row times -- UTC0
				$event_start_unix = $EVENT->start_unix;
				$event_end_unix = $event_start_unix + $EVENT->duration;
				//$event_end_unix = $EVENT->end_unix;

			$_is_allday = $EVENT->is_all_day();
			$_hide_endtime = $EVENT->is_hide_endtime();
				if( $cal_hide_end_time) $_hide_endtime = true; // override by calendar values

			// Get event times in pieces -- evnet time in UTC0
				$DATE_start_val = eventon_get_formatted_time( $event_start_unix , $EVENT->tz );
				$DATE_end_val = eventon_get_formatted_time( $event_end_unix , $EVENT->tz );


			//if virtual event end time set > override visible end time
			if( $EVENT->vir_duration && !$EVENT->is_repeating_event() ){
				$event_end_unix = $event_start_unix + $EVENT->vir_duration;
				$DATE_end_val = eventon_get_formatted_time( $event_end_unix, $EVENT->tz );
			}

			//echo $EVENT->start_unix_raw.' '. $event_end_unix;

			// FOCUSED values
			$CURRENT_month_INT = (!empty($FOCUS_month_int))?
				$FOCUS_month_int: (!empty($focus_month_beg_range)?
					date('n', $focus_month_beg_range ): date('n')); //
			$_current_date = (!empty($focus_month_beg_range))? date('j', $focus_month_beg_range ): 1;

			// time format
			$wp_time_format = get_option('time_format');
			

			$_ES = $EVENT->get_translated_datetime( $wp_time_format , $EVENT->start_unix , false );
			$_EE = $EVENT->get_translated_datetime( $wp_time_format , $event_end_unix , false );

			$ES = $EVENT->get_translated_datetime( $date_format , $EVENT->start_unix, false );
			$ESt = $EVENT->get_translated_datetime( $time_format , $EVENT->start_unix, false );
			$EE = $EVENT->get_translated_datetime( $date_format , $event_end_unix, false );
			$EEt = $EVENT->get_translated_datetime( $time_format , $event_end_unix, false );

			//echo $date_format.'--';
			
		// date values
			$date_args = array(
				'cdate'=>$_current_date,
				'eventstart'=>$DATE_start_val,
				'eventend'=>$DATE_end_val,
				'stime'=>$_ES,
				'etime'=>$_EE,
				'_hide_endtime'=>$_hide_endtime
			);

			//print_r($date_args);

		// validate
		if(!is_array($DATE_start_val) || !is_array($DATE_end_val)) return array();
		

		// CHECKS
			$_start_end_same = false;
			$_same_day = false;

		// same start and end months
		if($DATE_start_val['n'] == $DATE_end_val['n']){

			//print_r($DATE_end_val);
			//print_r($_EE);

			/** EVENT TYPE = start and end in SAME DAY **/
			if($DATE_start_val['j'] == $DATE_end_val['j']){

				$_same_day = true;

				// check all days event
				if($_is_allday){
					$__from_to ="<em class='evcal_alldayevent_text'>(".$__txt_allday.": ".$DATE_start_val['l'].")</em>";
					$__prettytime = '<span class="start">'. $ES.'</span> '. $__txt_allday ;
					$__time = "<span class='start'>".$__txt_allday."</span>";

					$data_array['start'] = array(
						'year'=>	$DATE_start_val['Y'],
						'month'=>	$DATE_start_val['M'],
						'date'=>	$DATE_start_val['d'],
					);
					$data_array['end'] = '';


				// NOT all day
				}else{
					$__from_to = ($_hide_endtime)?	$_ES:	$_ES.' - '. $_EE;
					$__time = "<span class='start'>".$_ES."</span>". (!$_hide_endtime ? "<span class='end'>- ".$_EE."</span>": null);

					$data_array['start'] = array(
						'year'=>	$DATE_start_val['Y'],
						'month'=>	$DATE_start_val['M'],
						'date'=>	$DATE_start_val['d'],
					);

					$__prettytime = '<span class="start">'.$ES .'</span> <span class="stime">'. $ESt .'</span>'. (!$_hide_endtime ? ' - <span class="etime">' . $EEt .'</span>':'');
				}


				$_event_date_HTML = array(
					'html_date'=> '<span class="start">'.$DATE_start_val['j'].'<em>'.$DATE_start_val['M'].'</em></span>',
					'html_time'=>$__time,
					'html_fromto'=> apply_filters('eventon_evt_fe_time', $__from_to, $DATE_start_val, $DATE_end_val),
					'html_prettytime'=> $__prettytime,
					'class_daylength'=>"sin_val",
					'start_month'=>$DATE_start_val['M'],
				);

			}else{ // different start and end date


				// check all days event
				if($_is_allday){
					$__from_to ="<em class='evcal_alldayevent_text'>(".$__txt_allday.")</em>";
					$__prettytime = '<span class="start">'. $ES .'</span> '. (!$_hide_endtime ? ' - <span class="end">'. $EE ."</span>":'') .' ('. $__txt_allday .')';
					$__time = "<span class='start'>".$__txt_allday."</span>";

					$data_array['start'] = array(
						'year'=>	$DATE_start_val['Y'],
						'month'=>	$DATE_start_val['M'],
						'date'=>	$DATE_start_val['d'],
					);
					$data_array['end'] = array(
						'date'=>	$DATE_end_val['d'],
					);
				}else{

					// if start date is before current date
						$date_inclusion = ($DATE_start_val['j'] < $_current_date) ? ' ('.$DATE_start_val['j'].')':'';
					$__from_to = ($_hide_endtime)?
						$_ES:
						$_ES. $date_inclusion.' - '.$_EE. ' <em class="evo_endday evomarl5">('.$DATE_end_val['j'].')</em>';
					
					$__prettytime = '<span class="start">'. $ES .'</span> <span class="stime">'. $ESt .'</span>'. 
						(!$_hide_endtime ? " - <span class='end'>{$EE}</span> <span class='etime'>$EEt</span>" :'');

					$data_array['start'] = array(
						'year'=>	$DATE_start_val['Y'],
						'month'=>	$DATE_start_val['M'],
						'date'=>	$DATE_start_val['d'],
					);
					$data_array['end'] = array(
						'date'=>	$DATE_end_val['d'],
					);

				}

				$__time = "<span class='start'>".$_ES."</span>". (!$_hide_endtime ? "<span class='end'>- ".$_EE."</span>": null);


				$_event_date_HTML = array(
					'html_date'=> '<span class="start">'.$DATE_start_val['j'].'<em>'.$DATE_start_val['M'].'</em></span>'. ( !$_hide_endtime? '<span class="end"> - '.$DATE_end_val['j'].'</span>': ''),
					'html_time'=>$__time,
					'html_fromto'=> apply_filters('eventon_evt_fe_time', $__from_to, $DATE_start_val, $DATE_end_val),
					'html_prettytime'=> $__prettytime,
					'class_daylength'=>"mul_val",
					'start_month'=>$DATE_start_val['M']
				);
			}
		}else{
			/** EVENT TYPE = different start and end months **/

			$__time = "<span class='start'>".$_ES."</span>". (!$_hide_endtime ? "<span class='end'>- ".$_EE."</span>": null);

			/** EVENT TYPE = start month is before current month **/
			if($CURRENT_month_INT != $DATE_start_val['n']){
				// check all days event
				if($_is_allday){
					$__from_to ="<em class='evcal_alldayevent_text'>(".$__txt_allday.")</em>";
					$__time = "<span class='start'>".$__txt_allday."</span>";
				}else{
					$__start_this = '('.$DATE_start_val['F'].' '.$DATE_start_val['j'].') '.$_ES;
					$__end_this = (!$_hide_endtime? ' - ('.$DATE_end_val['F'].' '.$DATE_end_val['j'].') '.$_EE :'' );

					$__from_to = (($_hide_endtime)?
						$__start_this:$__start_this.$__end_this);
				}

			}else{

				/** EVENT TYPE = start month is current month and end month is future month **/
				// check all days event
				if($_is_allday){
					$__from_to ="<em class='evcal_alldayevent_text'>(".$__txt_allday.")</em>";
					$__time = "<span class='start'>".$__txt_allday."</span>";
				}else{
					$date_inclusion = ($DATE_start_val['j'] < $_current_date) ? ' ('.$DATE_start_val['j'].')':'';
					$__start_this = $_ES.$date_inclusion;
					$__end_this = ' - ('.$DATE_end_val['F'].' '.$DATE_end_val['j'].') '.$_EE;

					$__from_to =($_hide_endtime)? $__start_this:$__start_this.$__end_this;
				}
			}

			$data_array['start'] = array(
				'year'=>	$DATE_start_val['Y'],
				'month'=>	$DATE_start_val['M'],
				'date'=>	$DATE_start_val['d'],
			);
			$data_array['end'] = array(
				'month'=>	$DATE_end_val['M'],
				'date'=>	$DATE_end_val['d'],
			);

			// check all days event
			if($_is_allday){
				$__prettytime = "<span class='start'>$ES</span>" . (!$_hide_endtime ? " - <span class='end'>$EE</span>" :'') .' ('. $__txt_allday .')';
			}else{
				$__prettytime = "<span class='start'>$ES</span> <span class='stime'>$ESt</span>" . 
					(!$_hide_endtime ? " - <span class='end'>$EE</span> <span class='etime'>$EEt</span>" :'');
			}


			// html date
			$__this_html_date = ($_hide_endtime)?
				'<span class="start">'.$DATE_start_val['j'].'<em>'.$DATE_start_val['M'].'</em></span>':
				'<span class="start">'.$DATE_start_val['j'].'<em>'.$DATE_start_val['M'].'</em></span><span class="end"> - '.$DATE_end_val['j'].'<em>'.$DATE_end_val['M'].'</em></span>';

			$_event_date_HTML = apply_filters('evo_eventcard_dif_SEM', array(
				'html_date'=> $__this_html_date,
				'html_time'=>$__time,
				'html_fromto'=> apply_filters('eventon_evt_fe_time', $__from_to, $DATE_start_val, $DATE_end_val),
				'html_prettytime'=> $__prettytime,
				'class_daylength'=>"mul_val",
				'start_month'=>$DATE_start_val['M'],
			));
		}

		// start and end years are different
			if($DATE_start_val['Y'] != $DATE_end_val['Y']){
				$data_array['start']['year'] = $DATE_start_val['Y'];
				if( is_array($data_array['end']) ){
					$data_array['end']['year'] = $DATE_end_val['Y'];
				}else{
					$data_array['end'] = array('year' => $DATE_end_val['Y'] );
				}
			}

		// Include day name
			if( !$_same_day && !empty($data_array['end']) && isset($DATE_end_val['D'])) $data_array['end']['day'] = $DATE_end_val['D'];
			if( isset($DATE_start_val['D'])) $data_array['start']['day'] = $DATE_start_val['D'];
		
		// year long event
			$__is_year_long = $EVENT->is_year_long();

			//if year long event
			if($__is_year_long){
				$evcal_lang_yrrnd = $this->lang_array['evcal_lang_yrrnd'];
				$_event_date_HTML = array(
					'html_date'=> '<span class="yearRnd"></span>',
					'html_time'=>'',
					'html_fromto'=> $evcal_lang_yrrnd . ' ('. $DATE_start_val['Y'] .')',
					'html_prettytime'=> $evcal_lang_yrrnd . ' ('. $DATE_start_val['Y'] .')',
					'class_daylength'=>"no_val",
					'start_month'=>$_event_date_HTML['start_month']
				);
				$data_array['start'] = array(
					'year'=>	$DATE_start_val['Y'],
					'month'=>	'',
					'date'=>	'',
				);
				$data_array['end'] = array(
					'year'=>	'',
					'month'=>	'',
					'date'=>	'',
				);
			}

		// Month long event
			$__is_month_long = $__is_year_long? false: $EVENT->is_month_long();

			//if month long event
			if($__is_month_long){
				$evcal_lang_mntlng = $this->lang_array['evcal_lang_mntlng'];
				$_event_date_HTML = array(
					'html_date'=> '<span class="yearRnd"></span>',
					'html_time'=>'',
					'html_fromto'=> $evcal_lang_mntlng . ' ('. $DATE_start_val['F'] .')',
					'html_prettytime'=> $evcal_lang_mntlng . ' ('. $DATE_start_val['F'] .')',
					'class_daylength'=>"no_val",
					'start_month'=>$_event_date_HTML['start_month']
				);

				$data_array['start'] = array(
					'year'=>	$DATE_start_val['Y'],
					'month'=>	$DATE_start_val['M'],
					'date'=>	'',
				);
				$data_array['end'] = array(
					'year'=>	'',
					'month'=>	'',
					'date'=>	'',
				);
			}

		// all day event check
			if($_is_allday){
				$data_array['start']['time'] = $__txt_allday;					
			}else{
				$dv_time = $this->generate_time($date_args);
				$data_array['start']['time'] = $dv_time['start'];
				$data_array['end']['time'] = ($_hide_endtime?'' :$dv_time['end']);
			}

		// remove event time for month and year long events
			if($__is_month_long || $__is_year_long){
				$data_array['start']['time'] = '';
				$data_array['end']['time'] = '';
			}

		// if hide end time
			if($_hide_endtime){
				$data_array['end'] = array(
					'year'=>	'',
					'month'=>	'',
					'date'=>	'',
					'day'=>	'',
					'time'=>	'',
				);
			}

		//EVO_Debug($data_array);


		$_event_date_HTML = array_merge($_event_date_HTML, $data_array);

		return $_event_date_HTML;
	}

}
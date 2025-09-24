<?php
/**
 * calendar outter shell content.
 *
 * @class 		evo_cal_shell
 * @version		4.5.5
 * @package		EventON/Classes
 * @category	Class
 * @author 		AJDE
 */

class evo_cal_shell {
	private $cal;
	public function __construct($CAL){
		$this->cal = $CAL;
	}
	
	// Event types and other functions
		public function get_event_types() {
		    $output = [0 => ''];
		    foreach (eventon_get_valid_ett() as $key => $nn) {
		        $output[$key] = 'event_type' . ($key == 1 ? '' : '_' . $key);
		    }
		    return $output;
		}
		public function get_extra_tax(){
			$output;
			$extras = apply_filters('eventon_extra_tax', array(
				'evloc'=>'event_location',
				'evorg'=>'event_organizer',
			));
			foreach($extras as $ff=>$vv){
				$output[$ff] = $vv;
			}
			return $output;
		}
		function get_event_tags(){
			return array('evotag'=> 'event_tag');
		}
		function get_non_tax_filters(){
			return array('evpf'=>'event_past_future', 'evvir'=>'event_virtual', 'evst'=>'event_status');
		}
		public function get_all_event_tax(){
			$A = array_merge($this->get_event_types(), $this->get_extra_tax(), $this->get_event_tags(), $this->get_non_tax_filters());
			$A = apply_filters('eventon_all_filters', $A);
			
			return array_filter($A);
		}
		public function verify_eventtypes(){
			for($x= 3; $x<= evo_max_ett_count(); $x++){
				if( !empty($this->cal->evopt1['evcal_ett_'.$x]) && $this->cal->evopt1['evcal_ett_'.$x]=='yes'){
					$this->cal->event_types = $x+1;
				}else{
					break;
				}
			}
		}

	

	// generate calendar date range and starting month year
	// v2.8 @u 4.9.2
		public function set_calendar_range($atts=''){


			EVO()->cal->set_cur('evcal_1'); // set the current settings 
			$SC = $this->cal->shortcode_args;
			extract($SC);

			$DD = new DateTime('now', $this->cal->cal_tz);
			if(empty($focus_start_date_range) && empty($focus_end_date_range)){

				// Set fixed day if not provided
		        if (!isset($SC['fixed_day']))     $this->cal->_update_sc_args('fixed_day', $DD->format('j'));

				// get local month and year now
				$DD->setTime(0, 0, 0);
		        $DD->modify('first day of this month');
		        $_start = $DD->format('U');					

				// Adjust for month increment
		        if (!empty($month_incre) && $month_incre != 0) {
		            $month_incre = (strpos($month_incre, '+') === false && strpos($month_incre, '-') === false ? '+' : '') . (int)$month_incre;
		            $DD->modify($month_incre . ' month');
		            $_start = $DD->format('U');
		        }

		        // Use fixed month/year if provided
		        $fixed_month = isset($fixed_month) ? (int)$fixed_month : $DD->format('n');
        		$fixed_year = isset($fixed_year) ? (int)$fixed_year : $DD->format('Y');
		        
		        if ($fixed_month > 0 && $fixed_year > 0) {
		            $DD->setDate($fixed_year, $fixed_month, 1)->setTime(0, 0, 0);
		            $_start = $DD->format('U');
		        }

				$this->cal->_update_sc_args('focus_start_date_range', $_start);
				$this->cal->_update_sc_args('fixed_month', $DD->format('n'));
				$this->cal->_update_sc_args('fixed_year', $DD->format('Y'));

				// adjust end range for multiple months
				if( !empty($number_of_months) && $number_of_months > 1 && is_numeric( $number_of_months )){
					$DD->modify('+'. ($number_of_months-1) .' month');
				}

				$DD->modify('last day of this month')->setTime(23, 59, 59);
        		$this->cal->_update_sc_args('focus_end_date_range', $DD->format('U'));

			}else{

				$DD->setTimestamp((int)$focus_start_date_range);
		        $this->cal->_update_sc_args('fixed_month', $atts['fixed_month'] ?? $DD->format('n'));
		        $this->cal->_update_sc_args('fixed_year', $atts['fixed_year'] ?? $DD->format('Y'));
		        if (empty($atts['fixed_day']) && !isset($SC['fixed_day'])) {
		            $this->cal->_update_sc_args('fixed_day', $DD->format('j'));
		        }
			}

		}

	// check if a event dates are in set calendar date range
	// $S, $E is date range $start, $end event time to check
		function is_in_range($S, $E, $start, $end){
			return (
				($E == 0 && $S == 0) ||
				( $start <= $S && $end >= $E ) ||
				( $start <= $S && $end >= $S && $end <= $E) ||
				( $start <= $E && $end >= $E ) ||
				( $start >= $S && $end <= $E )
			) ? true: false;
		}

	

	/**
	 * sort events list array
	 * @param  array $events_array list of events
	 * @param  array $args         shortcode arguments
	 * @return array               sorted events list
	 */
		public function evo_sort_events_array($events_array){

			$ecv = $this->cal->shortcode_args;

			//echo $ecv['sort_by'];

			if(is_array($events_array) && isset($ecv['sort_by'])){
				switch($ecv['sort_by']){
					case has_action("eventon_event_sorting_{$ecv['sort_by']}"):
						do_action("eventon_event_sorting_{$ecv['sort_by']}", $events_array);
					break;
					case 'sort_date':
						usort($events_array, 'cmp_esort_enddate' );
						usort($events_array, 'cmp_esort_startdate' );

					break;case 'sort_title':
						usort($events_array, 'cmp_esort_title' );
					break; case 'sort_color':
						usort($events_array, 'cmp_esort_color' );
					break;
					case 'sort_rand':
						shuffle($events_array);
					break;
				}
			}


			// ALT: reverse events order within the events array list
			$events_array = (isset($ecv['event_order']) && $ecv['event_order']=='DESC')?
				array_reverse($events_array) : $events_array;

			return $events_array;
		}

	/**
	 * reusable variables within the calendar
	 * @return
	 */
		public function reused(){
			$lang = (!empty($this->cal->shortcode_args['lang']))? $this->cal->shortcode_args['lang']: 'L1';


			// for each event type category
			$ett_i18n_names = evo_get_localized_ettNames( $lang, $this->cal->evopt1, $this->cal->evopt2);

			for($x = 1; $x< $this->cal->event_types ; $x++){
				$ab = ($x==1)? '':$x;

				$this->cal->lang_array['et'.$ab] = $ett_i18n_names[$x];
			}

			$this->cal->lang_array['no_event'] = html_entity_decode( evo_lang('No Events') );
			$this->cal->lang_array['evcal_lang_yrrnd'] = $this->cal->lang('evcal_lang_yrrnd','Year Around Event',$lang);
			$this->cal->lang_array['evcal_lang_mntlng'] = $this->cal->lang('evcal_lang_mntlng','Month Long Event',$lang);
			$this->cal->lang_array['evloc'] = $this->cal->lang('evcal_lang_evloc','Event Location', $lang);
			$this->cal->lang_array['evorg'] = $this->cal->lang('evcal_lang_evorg','Event Organizer', $lang);
			$this->cal->lang_array['evsme'] = $this->cal->lang('evcal_lang_sme','Show More Events', $lang);


			//print_r($this->cal->lang_array);
		}

	/**
	 * update or change shortcode argument values after its processed on globally
	 * @param  string $field   shortcode field
	 * @param  string $new_val value of the field
	 * @return
	 */
		public function update_shortcode_args($field, $new_val){
			$sca = $this->cal->shortcode_args;
			if(!empty($sca) && !empty($sca[$field])){
				$new_sca = $sca;
				$new_sca[$field]= $new_val;

				$this->cal->shortcode_args = $new_sca;
			}

			if($field=='lang' && empty($sca)){
				$this->cal->shortcode_args = array('lang'=>$new_val);
			}
		}
}
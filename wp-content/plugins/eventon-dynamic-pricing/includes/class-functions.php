<?php
/**
 * functions
 * @version 1.0
 */

class evodp_fnc{
	public $DD, $timezone0, $current_time;
	public function __construct(){
		$this->DD = new DateTime();
		$this->timezone0 = new DateTimeZone( 'UTC' );
		$this->current_time = current_time('timestamp');
		$this->DD->setTimezone( $this->timezone0 );
		$this->DD->setTimestamp( $this->current_time );		
	}


// get time format
	function get_time_format(){
		$wp_time_format = get_option('time_format');
		return (strpos($wp_time_format, 'H')!==false || strpos($wp_time_format, 'G')!==false)? 'H:i':'h:i:A';
	}
	function check_data($data, $key){
		return !empty($data[$key])? $data[$key]: false;
	}

	function get_unix_time($post){
		$P = $post;

		if(!isset( $P['event_dst_date_x'])) return false;

		// start
		$_h = $this->_get_hour($P['_dst_hour'], isset($P['_dst_ampm'])? $P['_dst_ampm']:'' );
		$str = $P['event_dst_date_x'].' '.$_h.":".$P['_dst_minute'].':00';

		$this->DD = new DateTime($str );
		$this->DD->setTimezone( $this->timezone0 );

		$_S = $this->DD->format('U');

		// end
		$_h = $this->_get_hour($P['_den_hour'], isset($P['_den_ampm'])? $P['_den_ampm']:'' );
		$str = $P['event_den_date_x'].' '.$_h.":".$P['_den_minute'].':00';

		$this->DD = new DateTime($str );
		$this->DD->setTimezone( $this->timezone0 );

		$_E = $this->DD->format('U');

		$R = array(	0=> $_S, 1=>$_E	);
		return $R;
	}
	// return hour in 24 format
			function _get_hour($h, $ampm=''){
				if(!empty($ampm) && $ampm == 'pm' && $h <12) return ((int)$h) +12;
				return $h;
			}
	function get_unix_time_legacy($date, $time, $args){
		global $evodp;

		$this->set_timezone();

		//date_default_timezone_set('UTC');

		// time format
		if(empty($args['time_format']) ){
			$args['time_format'] = get_option('time_format');
		}

		$_wp_date_format = !empty($args['date_format'])? $args['date_format']: get_option('date_format');

		$__ti = date_parse_from_format($_wp_date_format.' '.$args['time_format'], $date.' '.$time);

		return mktime($__ti['hour'], $__ti['minute'],0, $__ti['month'], $__ti['day'], $__ti['year'] );
	}

	// -- can be get from datetime obj v2.6.1
	function set_timezone(){
		$tzstring = $this->get_timezone_str();
		$tzstring = ($tzstring == 'UTC+0')? 'UTC':$tzstring;
		date_default_timezone_set($tzstring);
	}

	// get local unix now
	function get_local_unix_now(){
		// set local time zone
		$this->set_timezone();

		return time();
	}

	// get the saved time zone value
	function get_timezone_str(){
		$tzstring = get_option('timezone_string');
		$current_offset = get_option('gmt_offset');

		// Remove old Etc mappings. Fallback to gmt_offset.
		if ( false !== strpos($tzstring,'Etc/GMT') ) $tzstring = '';
		if ( false !== strpos($tzstring,'UTC') ) $tzstring = '';

		if( empty($tzstring)) $tzstring = 'UTC';

		return $tzstring;
	}
}
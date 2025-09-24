<?php
/**
 * Calendar Data Generator, Calendar Options
 * @since 2.6.14
 * @version 4.9.2

 * called using EVO()->cal
 * opt_pre = evcal_options_
 * op_tab = evcal_x
 */

class EVO_Cal_Gen{
	public $loaded_options = array();
	public $debug = 'good';

	public $current_options = false; // same as op_tab eg. evcal_1
	// $op_name = $op_pre.$op_tab;
	private $op_pre = '';

	public function __construct(){
		// initiate global eventon calendar settings
		foreach(apply_filters('evo_cal_gen_options',array(
			'evcal_1'=> array('evcal_options_', true),
			'evcal_2'=> array('evcal_options_', true),
		)) as $tab=>$data){
			$this->op_pre = !isset($data[0])? 'evcal_options_': $data[0];
			$op_global = !isset($data[1])? true: $data[1];
			
			$this->loaded_options[$tab] = $this->get_options_data($this->op_pre,$tab, $op_global);
		}
	}

	// set the current options tab
	public function set_cur($op_tab){
		$this->current_options = $op_tab;
	}

	// load more calendar options into object after construct
	// @updated 2.7.2
	public function load_more($op_tab,$op_pre ='evcal_options_' , $option_values=''){
		if(isset($this->loaded_options[$op_tab])) return true; // avoid reloading already loaded values

		if( !empty($option_values) && is_array($option_values)){
			$this->loaded_options[$op_tab] = $option_values;
			return true;
		}
		$this->loaded_options[$op_tab] = $this->get_options_data($op_pre,$op_tab, true);
	}

	// return all option valyes for already loaded cal options for a tab 
	public function get_op($op_tab){
		if(!isset($this->loaded_options[$op_tab])) return false;
		if(empty($this->loaded_options[$op_tab])) return false; // @4.9.2
		$this->current_options = $op_tab;
		return $this->loaded_options[$op_tab];
	}
	public function get_prop($field, $current_op_tab=''){
		if(!empty($current_op_tab)) $this->current_options = $current_op_tab;
		if(!isset($this->loaded_options[$this->current_options])) return false;
		if(!isset($this->loaded_options[$this->current_options][$field])) return false;
		return maybe_unserialize( $this->loaded_options[$this->current_options][$field] );
	}

	// get property value with default fallback @4.9
	public function get_prop_def( $field, $current_op_tab='', $default = ''){
		$val = $this->get_prop($field, $current_op_tab );
		return $val !== false && $val !== null ? $val : ($default ?: false);
	}


	public function check_yn($field, $current_op_tab=''){
		if(!empty($current_op_tab)) $this->current_options = $current_op_tab; // setting current focused options tab if passed
		return ($this->get_prop($field) && $this->get_prop($field) == 'yes')? true: false; 
	}

	private function get_options_data($op_pre, $op_tab, $load_fresh = false){
		$op_name = $op_pre.$op_tab;
		return ($load_fresh)? get_option($op_name): $this->get_global($op_name);
	}

	// + 3.0.6
	public function reload_option_data($op_tab, $op_pre='evcal_options_'){
		$op_name = $op_pre.$op_tab;
		$this->loaded_options[$op_tab] = get_option($op_name);
	}

	// retrieve from global if value exists or get from DB and set to global
		private function get_global($op_name){	
			if(array_key_exists('EVO_Settings', $GLOBALS) && isset($GLOBALS['EVO_Settings'][$op_name])){
				global $EVO_Settings;
				return $EVO_Settings[$op_name];
			}else{
				return $GLOBALS['EVO_Settings'][$op_name] = get_option( $op_name);
			}		
		}

	// SET values for an option
		public function set_prop($field, $value){
			if(!isset($this->loaded_options[$this->current_options])) return false;

			if( !is_array( $this->loaded_options)) $this->loaded_options = array();

			$this->loaded_options[$this->current_options][$field] = $value;
			$op_name = $this->op_pre.$this->current_options;

			update_option($op_name, $this->loaded_options[$this->current_options]);

			return true;
		}
		// @4.5
		public function set_option_values( $new_values ){
			$op_name = $this->op_pre.$this->current_options;
			update_option($op_name, $new_values );

			//delete_option($op_name);
			//EVO_Debug( $op_name);
			//EVO_Debug( $new_values);

			return true;
		}


	// Testing  debug functions
	public function _print($op_tab){
		if(!isset($this->loaded_options[$op_tab])) return false;
		print_r($this->loaded_options[$op_tab]);
	}
	public function _get_loaded_op_tabs(){
		if(count($this->loaded_options)==0) return false;
		return array_keys( $this->loaded_options);
	}


	// Date format
		function get_date_format(){
			$_use_default_wp_date_format = $this->check_yn('evo_usewpdateformat', 'evcal_1');
			return $_use_default_wp_date_format? get_option('date_format'):'Y/m/d';
		}

	// Event Statuses Array
		function get_status_array($end = 'back'){
			$A = apply_filters('evo_event_statuses' ,array(
				'scheduled'=> 	array(evo_lang('Scheduled'), __('Scheduled','eventon') ),
				'cancelled'=> 	array(evo_lang('Cancelled'), __('Cancelled','eventon') ),
				'movedonline'=> array(evo_lang('Moved Online'), __('Moved Online','eventon') ),
				'postponed'=> 	array(evo_lang('Postponed'), __('Postponed','eventon') ),
				'rescheduled'=> array(evo_lang('Rescheduled'), __('Rescheduled','eventon') ),
				'preliminary'=> array(evo_lang('Preliminary'), __('Preliminary','eventon') ),
				'tentative'=> array(evo_lang('Tentative'), __('Tentative','eventon') ),
			));

			$O = array();
			foreach($A as $f=>$v){
				$O[ $f ] = ($end == 'back')? $v[1]: $v[0];
			}
			return $O;
		}

	// event get attendance mode array
		public function get_attendance_modes($end = 'back'){
			$A = apply_filters('evo_event_attendance_modes' ,array(
				'offline'=> array(evo_lang('Physical Event'), __('Physical Event','eventon') ),
				'online'=> 	array(evo_lang('Online Event'), __('Online Event','eventon') ),
				'mixed'=> array(evo_lang('Online and Physical Event'), __('Online and Physical Event','eventon') ),
			));

			$O = array();
			foreach($A as $f=>$v){
				$O[ $f ] = ($end == 'back')? $v[1]: $v[0];
			}
			return $O;
		}
	

	// Initial loading values for calendars
		
		// return full or short
		function get_all_day_names($type = 'full'){
			$N = array(0=>'sunday','monday','tuesday','wednesday','thursday','friday','saturday');
			$O = array();

			//$OPT = EVO()->cal->get_op('evcal_2');
			$OPT = $this->get_op('evcal_2');
			$lang =  evo_get_current_lang();

			$pre_var = $type == 'full'? 'evcal_lang_day': 'evo_lang_3Ld_';

			foreach($N as $i=>$nn){
				$_i = ($i==0)? 7:$i;

				$pre_var = ''; $def = $nn;
				if( $type == 'full'){
					$pre_var = 'evcal_lang_day';
				}
				if($type == 'three'){
					$pre_var = 'evo_lang_3Ld_';
					$def = substr($nn,0,3);
				}
				if($type == 'one'){
					$pre_var = 'evo_lang_1Ld_';
					$def = substr($nn,0,1);
				}

				$O[$i] = !empty($OPT[ $lang ][$pre_var.$_i]) ? $OPT[ $lang ][$pre_var.$_i]: $def;
			}

			return $O;
		}		
		function _get_all_month_names($type ='full'){
			$N = array(1=>'january','february','march','april','may','june','july','august','september','october','november','december');
			$O = array();

			$OPT = $this->get_op('evcal_2');
			$lang = evo_get_current_lang();

			$pre_var = $type == 'full'? 'evcal_lang_': 'evo_lang_3Lm_';

			foreach($N as $i=>$nn){
				$O[$i] = !empty($OPT[ $lang ][$pre_var.$i]) ? $OPT[ $lang ][$pre_var.$i]: 
					($type=='full'? $nn: substr($nn,0,3) );
			}

			return $O;
		}

	// Calendar settings @since 4.7
		public function can_load_eventon_content(){
			$this->set_cur('evcal_1');
			if( $this->check_yn('evo_load_scripts_only_onevo') ) return false;

			return true;
		}

	// Get eventtop text color values @4.7.4
		public function get_ett_color_prop(){
			$this->set_cur('evcal_1');

			$old_auto = $this->check_yn('evo_dis_auto_color') ?  true: false;
			$new = $this->get_prop('evo_ett_colorful_color');

			if( !$new ){
				return $old_auto ? 'auto' : 'custom';
			}else{
				return $new;
			}
		}
	// get custom ics url slug
		function get_ics_url_slug(){
			$url_slug = EVO()->cal->get_prop('evo_addtocal_slug','evcal_1');
			$url_slug = $url_slug ? $url_slug : 'export-events';

			// Filter to allow only letters, numbers, dashes, and underscores
			$url_slug = preg_replace('/[^a-zA-Z0-9_-]/', '', $url_slug);

			return $url_slug;

		}
		
}
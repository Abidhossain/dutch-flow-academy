<?php
/**
 * Event Slider shortcode
 * Handles all shortcode related functions
 *
 * @author 		AJDE
 * @category 	Core
 * @package 	EventON-SL/Functions/shortcode
 * @version     2.1.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class evosl_shortcode{
	
	function __construct(){
		add_shortcode('add_eventon_slider', array($this,'slider_events'));
		add_filter('eventon_shortcode_popup',array($this,'add_shortcode_options'), 10, 1);
		add_filter('eventon_shortcode_defaults',array($this,  'add_shortcode_defaults'), 10, 1);		
	}


	/**	Shortcode processing */	
		public function slider_events($atts){

			EVO()->frontend->load_evo_scripts_styles();
	        $supported_defaults = EVO()->evo_generator->get_supported_shortcode_atts();
	        $args = shortcode_atts($supported_defaults, $atts);
	        ob_start();
	        echo EVOSL()->frontend->get_slider_content($args);
	        return ob_get_clean();
					
		}

		public function update_shortcode_default($arr){
			return array_merge($arr, array(	'ux_val'=>3 ));
		}

	// add new default shortcode arguments
		function add_shortcode_defaults($arr){			
			return array_merge($arr, array(
				//'mobreaks'=>'no',
				'el_type'=>'ue',
				'slider_type'=>'def',
				'slide_style'=>'def',
				'control_style'=>'def',
				'slide_auto'=>'no',
				'slide_pause_hover'=>'no',
				'slide_hide_control'=>'no',
				'slide_nav_dots'=>'no',
				'slider_pause'=>'2000',
				'slider_speed'=>'400',
				'slides_visible'=>1,
			));			
		}

	/*	ADD shortcode buttons to eventON shortcode popup	*/
		function add_shortcode_options($shortcode_array){
			global $evo_shortcode_box;
			
			$new_shortcode_array = [[
            'id'=>'s_sl', 'name'=>__('Event Slider','evosl'), 'code'=>'add_eventon_slider',
            'variables'=>[
                $evo_shortcode_box->shortcode_default_field('cal_id'),
                ['name'=>__('Select Slider Type','evosl'), 'type'=>'select', 'var'=>'slider_type', 'options'=>[
                    'def'=>__('Default: Single Event','evosl'), 'multi'=>__('Multi-Events Horizontal Scroll','evosl'),
                    'mini'=>__('Mini Multi-Events Horizontal Scroll','evosl'), 'micro'=>__('Micro Multi-Events Horizontal Scroll','evosl'),
                    'vertical'=>__('Vertical Scroll','evosl')
                ]],
                ['name'=>__('Slides visible at once time','evosl'), 'type'=>'select', 'var'=>'slides_visible', 'guide'=>__('How many slides to be visible at once. This number may be reduced when on smaller screens.','evosl'), 'options'=>['1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5']],
                ['name'=>__('Slide Display Style','evosl'), 'type'=>'select', 'var'=>'slide_style', 'guide'=>__('Mini slider does not support image above data','evosl'), 'options'=>[
                    'def'=>__('Default: Just event data','evosl'), 'imgbg'=>__('Event Image as background','evosl'),
                    'imgtop'=>__('Event Image above data','evosl'), 'imgleft'=>__('Event Image left of data (only for single event slider type)','evosl')
                ]],
                ['name'=>__('Slide Controls Display Style','evosl'), 'type'=>'select', 'var'=>'control_style', 'options'=>[
                    'def'=>__('Default: Bottom arrow circles','evosl'), 'Dbac'=>__('Dark Bottom Arrow circles','evosl'),
                    'tb'=>__('Top/bottom arrow bars','evosl'), 'lr'=>__('Left/right arrow bars','evosl'), 'lrc'=>__('Left/right arrow circles','evosl')
                ]],
                ['name'=>__('Enable slides nav dots','evosl'), 'type'=>'YN', 'guide'=>__('Enabling this will add dots under slider for instance slide move','evosl'), 'var'=>'slide_nav_dots', 'default'=>'no'],
                ['name'=>__('Auto Start and Slide','evosl'), 'type'=>'YN', 'guide'=>__('This will make slider run automatically on load','evosl'), 'var'=>'slide_auto', 'default'=>'no', 'afterstatement'=>'slider_pause'],
                ['name'=>__('The Time (in ms) Between each Auto Transition','evosl'), 'type'=>'select', 'options'=>['2000'=>'2000','4000'=>'4000','6000'=>'6000','8000'=>'8000','10000'=>'10000'], 'guide'=>__('Miliseconds between each auto slide pause','evosl'), 'var'=>'slider_pause', 'default'=>'2000'],
                ['name'=>__('Pause Autoplay on Hover','evosl'), 'type'=>'YN', 'guide'=>__('This will pause the auto slider when hover','evosl'), 'var'=>'slide_pause_hover', 'default'=>'no', 'closestatement'=>'slider_pause'],
                ['name'=>__('Transition Duration (in ms)','evosl'), 'type'=>'select', 'options'=>['200'=>'200','400'=>'400','600'=>'600','800'=>'800','1000'=>'1000'], 'guide'=>__('How many miliseconds it will take for transition between each event slide','evosl'), 'var'=>'slider_speed', 'default'=>'400'],
                ['name'=>__('Hide Event Slide Controls','evosl'), 'type'=>'YN', 'guide'=>__('This will hide prev/next buttons on the slider','evosl'), 'var'=>'slide_hide_control', 'default'=>'no'],
                ['name'=>__('Select Event List Type','evosl'), 'type'=>'select', 'guide'=>__('Type of event list you want to show.','evosl'), 'var'=>'el_type', 'options'=>['ue'=>__('Default (Upcoming Events)','evosl'), 'pe'=>__('Past Events','evosl')]],
                ['name'=>__('Event Cut-off','evosl'), 'type'=>'select_step', 'guide'=>__('Past or upcoming events cut-off time. This will allow you to override past event cut-off settings for calendar events. Current date = today at 12:00am','evosl'), 'var'=>'pec', 'default'=>__('Current Time','evosl'), 'options'=>[
                    'ct'=>__('Current Time: ','evosl').date('m/j/Y g:i a', current_time('timestamp')),
                    'cd'=>__('Current Date: ','evosl').date('m/j/Y', current_time('timestamp')),
                    'ft'=>__('Fixed Time','evosl')
                ]],
                ['type'=>'open_select_steps', 'id'=>'ct'], ['type'=>'close_select_step'],
                ['type'=>'open_select_steps', 'id'=>'cd'], ['type'=>'close_select_step'],
                ['type'=>'open_select_steps', 'id'=>'ft'], $evo_shortcode_box->shortcode_default_field('fixed_d_m_y'), 
                ['type'=>'close_select_step'],
                $this->event_opening_array(),
                ['name'=>__('Number of Months','evosl'), 'type'=>'text', 'var'=>'number_of_months', 'default'=>'0', 'guide'=>__('If number of month is not provided, by default it will get events from one month either back or forward of current month','evosl'), 'placeholder'=>'eg. 5'],
                ['name'=>__('Event Count Limit','evosl'), 'type'=>'text', 'guide'=>__('Limit number of events displayed in the list eg. 3','evosl'), 'var'=>'event_count', 'default'=>'0', 'placeholder'=>'eg. 3'],
                $evo_shortcode_box->shortcode_default_field('event_order'),
                $evo_shortcode_box->shortcode_default_field('hide_mult_occur'),
                ['name'=>__('Show All Repeating Events While HMO','evosl'), 'type'=>'YN', 'guide'=>__('If you are hiding multiple occurence of event but want to show all repeating events set this to yes','evosl'), 'var'=>'show_repeats', 'default'=>'no'],
                $evo_shortcode_box->shortcode_default_field('event_type'),
                $evo_shortcode_box->shortcode_default_field('event_type_2'),
                $evo_shortcode_box->shortcode_default_field('etc_override'),
                $evo_shortcode_box->shortcode_default_field('only_ft')
            ]
        ]];
        return array_merge($shortcode_array, $new_shortcode_array);
    }

    function event_opening_array() {
        return [
        	'name'=>__('Open events as','evosl'), 'type'=>'select', 'var'=>'ux_val', 
        	'options'=>apply_filters('eventon_uix_shortcode_opts', [
            	'3'=>__('Lightbox popup window','evosl'), 
            	'3a'=>__('Lightbox popup window AJAX','evosl'), 
            	'4'=>__('Single Events Page','evosl'), 
            	'X'=>__('Do not interact','evosl')
        ])];
    }
}
?>
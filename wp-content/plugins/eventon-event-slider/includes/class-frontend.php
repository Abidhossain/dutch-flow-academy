<?php
/**
 * Event Slider front end class
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	eventon-slider/classes
 * @version     2.0.7
 */
class evosl_front{
	public $evopt1;
	function __construct(){
		$this->evopt1 = get_option('evcal_options_evcal_1');

		// scripts and styles 
		add_action( 'init', array( $this, 'register_styles_scripts' ) ,15);	

	}

	// STYLES: for photos 
		public function register_styles_scripts(){

			if(is_admin()) return false;
					
			wp_register_style( 'evosl_styles',EVOSL()->assets_path.'evosl_styles.css', array(), EVOSL()->version);
			
			//wp_register_script('mainscript',EVOSL()->assets_path.'evoslider.js', array('jquery'), EVOSL()->version, true );
			wp_register_script('evosl_script',EVOSL()->assets_path.'evosl_script.js', array('jquery'), EVOSL()->version, true );
			
			$this->print_scripts();
			add_action( 'wp_enqueue_scripts', array($this,'print_styles' ));			
		}
		public function print_scripts(){
			//wp_enqueue_script('mainscript');		
			wp_enqueue_script('evosl_script');		
		}
		function print_styles(){	wp_enqueue_style( 'evosl_styles');	}

		function remove_event_padding_style($_eventInAttr){
			$_eventInAttr['style'][] = 'padding-top: 0px;';
			return $_eventInAttr;
		}

	// Generate Slider HTML content
		
		public $shortcode_args;
		public function get_slider_content($args){	
			$args['show_et_ft_img']='yes';
			//$args['ux_val']='3';

			// slides visible override
				if( $args['slider_type'] =='def') $args['slides_visible'] = 1;
			
			$this->only__actions();
			$content = '';

			// Old shortcode values compatibility
				if($args['slider_type'] == 'imgab'){
					$args['slider_type'] = 'def'; $args['slide_style'] = 'imgtop';
				}
				if($args['slider_type'] == 'multiimgab'){
					$args['slider_type'] = 'multi'; $args['slide_style'] = 'imgtop';
				}
				if($args['slider_type'] == 'multiimgab') $args['slider_type'] = 'mini'; 
				if($args['slider_type'] == 'minicar') $args['slider_type'] = 'mini'; 

			// Full event featured image using tiles for slide style
				if( $args['slider_type'] == 'multi' && $args['slide_style'] == 'imgleft') 
					$args['slide_style'] = 'imgtop';

				if( $args['slider_type'] == 'mini' && $args['slide_style'] == 'imgtop'){
					$args['slide_style'] = 'def';
				} 

				if($args['slide_style'] == 'imgbg' || $args['slide_style'] == 'imgtop' || $args['slide_style'] == 'imgleft'){
					//add_filter('evo_cal_eventtop_in_attrs', array($this, 'remove_event_padding_style'), 10, 1);
					$args['tiles'] = 'yes';
					$args['tile_style'] = '0';
					$args['tile_bg'] = '1';
					if( $args['slide_style'] == 'imgtop' || $args['slide_style'] == 'imgleft' ){
						$args['tile_style'] = '1';
					}
				}

			// date time
				$DD = new DateTime( 'now', EVO()->calendar->cal_tz);
				$current_timestamp = EVO()->calendar->current_time;
				
			// CUT OFF time calculation
				//fixed time list
				if(!empty($args['pec']) && $args['pec']=='ft'){
					$__D = (!empty($args['fixed_date']))? $args['fixed_date']:date("j", current_time('timestamp'));
					$__M = (!empty($args['fixed_month']))? $args['fixed_month']:date("m", current_time('timestamp'));
					$__Y = (!empty($args['fixed_year']))? $args['fixed_year']:date("Y", current_time('timestamp'));

					$DD->setDate($_Y, $_M, $_D)->setTime(0, 0, 0);


				// current date cd
				}else if(!empty($args['pec']) && $args['pec']=='cd'){
					$DD->setTime(0,0,0);
				}

				// reset arguments
				$args['fixed_date']= $args['fixed_month']= $args['fixed_year']='';
			
			// restrained time unix
				$number_of_months = (!empty($args['number_of_months']))? (int)($args['number_of_months']):0;
				$month_dif = ($args['el_type']=='ue')? '+':'-';
										

			// upcoming events list 
				if($args['el_type']=='ue'){

					$__focus_start_date_range = $DD->format('U');

					$DD->modify('+' . ((int)$number_of_months - 1) . ' months')
						->modify('last day of this month')
						->setTime(23, 59, 59);
        			$__focus_end_date_range = $DD->format('U');

								
				}else{// past events list

					if (empty($args['event_order'])) $args['event_order'] = 'DESC';
			        $args['hide_past'] = 'no';

			        $__focus_end_date_range = $DD->format('U');

			        $DD->modify('-' . $number_of_months . ' months')->modify('first day of this month')->setTime(0, 0, 0);
			        $__focus_start_date_range = $DD->format('U');

			        // Calculate number of months
			        $min_date = min($__focus_start_date_range, $__focus_end_date_range);
			        $max_date = max($__focus_start_date_range, $__focus_end_date_range);
			        $i = 1;
			        while (($min_date = strtotime("+1 MONTH", $min_date)) <= $max_date) {
			            $i++;
			        }
			        $args['number_of_months'] = $i;

				}
			
			
			// Add extra arguments to shortcode arguments
			$new_arguments = array(
				'focus_start_date_range'=>$__focus_start_date_range,
				'focus_end_date_range'=>$__focus_end_date_range,
			);

			// Alter user interaction
				if($args['ux_val']== '1' || !isset( $args['ux_val']) || $args['ux_val'] == 0) 	$args['ux_val'] = 3;		

			//print_r($args);
			$args = (!empty($args) && is_array($args))? 
				wp_parse_args($new_arguments, $args): $new_arguments;


			// PROCESS variables
			$args__ =  EVO()->calendar->process_arguments($args);
			$this->shortcode_args = EVO()->calendar->shortcode_args = $args__;
			
			
			// Content for the slider
			$content .= $this->html_header($args__);

			$content .= EVO()->evo_generator->_generate_events( 'html');

			$content .= $this->html_footer( $args__ );

			$this->remove_only__actions();

			remove_filter('evo_cal_eventtop_in_attrs', array($this, 'remove_event_padding_style'), 10, 1);
			
			return  $content;	
		}

		// Header content for the slider
			function html_header($args){
				global $eventon;

				$dataString = '';

				// need compatibility for
					/*
					imgab, multiimgab, multimini - slider_type
					*/

				// class names for slider container
				$classNames = array();
				if(!empty($this->evopt1['evo_rtl']) && $this->evopt1['evo_rtl']=='yes')	
					$classNames[] = 'rtlslider';
				if(!empty($args['slider_type']) ) 	$classNames[] = $args['slider_type'].'Slider';

				if(isset($args['control_style'])) $classNames[] = 'cs_'.$args['control_style'];
				if(isset($args['slide_style'])) $classNames[] = 'ss_'.$args['slide_style'];

				$classNames[] ='etttc_'. EVO()->cal->get_ett_color_prop();
				$classNames[] ='color';
				
				array_filter($classNames);
				if(is_array($classNames)) $class_names = implode(' ', $classNames);

				$cal_id = (empty($cal_id))? rand(100,900): $cal_id;
				$cal_id = str_replace(' ', '-', $cal_id);

				$out = '';
				$out .= '<div id="evcal_calendar_'. esc_attr( $cal_id ) .'" class="ajde_evcal_calendar evoslider evosliderbox '. esc_attr( $class_names ) .' sltac">';
				$out .= '<div class="evo_slider_outter" >
					<div class="evo_slider_slide_out">';
	            
	            $out .= EVO()->elements->get_preload_html( array(
	            	's'=> array(	            		
						array('w'=> '100%', 'h'=>'50px'),
						array('w'=> '100%', 'h'=>'50px'),
						array('w'=> '100%', 'h'=>'50px'),
						array('w'=> '100%', 'h'=>'50px'),
						array('w'=> '50%', 'h'=>'50px'),
					),
					'pclass'=>'evofx_ai_c',
					'echo'=> false
	            ));

	            $out .= '<div class="eventon_events_list" style="display:none">';


	           

	            return $out;
			}

			function html_footer( $args ){


				$out = '';
				$out .= '</div>';
				$out .= '</div>';				
				$out .= '</div>';
				$out .= '<div class="evosl_footer_outter"><div class="evosl_footer">';
				$out .= '</div></div>';

				ob_start();
				EVO()->calendar->body->print_evo_cal_data();
				$out .= ob_get_clean();
				$out .= '</div>';
				return $out;
			}


	// SUPPORT functions
		// ONLY for el calendar actions 
		public function only__actions(){
			add_filter('eventon_cal_class', array($this, 'eventon_cal_class'), 10, 1);	
		}
		public function remove_only__actions(){
			//add_filter('eventon_cal_class', array($this, 'remove_eventon_cal_class'), 10, 1);
			remove_filter('eventon_cal_class', array($this, 'eventon_cal_class'));				
		}
		// add class name to calendar header for DV
		function eventon_cal_class($name){
			$name[]='evoSL';
			return $name;
		}
		// add class name to calendar header for DV
		function remove_eventon_cal_class($name){
			if(($key = array_search('evoSL', $name)) !== false) {
			    unset($name[$key]);
			}
			return $name;
		}
		// function replace event name from string
			function replace_en($string){
				return str_replace('[event-name]', "<span class='eventName'>Event Name</span>", $string);
			}		
		
	    
}

<?php
/** 
 * Helper functions to be used by eventon or its addons
 * front-end only
 *
 * @version 4.9.8
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class evo_helper{	
	public $options2, $opt2;
	public function __construct(){
		$this->opt2 = get_option('evcal_options_evcal_2'); 		
	}

	// Process permalink appends
		public function process_link($link,  $var, $append, $force_par = false){
			if(strpos($link, '?')=== false ){

				if($force_par){
					if( substr($link,-1) == '/') $link = substr($link,0,-1);
					$link .= "?".$var."=".$append;
				}else{
					if( substr($link,-1) == '/') $link = substr($link,0,-1);
					$link .= "/".$var."/".$append;
				}
				
			}else{
				$link .= "&".$var."=".$append;
			}
			return $link;
		}
	// process urls to complete url @4.8.1
		public function _process_url($url){
			return strpos($url, 'http') === false ? 'https://'. $url : $url;
		}

	// sanitization
		// @since 4.9.8
		public function validate_request( 
			$nonce_field = 'nn', 
			$nonce_action = 'eventon_admin_nonce', 
			$capability = 'edit_eventons', 
			$require_admin = false, 
			$require_auth = true, 
			$output_type = 'json' , 
			$use_admin_referer = false
		) {
		    $error_msg = '';

		    // Check if in admin context if required
		    if ( $require_admin && ! is_admin() ) {
		        $error_msg = __( 'Only available in admin side.', 'eventon' );
		    }
		    // Check authentication if required
		    elseif ( $require_auth && ! is_user_logged_in() ) {
		        $error_msg = __( 'Authentication required', 'eventon' );
		    }
		    // Verify user permissions if capability is specified
		    elseif ( $capability && ! current_user_can( $capability ) ) {
		        EVO_Debug( 'Unauthorized access attempt to ' . $nonce_action );
		        $error_msg = __( 'You do not have proper permission', 'eventon' );
		    } 
		    // admin referer check
		    elseif ( $use_admin_referer ) {
		        if ( ! check_admin_referer( $nonce_action, $nonce_field ) ) {
		        	EVO_Debug( 'Nonce failed for ' . $nonce_action );
		            $error_msg = __( 'Nonce or referrer validation failed', 'eventon' );
		        }
		    } 
		    // Verify nonce
		    elseif ( empty( $_REQUEST[$nonce_field] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST[$nonce_field] ), $nonce_action ) ) {
		    	EVO_Debug( 'Nonce failed for ' . $nonce_action );
		        $error_msg = __( 'Nonce validation failed', 'eventon' );
		    }

		    // Handle output based on $output_type
		    if ( $error_msg ) {
		        if ( $output_type === 'json' ) {
		            wp_send_json( array( 'status' => 'bad', 'msg' => $error_msg ) );
		        } else {
		            wp_die( $error_msg );
		        }
		    }
		}
		// @4.5.5
		public function sanitize_xss($value){
			return str_replace(array(
				'alert(', 'onanimationstart', 'onclick'
			), '', $value);
		}
		// @+ 4.0.3
		public function sanitize_array($array){
			return $this->recursive_sanitize_array_fields($array);
		}
		// @ 4.7.2
		// returned sanitized $_POST
		public function sanitize_post(){
			return $this->recursive_sanitize_array_fields($_POST);
		}
		public function recursive_sanitize_array_fields($array){
			if(is_array($array)){
				$new_array = array();
				foreach ( $array as $key => $value ) {
		        	if ( is_array( $value ) ) {
		        		$key = sanitize_title($key);
		            	$new_array[ $key ] = $this->recursive_sanitize_array_fields($value);
		        	}
		        	else {
		            	$new_array[ $key ] = sanitize_text_field( $value );
		        	}
	    		}

	    		return $new_array;
	    	}else{
	    		return sanitize_text_field( $array );	    		
	    	}
		}	
		

		// check ajax submissions for sanitation and nonce verification
		// @+3.1
		public function process_post($array, $nonce_key='', $nonce_code='', $filter = true){
			$array = $this->sanitize_array( $array);

			if( !empty($nonce_key) && !empty($nonce_code)){

				if( !wp_verify_nonce( $array[ $nonce_key], $nonce_code ) ) return false;
			}

			if($filter)	$array = array_filter( $array );
			return $array;
		}	

		// sanitize html content @u 4.6.1
			function sanitize_html($content){
				if( !EVO()->cal->check_yn('evo_sanitize_html','evcal_1')) return $content;

				if( is_array($content)) return $content;

				//return wp_kses_post( $content );

				return wp_kses( $content, apply_filters('evo_sanitize_html', array( 
				    'a' => array(
			            'href' => array(),
			            'title' => array()
			        ),
			        'br' => array(),
			        'p' => array(),
			        'i' => array(),
			        'b' => array(),
			        'u' => array(),              
			        'ul' => array(),
			        'li' => array(),
			        'em' => array(),
			        'strong' => array(),
			        'span' => array(
			            'class' => array(),
			            'style' => array(),
			        ),
			        'font' => array(
			            'color' => array()
			        ),
			        'img' => array(
			            'src'      => true,
			            'srcset'   => true,
			            'sizes'    => true,
			            'class'    => true,
			            'id'       => true,
			            'width'    => true,
			            'height'   => true,
			            'alt'      => true,
			            'title'    => true,
			            'align'    => true,
			            'style'    => true,
			            'data-*'   => true, // Allow any data attributes
			        ),
				) ) );
			}
			function sanitize_html_for_eventtop( $content ){
				return wp_kses( $content, apply_filters('evo_sanitize_html_eventtop',
					array( 				    
				    'i' => array(),
				    'b' => array(),
				    'u' => array(),			    
				    'br' => array(),
				    'em' => array(),
				    'strong' => array(),
				    'img' => array(
				    	'src'      => true,
				        'srcset'   => true,
				        'sizes'    => true,
				        'class'    => true,
				        'id'       => true,
				        'width'    => true,
				        'height'   => true,
				        'alt'      => true,
				        'align'    => true,
				    ),
				) ) );
			}


		// sanitize unix
		function sanitize_unix( $unix){
			$t = explode('Z', $unix);
			$u = explode('T', $t[0]);

			$a = (int)$u[0];
			$b = isset($u[1]) ? (int)$u[1]:0;

			if(strlen($a)<6) $a = sprintf('%06d', $a);
			if(strlen($b)<6) $b = sprintf('%06d', $b);

			return $a.'T'. $b .'Z';
		}


	// Create posts 
		function create_posts($args){
			$DATA_Store = new EVO_Data_Store();
			return $DATA_Store->create_new( $args );
		}

		function create_custom_meta($post_id, $field, $value){
			add_post_meta($post_id, $field, $value);
		}

	// check if post exist for a ID
	// @+ 3.0
		function post_exist($ID, $post_status = 'publish'){
			global $wpdb;

			$post_id = $ID;
			$post_exists = $wpdb->get_row(
				$wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE id = %d AND post_status = %s", $post_id, $post_status)
			, 'ARRAY_A');
			return $post_exists? $post_exists['ID']: false;
		}
			
	// Eventon Settings helper
		function get_email_preview_html(  $args = array()){ // @4.9.2
			return $this->get_html( 'email_preview', $args);
		}
		function get_html($type, $args){
			extract(array_merge(array(
				'subject'=> 'Email Subject',
				'to'=> 'json@msn.com',
				'from_name'=> get_bloginfo('name'), 
				'from_email' => get_bloginfo('admin_email'),
				'message'=> 'Message_content_here',
				'wrap_message'=> false, // wrap message in eventON email wrappers
				'headers'=> array(),
			), $args));
			switch($type){
				case 'email_preview':

					$content = "<div class='evomarb10 evodfx evofx_dr_c evogap5'>";
					$content .= "<p class='evomarb0i evopadb0i'><b>". __('Subject') ."</b>: ". $subject ."</p>";
					$content .= "<p class='evomarb0i evopadb0i'><b>". __('To') ."</b>: ". $to ."</p>";
					$content .= "<p class='evomarb0i evopadb0i'><b>". __('From') ."</b>: ". $from_name .' [ '. $from_email ." ]</p>";
					
					if( count($headers) > 0 ){
						foreach($headers as $header){
							$content .= "<p class='evomarb0i evopadb0i evoop7'><b>". __('Headers') ."</b>: ".$header.'</p>';
						}						
					}
					$content .= "</div>";
					
					// message
					$msg = "<div class='' style='padding:15px'>". $message . "</div>";

					$content .= "<div class='evobr15 evo_of_h'>";

					if( $wrap_message ) $content .= EVO()->get_email_part('header');
					$content .= $message;
					if( $wrap_message ) $content .= EVO()->get_email_part('footer');

					$content .= "</div>";

					return $content;

					ob_start();
					echo '<div class="evo_email_preview">';
					echo '<p>Headers: '.$args['headers'][0].'</p>';
					echo '<p>To: '.$args['to'].'</p>';
					echo '<p>Subject: '.$args['subject'].'</p>';
					echo '<div class="evo_email_preview_body">'.$args['message'].'</div></div>';
					return ob_get_clean();
				break;
			}
		}

	// ADMIN & Frontend Helper
		// @updated 4.5.7
		public function send_email($args){
			$defaults = array(
				'html'=>'yes',
				'preview'=>'no',
				'to'=>'',
				'bcc'=>'', // @s 4.5.5 support array() or single
				'from'=>'',
				'from_name'=>'','from_email'=>'',
				'header'=>'',
				'subject'=>'',
				'message'=>'',
				'type'=>'',// bcc
				'attachments'=> array(),
				'return_details'=>false,
				'reply-to' => ''
			);

			$args = wp_parse_args( $args, $defaults );
			//extract($args);

			if($args['html']=='yes'){
				add_filter( 'wp_mail_content_type',array($this,'set_html_content_type'));
				add_filter( 'wp_mail_charset', array($this,'change_mail_charset') );
			}

			$headers = '';

			if(!empty($args['header'])){
				$headers = $args['header'];
			}else{
				$headers = array();
				if(empty($args['from_email'])){
					$headers[] = 'From: '.$args['from'];
				}else{
					$headers[] = 'From: '.(!empty($args['from_name'])? $args['from_name']:'') .' <'.
						$args['from_email'] . '>';
				}
			}	


			// add reply to into headers // @2.8.6
				if(!empty($args['reply-to']) && isset($args['reply-to'])){

					if( is_array($headers)){
						$headers[] = 'Reply-To: '. $args['reply-to'];
					}else{
						$headers .= 'Reply-To: '. $args['reply-to'];
					}
				}

			// email type as html
				if($args['html']=='yes'){
					if( is_array($headers)){
						$headers[] = 'Content-Type: text/html; charset=UTF-8';	
					}else{
						$headers .= 'Content-Type: text/html; charset=UTF-8';	
					}
				}

			$return = '';	

			if($args['preview']=='yes'){
				$return = array(
					'to'=>$args['to'],
					'subject'=>$args['subject'],
					'message'=>$args['message'],
					'headers'=>$headers
				);
			
			// bcc version of things everything
			}else if(!empty($args['type']) && $args['type']=='bcc' ){
				if(is_array($args['to']) ){
					foreach($args['to'] as $EM){
						$headers[] = "Bcc: ".$EM;
					}
				}else{
					$headers[] = "Bcc: ".$args['to'];
				}

				$return = wp_mail($args['from'], $args['subject'], $args['message'], $headers, $args['attachments']);	
			}else{

				// if bcc emails are provided add those to header @4.5.7
					if( !empty( $args['bcc'])){
						if(is_array( $args['bcc'] ) ){
							foreach( $args['bcc'] as $bcc_email){
								$headers[] = "Bcc: ".$bcc_email;
							}
						}else{	$headers[] = "Bcc: ". $args['bcc'];	}
					}

				// Send email using wp_mail()
				$return = wp_mail($args['to'], $args['subject'], $args['message'], $headers, $args['attachments']);
			}

			if($args['html']=='yes'){
				remove_filter( 'wp_mail_content_type', array($this,'set_html_content_type') );
			} 

			if($args['return_details']){
				// get the errors
				$ts_mail_errors = array();
				if(!$return){
					global $ts_mail_errors;
					global $phpmailer;

					if (!isset($ts_mail_errors)) $ts_mail_errors = array();

					if (isset($phpmailer)) {
						$ts_mail_errors[] = $phpmailer->ErrorInfo;
					}
				}
				return array('result'=>$return, 'error'=>$ts_mail_errors);
			}else{
				return $return;
			}
			
		}	
		
		// set a custom encoding character type for emails
		function change_mail_charset( $charset ) {

			$encoding = EVO()->cal->get_prop('_evo_email_encode','evcal_1');

			if( !$encoding) return $charset;
			if( $encoding == 'def') return $charset;
			if( $encoding == 'utf8') return 'UTF-8';
			if( $encoding == 'utf16') return 'UTF-16';
		}
	 
		function set_html_content_type() {
			return 'text/html';
		}
		function set_charset_type() {
			return 'utf8';
		}

		// GET email body with eventon header and footer for email included
		public function get_email_body_content($message='', $outside = true){
			
			ob_start();
			if($outside) echo EVO()->get_email_part('header');
			echo !empty($message)? $message:'';
			if($outside) echo EVO()->get_email_part('footer');
			return ob_get_clean();
		}

	// YES NO Button
		function html_yesnobtn($args=''){
			return EVO()->elements->yesno_btn( $args );
		}

	// tool tips
		function tooltips($content, $position='', $handleClass=false, $echo = false){
			return EVO()->elements->tooltips( $content, $position, $echo, $handleClass);
		}
		function echo_tooltips($content, $position=''){
			$this->tooltips($content, $position='',true);
		}

	// ICS - date time processing
		public function get_ics_format_from_unix($unix, $separate = true){
			$enviro = new EVO_Environment();

			$unix = $unix - $enviro->get_UTC_offset();

			if( !$separate) return $unix;


			$new_timeT = date("Ymd", $unix);
			$new_timeZ = date("Hi", $unix);

			return $new_timeT.'T'.$new_timeZ.'00Z';
		}


		// Escape ICS text
			function esc_ical_text( $text='' ) {
				
			    $text = str_replace("\\", "", $text);
			    $text = str_replace("\r", "\r\n ", $text);
			    $text = str_replace("\n", "\r\n ", $text);
			    $text = str_replace(",", "\, ", $text);
			    $text = EVO()->calendar->helper->htmlspecialchars_decode($text);
			    return $text;
			}

	// template locator
	// pass: paths array, file name, default template with full path and file
		function template_locator($paths, $file, $template){
			foreach($paths as $path){
				if(file_exists($path.$file) ){	
					$template = $path.$file;
					break;
				}
			}				
			if ( ! $template ) { 
				$template = AJDE_EVCAL_PATH . '/templates/' . $file;
			}

			return $template;
		}	
	// Humanly readable time
	// @+ 4.6.9
		function get_human_time($time){

			$output = '';
			$minFix = $hourFix = $dayFix = 0;

			$day = $time/(60*60*24); // in day
			$dayFix = floor($day);
			$dayPen = $day - $dayFix;
			if($dayPen > 0)
			{
				$hour = $dayPen*(24); // in hour (1 day = 24 hour)
				$hourFix = floor($hour);
				$hourPen = $hour - $hourFix;
				if($hourPen > 0)
				{
					$min = $hourPen*(60); // in hour (1 hour = 60 min)
					$minFix = floor($min);
					$minPen = $min - $minFix;
					if($minPen > 0)
					{
						$sec = $minPen*(60); // in sec (1 min = 60 sec)
						$secFix = floor($sec);
					}
				}
			}
			$str = "";
			if($dayFix > 0)
				$str.= $dayFix . " " . ( $dayFix > 1 ? evo_lang('Days') : evo_lang('Day') );
			if($hourFix > 0)
				$str.= ' '. $hourFix . ' '. ( $hourFix > 1 ? evo_lang('Hours') : evo_lang('Hour') );
			if($minFix > 0)
				$str.= ' '. $minFix . ' '. ( $minFix > 1 ? evo_lang('Minutes') : evo_lang('Minute') );
			//if($secFix > 0)	$str.= $secFix." sec ";
			return $str;
		}

	// Woocommerce related @updated 4.9.11
		function convert_to_currency($price, $symbol = true){		
			// full returns complete array of data

			if (is_array($price) || is_null($price) || $price === ''){
				return $price;
			}

		    // Step 1: Parse input to a float
		    $price_str = (string) $price;
		    $price_str = trim($price_str);
		    $price_str = preg_replace('/[^0-9.,-]/', '', $price_str); // Remove non-numeric except .,,-

		    // Handle negative numbers
		    $is_negative = strpos($price_str, '-') === 0;
		    $price_str = str_replace('-', '', $price_str);

		    // Detect decimal separator based on last occurrence of . or ,
		    $dot_pos = strrpos($price_str, '.');
		    $comma_pos = strrpos($price_str, ',');

		    if ($dot_pos !== false && $comma_pos !== false) {
		        // Last one is decimal separator
		        if ($dot_pos > $comma_pos) {
		            $price_str = str_replace(',', '', $price_str); // Comma is thousand separator
		        } else {
		            $price_str = str_replace('.', '', $price_str); // Dot is thousand separator
		            $price_str = str_replace(',', '.', $price_str); // Comma to decimal point
		        }
		    } elseif ($comma_pos !== false) {
		        $parts = explode(',', $price_str);
		        if (isset($parts[1]) && strlen($parts[1]) <= 2) {
		            $price_str = str_replace(',', '.', $price_str); // Comma as decimal
		        } else {
		            $price_str = str_replace(',', '', $price_str); // Comma as thousand
		        }
		    } elseif ($dot_pos !== false) {
		        $parts = explode('.', $price_str);
		        if (isset($parts[1]) && strlen($parts[1]) > 2) {
		            $price_str = str_replace('.', '', $price_str); // Dot as thousand
		        }
		    }

		    $decimal_number = floatval($price_str);
		    if ($is_negative) {
		        $decimal_number = -$decimal_number;
		    }

		    // Step 2: Use WooCommerce's wc_price for formatting
		    $formatted_with_symbol = wc_price($decimal_number);

		    // Step 3: Format without symbol using WooCommerce settings
		    $decimals = wc_get_price_decimals();
		    $decimal_separator = wc_get_price_decimal_separator();
		    $thousand_separator = wc_get_price_thousand_separator();
		    $formatted_without_symbol = number_format(
		        abs($decimal_number),
		        $decimals,
		        $decimal_separator,
		        $thousand_separator
		    );
		    if ($is_negative) {
		        $formatted_without_symbol = '-' . $formatted_without_symbol;
		    }

		    // if return just the price
		    return $symbol ? $formatted_with_symbol : $formatted_without_symbol;

		    // Return array
		    $output =  array(
		        'decimal' => $decimal_number,          // 12.65 or 1332.88
		        'formatted' => $formatted_with_symbol, // "€12,65" or "€1.332,88"
		        'plain' => $formatted_without_symbol,   // "12,65" or "1.332,88"
		        'raw'=> $price,		        
		    );

		    // debug
		    $debug = false;
		    if( $debug){
		    	$output['settings' ]= [
		        	'decimals'=> $decimals,
		        	'decimal_separator'=> $decimal_separator,
		        	'thousand_separator'=> $thousand_separator,
		        ];
		        EVO_Debug($output);
		    }

		    return $output;
		}
		function get_price_format_data(){
			return array(
				'currencySymbol'=>get_woocommerce_currency_symbol(),
				'thoSep'=> get_option('woocommerce_price_thousand_sep'),
				'curPos'=> get_option('woocommerce_currency_pos'),
				'decSep'=> get_option('woocommerce_price_decimal_sep'),
				'numDec'=> get_option('woocommerce_price_num_decimals')
			);
		}

	
	// convert array to data element values - 3.1 / U 4.9
		public function array_to_html_data($array = array() ){

			if( count($array) == 0) return null;

			$html = '';
			foreach($array as $k=>$v){
				if( is_array($v)) $v = htmlspecialchars( json_encode($v), ENT_QUOTES);
				$html .= 'data-'. $k .'="'. $v .'" ';
			}
			return $html;
		}

	// Timezones	
		//@4.7.1
		private function __get_evo_timezone_choices($selected_zone, $locale = null ){

		
			return apply_filters(
				'evo_events_timezone_choice',
				wp_timezone_choice( $selected_zone, $locale ),
				$selected_zone,
				$locale
			);
		}

		// return readable list of wp based timezone values @4.7.1
		function get_modified_wp_timezone_list($unix = ''){
			// using WP timezones
			$html = $this->__get_evo_timezone_choices('UTC');
			//EVO_Debug($html);

			preg_match_all('/<option value="([^"]+)">/', $html, $matches);
			$tzs = $matches[1];

			$DD = new DateTime( 'now' );

			// if unix is passed adjust according to time present in unix
			//if( !empty( $unix ))	$DD->setTimestamp( $unix );

			$updated_zones = array();
			foreach($tzs as $tz_string ){

				if(	strpos($tz_string, 'UTC') !== false ) continue;

				try {
					$DD->setTimezone( new DateTimeZone( $tz_string ));
				}
				catch (Exception $e) {
					// invalid timezone name
					error_log(print_r($e->getMessage(), TRUE));
					continue;
				}

				$updated_zones[ $tz_string ] = '(GMT'. $DD->format('P').') '. $tz_string;
				
			}

			return $updated_zones;
		}

		// @updated 4.5.7
		// $unix value passed to calculate DST for a given date - otherwise DST for now
		function get_timezone_array( $unix = '' , $adjusted = true) {
			return $this->get_modified_wp_timezone_list( $unix );
		}


		// return time offset from saved timezone values @4.5.2
		public function _get_tz_offset_seconds( $tz_key, $unix = ''){

			$DD = new DateTime( 'now' );

			// set passed on tz key
			try {
				$DD->setTimezone( new DateTimeZone( $tz_key ));
			}
			catch (Exception $e) {
				// invalid timezone name
				error_log(print_r($e->getMessage(), TRUE));	
				$DD->setTimezone( new DateTimeZone( 'UTC' ));			
			}

			if( !empty( $unix ))	$DD->setTimestamp( $unix );

			$GMT_value = $DD->format('P');

			// if it is UTC 0
			if(strpos($GMT_value, '+0:') !== false)	return 0;

			// alter
			if(strpos($GMT_value, '+') !== false){
				$ss = str_replace('+', '-', $GMT_value);
			}else{
				$ss = str_replace('-', '+', $GMT_value);	
			}

			// convert to seconds
			sscanf($ss, "%d:%d", $hours, $minutes);

			return $hours * 3600 + $minutes * 60;
		}

		// return GMT value
		function get_timezone_gmt($key, $unix = false){

			$DD = new DateTime();
			if($unix) $DD->setTimestamp($unix);
			$DD->setTimezone( new DateTimeZone( $key ));

			return 'GMT'. $DD->format('P');
		}

}
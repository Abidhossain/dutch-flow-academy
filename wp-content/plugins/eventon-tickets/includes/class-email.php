<?php
/*
 * EMAILING object for event tickets
 * @version 2.2.10
 */

class evotx_email{
	public function __construct(){}

	function get_ticket_email_body($args){
		global $eventon;

		$evoHelper = new evo_helper();
		// get email body content with eventon header and footer
		return $evoHelper->get_email_body_content($this->get_ticket_email_body_only($args));
		//return $this->get_ticket_email_body_only($args);
	}		
	function get_ticket_email_body_only($args){
		ob_start();

		$args = array($args, true);
		// email body message
			$file_location = EVO()->template_locator(
				'ticket_confirmation_email.php', 
				EVOTX()->plugin_path."/templates/email/", 
				'templates/email/tickets/'
			);
			include($file_location);

		return ob_get_clean();
	}
	// this will return eventon email template driven email body
	// need to update this after evo 2.3.8 release
		function get_evo_email_body($message){
			global $eventon;
			// /echo $eventon->get_email_part('footer');
			ob_start();
			echo $eventon->get_email_part('header');
			echo $message;
			echo $eventon->get_email_part('footer');
			return ob_get_clean();
		}
	// reusable tickets HTML for an order -- not used anywhere
		function get_tickets($tix, $email=false){

			$args = array($tix, $email);

			// GET email HTML content
			$message = $this->get_ticket_email_body_only($args);
			return $message;
		}

	// EMAIL the ticket 
		public function send_ticket_email($order_id, $outter_shell = true, $initialSend = true, $toemail=''){
			// initials
				global $woocommerce, $evotx;
				$send_wp_mail = false;

			$order = new WC_Order( $order_id );

			// check if order contain event ticket data
			if( $order->get_meta('_order_type') ){

				// check if email is already sent
				if($initialSend){
					$emailSentAlready = $order->get_meta('_tixEmailSent') ? $order->get_meta('_tixEmailSent'):false;
					if($emailSentAlready) return false;
				}

				EVO()->cal->set_cur( 'evcal_tx');
				$evotx_opt = $evotx->evotx_opt;

				$evotx_tix = new evotx_tix();

				$ticket_numbers = $evotx_tix->get_ticket_numbers_for_order($order);
	
				// if there are no ticket numbers in the order				
				if(!$ticket_numbers) return false;
					

				// if a customer account was created
				if( $order->get_customer_id() ){
					$usermeta = get_user_meta( $order->get_customer_id() );
					$__to_email = $usermeta['billing_email'][0];
					$__customer_name = $usermeta['first_name'][0].' '.$usermeta['last_name'][0];
				}else{
					$__to_email = $order->get_billing_email();
					$__customer_name = $order->get_billing_first_name.' '.$order->get_billing_last_name;
				}
				

				// + subject line replacement tags

				// update to email address if passed
					$__to_email = (!empty($toemail))? $toemail: $__to_email;


					// if to email is not present
					if(empty($__to_email)) return false;

				
				// arguments for email body
					$email_body_arguments = array(
						'orderid'=>$order_id,
						'tickets'=>$ticket_numbers, 
						'customer'=>$__customer_name,
						'email'=>'yes' 
					);
				
					$from_email = $this->get_from_email();

					

					$subject = '[#'.$order_id.'] '. $this->get_subject();
					$headers = 'From: '.$from_email;	

					// get the email body				
					$body = ($outter_shell)? 
						$this->get_ticket_email_body($email_body_arguments): 
						$this->get_ticket_email_body_only($email_body_arguments);
					

				// Send the email
					$helper = new evo_helper();
					$data = apply_filters('evotx_beforesend_tix_email_data', array(
						'to'=>$__to_email,
						'subject'=>$subject,
						'message'=> $body,
						'from'=>$from_email,
						'html'=>'yes',
						'bcc'=>''
					), $order_id);

					
					
					$send_wp_mail = $helper->send_email($data);
					
					

				// if initial sending ticket email record that
				if($initialSend ){
					($send_wp_mail)?
						$order->update_meta_data('_tixEmailSent',true):
						$order->update_meta_data('_tixEmailSent',false);

					$order->save();
				}

				//echo $__to_email.' '.$headers;

				return $send_wp_mail;
			}else{
				return false;
			}
		}

		// emailing helpers @2.4.2
			public function get_subject(){
				EVO()->cal->set_cur( 'evcal_tx');

				$subject_content = __('Event Ticket','evotx');
				if( EVO()->cal->get_prop('evotx_notfiesubjest') ) 
					$subject_content = esc_html( EVO()->cal->get_prop('evotx_notfiesubjest') );

				return $subject_content;
			}

			function get_from_email(){
				
				$_from_email = $this->get_from_email_address();

				$_from_name = $this->get_from_email_name();
				
				// need space before < otherwise first character get cut off
				$from_email = (!empty($_from_name))? 
						$_from_name.' <'.$_from_email.'>' : $_from_email;

				return $from_email;
			}
			public function get_from_email_name(){

				EVO()->cal->set_cur( 'evcal_tx');

				$from_name = EVO()->cal->get_prop('evotx_notfiemailfromN');
				return $from_name ? htmlspecialchars_decode ( $from_name ) : get_bloginfo('name');

			}
			public function get_from_email_address(){

				EVO()->cal->set_cur( 'evcal_tx');

				$from_email = EVO()->cal->get_prop('evotx_notfiemailfrom');
				return $from_email ? htmlspecialchars_decode ( $from_email ) : get_bloginfo('admin_email');

			}

}
<?php
/** 
 * AJAX for only backend of the tickets
 * @version 2.4.12
 */
class evotx_admin_ajax{
	public $help, $post_data;
	public function __construct(){
		$ajax_events = array(
			'the_ajax_evotx_a1'=>'evotx_get_attendees',			
			'the_ajax_evotx_a3'=>'generate_csv',
			'the_ajax_evotx_a55'=>'admin_resend_confirmation',
			'evoTX_ajax_07'=>'get_ticektinfo_by_ID',
			'the_ajax_evotx_a8'=>'emailing_attendees_admin',
			'evotx_assign_wc_products'=>'assign_wc_products',
			'evotx_save_assign_wc_products'=>'save_assign_wc_products',
			
			'evotx_sales_insight'=>'evotx_sales_insight',
			'evotx_sync_with_order'=>'evotx_sync_with_order',
			'evotx_emailing_form'=>'evotx_emailing_form',

			'evotx_email_preview'=>'evotx_email_preview',

			'evotx_get_event_tix_settings'=>'get_event_settings',
			'evotx_save_event_tix_settings'=>'save_event_settings',

			'evotx_manual_tickets_gen'=>'manual_generate_tickets',
		);
		foreach ( $ajax_events as $ajax_event => $class ) {
			add_action( 'wp_ajax_'.  $ajax_event, array( $this, $class ) );

			$nopriv_class = method_exists($this, 'nopriv_'. $class )? 'nopriv_'. $class: $class;
			add_action( 'wp_ajax_nopriv_'.  $ajax_event, array( $this, $nopriv_class ) );
		}

		$this->help = new evo_helper();
		$this->post_data = $this->help->sanitize_array( $_POST );
	}

	// event settings
		function get_event_settings(){

			// check permission
			if( !current_user_can('edit_post', $this->post_data['eid'] ) ){
				wp_send_json(array(
					'status'=>'bad',
					'content'=> __('Required permission missing', 'evotx')
				));
				wp_die();
			}

			$EVENT = new EVOTX_Event( $this->post_data['eid'] );

			ob_start();

			include_once('views-event_settings.php');

			wp_send_json(array(
				'status'=>'good',
				'content'=> ob_get_clean()
			));
			wp_die();

		}

		// save ticket settings
		function save_event_settings(){

			// check permission
			if( !current_user_can('edit_post', $this->post_data['event_id'] ) ){
				wp_send_json(array(
					'status'=>'bad',
					'content'=> __('Required permission missing', 'evotx')
				));
				wp_die();
			}

			global $evotx_admin;

			$post_data = $this->help->sanitize_array( $_POST);
			$EVENT = new EVOTX_Event( $post_data['event_id'] );

			
			// woo product update
				$create_new = false;

				// check if woocommerce product id exist
				if(isset($post_data['tx_woocommerce_product_id']) && !empty($post_data['tx_woocommerce_product_id'])){
					
					$product_id = (int)$post_data['tx_woocommerce_product_id'];

					$post_exists = $evotx_admin->post_exist($product_id);

					// make sure woocommerce stock management is turned on
					update_option('woocommerce_manage_stock','yes');								
					

					// if product exists
					if($post_exists){
						$EVENT->update_wc_product( $product_id , $post_data );
					}else{
						$create_new = true;	
					}						
					
				}else{					

					// check if wc prod association already made
						$post_wc = $EVENT->get_wcid(); 
						
						if($post_wc){
							$product_id = (int)$post_wc;
							$post_exists = $evotx_admin->post_exist($product_id);

							if($post_exists){	
								$EVENT->update_wc_product( $product_id , $post_data );	
							}else{	$create_new = true;	}

						}else{	$create_new = true;	}					
				}


				// Create new WC Product for ticket
				if($create_new){
					$product_id = $EVENT->create_wc_product( $post_data );					
				}

			

			wp_send_json(array(
				'status'=>'good',
				'content'=> '',
				'msg'=> __('Event Ticket Values Saved Successfully!')
			));
			wp_die();

		}

	// Manually generate event tickets for order
		public function manual_generate_tickets(){

			// verify nonce
			if(empty( $_REQUEST['nn'] ) || !wp_verify_nonce( wp_unslash( $_REQUEST['nn'] ), 'eventon_admin_nonce')) {
				wp_send_json(['msg'=> __('Security Check Failed!','evotx')]); wp_die();
			}

			if( !isset( $this->post_data['order_id'])){
				wp_send_json(['msg'=> __('Required data missing!','evotx')]); wp_die();
			}

			$ET = new evotx_tix();
			$order_id = (int)$this->post_data['order_id'];
			$order = wc_get_order( $order_id );

			$order_has_tickets = $ET->order_has_event_tickets( $order );
			if( !$order_has_tickets){
				wp_send_json(['msg'=> __('Order does not have events!','evotx')]); wp_die();
			}

			// check if event tickets exists with this order id
			$has_event_tickets = $ET->order_has_evotix( $order_id );
			if( $has_event_tickets){
				wp_send_json(['msg'=> __('Order already have event tickets!','evotx')]); wp_die();
			}

			$output = $ET->create_tickets_for_order( $order, true );

			wp_send_json([
				'msg'=> __('Successfully Generated Tickets','evotx')

			]); wp_die();

		}

// assign WC Product to event ticket
	function assign_wc_products(){
		$wc_prods = new WP_Query(array(
				'post_type'=>'product', 
				'posts_per_page'=>-1,
				'tax_query' => array(
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						'terms'    => 'ticket',
					),
				),
			)
		);

		ob_start();

		?><div class='evopad20' style=''><?php

		if($wc_prods->have_posts()):
			?>
			<form class='evotx_assign_wc_form'>
				<?php
					EVO()->elements->print_hidden_inputs( array(
						'eid'=> $this->post_data['eid'],
						'action'=>'evotx_save_assign_wc_products',
					));
				?>
				<p><?php _e('Select a WC Product to assign this event ticket, instead of the already assigned WC Product','evotx');?><br/><br/>
				<i><?php _e('This event ticket is currently assigned to the below WC Product!','eventon');?></i><br/><code> (ID: <?php echo $_POST['wcid'];?>) <?php echo get_the_title($_POST['wcid']);?></code></p>

				<select class='field' name='wcid'><?php

					while($wc_prods->have_posts()): $wc_prods->the_post();

						$selected = (!empty($_POST['wcid']) && $wc_prods->post->ID == $_POST['wcid'])? 'selected="selected"':'';

						?><option <?php echo $selected;?> value="<?php echo $wc_prods->post->ID;?>">(ID: <?php echo $wc_prods->post->ID;?>) <?php the_title();?></option><?php
					endwhile;

				?></select>

				<br/><br/><p><i><?php _e('NOTE: When selecting a new WC Product be sure the product is published and can be assessible on frontend of your website','evotx');?></i></p>
				
				<p><?php
				// send emails button
					EVO()->elements->print_trigger_element(array(
						'title'=>__('Save Changes','evotx'),
						'uid'=>'evotx_assign_wc_submit',
						'lb_class' =>'evotx_manual_wc_product',
					), 'trig_form_submit');
				?></p>
				
			</form>

			<?php
			wp_reset_postdata();

		else:
			?><p><?php _e('You do not have any items saved! Please add new!','eventon');?></p><?php
		endif;

		echo "</div>";

		echo json_encode(array('content'=>ob_get_clean(), 'status'=>'good')); exit;

	}

	public function save_assign_wc_products(){
		$wcid = (int)$this->post_data['wcid'];
		$eid = (int)$this->post_data['eid'];

		$EVENT = new EVOTX_Event( $eid );

		$product_id = $EVENT->get_wcid();

		if( $product_id == $wcid ){
			echo json_encode(array('msg'=> __('Already assigned to this Product','evotx'), 'status'=>'good')); 
			exit;
		}

		$product = wc_get_product( $product_id );

		$EVENT->set_prop('tx_woocommerce_product_id', $wcid);

		$EVENT->assign_wc_product_cat( $product );
		//$EVENT->set_wc_product_meta( )


		$msg = __('Successfully Assigned New WC Product to Event Ticket!','evotx');

		echo json_encode(array('msg'=> esc_html( $msg ), 'status'=>'good')); exit;
	}

// GET attendee list view for event
		function evotx_get_attendees(){	
			global $evotx;

			$status = 0;
			$message = $content = $json = '';
			$filter_vals = array();

			$postdata = $this->post_data;

			ob_start();

			$source = isset($postdata['source'])? $postdata['source']: false;

			$event_id = sanitize_text_field($postdata['eid']);
			$ri = (isset($postdata['ri']) )? $postdata['ri']:'all'; // repeat interval

			$EA = new EVOTX_Attendees();
			$json = $EA->get_tickets_for_event( $event_id);


			if(!count($json)>0){
				echo "<div class='evotx'>";
				echo "<p class='header nada'>".__('Could not find attendees with completed orders.','evotx')."</p>";	
				echo "</div>";
			}else{

				/// get sorted event time list
				$event_start_time = array();
				foreach($json as $tidx=>$td){
					if(!isset($td['oD'])) continue;
					if(!isset($td['oD']['event_time'])) continue;

				
					$ET = $td['oD']['event_time'];
					if( strpos($ET, '-') !== false )$ET = explode(' - ', $ET);
					if( strpos($ET[0], '(') !== false ) $ET = explode(' (', $ET[0]);

					if( in_array($ET[0], $event_start_time)) continue;

					$event_start_time[ $td['oD']['event_time'] ] = $ET[0];					
				}


				uasort($event_start_time, array($this, "compareByTimeStamp") );

				$filter_vals['event_time'] = $event_start_time;
			}

			$content = ob_get_clean();

			$return_content = array(
				'attendees'=> array(
					'tickets'=>$json, 
					'od_gc'=>$EA->_user_can_check(),
					'source' =>$source, 
				),
				'filter_vals'=> $filter_vals,
				'temp'=> EVO()->temp->get('evotx_view_attendees'),
				'message'=> $message,
				'status'=>$status,
				'content'=>$content,
			);		
			
			
			echo json_encode($return_content);		
			exit;
		}

		function compareByTimeStamp($time1, $time2){
		    if (strtotime($time1) < strtotime($time2))
		        return 1;
		    else if (strtotime($time1) > strtotime($time2)) 
		        return -1;
		    else
		        return 0;
		}

// Download csv list of attendees
	function nopriv_generate_csv(){
		echo "You do not have permission!";exit;
	}
	public function generate_csv(){

		$nonce = isset($_REQUEST['nonce']) ? $_REQUEST['nonce'] : '';
    	$e_id = isset($_REQUEST['e_id']) ? (int)$_REQUEST['e_id'] : 0;

    	if (!wp_verify_nonce($nonce, 'evotx_csv_export_' . $e_id)) {
	        http_response_code(403);
	        exit('Security check failed');
	    }
    
		if ($e_id <= 0) {
	        http_response_code(400);
	        exit('Invalid event ID');
	    }

		if (!current_user_can('edit_eventons')) {
		    http_response_code(403);
		    exit('Unauthorized access');
		}

		$e_id = (int)$_REQUEST['e_id'];
		$EVENT = new EVO_Event($e_id);
		$EVENT->get_event_post();

		header('Content-Encoding: UTF-8');
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=".$EVENT->post_name."_".date("d-m-y").".csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo "\xEF\xBB\xBF"; // UTF-8 BOM


		$EA = new EVOTX_Attendees();
		$ticket_data = $EA->get_tickets_for_event($e_id);

		if (empty($ticket_data)) {
		    echo implode(',', array_map('self::escape_csv_field', $csv_headers)) . "\n";
		    exit('No attendees found');
		}
		

		// CSV header
        $csv_headers = apply_filters('evotx_csv_headers', [
            'Name', 'Email Address', 'Company', 'Address', 'Phone', 'Ticket IDs',
            'Quantity', 'Ticket Type', 'Event Time', 'Order Status', 'Ordered Date'
        ], $EVENT);
        echo implode(',', array_map([__CLASS__, 'escape_csv_field'], $csv_headers)) . "\n";

		$index = 1;				
		
		// CSV rows
        foreach ($ticket_data as $ticket_number => $data) {
            $row = apply_filters('evotx_csv_row', [
                'name' => $data['n'],
                'email' => $data['e'],
                'company' => $data['company'] ?? '',
                'address' => $data['aD'] ?? '',
                'phone' => $data['phone'] ?? '',
                'ticket_number' => $ticket_number,
                'qty' => '1',
                'ticket_type' => $data['type'],
                'event_time' => $data['oD']['event_time'] ?? '',
                'order_status' => $data['oS'],
                'ordered_date' => $data['oD']['ordered_date'] ?? ''
            ], $ticket_number, $data, $EVENT);

            echo implode(',', array_map([__CLASS__, 'escape_csv_field'], $row)) . "\n";
        }
	}

	// Helper to properly escape CSV fields
	private static function escape_csv_field($value) {
	    if (is_null($value) || $value === '') return '""';
	    $value = str_replace('"', '""', (string)$value); // Escape internal quotes
	    return '"' . trim($value) . '"'; // Wrap in quotes
	}

// Email attendee list to someone
	function evotx_emailing_form(){

		$post_data = $this->post_data;
		$EVENT = new EVOTX_Event($post_data['e_id']);

		ob_start();?>
		<div id='evotx_emailing' class='' style=''>
			<form class='evotx_emailing_form'>
			<?php
				EVO()->elements->print_hidden_inputs( array(
					'eid'=>$EVENT->ID,
					'wcid'=>$EVENT->get_wcid(),
					'action'=>'the_ajax_evotx_a8',
				));
			?>
			<p><label><?php _e('Select emailing option','evotx');?></label>
				<select name="type" id="evotx_emailing_options">
					<option value="someone"><?php _e('Email Attendees List to someone','evotx');?></option>
					<option value="completed"><?php _e('Email only to completed order guests','evotx');?></option>
					<option value="pending"><?php _e('Email only to pending order guests','evotx');?></option>
				</select>
			</p>
			<?php
				// if repeat interval count separatly						
				if($EVENT->is_ri_count_active() && $EVENT->get_repeats() ){
					$repeat_intervals = $EVENT->get_repeats();
					if(count($repeat_intervals)>0){

						$datetime = new evo_datetime();
						$wp_date_format = get_option('date_format');
						$pmv = $EVENT->get_data();	

						echo "<p><label>". __('Select Event Repeat Instance','evotx')."</label> ";
						echo "<select name='repeat_interval' id='evotx_emailing_repeat_interval'>
							<option value='all'>".__('All','evotx')."</option>";																
						$x=0;								
						foreach($repeat_intervals as $interval){
							$time = $datetime->get_correct_formatted_event_repeat_time($pmv,$x, $wp_date_format);
							echo "<option value='".$x."'>".$time['start']."</option>"; $x++;
						}
						echo "</select>";
						echo EVO()->throw_guide("Select which instance of repeating events of this event you want to use for this emailing action.", '',false);
						echo "</p>";
					}
				}
			?>
			<p style='' class='text'>
				<label for=""><?php _e('Email Addresses (separated by commas)','evotx');?></label>
				<input name='emails' style='width:100%' type="text"></p>
			<p style='' class='subject'>
				<label for=""><?php _e('Subject for email','evotx');?> *</label>
				<input name='subject' style='width:100%' type="text"></p>
			<p style='' class='textarea'>
				<label for=""><?php _e('Message for the email','evotx');?></label>
				<textarea name='message' id='evotx_emailing_message' cols="30" rows="5" style='width:100%'></textarea>
				
			</p>
			<p><?php
			// send emails button
				EVO()->elements->print_trigger_element(array(
					'title'=>__('Send Email','evotx'),
					'uid'=>'evotx_send_emails',
					'lb_class' =>'evotx_emailing',
					'lb_loader'=>true
				), 'trig_form_submit');
			?>
				<button class='evo_btn grey evotx_attendee_email_prev_trig'><?php _e('Preview Email');?></button>
			</p>

		</form>
		</div>
		
		<?php $emailing_content = ob_get_clean();
		$return_content = array(
			'status'=> 'good',
			'content'=>$emailing_content,
		);
		
		echo json_encode($return_content);		
		exit;

	}
	function emailing_attendees_admin(){
		global $evotx, $eventon;

		$eid = (int)$_POST['eid'];
		$wcid = (int)$_POST['wcid'];
		$type = sanitize_text_field( $_POST['type'] );	
		$preview_email = 	
		$EMAILED = $_message_addition = false;
		$emails = array();

		// repeat interval
		$RI = !empty($_POST['repeat_interval'])? $_POST['repeat_interval']:'all'; 
		if( isset($_POST['repeat_interval']) && $_POST['repeat_interval'] == 0) $RI = '0'; 

		$event = new EVO_Event( $eid, '', $RI);
		$TA = new EVOTX_Attendees();
		$args = [];

		// email attendees list to someone
		if($type=='someone'){

			// get the emails to send the email to
			$emails = explode(',', str_replace(' ', '', htmlspecialchars_decode($_POST['emails'])));
			$TH = $TA->_get_tickets_for_event($eid,'order_status');

			
			//order completed tickets
			if(is_array($TH) && isset($TH['completed']) && count($TH['completed'])>0){
				ob_start();
				
				// Define default table columns with filter
			    $columns = apply_filters('evotx_guest_list_table_columns', [
			        'ticket_holder' => [
			            'label' => 'Ticket Holder',
			            'data_key' => 'n',
			        ],
			        'email' => [
			            'label' => 'Email Address',
			            'data_key' => 'e',
			        ],
			        'phone' => [
			            'label' => 'Phone',
			            'data_key' => 'phone',
			        ],
			        'ticket_number' => [
			            'label' => 'Ticket Number',
			            'data_key' => 'tn', // Special handling for ticket number
			        ],
			    ], $event);

			    echo "<p>Confirmed Guests for " . esc_html( $event->get_title() ) . " on " . esc_html( $event->get_formatted_smart_time( $RI)) . "</p>";
    			echo "<table style='padding-top:15px; width:100%; text-align:left'><thead><tr>";
    			// Output table headers
			    foreach ($columns as $column) {
			        echo "<th>" . esc_html($column['label']) . "</th>";
			    }
			    echo "</tr></thead><tbody>";


				// create the attenee list
				foreach($TH['completed'] as $tn=>$guest){
					//EVO_Debug($guest);

					// repeat interval filter
					if( $RI != 'all' && $guest['ri'] != $RI) continue;

					echo "<tr>";
			        foreach ($columns as $col_key => $column) {
			        	//EVO_Debug($column);

			        	$column_key = isset($column['data_key']) ? $column['data_key'] :'';

			            // Determine the cell value
			            $value = '';
			            if ($col_key === 'ticket_number') {
			                $value = $tn; // Use ticket number directly
			            } elseif ( !empty($column_key) && isset($guest[ $column_key ]) ) {
			                $value = $guest[ $column_key ];
			            }

			            // Allow filtering of individual cell values
			            $filtered_value = apply_filters(
			                'evotx_guest_list_table_cell',
			                $value,
			                $col_key,
			                $column,
			                $guest,
			                $tn,$event
			            );

			            echo "<td>" . esc_html($filtered_value) . "</td>";
			        }
			        echo "</tr>";
				}
				echo "</tbody></table>";
				$_message_addition = ob_get_clean();

			}else{
				ob_start();
				echo "<p>".__('There are no completed orders!','evotx')."</p>";
				$_message_addition = ob_get_clean();
			}

			//print_r($_message_addition);

		}elseif($type=='completed'){
			$TH = $TA->_get_tickets_for_event($eid,'order_status');
			foreach(array('completed') as $order_status){
				if(is_array($TH) && isset($TH[$order_status]) && count($TH[$order_status])>0){
					foreach($TH[$order_status] as $guest){

						// repeat interval filter
						if( $RI != 'all' && $guest['ri'] != $RI) continue;

						$emails[] = $guest['e'];
					}
				}
			}
		}elseif($type=='pending'){
			$TH = $TA->_get_tickets_for_event($eid,'order_status');
			foreach(array('pending','on-hold') as $order_status){
				if(is_array($TH) && isset($TH[$order_status]) && count($TH[$order_status])>0){
					foreach($TH[$order_status] as $guest){

						// repeat interval filter
						if( $RI != 'all' && $guest['ri'] != $RI) continue;

						$emails[] = $guest['e'];
					}
				}
			}
		}

		// emaling
		if($emails && is_array($emails) && count($emails)>0 ){	
			$email = new evotx_email();	
			$helper = new evo_helper();

			$messageBODY = "<div style='padding:15px'>".
				(!empty($_POST['message'])? html_entity_decode(stripslashes($_POST['message']) ).'<br/><br/>':'' ).
				($_message_addition?$_message_addition:'') . "</div>";
				
			$messageBODY = $email->get_evo_email_body($messageBODY);
			$from_email = $email->get_from_email_address();

			$emails = array_unique($emails);

			$args = array(
				'html'=>'yes',
				'to'=> $emails,
				'type'=> ($type=='someone'? '':'bcc'),
				'subject'=>$_POST['subject'],
				'from'=>$from_email,
				'from_name'=> $email->get_from_email_name(),
				'from_email'=> $from_email,
				'message'=>$messageBODY,
			);

			//print_r($args);

			// if only preview
			if( isset($_POST['email_preview']) && $_POST['email_preview']){
				ob_start();
				echo $email->get_evo_email_body($messageBODY);		

				wp_send_json( array(
					'status'=>'good',
					'content'=> ob_get_clean()
				)); wp_die();
			}

			
			$EMAILED = $helper->send_email($args);

		}	


		$response_message = __('Email Sent Successfully');		
		if( $EMAILED === false) $response_message = __('Could not send email');
		if( count($emails) == 0) $response_message = __('No attendees found to email','evotx');

		$return_content = array(
			'status'=> ($EMAILED?'good':'bad'),	
			'msg'=> $response_message,
			'other'=>$args
		);		
		wp_send_json($return_content);	 wp_die();
	}

	// preview ticket confirmation email @2.3.1
	public function evotx_email_preview(){

		$post_data = $this->post_data;

		$ticket = new EVO_Ticket( $post_data['evo_ticket_id']);


		$order_id = $ticket->get_order_id();
		$order = new WC_Order( $order_id);
		$tickets = $order->get_items();

		$TIX 		= new evotx_tix();

		$order_tickets = $TIX->get_ticket_numbers_for_order($order);

		$email_body_arguments = array(
			'orderid'=>$order_id,
			'tickets'=>$order_tickets, 
			'customer'=>'Ashan Jay',
			'email'=>'yes'
		);

		ob_start();
		$email = new evotx_email();
		echo $email->get_ticket_email_body($email_body_arguments);
		

		wp_send_json( array(
			'status'=>'good',
			'content'=> ob_get_clean()
		)); wp_die();
	}

// Resend Ticket Email
// Used in both evo-tix and order post page
		function admin_resend_confirmation(){
			$order_id = false;
			$status = 'bad';
			$email = '';	

			// get order ID
			$order_id = (!empty($_POST['orderid']))? $_POST['orderid']:false;			
			$ts_mail_errors = array();

			if($order_id){

				$order = new WC_Order( $order_id );

				// use custom email if passed or else get email to send ticket from order information
				$email = !empty($_POST['email'])? 
					$_POST['email']: 
					$order->get_billing_email();


				if(!empty($email)){
					$evoemail = new evotx_email();
					$send_mail = $evoemail->send_ticket_email($order_id, false, false, $email);

					if($send_mail) $status = 'good';

					if(!$send_mail){
						global $ts_mail_errors;
						global $phpmailer;

						if (!isset($ts_mail_errors)) $ts_mail_errors = array();

						if (isset($phpmailer)) {
							$ts_mail_errors[] = $phpmailer->ErrorInfo;
						}
					}
				}				
			}	

			// return the results
			$return_content = array(
				'status'=> $status,
				'email'=>$email,
				'errors'=>$ts_mail_errors,
			);
			
			echo json_encode($return_content);		
			exit;
		}

// get information for a ticket number
	function get_ticektinfo_by_ID(){

		$tickernumber = $_POST['tickernumber'];

		// decode base 64
		if( $this->_is_base64encoded($tickernumber) ){
			$tickernumber = base64_decode( $tickernumber );
		}
		
		$content = $this->get_ticket_info($tickernumber);

		$return_content = array(
			'content'=>$content,
			'status'=> ($content? 'good':'bad'),
		);
		
		echo json_encode($return_content);		
		exit;

	}

		function _is_base64encoded($data){
			if (preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $data)) {
		       return TRUE;
		    } else {
		       return FALSE;
		    }
		}

		function get_ticket_info($ticket_number){
			if(strpos($ticket_number, '-') === false) return false;

			$tixNum = explode('-', $ticket_number);

			if(!get_post_status($tixNum[0])) return false;

			$tixPMV = get_post_custom($tixNum[0]);

			ob_start();

			$evotx_tix = new evotx_tix();
			$EA = new EVOTX_Attendees();

			$tixPOST = get_post($tixNum[0]);
			$orderStatus = get_post_status($tixPMV['_orderid'][0]);
				$orderStatus = str_replace('wc-', '', $orderStatus);

			$ticket_holder = $EA->get_attendee_by_ticket_number($ticket_number);
			$ticket_status = isset($ticket_holder['s'])? $ticket_holder['s']: 'check-in';

			echo "<p><em>".__('Ticket Purchased By','evotx').":</em> {$tixPMV['name'][0]}</p>";

			// additional ticket holder associated names
				if(!empty($ticket_holder))
					echo "<p><em>".__('Ticket Holder','evotx').":</em> {$ticket_holder['n']}</p>";

			echo "<p><em>".__('Email Address','evotx').":</em> {$tixPMV['email'][0]}</p>
				<p><em>".__('Event','evotx').":</em> ".get_the_title($tixPMV['_eventid'][0])."</p>
				<p><em>".__('Purchase Date','evotx').":</em> ".$tixPOST->post_date."</p>
				<p><em>".__('Ticket Status','evotx').":</em> <span class='tix_status {$ticket_status}' data-tiid='{$tixNum[0]}' data-tid='{$ticket_number}' data-status='{$ticket_status}'>{$ticket_status}</span></p>
				<p><em>".__('Payment Status','evotx').":</em> {$orderStatus}</p>";

				// other tickets in the same order
				$otherTickets = $evotx_tix->get_other_tix_order($ticket_number);

				if(is_array($otherTickets) && count($otherTickets)>0){
					echo "<div class='evotx_other_tickets'>";
					echo "<p >".__('Other Tickets in the same Order','evotx')."</p>";
					foreach($otherTickets as $num=>$status){
						echo "<p><em>".__('Ticekt Number','evotx').":</em> ".$num."</p>";
						echo "<p style='padding-bottom:10px;'><em>".__('Ticekt Status','evotx').":</em> <span class='tix_status {$status}' data-tiid='{$tixNum[0]}' data-tid='{$num}' data-status='{$status}'>{$status}</span></p>";
					}
					echo "</div>";
				}

			return ob_get_clean();
		}

// Sales Insight @2.4
	function evotx_sales_insight(){

		include_once('class-admin_sales_insight.php');

		$insight = new EVOTX_Sales_Insight();

		$insight_data =  $insight->get_insight();

		wp_send_json(array(
			'content'=> $insight_data['content'], 
			'status'=>'good',
			'chart_data'=> $insight_data['chart_data'],
		)); wp_die();
	}

// SYNC with order
	public function evotx_sync_with_order(){
		$order_id = sanitize_text_field( $_POST['oid']);

		$MM = '';

		$order = new WC_Order( $order_id );	

		$TIXS = new evotx_tix();

		$MM = 'Sync Completed';

		$TIXS->re_process_order_items( $order_id, $order);
		
		wp_send_json(array('message'=> $MM, 'status'=>'good')); wp_die();

	}

	
}
new evotx_admin_ajax();
<?php
/**
 * evo-tix post html
 * @version 2.3.4
 */

// INITIAL VALUES
	global $post, $evotx, $ajde;

	wp_nonce_field( 'evotx_edit_post', 'evo_noncename_tix' );

	$TIX 		= new evotx_tix();
	$HELPER 	= new evotx_helper();
	$TIX->evo_tix_id = $post->ID;

	$TIX_CPT = new EVO_Ticket( $post->ID );

	$event_id 			= $TIX_CPT->get_event_id();	
	$repeat_interval 	= $TIX_CPT->get_repeat_interval();
	$EVENT 				= new EVO_Event( $event_id, '', $repeat_interval );
	$event_meta 		= $EVENT->get_data();
	$ticket_number 		= $TIX_CPT->get_ticket_number();

	//print_r( get_post_custom(2059));


	// Order data
	$order_id 		= $TIX_CPT->get_order_id();	
	$order 			= new WC_Order( $order_id );	
	$order_status 	= $order->get_status();

	//print_r( $order->get_meta('_tixholders', true) );

	$EA = new EVOTX_Attendees();
	$TH = $all_ticket_in_order = $EA->_get_tickets_for_order($order_id);


// new ticket number method in 1.7
	if( $ticket_number){
		if( isset($TH[$event_id][$ticket_number]) ){
			$_TH = array();
			$_TH[$event_id][$ticket_number] = $TH[$event_id][$ticket_number];
			$TH = $_TH;
		} 
	}

// get event times			
	$event_time = $EVENT->get_formatted_smart_time();

$this_ticket_data = array();

?>	
<div class='eventon_mb' style='margin:-6px -12px -12px'>
<div style='background-color:#ECECEC; padding:15px;'>
	<div style='background-color:#fff; border-radius:8px;'>
	<table width='100%' class='evo_metatable' cellspacing="" style='vertical-align:top' valign='top'>


		
		<?php // Ticket 

		if($TH): ?>
		<tr><td colspan='2'>	
			<div class='evodfx evofx_jc_sb evofz18 evofsn evomar10'>
				<span><?php _e('Event Ticket','evotx');?></span>	
				<span class=''>#<?php echo $ticket_number;?></span>
			</div>

			<div id='evotx_ticketItem_tickets' >
				<?php 
					//print_r($TH);
					foreach($TH[$event_id] as $ticket_number=>$td):
						$this_ticket_data = $td;
						echo $EA->__display_one_ticket_data($ticket_number, $td, array(
							'orderStatus'=> $order_status,
							'showStatus'=>true,
							'guestsCheckable'=>$EA->_user_can_check(),	
						));
					endforeach;
				?>
			</div>			
		</td></tr>

		<?php
		endif;
		
		// build the data fields
		$data_fields = array();

		foreach( array(
			'type'=>__('Ticket Type','evotx'),
			'email'=>__('Order Email','evotx'),
			'qty'=>__('Quantity','evotx'),
			'cost'=>__('Cost for ticket(s)','evotx'),

		) as $k=>$v){
			$d = $TIX_CPT->get_prop($k);	

			if( !$d){
				if( isset( $this_ticket_data[ $k]) ) $d = $this_ticket_data[ $k];
			}
			$d = !$d? '--': $d;
			if( $k=='cost') $d = $HELPER->convert_price_to_format($d);

			$data_fields[ $k] = array( $v , $d);
		}

		// ticket number and time
		if( $ticket_number ) $data_fields['tix_num'] = array(__('Ticket Number','evotx'),$ticket_number);
		$data_fields['tix_time'] = array(__('Ticket Time','evotx'),$event_time);
		
		// checked in 
			$st_count = $TIX->checked_count($post->ID);
			$status = $TIX->get_checkin_status_text('checked');
			$__count = ': '.(!empty($st_count['checked'])? $st_count['checked']:'0').' out of '. $TIX_CPT->get_prop('qty');
			$data_fields['tix_checkin'] = array(__('Ticket Checked-in Status','evotx'), $status.$__count );

		// tickets purchased by
			$purchaser_id = $TIX_CPT->get_prop('_customerid');
			$purchaser = get_userdata($purchaser_id);
			if($purchaser) 
				$data_fields['tix_purch'] = array(__('Ticket Purchased by','evotx'), $purchaser->last_name.' '.$purchaser->first_name );

		// Ticket number instance
			$_ticket_number_instance = $TIX_CPT->get_ticket_number_instance();
			$data_fields['tix_numinst'] = array(__('Ticket Instance Index in Order','evotx') . $ajde->wp_admin->tooltips('This is the event ticket instance index in the order. Changing this will alter ticket holder values. Edit with cautions!') , "<input style='width:100%' type='text' name='_ticket_number_instance' value='{$_ticket_number_instance}'/>");

		// Other Ticket Data
			$data_fields['tix_od'] = array( '<b>'. __('Other Ticket Data','evotx') . '</b>', null);
			$data_fields['tix_od1'] = array(  __('Order Item ID','evotx'), $TIX_CPT->get_order_item_id() );
			$data_fields['tix_od2'] = array(  __('Woocommerce Product ID','evotx'), $TIX_CPT->get_prop('wcid') );

		// print HTML@2.4.3
		foreach(
			apply_filters('evotx_tixpost_data', $data_fields, $TIX_CPT, $EVENT ) 
			as $key=>$val
		){
			$CP = ( !empty($val[2]) && $val[2]) ? "colspan='2'":null;
			echo "<tr><td {$CP}>". $val[0] ."</td><td>". (isset($val[1])?$val[1]:null) ."</td></tr>";
		}
		
		
		 
		if($TH):

			$ticket_number_index = $TIX_CPT->get_prop('_ticket_number_index');
			$ticket_number_index = $ticket_number_index? $ticket_number_index: '0';
			
			foreach(array(
				'order_id'=> $order_id,
				'event_id'=> $event_id,
				'ri'=> $repeat_interval,
				'Q'=>$ticket_number_index,
				'event_instance'=>$_ticket_number_instance
			) as $F=>$V){
				echo "<input type='hidden' name='{$F}' value='{$V}'/>";
			}

		// Additional ticket holder information
		?>
			<tr><td colspan='2'><b><?php _e('Additional Ticket Holder Information','evotx');?></b></td></tr>
			<tr><td><?php _e('Name','evotx');?>: </td>
				<td data-d=''>
					<input style='width:100%' type='text' name="_ticket_holder[name]" value='<?php 	echo $TH[$event_id][$ticket_number]['name'];	?>'/>
				</td>
			</tr>

			<?php 

			// print out additional ticket holder data
			if( isset($TH[$event_id][$ticket_number]['th']) && is_array($TH[$event_id][$ticket_number]['th']) && isset($TH[$event_id][$ticket_number]['th']['name']) ):

				unset($TH[$event_id][$ticket_number]['th']['name']);


				foreach($TH[$event_id][$ticket_number]['th'] as $f=>$v){

					if( in_array($f, array('customer_id','oS','aD'))) continue;

					?>
					<tr><td><?php echo __(sprintf( '%s', $f), 'evotx');?>: </td>
						<td data-d=''>
							<input style='width:100%' type='text' name='_ticket_holder[<?php echo $f;?>]' value='<?php 	echo $v;	?>'/>
						</td>
					</tr>
					<?php
				}					


			endif;?>


		<?php
		// Other tickets on the same order
		?> 

			<tr><td colspan='2'><b><?php _e('Other Tickets on Same Order ID','evotx');?>: <?php echo $order_id;?></b></td></tr>
			<tr><td colspan='2'>
				<?php
					$count = 0;
					foreach($all_ticket_in_order as $__event_id=>$_event_tickets){
						
						foreach( $_event_tickets as $ticket_number => $ticket_data){

							if( $ticket_data['id'] ==  $post->ID) continue;

							//print_r($ticket_data);
							echo '<a href="'. get_edit_post_link( $ticket_data['id'] ) .'" class="evo_admin_btn">'.$ticket_number.'</a> ';
							$count ++;
						}
						
					}

					// no other tickets message
					if( $count == 0){
						echo __('No other tickets','evotx');
					}

				?>
				</td>
			</tr>


		<?php endif;?>
		<?php						
			do_action('eventontx_tix_post_table',$post->ID, $TIX_CPT->get_props(), $event_id, $TIX_CPT);
		?>
		
		
	</table>
	</div>
</div>
</div>
<?php
/**
 * Meta data content for Order details page
 * @version 2.4.12
 */


// check to see if order has event tickets
$ET = new evotx_tix();
$order_has_tickets = $ET->order_has_event_tickets( $order );


$order_type = $order->get_meta( '_order_type' );

// if order has tickets but ind tickets were not generated
if( $order_has_tickets && !$order_type ){
	?>
	<h3 style='margin: 10px 0 0;'><?php esc_html_e( 'Event Ticket Info', 'evotx' ); ?></h3>
	<div class='evotx_order_tix_details evomart10 evopad15 borderr10' style='clear:both;background-color: #f5f5f5;'>
	<p><?php _e('Event Tickets were not auto generated for this order.','evotx');?></p>
	<?php
	echo EVO()->elements->print_trigger_element([
		'title'=> __('Manually Generate Event Tickets for this Order'),
		'uid'=> 'evotx_man_gen_tix',
		'adata'=> [
			'a'=> 'evotx_manual_tickets_gen',
			'data'=>[
				'order_id'=> $order->get_id(),
			],
			'loader_btn_el'=> true,
			'show_snackbar'=> true
		],
	],'trig_ajax'); 

	echo "</div>";
}



if( $order_type != 'evotix') return;

$tixEmailSent = ($order->get_meta('_tixEmailSent') ==true)? true:false;
$stock_reduced = ( $order->get_meta('evo_stock_reduced') =='yes')? true:false;


// inline styles for elements on page
$__styles__1 = "    background-color: #2a2a2a;  padding: 3px 13px 5px;border-radius: 20px; color: #fff;margin-left:15px;";
$__styles__2 = "display:flex;align-items:center;flex-direction:row;padding:0 0 10px;justify-content:space-between; border-bottom:1px solid var(--evo_cl_b20)";
?>


<h3 style='margin: 0;'><?php esc_html_e( 'Event Ticket Info', 'evotx' ); ?></h3>
<div class='evotx_order_tix_details evomart10 evopad15 borderr10' style='clear:both;background-color: #f5f5f5;'>
	

	<p style='<?php echo $__styles__2;?>'><?php echo __('Initial Ticket Email','evotx') .'<span style="'. $__styles__1 .'">'. (($tixEmailSent)? __('Sent','evotx'): __('Not Sent','evotx'));?>
	</span></p>
	<p style='<?php echo $__styles__2;?>'><?php echo __('Ticket Stock Reduced','evotx') .'<span style="'. $__styles__1 .'">'. (($stock_reduced)? __('Yes','evotx'): __('No','evotx'));?>
	</span></p>

	<?php

	// order status
	if($order->get_status() =='completed'):?>

		<p><span class='evotx_email_options evo_admin_btn btn_triad'><?php _e('Emailing Option');?> <i class='fa fa-chevron-down'></i></span></p>

		<div class='evoTX_resend_conf evomarb15' style='display: none;'>			
			<div class='evoTX_rc_in'>
				<p><?php _e('You can re-send the Event Ticket confirmation email to customer if they have not received it. Make sure to check spam folder.','evotx');?></p>
				<a id='evoTX_resend_email' class='evoTX_resend_email button' data-orderid='<?php echo $order->get_id();?>'><?php _e('Re-send Ticket(s) Email','evotx');?></a>

				<p style='padding-top:5px'>
					<span><?php _e('Send Ticket(s) Email to custom Email','evotx');?>
					<input style='width:100%' type='text' name='customemail' placeholder='<?php _e('Type Email Address','evotx');?>'/>
					<a id='evoTX_resend_email' class='evoTX_resend_email button customemail' style='margin-top:5px;' data-orderid='<?php echo $order->get_id();?>'><?php _e('Send Ticket(s) Email','evotx');?></a>
				</p>

				<p class='message' style='display:none; text-align:center;' data-s='<?php _e('Ticket Email Re-send!','evotx');?>' data-f='<?php _e('Could not send email.','evotx');?>'></p>
			</div>
		</div>
	<?php
		else:
			echo '<p style="background-color:#515151; color:#fff;padding: 5px; border-radius:10px; text-align:center;">'.__('Ticket(s) Order is Not Completed Yet!','evotx')."</p>";
		endif;
	?>

	<?php
	// Tickets for this order
	$TA = new EVOTX_Attendees();
	$tickets = $TA->_get_tickets_for_order($order->get_id(), 'event');

	if($tickets){
		
		echo "<h3 style='margin: 0;'>".__('Event Tickets','evotx') . "</h3>";

		echo "<div class='evotx_wc_order_cpt'>";
		foreach($tickets as $e=>$dd){
			//echo '<span style="display:block; text-transform:uppercase;font-weight:bold; font-size:12px;  color: var(--evo_color_1); padding: 5px 10px; margin-top:10px;"><span style="opacity:0.5;">Event</span> '. get_the_title($e) . '</span>';
			foreach($dd as $tn=>$td){
				echo '<span style="display:block;font-size:12px;margin:5px 0;">';
				echo $TA->__display_one_ticket_data($tn, $td, array(
					'inlineStyles'=>false,
					'orderStatus'=>$order->get_status(),
					'linkTicketNumber'=>true,
					'showStatus'=>true,
					'showExtra'=>false,
					'guestsCheckable'=>$TA->_user_can_check(),				
				));
				
				echo "</span>";
			}
		}
		echo "</div>";
    
	}
?>

</div>
<?php 
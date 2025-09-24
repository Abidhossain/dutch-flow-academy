<?php
/**
 * Events Meta box content
 * @version 2.4.10
 */

			
$EVENT = $event = new EVOTX_Event( $EVENT->ID );
$help = new evo_helper();
$settings = new EVO_Settings();	

?>
<div class='eventon_mb evotx' data-eid='<?php echo $EVENT->ID;?>'>
	
	<?php
	// activate ticket setting 
	$settings->print_event_edit_box_yn_header(array(
		'id'=>'evotx_tix',
		'value'=> $EVENT->get_prop('evotx_tix'),
		'afterstatement' => 'evotx_details',
		'name'=> __('Activate tickets for this Event','evotx'),
		'tooltip'=> __('You can allow ticket selling via Woocommerce for this event in here.','evotx'),
		'reload_id'=> 'evotx',
		'eid'=> $EVENT->ID
	));
	?>
	<div id='evotx_details' class='evotx_details evomb_body ' <?php echo $EVENT->check_yn('evotx_tix') ? null:'style="display:none"'; ?>>
<?php

if( $EVENT->is_repeating_event() && $EVENT->is_current_event()){
	echo "<p style='padding: 10px 25px;border-bottom:1px solid #e4e4e4'><i>".__('IMPORTANT: Event must have current or future event date for ticket purchasing information to display on front-end!','evotx')."</i></p>";
}

$woo_product_id = $EVENT->product_id;


// product type
$product_type = $event->get_product_type();
$product_type = (!empty($product_type))? $product_type: 'simple';


// stats
	$tickets_instock = $EVENT->has_tickets();

	$TA = new EVOTX_Attendees();
	$TH = $TA->_get_tickets_for_event($EVENT->ID, 'order_status_tally');
	
	if($TH):
		ksort($TH);
		$denominator = (int)$tickets_instock + (int)$TH['total'];
											
	?>
	<div class="evotx_ticket_data">								
		<div class="evotx_stats_bar">
			<p class='evotx_stat_subtitle' ><?php _e('Event Ticket Order Data','evotx'); echo ' - '.  (int)$TH['total'].' '. __('Tickets','evotx'); ?></p>
			<p class='stat_bar'>
			<?php
				foreach($TH as $st=>$td){
					if($st == 'total') continue;
					$status = $st;
					$W = ($td!=0)? (($td/$denominator)*100) :0;	
					?><span class="<?php echo $st;?>" style='width:<?php echo $W;?>%'></span><?php											
				}
			?>
			</p>

			<p class="evotx_stat_text">
				<?php
				foreach($TH as $st=>$td){
					if($st == 'total') continue;
					?><span class="<?php echo $st;?>" style='width:<?php echo $W;?>%'></span>
					<span><em class='<?php echo $st;?>'></em><?php echo $st;?>: <?php echo $td;?></span><?php											
				}
				?>
			</p>
		</div>
	</div>
	<?php endif; ?>

<div class='evotx_settings evo_borderb evopad20 '>
	<?php
		EVO()->elements->get_element(array(
			'type'=>'detailed_button', '_echo'=> true,
			'name'=>__('Ticket Settings','evotx'),
			'description'=>__('Configure event ticket settings','evotx'),
			'field_after_content'=> "Configure",
			'row_class'=>'evo_bordern evomar0',
			'trig_data'=> array(
				'uid'=>'evo_get_tix_settings',
				'lb_class' =>'config_tix_data',
				'lb_title'=> __('Configure Event Ticket Settings','evotx'),	
				'ajax_data'=>array(
					'a'=>'evotx_get_event_tix_settings',
					'eid'=>		$EVENT->ID,
				),
			),
		));
	?>
</div>

<?php do_action('evotx_admin_before_settings', $EVENT);	?>

<?php 
// promote variations and options addon 
	if( $product_type != 'simple' && !function_exists('EVOVO')):?>
		<div class='evo_borderb evopad20'>
			<p style='padding:15px 25px; margin:-5px -25px; background-color:#f9d29f; color:#474747; text-align:center; ' class="evomb_body_additional">
				<span style='text-transform:uppercase; font-size:18px; display:block; font-weight:bold'><?php 
				_e('Do you want to make ticket variations look better?','evotx');
				?></span>
				<span style='font-weight:normal'><?php echo __( sprintf('Check out our EventON Variations & Options addon and sell tickets with an ease like a boss!<br/> <a class="evo_btn button_evo" href="%s" target="_blank" style="margin-top:10px;">Check out eventON Variations & Options Addon</a>', 'http://www.myeventon.com/addons/'),'evotx');?></span>
			</p>
		</div>	

<?php endif;?>	

<?php 
	// pluggable hook
	do_action('evotx_event_metabox_end', $EVENT->ID, $EVENT->get_data(),  $woo_product_id, $product_type, $EVENT);
?>

<?php 
// ticket woo information
if($woo_product_id):?>

	<div class='evotx_wc_options evopad20'>
		<?php 

		EVO()->elements->get_element(array(
			'type'=>'detailed_button', '_echo'=> true,
			'name'=>__('Edit WC Product','evotx'),
			'description'=>__('Further Edit WC product associated with this event ticket','evotx'),
			'content'=> "<i class='fa fa-wrench'></i>",
			'link'=> get_edit_post_link($woo_product_id)
		));

		EVO()->elements->get_element(array(
			'type'=>'detailed_button', '_echo'=> true,
			'name'=>__('Assign Different WC Product','evotx'),
			'description'=>__('Change the associated WC product for event ticket','evotx'),
			'trig_data'=> array(
				'uid'=>'evotx_assign_wc',
				'lb_class' =>'evotx_manual_wc_product',
				'lb_title'=> __('Assign Different WC Product','evotx'),	
				'ajax_data'=>array(
					'a'=>'evotx_assign_wc_products',
					'eid'=>		$EVENT->ID,
					'wcid'=>	$woo_product_id
				),
			),
		));

		// guide on WC variations 
		if( !class_exists('evovo')):
			EVO()->elements->get_element(array(
				'type'=>'detailed_button',
				'_echo'=> true,
				'name'=>__('Learn how to add variations','evotx'),
				'description'=>__('Guide on how to add Woocommerce variable price tickets','evotx'),
				'link'=> 'http://www.myeventon.com/documentation/set-variable-prices-tickets/',
				'content'=>'<i class="fa-solid fa-arrow-up-right-from-square"></i>',
				'_blank'=>true
			));
		endif;
		?>
	</div>	
		
<?php endif;
	

?>
<div class='evotx_other_options evopad20 evo_bordert'>
	<h4 class='evopadl10 evottu'><?php _e('Other ticket options','evotx');?></h4>
	<?php

		if( $event->get_product_total_sales() > 0 ):
			EVO()->elements->get_element(array(
				'type'=>'detailed_button',
				'_echo'=> true,
				'name'=>__('Sales Insight','evotx'),
				'description'=>__('Data visualization of ticket sales','evotx'),
				'content'=> "<i class='fa fa-gauge'></i>",
				'trig_data'=> array(
					'uid'=>'evotx_salesinsight',
					'lb_class' =>'config_evotx_salesinsight',
					'lb_padding'=>'evopad0',
					'lb_title'=>__('Extended Insight on Ticket Sales','evotx'),	
					'ajax_data'=>array(					
						'event_id'=> $EVENT->ID,
						'action'=>'evotx_sales_insight',
					),
				),
			));
		endif;

		EVO()->elements->get_element(array(
			'type'=>'detailed_button',
			'_echo'=> true,
			'name'=>__('View Attendees','evotx'),
			'description'=>__('Complete list of attendees for this event','evotx'),
			'content'=> "<i class='fa fa-user'></i>",
			'trig_data'=> array(
				'uid'=>'evotx_view_attendees',
				'lb_class' =>'config_evotx_viewattend',
				'lb_title'=>__('View Attendees','evotx'),	
				'lb_padding'=> 'evopad0',	
				'ajax_data'=>array(					
					'eid'=> $EVENT->ID,
					'action'=> 'the_ajax_evotx_a1',
				),
			),
		));

		EVO()->elements->get_element(array(
			'type'=>'detailed_button',
			'_echo'=> true,
			'name'=>__('Emailing','evotx'),
			'description'=>__('Email attendees of this event','evotx'),
			'content'=> "<i class='fa fa-envelope'></i>",
			'trig_data'=> array(
				'uid'=>'evotx_emailing',
				'lb_class' =>'evotx_emailing',
				'lb_padding' =>'evopad50',
				'lb_title'=>__('Email Attendees','evotx'),		
				'ajax_data'=>array(					
					'e_id'=> $EVENT->ID,
					'action'=> 'evotx_emailing_form',
				),
			),
		));

		// DOWNLOAD CSV link 
			$exportURL = add_query_arg(array(
			    'action' => 'the_ajax_evotx_a3',
			    'e_id' => $EVENT->ID,
			    'pid'=> $woo_product_id,
			    'nonce'=> wp_create_nonce('evotx_csv_export_' . $EVENT->ID),
			), admin_url('admin-ajax.php'));

		EVO()->elements->get_element(array(
			'type'=>'detailed_button',
			'_echo'=> true,
			'name'=>__('Download (CSV)','evotx'),
			'description'=>__('Attendees data as CSV file','evotx'),
			'link'=> $exportURL,
			'content'=>'<i class="fa fa-download"></i>',
		));

		EVO()->elements->get_element(array(
			'type'=>'detailed_button',
			'_echo'=> true,
			'name'=>__('Help','evotx'),
			'description'=>__('Troubleshoot Tickets Addon','evotx'),
			'link'=> get_admin_url('','/admin.php?page=eventon&tab=evcal_5'),
			'content'=>'<i class="fa fa-life-ring"></i>',
		));
	?>
</div>

</div><!-- #evotx_details-->
</div><!-- eventon_mb-->
<?php  
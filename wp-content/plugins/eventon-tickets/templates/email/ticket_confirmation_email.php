<?php
/**
 * Ticket Confirmation email Template
 * @version 2.4.13
 *
 * To customize: copy this file to your theme folder as below path
 * path: your-theme-dir/eventon/templates/email/tickets/
 */

$_args = $args[0]; // Extract ticket arguments
$email = isset( $_args['email'])? $_args['email']: ( isset( $args['1'])? $args['1']: 'text@world.com');
$args = $_args;



$eotx = EVOTX()->evotx_opt;
$evo_options = $eo = get_option('evcal_options_evcal_1');
$evo_options_2 = $eo2 = get_option('evcal_options_evcal_2');

// Inline styles for PDF
$font = "'Helvetica Neue', Helvetica, Arial, sans-serif";
$border_color_1 = '#ADADAD';
$__styles_button = "font-size:14px; background-color:#".( !empty($evo_options['evcal_gen_btn_bgc'])? $evo_options['evcal_gen_btn_bgc']: "00aafb")."; color:#".( !empty($evo_options['evcal_gen_btn_fc'])? $evo_options['evcal_gen_btn_fc']: "ffffff")."; padding: 10px 25px; text-decoration:none; border-radius:20px; display:inline-block; box-shadow:none;  font-size:14px;";
		
// styles
$styles = array(
	'000'=>"color:#262626; background-color:#fff; border-top:1px solid {$border_color_1};border-bottom:1px solid {$border_color_1}; padding:0;",
	'001'=>"font-size:18px; font-family: {$font}; padding:0px; margin:0px; text-transform:none;",
	'002'=>"font-size:36px; font-family: {$font}; padding:0px; margin:0px; font-weight:bold; line-height:38px;",
	'003'=>"",
	'004'=>"color:#9e9e9e; font:14px {$font}; padding:0px; margin:0px; font-weight:normal; line-height:100%;",
	'005'=>"font:16px {$font}; padding:0px; margin:0px; font-weight:bold; line-height:100%; text-transform:none;",
	'006'=>"font:14px {$font}; padding:0px; margin:0px; font-weight:bold; line-height:100%;",
	'007'=>"font:16px {$font}; padding:0px; margin:0px; font-weight:bold; line-height:100%;", 
	'008'=>"color:#a5a5a5; font-size:10px; font-family: {$font}; padding:0px; margin:0px; font-weight:normal; text-transform:none;",

	'100'=>"padding:15px 20px 10px;",
	'101'=>"text-align:right;",
	'102'=>"margin:0px; padding:0px;",
	'103'=>"padding:10px 20px;",

	'p0'=>'padding:0px;',
	'pb5'=>'padding-bottom:5px;',
	'pb10'=>'padding-bottom:10px;',
	'pt5'=>'padding-top:5px;',
	'pt10'=>'padding-top:10px;',
	'm0'=>'margin:0px;',
	'lh100'=>'line-height:100%;',
	'wbbw'=>'word-break:break-word',
	'fz24'=>'font-size:24px;'
);
extract( $styles );
?>
<table class='evotx_ticket' width='100%' style='width:100%; margin:0;font-family:"open sans",Helvetica;' cellspacing='0' cellpadding='0'>
<?php 
$count = 1;

	
if(empty($args['tickets'])) return;

// Store check values
	$_event_id = $_repeat_interval = '';
	$taxMeta = get_option( "evo_tax_meta");


$processed_ticket_ids = array();
$evotx_tix = new evotx_tix();

// get all ticket hodlers for this order
	$EA = new EVOTX_Attendees();
	$TH = $EA->_get_tickets_for_order( $args['orderid']);

	$order = wc_get_order( $args['orderid'] );


$tix_holder_index = 0;

// order items as ticket items - run through each ticket
foreach($args['tickets'] as $ticket_number):
	$show_add_cal = false;

	// WC order item product ID
		$product_id = $evotx_tix->get_product_id_by_ticketnumber($ticket_number);

	// initiate ticket order item class
		$event_id = $evotx_tix->get_event_id_by_product_id($product_id);

		if(empty($event_id)) continue;	

	// get evo-tix CPT ID	
		$ticket_item_id = $evotx_tix->get_evotix_id_by_ticketnumber($ticket_number);

		$TIX_CPT = new EVO_Ticket($ticket_item_id);
		$repeat_interval = $TIX_CPT->get_repeat_interval();

	// adjust the evo lang value based on item language @+ 1.9.3		
		$lang = $TIX_CPT->get_order_item_lang();
		evo_set_global_lang($lang);


	$EVENT = new EVO_Event( $event_id,'', $repeat_interval);
	$e_pmv = $EVENT->get_data();

	// event time		
		$eventTime = $EVENT->get_formatted_smart_time();

	$_this_ticket = $TH[$event_id][$ticket_number];

	//print_r($_this_ticket['oD']);

	// set check values
		if(empty($_event_id) || $_event_id != $event_id){
			$show_add_cal = true;
			$_event_id = $event_id;
			$tix_holder_index = 0;
		}

		if($_event_id == $event_id){
			if(empty($_repeat_interval)){
				$_repeat_interval = $repeat_interval;
			}
			if(!empty($_repeat_interval) && $_repeat_interval != $repeat_interval){
				$show_add_cal = true;
				$_repeat_interval = $repeat_interval; 
			}
		}
	
	// location data
		$location_terms = wp_get_post_terms($event_id, 'event_location');
		$location = false;
		if($location_terms && ! is_wp_error( $location_terms )){
			$locTermMeta = evo_get_term_meta( 'event_location', $location_terms[0]->term_id ,$taxMeta);
			$location = $location_terms[0]->name;
			if(!empty($locTermMeta['location_address']))
				$location .=', '.$locTermMeta['location_address'];
		}

	// organizer
		$organizer_terms = wp_get_post_terms($event_id, 'event_organizer');
		$organizer = false;
		if($organizer_terms && ! is_wp_error( $organizer_terms )){
			$organizer = $organizer_terms[0]->name;
		}
	
	// event ticket image
		$img_src = ($tixImg = $EVENT->get_prop('_tix_image_id') ) ?	 
			wp_get_attachment_image_src( $tixImg ,'full'): false;

	// Add to calendar
		?>
		<tr class='evotx_add_to_cal'>
			<td class='add_to_cal' colspan='' style='padding:20px 20px 15px'>

			<?php

			// add to calendar button
			if($show_add_cal):
				$ET = $EVENT->get_start_end_times( $repeat_interval );
				extract($ET);

				$nonce = wp_create_nonce('export_event_nonce');
				$__ics_url = home_url("/". EVO()->cal->get_ics_url_slug() . "/{$event_id}_{$repeat_interval}/?key={$nonce}");

				?>
				
					<p style="<?php echo $styles['102'];?>">
						<a style='<?php echo $__styles_button;?> ' href='<?php echo $__ics_url;?>' target='_blank'><?php echo evo_lang_get( 'evcal_evcard_addics', 'Add to calendar','',$eo2);?></a>
					</p>		
				</td>
			<?php endif;	?>

			<td>
				<p style="text-align: right; padding:5px 20px 5px 0;"><?php _e('Order ID');?> <strong>#<?php echo $args['orderid'];?></strong></p>
			</td>
			<td></td>

		</tr>
<?php

// Ticket Status
	$TS = $_this_ticket['s'];
	$TS = $TS? $TS: 'check-in';
?>


 <tr>
 	<td colspan='3'>
	<table style="<?php echo $styles['000'];?> width:100%;" >
		<tbody>
		<!-- title and images -->
		<tr>			
			<td colspan='2' style='padding:20px 20px 10px;<?php echo $TS=='refunded'? 'background-color:#ff6f6f;color:#fff':'';?>'>
				<div style="">
					<?php if($img_src):?>	
						<p style='padding-bottom: 15px;'>
							<img style='border-radius:20px;max-height:200px; width:auto; max-width:none' src="<?php echo $img_src[0];?>" alt=""></p>
					<?php endif;?>

					<?php if($TS=='refunded'):?><p><?php echo $TS;?></p><?php endif;?>
					<p style="<?php echo $styles['001'];?> padding-bottom: 15px;"><?php echo evo_lang_get( 'evotxem_001', 'Your Ticket for','',$eo2);?></p>
					<h2 style="<?php echo $styles['002'].$styles['pb10'];?> ">
						<a style='box-shadow:none;color:#262626;text-decoration: none; font-size: 36px;' href='<?php echo $EVENT->get_permalink();?>'><?php echo get_the_title($event_id);?></a>
					</h2>

				</div>				
			</td>
		</tr>

		<!-- Main ticket data -->
		<tr>			
			<td colspan='2' style="<?php echo $styles['100'];?>" >	
					
				<?php
					foreach(apply_filters('evotx_confirmation_email_data_ar', array(
						array(
							'data'=>	$_this_ticket['n'],
							'label'=>	evo_lang_get( 'evoTX_004', 'Primary Ticket Holder','',$eo2),
							'type'=>	'holder'
						),
						array(
							'data'=> $_this_ticket['e'],
							'label'=>evo_lang( 'Email Address'),
							'type'=>'normal'
						),
						array(
							'data'=> $eventTime,
							'label'=>evo_lang( 'Date and time'),
							'type'=>'normal'
						),
						array(
							'data'=>	$location,
							'label'=>	evo_lang_get( 'evcal_lang_location', 'Location','',$eo2),
							'type'=>	'normal'
						),array(
							'data'=>	$organizer,
							'label'=>	evo_lang_get( 'evcal_evcard_org', 'Organizer','',$eo2),
							'type'=>	'normal'
						),
					)) as $item){
						if(!empty($item['data'])):?>
						<div style='<?php echo $pb10;?>'>
							<p style="<?php echo $styles['005'].$styles['pb5']; ?><?php echo $item['type'] == 'holder'? $fz24:'';?>"><?php echo $item['data'];?></p>
							<p style="<?php echo $styles['004'].$styles['pb5']; ?>"><?php echo $item['label'];?></p>
						</div>
						<?php endif;
					}
			?>
			</td>
		</tr>

		<!-- ticket number and extra-->
		<tr>
			<td colspan='2' style="<?php echo $styles['100'];?>">
				<?php

				$encrypt_TN = base64_encode($ticket_number);

				if($_this_ticket['oS'] == 'completed'):				
				?><p style="<?php echo $styles['007'];?>; text-transform:none;"><?php echo apply_filters('evotx_email_tixid_list', $encrypt_TN,$ticket_number, $_this_ticket);?></p>
				<?php else:?>
					<p style="<?php echo $styles['007'];?>;text-transform:none;"><?php echo $encrypt_TN;?></p>
				<?php endif;?>
				
				<p style="<?php echo $styles['004'].$styles['pt5'];?>"><?php echo evo_lang_get( 'evotxem_003', 'Ticket Number','',$eo2);?></p>
			</td>
		</tr>

		<!-- Ticket additional information -->

		<?php 

		// extra data pluggable filter @2.3.3
		$extra_data = array();

		if( $TIX_CPT->get_ticket_type() == 'variation'){
			$variation_data = $TIX_CPT->get_ticket_wc_variation_data();
			if( $variation_data ){

				foreach($variation_data as $key=>$value){
					$extra_data[ $key ] = array(
						'label'=> $key , 'value'=> ucfirst( $value )
					); 
				}

			}
		}

		// plug for extra data
		$extra_data = apply_filters('evotx_confirmation_email_additional_data_array', $extra_data , 
			$_this_ticket , $TIX_CPT, $EVENT );

		// print out the extra data
		if( is_array( $extra_data ) && count( $extra_data )> 0 ):
		foreach($extra_data as $key => $data){

			if( !isset( $data['value'] )) continue;

			$label = !empty( $data['label'] ) ? esc_attr( $data['label'] ): false;
			$value = esc_attr( $data['value'] );

			?>
			<tr>
				<td colspan='2' style='padding:8px 20px'>
					<p style="<?php echo $styles['007'];?>; text-transform:none;"><?php echo $value;?></p>
					<?php if( $label):?>
						<p style="<?php echo $styles['004'].$styles['pt5'];?>"><?php echo $label;?></p>
					<?php endif;?>
				</td>
			</tr>

			<?php
		}
		endif;

		?>
		<tr>
			<td colspan='2' style='padding:8px 20px'>
			<?php
				
				// pluggable function  for expansion of data // deprecating @2.3
				do_action('evotix_confirmation_email_data', 
					$ticket_item_id, 
					$TIX_CPT->get_props(), 
					$styles, 
					$ticket_number, 
					$tix_holder_index,
					$event_id,
					$EVENT
				);

				// new hook since @2.3 @updated 2.3.3
				do_action('evotix_confirmation_email_additional_data',
					$TIX_CPT, $EVENT,$styles, $ticket_number, $tix_holder_index, $_this_ticket
				);
			?>
			</td>
		</tr>

		<?php

		// @updated 2.3.3
		do_action('evotix_confirmation_email_data_after_tr', $EVENT, $TIX_CPT, $order, $styles, $_this_ticket );

		?>
	<?php
	// terms and conditions
	if(!empty($eotx['evotx_termsc'])):
	?>	
		<tr><td style="<?php echo $styles['103'];?>">
			<p style="<?php echo $styles['008'];?>"><?php echo $eotx['evotx_termsc'];?></p>
		</td></tr>
	<?php endif;?>

	</tbody>
	</table>

	</td>
 </tr>

<?php		
	$tix_holder_index++;
	endforeach;
?>
	
<?php do_action('evotx_before_footer',  $order); ?>

<?php if($email):?>
	<tr>
		<td class='email_footer' colspan='3' style='padding:20px; text-align:left;font-size:12px;'>
			<?php
				$__link = (!empty($eotx['evotx_conlink']))? $eotx['evotx_conlink']:site_url();
			?>
			<p style='<?php echo $styles['m0'];?>'><?php echo evo_lang_get( 'evoTX_007', 'We look forward to seeing you!','', $eo2)?></p>
			<p style='<?php echo $styles['m0'];?>'><a style='' href='<?php echo $__link;?>'><?php echo evo_lang_get('evoTX_008', 'Contact Us for questions and concerns','', $eo2)?></a></p>
		</td>
	</tr>
<?php endif;?>
</table>
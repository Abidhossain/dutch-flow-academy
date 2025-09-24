<?php
/**
 * Event edit main meta box content all
 * @version 4.9.2
 */

$evcal_opt1= EVO()->cal->get_op('evcal_1');
$evcal_opt2= EVO()->cal->get_op('evcal_2');

EVO()->cal->set_cur('evcal_1');
		
$this->helper = new evo_helper();

$select_a_arr= array('AM','PM');

				
// array of all meta boxes
	$metabox_array = apply_filters('eventon_event_metaboxs', array(
		
		
		array(
			'id'=>'ev_timedate',
			'name'=>__('Time and Date','eventon'),	
			'hiddenVal'=>'',	
			'iconURL'=>'fa-clock-o','variation'=>'customfield','iconPOS'=>'',
			'type'=>'code',
			'content'=>'',
			'slug'=>'ev_timedate'
		),
		array(
			'id'=>'ev_otherdata',
			'name'=>__('Other Event Data','eventon'),
			'variation'=>'customfield',	
			'hiddenVal'=>'',	
			'iconURL'=>'fa-pencil',
			'iconPOS'=>'',
			'type'=>'code',
			'content'=>'',
			'slug'=>'ev_otherdata'
		),
		array(
			'id'=>'ev_virtual',
			'name'=>__('Virtual Event','eventon'),	
			'iconURL'=>'fa-globe','variation'=>'customfield','iconPOS'=>'',
			'type'=>'code','slug'=>'ev_virtual',
		),
		array(
			'id'=>'ev_health',
			'name'=>__('Health Guidelines','eventon'),	
			'iconURL'=>'fa-heartbeat','variation'=>'customfield','iconPOS'=>'',
			'type'=>'code','slug'=>'ev_health',
		),
		array(
			'id'=>'ev_location',
			'name'=>__('Location and Venue','eventon'),	
			'iconURL'=>'fa-map-marker','variation'=>'customfield','iconPOS'=>'',
			'type'=>'code',
			'slug'=>'ev_location',
		),
		array(
			'id'=>'ev_organizer',
			'name'=>__('Organizer','eventon'),	
			'hiddenVal'=>'',	
			'iconURL'=>'fa-microphone','variation'=>'customfield','iconPOS'=>'',
			'type'=>'code',
			'content'=>'',
			'slug'=>'ev_organizer'
		),array(
			'id'=>'ev_uint',
			'name'=>__('User Interaction for event click','eventon'),	
			'hiddenVal'=>'',	
			'iconURL'=>'fa-street-view','variation'=>'customfield','iconPOS'=>'',
			'type'=>'code',
			'content'=>'',
			'slug'=>'ev_uint',
			'guide'=>__('This define how you want the events to expand following a click on the eventTop by a user','eventon')
		),array(
			'id'=>'ev_learnmore',
			'name'=>__('Learn more about event link','eventon'),	
			'hiddenVal'=>'',	
			'iconURL'=>'fa-random','variation'=>'customfield','iconPOS'=>'',
			'type'=>'code',
			'content'=>'',
			'slug'=>'ev_learnmore',
			'guide'=>__('This will create a learn more link in the event card. Make sure your links start with http://','eventon')
		),
		array(
			'id'=>'ev_releated',
			'name'=>__('Related Events','eventon'),	
			'hiddenVal'=>'',	
			'iconURL'=>'fa-calendar-plus','variation'=>'customfield','iconPOS'=>'',
			'type'=>'code',
			'content'=>'',
			'slug'=>'ev_releated',
			'guide'=>__('Show events that are releated to this event','eventon')
		),
		/*
		array(
			'id'=>'ev_goals',
			'name'=>__('Event Goals','eventon'),	
			'hiddenVal'=>'',	
			'iconURL'=>'fa-bullseye','variation'=>'customfield','iconPOS'=>'',
			'type'=>'code',
			'content'=>'',
			'slug'=>'ev_goals',
			'guide'=>__('Goals expected to acomplish with this event.','eventon')
		),
		*/
		array(
			'id'=>'ev_seo',
			'name'=>__('SEO Additions for Event','eventon'),	
			'hiddenVal'=>'',	
			'iconURL'=>'fa-search','variation'=>'customfield','iconPOS'=>'',
			'type'=>'code',
			'content'=>'',
			'slug'=>'ev_seo',
		)
	), $EVENT);

	// if language corresponding enabled
		if( EVO()->cal->check_yn('evo_lang_corresp')){
			$metabox_array[] = array(
				'id'=>'ev_lang',
				'name'=>__('Language for Event','eventon'),	
				'hiddenVal'=>'',	
				'iconURL'=>'fa-font','variation'=>'customfield','iconPOS'=>'',
				'type'=>'code',
				'content'=>'',
				'slug'=>'ev_lang',
			);
		}

$closedmeta = eventon_get_collapse_metaboxes($EVENT->ID);

// include content into meta box
foreach($metabox_array as $index=>$mBOX){
	if(!empty($mBOX['content'])) continue;

	ob_start();

	switch($mBOX['id']){

			// VIRTUAL
			case 'ev_virtual':
				include_once 'class-meta_boxes-virtual.php';
			break;

			// health guidance
			case 'ev_health':
				include_once 'class-meta_boxes-health.php';
			break;
			// Other Event Data
			case 'ev_otherdata':
				include_once 'class-meta_boxes-other-data.php';
			break;			

			case 'ev_releated':
				include_once 'class-meta_boxes-related.php';								
			break;

			case 'ev_goals':
				include_once 'class-meta_boxes-goals.php';								
			break;
			
			case 'ev_seo':
				echo "<div class='evo_meta_elements'>";
					echo EVO()->elements->process_multiple_elements(
						array(
							array(
								'type'=>'text',
								'name'=> __('Offer Price','eventon'),
								'tooltip'=> __('Offer price without the currency symbol.','eventon'),
								'id'=>'_seo_offer_price',
								'value'=> $EVENT->get_prop('_seo_offer_price')
							),
							array(
								'type'=>'text',
								'name'=> __('Offer Price Currency Symbol','eventon'),
								'id'=>'_seo_offer_currency',
								'value'=> $EVENT->get_prop('_seo_offer_currency')
							),array(
								'type'=>'notice',
								'name'=> __('NOTE: Leaving them blank may show a mild warning on google SEO, but will not effect your SEO rankings. For free events you can use 0.00 or Free as the Offer price.','eventon'),
							)
						)
					);
				
					echo "</div>";
			break;
			case 'ev_learnmore':
				include_once ('class-meta_boxes-learnmore.php');

			break;
			case 'ev_lang':
				echo "<div class='evcal_data_block_style1'>
				<div class='evcal_db_data'>";
					?>
					<p><?php _e('You can select the eventon language corresponding to this event. Eg. If you have eventon language L2 in French and this event is in french select L2 as eventon language correspondant for this event.','eventon');?></p>
					<p>
						<label for="_evo_lang"><?php _e('Corresponding eventON language','eventon');?></label>
						<select name="_evo_lang">
						<?php 

						$_evo_lang = ($EVENT->get_prop("_evo_lang") )? $EVENT->get_prop("_evo_lang"): 'L1';

						$lang_variables = apply_filters('eventon_lang_variation', array('L1','L2', 'L3'));

						foreach($lang_variables as $lang){
							$slected = ($lang == $_evo_lang)? 'selected="selected"':null;
							echo "<option value='{$lang}' {$slected}>{$lang}</option>";
						}
						?></select>
					</p>

				<?php echo "</div></div>";
			break;
			case 'ev_uint':

				include_once ('class-meta_boxes-ui.php');
				
			break;

			case 'ev_location':

				include_once ('class-meta_boxes-location.php');
			break;

			case 'ev_organizer':
				include_once ('class-meta_boxes-organizer.php');
			break;

			case 'ev_timedate':
				
				include_once ('class-meta_boxes-timedate.php');
				
			break;
		}

	$metabox_array[$index]['content'] = ob_get_clean();
	$metabox_array[$index]['close'] = ( $closedmeta && in_array($mBOX['id'], $closedmeta) ? true:false);

}

echo EVO()->evo_admin->metaboxes->process_content( $metabox_array );


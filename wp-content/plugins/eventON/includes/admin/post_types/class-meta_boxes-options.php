<?php 
/**
 * Event Options
 * @version 4.9
 */


?>	
<div class='evomb_side_box evo_event_opts evomb_section evo_borderb evopad10'>	
<?php 
	// header
	EVO()->evo_admin->metaboxes->event_metabox_parts_header( array(
		'name'=> __('Event Options'),
		'iconURL'=> 'fa-gear',
		'id'=> 'event_options',
		'slug'=> 'event_options',
		'variation'=>'customfield',
	));
	

echo EVO()->elements->process_multiple_elements(
	array(
		array(
			'id'=>'evo_exclude_ev', 
			'type'=>'yesno_btn',
			'value'=> $EVENT->get_prop('evo_exclude_ev'),
			'input'=>true,
			'label'=>__('Exclude from Cal','eventon'),
			'tooltip'=>__('Hide this event from showing in all calendars','eventon'),
			'tooltip_position'=>'L'
		),
		array(
			'id'=>'_featured', 'type'=>'yesno_btn',
			'value'=> $EVENT->get_prop('_featured'),
			'input'=>true,
			'label'=>__('Featured Event','eventon'),
			'tooltip'=>__('Make this event a featured event','eventon'),
			'tooltip_position'=>'L'
		),
		array(
			'id'=>'_completed', 'type'=>'yesno_btn',
			'value'=> $EVENT->get_prop('_completed'),
			'input'=>true,
			'label'=>__('Event Completed','eventon'),
			'tooltip'=>__('Mark this event as completed','eventon'),
			'tooltip_position'=>'L'
		),
		array(
			'id'=>'_onlyloggedin', 'type'=>'yesno_btn',
			'value'=> $EVENT->get_prop('_onlyloggedin'),
			'input'=>true,
			'label'=>__('Loggedin Users Only','eventon'),
			'tooltip'=>__('This will make this event only visible if the users are loggedin to this site','eventon'),
			'tooltip_position'=>'L',
		)
	)
);

// export event data as CSV file
	$exportURL = add_query_arg(array(
	    'action' => 'eventon_export_events',
	    'eid'	=> $EVENT->ID,
	    'nonce'=> wp_create_nonce('eventon_download_events')
	), admin_url('admin-ajax.php'));

echo "<p class='evo_elm_row'><a class='evo_btn evotooltipfree L' data-d='". __('Download a CSV file format of event data from this event.','eventon')."' href='{$exportURL}'>" . __('Download CSV') .'</a></p>';
	// @since 2.2.28
	do_action('eventon_event_submitbox_misc_actions',$EVENT);
?>
</div>
<?php 
<?php
/**
 * Virtual Events Meta box content
 * @ 4.7
 */

EVO()->elements->_print_settings_toggle_nester_start(array(
	'id'=>'_virtual',
	'value'=>$EVENT->get_prop('_virtual'),
	'value_yn'=> $EVENT->check_yn('_virtual'),
	'afterstatement'=>'evo_virtual_details',			
	'tooltip'=>__('Enabling this will convert this into a virtual event with various virtual event specific features.','eventon'),
	'label'=> __('Make this a Virtual Event','eventon'),
	'toggle_class'=>'event_virtual_settings'
));

	EVO()->elements->get_element(array(
		'type'=>'detailed_button', '_echo'=> true,
		'name'=>__('Virtual Event Editor','evost'),
		'description'=>__('Configure Virtual Event Details','evost'),
		'field_after_content'=> "Configure",
		'row_class'=>'evomar0 evo_bordern',
		'trig_data'=> array(
			'uid'=>'evo_get_virtual_events',
			'lb_class' =>'config_vir_events',
			'lb_title'=>__('Configure Virtual Event Details','evost'),	
			'ajax_data'=>array(
				'eid'=> $EVENT->ID,
				'action'=> 'eventon_config_virtual_event',
			),
		),
	));

	
EVO()->elements->_print_settings_toggle_nester_close();

?>
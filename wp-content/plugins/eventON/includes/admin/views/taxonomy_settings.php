<?php
/*
 * Taxonomy Settings
 * @version 4.9
 */

$settings = new EVO_Settings();

$fields = EVO()->taxonomies->get_event_tax_fields_array($tax, $event_tax_term, $termMeta);

//print_r($termMeta);


$fields_processed = EVO()->elements->populate_field_values( $fields, $termMeta);



$footer_btns = array(
	'save_changes'=> array(
		'label'=> __('Save Changes','eventon'),
		'data'=> array(
			'uid'=>'evo_save_tax_edit_settings',
			'lightbox_key'=>'evo_config_term',
			'hide_lightbox'=> 2000,
			'end'=>'admin'
		),
		'class'=> 'evo_btn evolb_trigger_save prime'
	)
);

// generate coordinates button for location
	if( $tax == 'event_location' && EVO()->cal->get_prop('evo_gmap_api_key', 'evcal_1')){
		$footer_btns['generate_coords']= array(
			'label'=> __('Generate Location Coordinates','eventon'),
			'data'=> array(	),
			'class'=> 'evo_btn evo_auto_gen_latlng'
		);
	}

// if term id exists add further edit button
	if( $term_id){
		$footer_btns['further_edit']= array(
			'label'=> __('Edit from page','eventon'),
			'data'=> array(	),
			'class'=> 'evo_admin_btn btn_secondary',
			'href'=> get_admin_url(). 'term.php?taxonomy='. $tax .'&tag_ID=' . $term_id .'&post_type=ajde_events',
		);
	}

$data_array =  array(
	'nonce_action'=>'evo_save_term_form',
	'form_class'=>'evo_tax_event_settings',
	'container_class'=>'evo_tax',
	'hidden_fields'=>array(
		'tax'=>$tax,
		'event_id'=>$event_id,
		'term_id'=>$term_id,
		'type'=> ( $term_id ? 'edit':'new'),
		'action'=>'eventon_event_tax_save_changes'
	),
	'footer_btns'=> $footer_btns,
	'fields'=> $fields_processed
);


echo EVO()->elements->_get_settings_content( apply_filters('evo_eventedit_taxonomy_fields_array', $data_array, $post_data, $settings ) );


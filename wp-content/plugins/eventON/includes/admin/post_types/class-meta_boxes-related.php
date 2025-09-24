<?php
/**
 * Event Edit Meta box Related Events
 * @version 4.9.10
 */


$related_events = $EVENT->get_prop('ev_releated');


echo "<div class='evcal_data_block_style1'>
<div class='evcal_db_data evo_rel_events_box'>";
	
	EVO()->elements->print_hidden_inputs( array(
		'ev_releated' => esc_attr( $related_events ),
		'ev_related_event_id'=> $EVENT->ID,
		'ev_related_text' => __('Configure Related Event Details','eventon'),
	));

	if($EVENT->is_repeating_event()){
		echo "<p>".__('NOTE: You can not select a repeat instance of this event as related event.','eventon').'</p>';
	}
	?>
	<span class='ev_rel_events_list'><?php
		if($related_events){
			$D = json_decode($related_events, true);

			$rel_events = array();

			foreach($D as $I=>$N){
				$id = explode('-', $I);
				$EE = new EVO_Event($id[0]);

				if( !$EE->is_exists() ) continue;

				$x = isset($id[1])? $id[1]:'0';
				$time = $EE->get_formatted_smart_time($x);
				
				$rel_events[ $I.'.'. $EE->get_start_time() ] =  "<span class='l' data-id='{$I}'><span class='t'>{$time}</span><span class='n'>{$N}</span><i class='fa fa-close'></i></span>";
			}

			//krsort($rel_events);

			foreach($rel_events as $html){
				echo $html;
			}
			
		}
	?></span>


	<div class='evopadt10'>
		<?php 
			EVO()->elements->get_element(array(
				'type'=>'detailed_button', '_echo'=> true,
				'name'=>__('Add related event','eventon'),
				'description'=>__('Configure Related Event Details','eventon'),
				'field_after_content'=> __("Configure",'eventon'),
				'row_class'=> 'evo_bordern evomarb5',
				'field_class'=>'evo_configure_related_events'
			));
		?>
		

		<?php 
		// option to hide related event images
		echo EVO()->elements->get_element(array(
			'type'=>'yesno',
			'id'=>'_evo_relevs_hide_img',
			'value'=> $EVENT->get_prop('_evo_relevs_hide_img'),
			'name'=> __('Hide related event image','eventon'),
			'tooltip'=> __('This will show related events without the event image.','eventon'),
			'row_class'=>'evopadt10'
		));

		echo EVO()->elements->get_element(array(
			'type'=>'yesno',
			'id'=>'_evo_relevs_hide_past',
			'value'=> $EVENT->get_prop('_evo_relevs_hide_past'),
			'name'=> __('Hide past related events','eventon'),
			'tooltip'=> __('Hide past events from related events list','eventon'),
			'row_class'=>'evopadt10'
		));
		?>

	</div>

<?php echo "</div></div>";
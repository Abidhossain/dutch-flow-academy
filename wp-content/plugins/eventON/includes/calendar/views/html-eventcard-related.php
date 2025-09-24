<?php 
/**
 * EventCard Related Events html content
 * @version 4.9.10
 */


$rel_events = array();

// each related events
foreach($events as $I=>$N){
	$id = explode('-', $I);
	$EE = new EVO_Event($id[0]);
	if( !$EE->is_exists() ) continue;

	$repeat_index = isset($id[1])? $id[1]:'0';
	$time = $EE->get_formatted_smart_time($repeat_index);
	
	$__a_class = $img_content = '';

	// check if the related event is past
	if( $EVENT->check_yn('_evo_relevs_hide_past')){
		$event_time_now = $EE->timenow_etz;
		$rel_event_end_time = $EE->get_start_end_times( $repeat_index , 'end');		
		if( $rel_event_end_time < $event_time_now ) continue;
	}
	

	// if event image to be visible
	if( !$EVENT->check_yn('_evo_relevs_hide_img')){		
		$imgs = $EE->get_image_urls();
		if($imgs && isset( $imgs['full'] ) ){
			$__a_class = 'hasimg';
			$img_content = "<span class='img evofx00a' style='background-image: url(". $imgs['full'].")'></span>";
		}
	}

	$hex = $EE->get_hex();

	$rel_events[ $I .'.'. $EE->get_start_time() ] =  
		"<a class='{$__a_class} evogap10 evopad10i' style='' href='". $EE->get_permalink($repeat_index). "' >
		{$img_content}												
		<span class=''>
			<h4 class='evo_h4'>{$N}</h4>
			<em><i class='fa fa-clock-o'></i> {$time}</em>
		</span>

		</a>";
}

if( count($rel_events) == 0 ) return;

?>
<div class='evo_metarow_rel_events evorow evcal_evdata_row'>
	<span class='evcal_evdata_icons'><i class='fa <?php echo get_eventON_icon('evcal__fai_relev', 'fa-calendar-plus',$evOPT );?>'></i></span>
	<div class='evcal_evdata_cell'>
		<h3 class='evo_h3'><?php echo evo_lang('Related Events');?></h3>
		<div class='evcal_cell_rel_events'>
		<?php

		//krsort($rel_events);
		foreach($rel_events as $html){
			echo $html;
		}
		?>
		</div>
	</div>
</div>
<?php
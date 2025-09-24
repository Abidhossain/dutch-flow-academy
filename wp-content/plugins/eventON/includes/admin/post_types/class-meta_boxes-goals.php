<?php
/**
 * Event Goals Meta box content
 * @version 4.8
 */

$event_goals = $EVENT->get_prop('_ev_goals');

echo "<div class='evcal_data_block_style1'>
<div class='evcal_db_data evo_rel_events_box'>";

	echo "<p class=''>" . __('Define specific goals for your event, such as attendee numbers, fundraising targets, or key achievements. These goals help attendees get an idea of what to expect.','eventon') . "</p>";

	EVO()->elements->get_element(array(
		'type'=>'wysiwyg', '_echo'=> true,
		'name'=>__('Describe Event Goals','eventon'),
		'description'=>__('Configure Related Event Details','eventon'),
		'row_class'=> 'evo_bordern evomarb5',
	));


echo "</div></div>";
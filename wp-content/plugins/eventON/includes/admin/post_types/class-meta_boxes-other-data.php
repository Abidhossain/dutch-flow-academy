<?php
/**
 * Event Edit Meta box Other Data
 * @version 4.9.2
 */

?>
<div class='evodata_subtitle evcal_data_block_style1 evoposr evomarb10'>
	<p class=""><?php _e('Event Subtitle','eventon');?></p>
	<div class='evodata_subtitle_in evoposr'>
		<input type='text' id='evcal_subtitle' class='evoposr evomar0i evopad10 evofz16i' name='evcal_subtitle' value="<?php echo esc_html( $EVENT->get_prop('evcal_subtitle'));?>" style='width:100%'/>
	</div>
</div>
				
<div class='evodata_status evcal_data_block_style1 event_status_settings evomarb10'>
	<p class=""><?php _e('Event Status','eventon');?></p>
	<div class='evcal_db_data'>
		<?php
		$_status = $EVENT->get_event_status();
		echo EVO()->elements->get_element( array(
			'type'=>'select_row',
			'row_class'=>'es_values',
			'id'=>'_status',
			'value'=>$_status,
			'options'=>$EVENT->get_status_array()
		));
		?>
		<div class='cancelled_extra' style="display:<?php echo $_status =='cancelled'? 'block':'none';?>">
			<p><label><?php _e('Reason for cancelling','eventon');?></label><textarea name='_cancel_reason'><?php echo $EVENT->get_prop('_cancel_reason');?></textarea>
		</div>
		<div class='movedonline_extra' style="display:<?php echo $_status =='movedonline'? 'block':'none';?>">
			<p><label><?php _e('More details for online event','eventon');?></label><textarea name='_movedonline_reason'><?php echo $EVENT->get_prop('_movedonline_reason');?></textarea>
		</div>
		<div class='postponed_extra' style="display:<?php echo $_status =='postponed'? 'block':'none';?>">
			<p><label><?php _e('More details about postpone','eventon');?></label><textarea name='_postponed_reason'><?php echo $EVENT->get_prop('_postponed_reason');?></textarea>
		</div>
		<div class='rescheduled_extra' style="display:<?php echo $_status =='rescheduled'? 'block':'none';?>">
			<p><label><?php _e('More details about reschedule','eventon');?></label><textarea name='_rescheduled_reason'><?php echo $EVENT->get_prop('_rescheduled_reason');?></textarea>

			<?php /*
			<p>
				<label><?php _e('Previous start date (for SEO)','eventon');?></label></p>
			<div class='prev_start_date' style='background-color: #c3c3c3;padding: 10px; border-radius: 10px;'>
			<?php

				$wp_time_format = get_option('time_format');

				echo EVO()->elements->print_date_time_selector( array(
					'type'=>'prev',
					'unix'=> $EVENT->get_prop('_prevstartdate'),
					'time_format'=>$wp_time_format
				));

			?>	
			</div>
			*/?>
		</div>
	</div>
</div>
<div class='evcal_data_block_style1 event_attendance_settings'>
	<p class=""><?php _e('Event Attendance Mode','eventon');?></p>
	<div class='evcal_db_data'>
		<?php
		
		echo EVO()->elements->get_element( array(
			'type'=>'select_row',
			'row_class'=>'eatt_values',
			'name'=>'_attendance_mode',
			'value'=>	$EVENT->get_attendance_mode(),
			'options'=>	EVO()->cal->get_attendance_modes()
		));
		?>		
	</div>
</div>

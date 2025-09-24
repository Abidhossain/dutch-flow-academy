<?php
/**
 * Event User Interaction
 * @version 4.9
 */

// initial values
	$exlink_option = ($EVENT->get_prop("_evcal_exlink_option"))? $EVENT->get_prop("_evcal_exlink_option") :1;
	$_show_extra_fields = ($exlink_option=='1' || $exlink_option=='3' || $exlink_option=='X')? false:true;
?>
<div class='evcal_data_block_style1 evo_event_edit_ui_box' data-event_url='<?php echo get_permalink($EVENT->ID);?>'>
	<div class='evcal_db_data'>										
		
		<div class='evcal_db_uis'>
			<?php 
			echo EVO()->elements->get_element( array(
				'type'=>'select_row',
				'row_class'=>'event_edit_ui',
				'name'=>'_evcal_exlink_option',
				'value'=>	$exlink_option,
				'options'=>	array(
					'X'=> '<i class="fa evomarr10 fa-xmark"></i>Do Nothing',
					'1'=> '<i class="fa evomarr10 fa-arrow-down-wide-short"></i> Slide down EventCard',
					'2'=> '<i class="fa evomarr10 fa-up-right-from-square"></i> External Link',
					'3'=> '<i class="fa evomarr10 fa-window-maximize"></i> Popup Window',
					'4'=> '<i class="fa evomarr10 fa-window-restore"></i> Open Event Page',
				),
				'select_option_class'=> 'evo_eventedit_ui'
			));
			?>
								
		</div>

		<div class='event_edit_ui_extra ' <?php echo !$_show_extra_fields?"style='display:none'":null;?>>
			<input id='evcal_exlink_option' type='hidden' name='_evcal_exlink_option' value='<?php echo $exlink_option; ?>'/>
			
			<div  id='evo_new_window_io' class='event_edit_ui_target'>
				<?php 
				// option to hide related event images
				echo EVO()->elements->get_element(array(
					'type'=>'yesno',
					'id'=>'_evcal_exlink_target',
					'value'=> $EVENT->get_prop('_evcal_exlink_target'),
					'name'=> __('Open in new window','eventon'),
					'tooltip'=> __('This will open this link in a new window, when event is clicked.','eventon')
				));
				?>
			</div>
		
		<!-- external link field-->
			<input id='evcal_exlink' class='evomarb10' placeholder='<?php _e('Type the URL address eg. https://','eventon');?>' type='text' name='evcal_exlink' value='<?php echo ($EVENT->get_prop("evcal_exlink") )? $EVENT->get_prop("evcal_exlink"):null?>' style='width:100%;'/>
		</div>
	</div>
</div>
<?php
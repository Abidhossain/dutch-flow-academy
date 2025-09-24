<?php
/**
 * Event Edit Meta box Location
 * @version 4.9.6
 */

?>
<div class='evcal_data_block_style1'>
	<p class='edb_icon evcal_edb_map'></p>
	<div class='evcal_db_data'>
		<div class='evcal_location_data_section'>										
			<div class='evo_singular_tax_for_event event_location' data-tax='event_location' data-eventid='<?php echo $EVENT->ID;?>'>
			<?php
				echo EVO()->taxonomies->get_meta_box_content( 'event_location' ,$EVENT->ID, __('location','eventon'));
			?>
			</div>									
		</div>										
		<?php

			// if generate gmap enabled in settings
				$gen_gmap = (EVO()->cal->check_yn('evo_gen_map') && !$EVENT->check_yn('evcal_gmap_gen') ) ? true: false;

			// yea no options for location
			foreach(array(
				'evo_access_control_location'=>array('evo_access_control_location',
					__('Visible Only to Users','eventon'), 
					__('Make location only visible to registered users of your website.','eventon')
				),
				'evcal_hide_locname'=>array('evo_locname',
					__('Hide Location Name','eventon'),
					__('THis will hide location name from eventcard','eventon')
				),
				'evcal_gmap_gen'=>array('evo_genGmap',
					__('Generate Google Map','eventon'),
					__('Generate google map from the address in eventcard','eventon')
				),
				'evcal_gmap_link'=>array('evo_gmapL',
					__('Open Location in Google Maps','eventon'),
					__('Show a link to open the location in google maps page','eventon')
				),
				'evcal_name_over_img'=>array('evcal_name_over_img',
					__('Location Info over Location Image','eventon'),
					__('Show location information over the location image if you have set location image.','eventon')
				),
			) as $key=>$val){

				$variable_val = $EVENT->get_prop($key)? $EVENT->get_prop($key): 'no';

				if($variable_val == 'no' && $gen_gmap && $key=='evcal_gmap_gen')
						$variable_val = 'yes';

				$elm_args = [
					'type'=>'block_button',
					'label'=> $val[1], 'id'=> $key,
					'value'=> $variable_val,
				];

				if( $key == 'evo_access_control_location') $elm_args['nesting_start'] = 'loc evofxww';
				if( $key == 'evcal_name_over_img') $elm_args['nesting_end'] = true;
				if( !empty($val[2]) ) $elm_args['tooltip'] = $val[2];

				echo EVO()->elements->get_element( $elm_args );
			}

			// check google maps API key
			if( !EVO()->cal->get_prop('evo_gmap_api_key','evcal_1')){
				echo "<div class='evomart10'>";
				echo "<p class='evo_notice '>".__('Google Maps API key is required for maps to show on event. Please add them via ','eventon') ."<a href='". get_admin_url() .'admin.php?page=eventon#evcal_005'."'>".__('Settings','eventon'). "</a></p>";
				echo "</div>";
			}
		?>									
	</div>
</div>
<?php
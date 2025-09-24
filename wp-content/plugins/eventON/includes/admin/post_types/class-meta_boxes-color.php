<?php
/**
 * event post color meta box
 * @updated 4.9
 */

?>		

<div class='evomb_side_box evo_mb_color_box evomb_section evo_borderb'>	
	<?php
	// header
	EVO()->evo_admin->metaboxes->event_metabox_parts_header( array(
		'name'=> __('Colors'),
		'iconURL'=> 'fa-droplet',
		'id'=> 'event_colors',
		'slug'=> 'event_colors',
		'variation'=>'customfield',
	));

	echo "<div class='evomb_color_colors'>";

		// Hex value cleaning
		$hexcolor = eventon_get_hex_color($EVENT->get_prop('evcal_event_color')  );			

		echo EVO()->elements->get_element(array(
			'type'=>'colorpicker_2',
			'id'=>'color_selector_1',
			'value'=> $hexcolor,
			'value_2'=> $EVENT->get_prop('evcal_event_color_n'),
			'index'=>'',
			'label'=> __('Main Color','eventon'),
		));
		?>	
		
		<div class='evomb_color_colors_other'>
			<p style='margin-bottom:0; padding:5px 0'><?php _e('OR Select from other colors','eventon');?></p>		
			<div id='evcal_colors' class='evo_colors_used evopadb10'>
				<?php 

					global $wpdb;
					$tableprefix = $wpdb->prefix;

					// Attempt to retrieve cached results first
					$cache_key = 'event_colors_query';
					$results = wp_cache_get($cache_key, 'event_colors');

					if (false === $results) {
						// If not cached, perform the database query

						$results = $wpdb->get_results(
								"SELECT $wpdb->posts.ID, mt0.meta_value AS color, mt1.meta_value AS color_num
								FROM $wpdb->posts 
								INNER JOIN $wpdb->postmeta AS mt0 ON ( $wpdb->posts.ID = mt0.post_id )
								INNER JOIN $wpdb->postmeta AS mt1 ON ( $wpdb->posts.ID = mt1.post_id )
								WHERE 1=1 
								AND ( mt0.meta_key = 'evcal_event_color' )
								AND ( $wpdb->posts.post_type = 'ajde_events' )
								AND ( $wpdb->posts.post_status = 'publish' )
								GROUP BY $wpdb->posts.ID
								ORDER BY $wpdb->posts.post_date DESC LIMIT 50"					
						, ARRAY_A);

						// Cache the results for future use
					    if ($results) {
					        wp_cache_set($cache_key, $results, 'event_colors', HOUR_IN_SECONDS);
					    }

					}

					if($results){
						$other_colors = array();
						
						foreach($results as $color){
							// hex color cleaning
							$hexval = substr( str_replace('#', '', $color['color']) , 0,7);
							$hexval_num = !empty($color['color_num'])? $color['color_num']: 0;
							
							
							if(!empty( $hexval) && (empty($other_colors) || (is_array($other_colors) && !in_array($hexval, $other_colors)	)	)	){
								echo "<div class='evcal_color_box' style='background-color:#".$hexval."'color_n='".$hexval_num."' color='".$hexval."'></div>";
								
								$other_colors[]=$hexval;
							}
						}
					}							
				?>				
			</div>
		</div>

	</div>
		

	<?php 
	echo EVO()->elements->get_element(array(
		'type'=>'yesno_btn',
		'id'=>'_evo_event_grad_colors',
		'value'=> $EVENT->get_prop('_evo_event_grad_colors'),
		'afterstatement'=>'color_selector_content',
		'label'=> __('Set Gradient Colors'),
	));
	echo EVO()->elements->get_element(array(
		'type'=>'begin_afterstatement',
		'id'=>'color_selector_content',
		'value'=>$EVENT->get_prop('_evo_event_grad_colors')
	));

	// color selector
		$hexcolor2 = eventon_get_hex_color($EVENT->get_prop('evcal_event_color2')  );	
		echo EVO()->elements->get_element(array(
			'type'=>'colorpicker_2',
			'id'=>'color_selector_2',
			'value'=> $hexcolor2,
			'value_2'=> $EVENT->get_prop('evcal_event_color_n2'),
			'index'=>'2',
			'label'=> __('Secondary Color','eventon'),
		));
	
	// gradient angle
	echo EVO()->elements->get_element(array(
		'type'=>'angle_field',
		'id'=>'_evo_event_grad_ang',
		'value'=> $EVENT->get_prop('_evo_event_grad_ang'),
		'label'=> __('Set Gradient Angle'),
	));

	// Gradients preview
		$styles = "background-color:". $hexcolor .';';
		
		if( $hexcolor != $hexcolor2 ){

			$ang = $EVENT->get_prop('_evo_event_grad_ang') ? (int)$EVENT->get_prop('_evo_event_grad_ang') : 0; 
			$styles .= "background-image: linear-gradient({$ang}deg, {$hexcolor2} 0%, {$hexcolor} 100%);";

		}
		echo "<div class='evo_color_grad_prev' style='{$styles}'></div>";

		echo "<p class='evopadt15'>".__('Note: Gradients are only available with eventtop full background color on.','eventon') .'</p>';

		echo EVO()->elements->get_element(array(
			'type'=>'end_afterstatement',
		));
	?>
</div>	
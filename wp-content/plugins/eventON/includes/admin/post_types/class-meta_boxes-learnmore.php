<?php
/**
 * Learn more meta box data
 * @version 4.9.3
 */

echo "<div class='evo_meta_elements'>";
									
	EVO()->elements->print_process_multiple_elements(
		array(
			array(
				'type'=>'url',
				'name'=> esc_html__('Learn More Link','eventon'),
				'tooltip'=>'Type in your complete event link with http.',
				'id'=>'evcal_lmlink',
				'id2'=> 'evcal_lmlink_target',
				'value_2'=> $EVENT->get_prop('evcal_lmlink_target'),
				'value'=> esc_attr( $EVENT->get_prop('evcal_lmlink') )
			),
			
		)
	);

echo "</div>";

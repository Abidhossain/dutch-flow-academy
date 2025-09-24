<?php
/**
 * EVODP Post meta boxes
 * @version 1.0.1
 */
class evodp_meta_boxes{
	public function __construct(){
		add_action('evotx_event_metabox_end',array($this, 'event_tickets_metabox'), 10, 5);
		add_filter('evotx_save_eventedit_page',array($this, 'event_ticket_save'), 10, 1);
	}

	function event_tickets_metabox($eventid, $epmv, $wooproduct_id, $product_type, $EVENT){
		
		$event_edit_allow_dynamic_pricing = apply_filters('evodp_event_edit_enable_dp',true, $EVENT);

		$show_DP = true;

		// Check if repeating event
		if( $EVENT->is_repeating_event()) $show_DP = false; 
		if( empty($wooproduct_id) ) $show_DP = false; 
		if( $product_type != 'simple') $show_DP = false;
		if( !$event_edit_allow_dynamic_pricing) $show_DP = false;
				
		if( $show_DP ):
		
			EVO()->elements->_print_settings_toggle_nester_start(array(
				'id'=>'_evodp_activate',
				'value'=>$EVENT->get_prop('_evodp_activate'),
				'value_yn'=> $EVENT->check_yn('_evodp_activate'),
				'afterstatement'=>'evodp_pricing',			
				'tooltip'=>__('This will allow you to set dynamic ticket pricing options.','evodp'),
				'label'=>__('Enable dynamic ticket pricing options for this event','evodp'),
			));
			
				EVO()->elements->get_element(array(
					'type'=>'detailed_button', '_echo'=> true,
					'name'=>__('Dynamic Pricing Editor','evodp'),
					'description'=>__('Configure Dynamic Pricing Settings','evodp'),
					'field_after_content'=> "Configure",
					'row_class'=>'evo_bordern evomar0',
					'trig_data'=> array(
						'uid'=>'evodp_settings',
						'lb_class' =>'config_evodp_settings',
						'lb_title'=>__('Configure Dynamic Pricing Settings','eventon'),	
						'ajax_data'=>array(					
							'eid'=> $EVENT->ID,
							'action'=> 'evodp_load_editor',
						),
					),
				));
						
			EVO()->elements->_print_settings_toggle_nester_close();

		else:
			?>
			<div class='evopad20 evo_borderb'>
				<?php if(!$event_edit_allow_dynamic_pricing):?>
					<p><i><?php _e('NOTE: Dynamic Pricing is not available for current event ticket configurations.', 'eventon'); ?></i></p>
				<?php else:?>	
					<p><i><?php _e('NOTE: Dynamic Pricing is only available for simple ticket product with no repeat instances at the moment. The event ticket basic information must be saved first before configuring dynamic prices.', 'eventon'); ?></i></p>
				<?php endif;?>
			</div>
			<?php
		endif;
	}

	

	// save fields
		function event_ticket_save($array){
			$array[] = '_evodp_activate';
			return $array;
		}
}
new evodp_meta_boxes();
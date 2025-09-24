<?php 
/**
 * Price Blocks and single block
 * @version 1.0
 */

class EVODP_Price_Blocks{

	public $blocks_data, $EVENT, $block_index, $block_type, $block_key;

	public function __construct($EVENT, $block_type = 'tbp'){
		$this->EVENT = $EVENT;

		$this->block_type = $block_type;
		$this->block_key = $this->block_type == 'tbp' ? '_evodp_prices':'_evodp_una';
		$this->blocks_data = $EVENT->get_prop( $this->block_key );
	}

	function get_block_data($get_index){
		$price_blocks = $this->blocks_data;

		if($price_blocks && is_array($price_blocks) && count($price_blocks)> 0 ){
			
			foreach($price_blocks as $index=>$data){
				if( !is_array($data)) continue;

				if( $get_index == $index)	return $data;
			}
		}
		return false;
	}

	function get_block_prop($field){
		if( empty($field)) return false;
		if( empty($this->block_index)) return false;

		if( $this->blocks_data && is_array( $this->blocks_data ) && count( $this->blocks_data )>0 ){
			if( !isset( $this->blocks_data[ $this->block_index ]) ) return false;
			if( !is_array( $this->blocks_data[ $this->block_index ] )) return false;
			if( !isset( $this->blocks_data[ $this->block_index ][ $field ]) ) return false;			
			return $this->blocks_data[ $this->block_index ][ $field ];
		}else{
			return false;
		}
	}

	function get_block_start_unix(){
		if( empty($this->block_index)) return false;

		if( $this->blocks_data && is_array( $this->blocks_data ) && count( $this->blocks_data )>0 ){
			if( !isset( $this->blocks_data[ $this->block_index ]) ) return false;
			if( !is_array( $this->blocks_data[ $this->block_index ] )) return false;
			
			return $this->blocks_data[ $this->block_index ][0];

		}else{
			return false;
		}
	}

	// HTML content
	function get_block_list_html(){
		$_wp_date_format = get_option('date_format');

		$out = '';
		if($this->blocks_data && is_array($this->blocks_data) && count($this->blocks_data)> 0 ){
			
			foreach($this->blocks_data as $index=>$data){
				if( !is_array($data)) continue;

				$data['date_format'] = $_wp_date_format;
				$data['time_format'] = EVODP()->fnc->get_time_format();
				$data['block_key'] = 	$this->block_key;
				$data['block'] = 		$this->block_type;
				$data['eid'] = $this->EVENT->ID;
				$out .= $this->get_one_block_html($data, $index);
			}
		}else{
			$out.= "<p class='none' style='padding:8px;'>".
				( $this->block_type == 'tbp' ? 
					__('You do not have any pricing blocks yet!','eventon') :
					__('You do not have any unavailable time blocks yet!','eventon') )					
				."</p>";
		}

		return $out;
	}
	function get_one_block_html($args, $index){
		ob_start();

		$__woo_currencySYM = get_woocommerce_currency_symbol();

		$start = !empty($args[0])? $args[0]: EVODP()->fnc->get_unix_time_legacy($args['sd'] , $args['st'], $args);
		$end = !empty($args[1])? $args[1]: EVODP()->fnc->get_unix_time_legacy($args['ed'] , $args['et'], $args);

		$date_format = $args['date_format'];
		$date_format= 'Y/m/d';

		// block local time set
		$DD = EVODP()->fnc->DD;

		$DD->setTimestamp($start );
		$start_string = $DD->format($date_format.' '.$args['time_format']);
		
		$DD->setTimestamp($end );
		$end_string = $DD->format($date_format.' '.$args['time_format']);

		?>
		<li data-cnt="<?php echo $index;?>" class="new" style=''>

			<div class=''>
				<div class=''>
					<span><?php _e('Start','eventon');?> </span><?php echo $start_string;?> 
					<span class="e"><?php _e('End','eventon');?> </span> <?php echo $end_string;?> 
				</div>
				
				<?php if($args['block'] =='tbp'):?>
					<div class=''>
					<p>
						<i class='reg_price'><?php _e('Price','eventon');?>: <b><?php echo EVODP()->fnc->check_data($args, 'p')? 
						$__woo_currencySYM. (EVODP()->fnc->check_data($args, 'p')):'';?></b></i>
						<i class='mem_price'>/ <?php _e('Member Price','eventon');?>: <b><?php echo EVODP()->fnc->check_data($args, 'mp')? 
						$__woo_currencySYM. (EVODP()->fnc->check_data($args, 'mp')): __('-same-','eventon');?></b></i>
					</p>
					</div>
				<?php endif;?>
			</div>			

			<span class='evodp_actions'>
				<?php 
				// edit
				EVO()->elements->print_trigger_element(array(
					'class_attr'=>'evodp_block_item evolb_trigger fa fa-pencil',
					'dom_element'=> 'i',
					'uid'=>'evodp_edit_price_block',
					'lb_class' =>'config_evodp_editor',
					'lb_title'=>__('Price Block Editor','evodp'),	
					'ajax_data'=>array(
						'eid'=> $this->EVENT->ID,
						'block'=> $args['block'],
						'index'=> $index,
						'form_type'=> 'edit',
						'action'=> 'evodp_get_form'
					),
				), 'trig_lb');

				// delete
				EVO()->elements->print_trigger_element(array(
					'class_attr'=>'evodp_block_item evo_trigger_ajax_run fa fa-times',
					'dom_element'=> 'i',
					'uid'=>'evodp_del_price_block',
					'lb_class' =>'config_evodp_settings',
					'lb_loader' => true,
					'lb_load_new_content'=> true,			
					'load_new_content_id'=> 'evodp_event_settings_content',	
					'ajax_data' =>array(
						'eid'=> $this->EVENT->ID,
						'block'=> $args['block'],
						'index'=> $index,
						'action'=> 'evodp_delete_block'
					),
				), 'trig_ajax');

				?>
			</span>		
		</li>
		<?php
		return ob_get_clean();
	}

	function generate_block_index(){
		$rand_id = rand(100000,999990);

		if( is_array( $this->blocks_data )){
			if( array_key_exists($rand_id, $this->blocks_data )){
				$rand_id = rand(100000,999990);
			}
		}
		return $rand_id;
	}

	function delete_block( ){
		if( empty($this->block_index)) return false;

		if( $this->blocks_data && is_array( $this->blocks_data ) && count( $this->blocks_data )>0 ){
			if( !isset( $this->blocks_data[ $this->block_index ]) ) return false;

			unset( $this->blocks_data[ $this->block_index ] );

			$this->save_blocks();
		}
		return true;

	}

	public function save_blocks(){
		$block_key = $this->block_type == 'tbp' ? '_evodp_prices':'_evodp_una';
		$this->EVENT->set_prop( $block_key, $this->blocks_data );
	}

}
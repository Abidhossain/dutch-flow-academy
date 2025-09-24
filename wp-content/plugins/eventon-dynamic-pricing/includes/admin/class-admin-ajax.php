<?php
/**
 * Admin Ajax
 * @version 1.0.1
 */

class evodp_admin_ajax{
	private $help, $postdata;
	public function __construct(){
		$ajax_events = array(
			'evodp_load_editor'=>'settings',
			'evodp_save_editor'=>'save_settings',
			'evodp_get_form'=>'price_editor',
			'evodp_add_new_time_block'=>'save_new_block',			
			'evodp_delete_block'=>'delete_block',			
			'evodp_clear_all_data'=>'clear_all_data',			
		);
		foreach ( $ajax_events as $ajax_event => $class ) {
			add_action( 'wp_ajax_'.  $ajax_event, array( $this, $class ) );
			add_action( 'wp_ajax_nopriv_'.  $ajax_event, array( $this, $class ) );
		}

		$this->help = new evo_helper();
		$this->postdata = $this->help->sanitize_array( $_POST);

	}

// Settings
	function _get_settings($EVENT){
		$HELP = $this->help;
		$post = $this->postdata;
		
		$wcid = $EVENT->get_prop('tx_woocommerce_product_id');
		$fnc = new evodp_fnc();

		$__woo_currencySYM = get_woocommerce_currency_symbol();
		$_evodp_member_pricing = $EVENT->get_prop('_evodp_member_pricing' );
		$_evodp_time_pricing = $EVENT->get_prop('_evodp_time_pricing' );

		ob_start();

		?>
		<div id='evodp_event_settings_content' class='evodp_editor' style='padding:20px;'>	
			<form class='evodp_editor_form'>

			<?php 
			// hidden fields
			EVO()->elements->print_hidden_inputs( array(
				'event_id' => $EVENT->ID,
				'action' =>	'evodp_save_editor',
			));
			
			//-- time based pricing blocks -->
		
			echo EVO()->elements->get_element( array(
				'type'=>	'yesno_btn',
				'id'=> 		'_evodp_time_pricing',
				'value'=> 	$EVENT->get_prop('_evodp_time_pricing' ),
				'afterstatement'=>'evodp_time_pricing_section',
				'label'=>	 __('Enable Time Based Ticket Pricing Blocks','eventon'),
				'guide'=>	__('This will allow you to set dynamic ticket pricing options.','eventon')
			));			
			?>			
			<div id='evodp_time_pricing_section' class='evo_edit_field_box' style='display:<?php echo $EVENT->check_yn('_evodp_time_pricing')? '':'none';?>'>
				
				<?php 
				echo $this->get_settings_part('tbp_block', array('EVENT'=>$EVENT) );
				?>
				
				<p><?php
					EVO()->elements->print_trigger_element(array(
						'extra_classes'=>'',
						'title'=>__('Add New Pricing Block','eventon'),
						'dom_element'=> 'span',
						'uid'=>'evodp_new_price_block',
						'lb_class' =>'config_evodp_editor',
						'lb_title'=>__('Dynamic Price Block Editor','eventon'),	
						'ajax_data'=>array(					
							'eid'=> $EVENT->ID,
							'wcid'=>$wcid,
							'block'=>'tbp',
							'form_type'=>'new',
							'action'=>'evodp_get_form',
						),
					), 'trig_lb');
					
				?></p>
	
				<p style='opacity:0.5'><i><?php _e('NOTE: You can use this to create earlybird pricing and price increases as you get closer to event.','eventon');?></i></p>
				
				<?php 
					// Text when dynamic price is on
					echo EVO()->elements->get_element(array(
						'name'=> __('Text to show when time based price is active (Leave blank to show nothing)','eventon'),
						'id'=>'_evodp_tbp_msg',
						'type'=>'input',
						'value'=> $EVENT->get_prop('_evodp_tbp_msg')
					));
				?>
			</div>
			<?php
			
			//-- Member pricing -->
			echo EVO()->elements->get_element( array(
				'type'=>	'yesno_btn',
				'id'=> 		'_evodp_member_pricing',
				'value'=> 	$EVENT->get_prop('_evodp_member_pricing' ),
				'afterstatement'=>'evodp_member_pricing',
				'label'=>		__('Enable Separate Logged-in Member Pricing','eventon'),
				'tooltip'=>		__('This will allow you to set separate price for members of your website that have logged into your site.','eventon'),
			)); 
			?>

			<div id='evodp_member_pricing' class="evo_edit_field_box evodp_member_pricing" style='display:<?php echo $EVENT->check_yn('_evodp_member_pricing')?'':'none';?>'>
				<?php
					// default member price
					echo EVO()->elements->get_element(array(
						'name'=> sprintf( __('Default Ticket Price Only for Members (%s)','eventon'), $__woo_currencySYM),
						'id'=>'_evodp_member_def_price',
						'type'=>'input',
						'value'=> $EVENT->get_prop('_evodp_member_def_price')
					));
				?>
				<?php /*<p><label ><?php _e('Text to show (below price) when memeber price is active (Leave blank to show nothing)','eventon');?></label><br/>
					<input name='_evodp_member_msg' style='width:100%; margin-top:5px;'type="text" value='<?php echo evo_meta($epmv,'_evodp_member_msg');?>'>
				</p>*/?>
			</div>
			

			<!-- unavailable blocks -->
			<?php		
			echo EVO()->elements->get_element( array(
				'type'=>	'yesno_btn',
				'id'=> 		'_evodp_unavailables',
				'value'=> 	$EVENT->get_prop('_evodp_unavailables' ),
				'afterstatement'=>'evodp_una_section',
				'guide'=>	'This will allow you to set dynamic ticket pricing options.',
				'label'=> 	__('Enable Tickets Unavailable for Sale Time Blocks')
			));

			?>

			<div id='evodp_una_section' class='evo_edit_field_box' style='display:<?php echo $EVENT->check_yn('_evodp_unavailables') ?'':'none';?>'>
				
				<?php 
				echo $this->get_settings_part('una_block', array('EVENT'=>$EVENT) );
				?>			
				
				<p><?php
					EVO()->elements->print_trigger_element(array(
						'extra_classes'=>'',
						'title'=>__('Add New Unavailable Time Block','eventon'),
						'dom_element'=> 'span',
						'uid'=>'evodp_new_una_block',
						'lb_class' =>'config_evodp_editor',
						'lb_title'=>__('Dynamic Unavailable Time Block Editor','eventon'),	
						'ajax_data'=>array(					
							'eid'=> $EVENT->ID,
							'wcid'=>$wcid,
							'block'=>'una',
							'form_type'=>'new',
							'action'=>'evodp_get_form',
						),
					), 'trig_lb');
					
				?></p>
			</div>

			<?php 

			// strike thru regular price -->
			echo EVO()->elements->get_element(array(
				'type'=>	'yesno_btn',
				'id'=>			'_evodp_show_regularp',
				'value'=>			$EVENT->get_prop('_evodp_show_regularp'), 
				'input'=>		true,
				'label'=>		__('Show strikedthrough regular price as well on EventCard','eventon'),
				'tooltip'=>		__('This will show regular ticket price strikedthrough next to the discounted dynamic price you will set below.','eventon'),
			));
			
			?>
			<p class='evopadt10'><?php			
				// save changes button
					EVO()->elements->print_trigger_element(array(
						'extra_classes'=>'evodp_save_settings',
						'title'=>__('Save Changes','evodp'),
						'uid'=>'evodp_save_settings',
						'lb_loader'=>true,
						'lb_class' =>'config_evodp_settings',
						'lb_hide'=>2000,
					), 'trig_form_submit');

				// clear all blocks button
					EVO()->elements->print_trigger_element(array(
						'class_attr'=>'evo_trigger_ajax_run evo_btn',
						'title'=>'clear all blocks',
						'uid'=>'evodp_clearall_blocks',
						'lb_class' =>'config_evodp_settings',
						'lb_loader' => true,
						'lb_load_new_content'=> true,			
						'load_new_content_id'=> 'evodp_event_settings_content',	
						'ajax_data' =>array(
							'eid'=> $EVENT->ID,
							'action'=> 'evodp_clear_all_data'
						),
					), 'trig_ajax');

			?>				
			</p>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}
	function settings(){	
		$post = $this->postdata;

		$event_id = (int)$post['eid'];
		$EVENT = new EVO_Event($event_id);

		echo json_encode(array(
			'content'=> $this->_get_settings($EVENT),
			'status'=>'good'
		)); exit;
	}

	public function get_settings_part($part_key, $args = array()){
		ob_start();
		extract($args);
		switch ($part_key) {
			case 'tbp_block':
				?>
				<ul id='evodp_tbp_block' class="evodp_dpblocks evodp_blocks_tbp evodp_blocks_list">
					<?php
				
						$BLOCKS = new EVODP_Price_Blocks( $EVENT, 'tbp');
						echo $BLOCKS->get_block_list_html();
					?>
				</ul>
				<?php
			break;	

			case 'una_block':
				?>
				<ul id='evodp_una_block' class="evodp_blocks_una evodp_blocks_list">
				<?php

					$BLOCKS = new EVODP_Price_Blocks( $EVENT, 'una');
					echo $BLOCKS->get_block_list_html();
				?>
				</ul>	
				<?php 
			break;
		}

		return ob_get_clean();
	}

	function save_settings(){
		if( !isset($this->postdata['event_id'])){
			echo json_encode(array(
				'msg'=> __('Event ID Missing'),'status'=>'bad'
			)); exit;
		}
		$EVENT = new EVO_Event( $this->postdata['event_id']);

		foreach( array(
			'_evodp_unavailables','_evodp_show_regularp',
			'_evodp_member_pricing','_evodp_member_def_price','_evodp_time_pricing',
			'_evodp_tbp_msg'
		) as $key){
			if( !isset( $this->postdata[ $key ] )) continue;
			$EVENT->set_prop( $key, $this->postdata[ $key ]);
		}

		echo json_encode(array(
			'msg'=> __('Dynamic Pricing Settings Saved'),
			'status'=>'good'
		)); exit;
	}

// Price Editor -> save new/ edit
	public function price_editor(){		

		ob_start();

		$post = $this->postdata;
		$EVENT = new EVOTX_Event( $post['eid'] );
		$BLOCKS = new EVODP_Price_Blocks( $EVENT, $post['block'] );
		$block_index = (!empty($post['index'])? $post['index']: $BLOCKS->generate_block_index() );
		$BLOCKS->block_index = $block_index;

		$block_data = !empty($block_index) ? $BLOCKS->get_block_data( $block_index ) : false;

		// date time data
			$DT = EVO()->elements->_get_date_picker_data();
			extract($DT);

		// block type unavailable or dynamic price
		$block_key = $post['block']=='una'? '_evodp_una':'_evodp_prices';

		$__woo_currencySYM = get_woocommerce_currency_symbol();

		$event_start = $EVENT->get_start_time();
		$event_end = $EVENT->get_end_time(); 

		$datetime_format = 'Y/m/d H:i';

		?>
		<div class="evodp_add_una_block evodp_item_block_container evopad20" style="">
			<form class='evodp_price_block_editor'>
			<?php EVO()->elements->print_hidden_inputs( array(
				'action'=>'evodp_add_new_time_block',
				'form_type'=> isset($post['form_type']) ? $post['form_type']:'',
				'block'=> $post['block'],
				'eid'=> $post['eid'],				
				'bkey'=> $block_key,
				'index'=> $block_index,
			));

			EVO()->elements->process_multiple_elements(array(
				array(
					'_echo'=> true,
					'id'=>'wcid',
					'type'=>'static',
					'name'=> __('Price Block Index ID','eventon'),
					'value'=> $block_index
				),
				array(
					'_echo'=> true,
					'id'=>'wcid',
					'type'=>'static',
					'name'=> __('Event Time','eventon'),
					'value'=> date($datetime_format,$event_start) .' - '. date($datetime_format,$event_end)
				),
				array(
					'_echo'=> true,
					'id'=>'wcid',
					'type'=>'static',
					'name'=> __('Event Ticket Regular Price','eventon'),
					'value'=> $__woo_currencySYM . $EVENT->get_product_price()
				)
			));


			// dynamic price only
			if($post['block'] == 'tbp'):

				// regular price
				EVO()->elements->get_element(array(
					'_echo'=> true,
					'name'=> __('Price','eventon') ." * ($__woo_currencySYM)",
					'id'=>'p',
					'type'=>'input',
					'value'=> $BLOCKS->get_block_prop('p'),
					'default'=> $EVENT->get_product_price()
				));
			
				if( $EVENT->check_yn('_evodp_member_pricing')):
					// special member price
					EVO()->elements->get_element(array(
						'_echo'=> true,
						'name'=> __('Member Price','eventon') ." * ($__woo_currencySYM)",
						'id'=>'mp',
						'type'=>'input',
						'value'=> !empty($post['mprice'])? $post['mprice']:''
					));
				endif;?>

			<?php endif;?>			

			<p class='evodp_dt_pic'>
				<span><?php _e('Time Block Start date time','evodp');?>: *</span>				
				<?php 
				$rand_id = rand(100000,999990);
				$block_start = $BLOCKS->get_block_start_unix();
				$block_end = $BLOCKS->get_block_prop('1');

				EVO()->elements->print_date_time_selector(array(
					'disable_date_editing'=> false,
					'time_format'=> $time_format,
					'date_format'=>$date_format,
					'date_format_x'=>$date_format,
					'unix'=> ($block_start? $block_start: $event_start),				
					'type'=>'dst',
					'assoc'=>'reg',
					'names'=>true,
					'rand'=> $rand_id
				));				
				?>
				<span><?php _e('Time Block End date time','evodp');?>: *</span>
				<?php 
				EVO()->elements->print_date_time_selector(array(
					'disable_date_editing'=> false,
					'time_format'=> $time_format,
					'date_format'=>$date_format,
					'date_format_x'=>$date_format,
					'unix'=> ($block_end? $block_end: $event_start),					
					'type'=>'den',
					'assoc'=>'reg',
					'names'=>true,
					'rand'=> $rand_id
				));				
				?>
			</p>			
			
			<p><?php
			// Save Changes
			EVO()->elements->print_trigger_element(array(
				'extra_classes'=>'evo_submit_form',
				'title'=>__('Save Changes','evodp'),
				'dom_element'=> 'span',
				'uid'=>'evodp_save_editor',
				'lb_class' =>'config_evodp_editor',
				'lb_loader' => true,
				'lb_hide'=> 2000,
				'lb_load_new_content'=> true,			
				'load_new_content_id'=> $post['block'] =='tbp' ? 
					'evodp_tbp_block':
					'evodp_una_block',	
				
			), 'trig_form_submit');
			?>
				
			</p>

		</form>
		</div>
		<?php

		echo json_encode(array(
			'content'=> ob_get_clean(),
			'status'=>'good'
		)); exit;
	}

// function > save Add/edit new time block
	function save_new_block(){

		$post = $this->postdata;
		
		$EVENT = new EVO_Event($post['eid']);
		$block_type = isset($post['block'])? $post['block'] : 'tbp';

		$BLOCKS = new EVODP_Price_Blocks( $EVENT, $block_type );
		$block_data = $BLOCKS->blocks_data;
			$block_data = !$block_data ? array() : $block_data;

		$block_index = !isset($post['index']) ? $BLOCKS->generate_block_index() : $post['index'];


		// check for unix values
		$block_unix = EVODP()->fnc->get_unix_time( $post);
		if(!$block_unix){
			echo json_encode(array('status'=>'bad','msg'=>__('Could not convert to unix time')));
			exit;
		}

		$post[0] = $block_unix[0];
		$post[1] = $block_unix[1];

		// remove unneeded values
			unset($post['action']);
			unset($post['form_type']);

		// ONLY for time based price
		if( $post['block'] == 'tbp'){
			if( empty($post['p'])) $post['p'] = 0;
			if( empty($post['mp'])) $post['mp'] = 0;			
		}

		// save new block data
		$block_data[ $block_index ] = $post;
		$BLOCKS->blocks_data = $block_data;
		$BLOCKS->save_blocks();


		wp_send_json(array(
			'content'=>	$block_type == 'tbp' ? 
				$this->get_settings_part('tbp_block', array('EVENT'=>$EVENT) )
				: $this->get_settings_part('una_block', array('EVENT'=>$EVENT) ),
			'content_id'=>'evodp_event_settings_content',
			'status'=>	'good',
			'msg'=>	__('Successfully Save Block Data','eventon')
		)); wp_die();
	}

// delete a block
	function delete_block(){
		$post = $this->postdata;

		$EVENT = new EVO_Event($post['eid']);

		$BLOCKS = new EVODP_Price_Blocks( $EVENT , $post['block'] );
		$BLOCKS->block_index = $post['index'];

		$result = $BLOCKS->delete_block();
		
		echo json_encode(array(
			'content'=>	$this->_get_settings( $BLOCKS->EVENT ),
			'status'=>	$result ? 'good':'bad',
			'msg'=>	$result ? __('Successfully deleted block','eventon') : __('Could not delete the block','eventon')
		)); exit;
	}

	function clear_all_data(){
		$post = $this->postdata;
		
		$EVENT = new EVO_Event($post['eid']);
		$BLOCKS = new EVODP_Price_Blocks( $EVENT );

		$EVENT->delete_meta( '_evodp_prices');
		$EVENT->delete_meta( '_evodp_una');
		$EVENT->load_all_meta();

		echo json_encode(array(
			'content'=> $this->_get_settings( $BLOCKS->EVENT ),
			'status'=>	'good',
			'msg'=>	__('Successfully deleted all blocks','eventon')
		)); exit;
	}

}
new evodp_admin_ajax();
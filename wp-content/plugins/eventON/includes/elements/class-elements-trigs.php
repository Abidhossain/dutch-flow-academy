<?php
/**
 * Elements trigger elements
 * @version 4.8.1
 */

class EVO_Elm_Trigs{
	// Process trigger element data and return data-attribute @4.7
	public function _process_trigger_data( $data , $type = 'trig_lb', $return = 'all'){

		if( !is_array( $data )) return false;

		$help = new evo_helper();

		$processed_data = array_merge(array(					
			'class_attr'=>'', // pass class to replace default
			'extra_classes'=>'',
			'styles'=> '',
			'title'=>'',
			'id'=>'',
			'dom_element'=> 'span',
			'uid'=>'',// using this as id as well
			'hide_attr_data'=> false, //@4.7.2

			// @since 4.7.2
			'adata'=> array(), // @4.7.2 //include all ajax data in here, type (ajax,rest,endpoint),action/a, other data, end (client/admin)
			'lbdata'=> array(),//@4.7.2 lightbox data all in one place, class, title, size, padding, loader , hide, hide_msg, additional_class, new_content, new_content_id, size = mid small, s400, s500, s700, s800, 

			// legacy values
				'lb_class' =>'',
				'lb_title'=>'',
				'lb_size'=>'', // mid, small
				'lb_padding'=>'evopad30',
				'lb_loader'=> false,			
				'lb_load_new_content'=> true,	
				'lb_hide'=>'',
				'lb_hide_message'=>'',
				

				// Ajax		
				'ajax'=>'yes',
				'ajax_data'=>'',
				'end'=>'admin',// client or admin
				'ajax_action'=>'',// @since 4.4
				'ajax_type'=>'', // @since 4.4
				'ajax_url'=>'',
				'a'=> '',

			//'content_id'=>'',
			//'content'=>'', // pass dynamic content
			'load_new_content_id'=>'',			
		), $data );

		extract( $processed_data);



		$btn_data = array();

		// Ajax data
			$_adata = ( !empty($adata)) ? $adata : array();

			// set default needed values
			$def_avals = array(
				'a'            => '',
				'type'         => 'ajax',
				'end'          => 'admin',
				'data'         => array(),
				'loader_el'    => '',
				'loader_class' => '',
				'url'          => '',
				'loader_btn_el'=> '',// any value would load this
			);
			foreach ($def_avals as $key => $value) {
				if (!array_key_exists($key, $_adata) && !empty( $value )) {
					$_adata[$key] = $value;
				}
			}

			// move old to new
			$def_adata_mapping = array(
				'ajax_type'  	=>'type', // def ajax,rest,endpoint
				'ajax_action'  	=>'a', 
				'a'  			=>'a', 
				'end'  			=>'end', 
				'ajax_url'		=>'url',
				'ajax_data'		=> 'data',
			);
			foreach ($def_adata_mapping as $old => $new) {
				if( array_key_exists( $new , $_adata) && $_adata[ $new ] != '') continue;
				if (isset( $processed_data[ $old ] ) && !empty( $processed_data[ $old ]) ){
					$_adata[ $new ] = $processed_data[ $old ];
				}
			}

			// Move any additional values in _adata to _adata['data']
			foreach ($_adata as $key => $value) {
				if (!array_key_exists($key, $def_avals)) {
					$_adata['data'][$key] = $value;
					// Optionally remove the key from _adata main array if required
					// unset($_adata[$key]);
				}
			}


			// correct action param
				if( isset($_adata['data']['a'])) $_adata['a'] = $_adata['data']['a'];
				if( isset($_adata['data']['action'])) $_adata['a'] = $_adata['data']['action'];


		// lightbox
			$_lbdata = ( !empty($lbdata)) ? $lbdata : array();

			// if old passed > convert old to new
			$def_lbdata = array(
				'lb_title'			=>'title',
				'lb_class'			=>'class',
				'lb_size'			=> 'size', //size = mid small, s400, s500, s700, s800, 
				'lb_padding'		=>'padding',
				'lb_loader'			=>'loader',
				'lb_new_content'		=>'new_content',
				'load_new_content_id'	=>'new_content_id',
				'lb_hide'			=> 'hide',
				'lb_hide_message'	=> 'hide_msg',
			);
			foreach($def_lbdata as $old => $new){
				// if _lbdata has new key > skip
				if( array_key_exists( $new , $_lbdata) && $_lbdata[ $new ] != '') continue;
				if (isset( $processed_data[ $old ] ) && !empty( $processed_data[ $old ]) ){
					$_lbdata[ $new ] = $processed_data[ $old ];
				}
			}		

			// set default needed values
			$def_lbvals = array(
				'preload_temp_key'=> '',
				'padding'=> 'evopad30',
				'new_content'=> true,
			);
			foreach($def_lbvals as $key => $value ){
				// if _lbdata does not have these default values and default value is not empty
				if (!array_key_exists($key, $_lbdata) && !empty( $value )) {
					$_lbdata[$key] = $value;
				}
			}

			

			// use lightbox class from uid IF not passed
			if( empty( $lb_class) && empty($_lbdata['class']) && !empty($uid) ) 
				$_lbdata['class'] = $uid;

		
		// lightbox
		if( $type == 'trig_lb' || empty( $type )){

			$btn_data = array('lbvals'=> array());

			// add ajax data
			if( !empty($_adata['a'])) $btn_data['lbvals']['adata'] = $_adata;
			if( count($_lbdata) > 0 ) $btn_data['lbvals']['lbdata'] = $_lbdata;
			
			if( !empty($uid)) $btn_data['lbvals']['uid'] = $uid;			
		}
		
		// trigger ajax 
		if( $type == 'trig_ajax'){
			$btn_data = array('d'=> array( ));

			if( !empty($_adata)) $btn_data['d']['adata'] = $_adata; // @4.7.2
			if( !empty($_lbdata)) $btn_data['d']['lbdata'] = $_lbdata; // @4.7.2
			if( !empty($uid)) $btn_data['d']['lbdata']['uid'] = $uid;
		}

		// form submit
		if( $type =='trig_form_submit'){
			
			$btn_data = array('d' => array());

			if( !empty($_adata)) $btn_data['d']['adata'] = $_adata; // @4.7.2
			if( !empty($_lbdata)) $btn_data['d']['lbdata'] = $_lbdata; // @4.7.2
			if( !empty($uid)) $btn_data['d']['lbdata']['uid'] = $uid;
		}	
		
		// what to return
		switch($return){
			case 'all':
				return array(
					$help->array_to_html_data( $btn_data ),
					$processed_data
				);
			break;
			case 'all_raw':
				return array(
					$btn_data,
					$processed_data
				);
			break;	
			default:
				return $help->array_to_html_data( $btn_data );
			break;
		}
		
		
	}

// triggering button @since 4.3.5 @updated 4.7.2
	public function process_trigger_element_data( $args, $type, $uniqid ){

		$processed_data = $this->_process_trigger_data( $args, $type , 'all_raw');
		extract( $processed_data[1] );


		ob_start();

		switch($type){
			case 'trig_form_submit':
				/* easy copy
					'extra_classes'=>'',
					'styles'=> '',
					'title'=>'',
					'dom_element'=> 'span',
					'uid'=>'',
					'lb_class' =>'',
					'adata'=> array(),
					'lbdata'=> array(),
				*/

				$class_attr = empty($class_attr) ? 'evo_btn evolb_trigger_save': $class_attr;
				?><<?php echo $dom_element;?> id="<?php echo $uniqid;?>" class='<?php echo $class_attr . $extra_classes;?> has_dynamic_vals'  style='<?php echo $styles;?>'><?php echo $title;?></<?php echo $dom_element;?>>
				<?php

				return array(
					ob_get_clean(),
					$processed_data[0]
				);
			break;

		}

	}
	public function print_trigger_element($args, $type){
		$help = new evo_helper();

		$processed_data = $this->_process_trigger_data( $args, $type );
		extract( $processed_data[1] );

		switch($type){
			case 'trig_lb':
				/*
					'extra_classes'=>'',
					'styles'=> '',
					'id'=>'',
					'dom_element'=> 'span',
					'uid'=>'',
					'lb_class' =>'',
					'lb_title'=>'',	
					'title'=>'',
					'adata'=> array(),
					'lbdata'=> array(),

				*/
				$class_attr = empty($class_attr) ? 'evo_btn evolb_trigger ': $class_attr;
				if( $hide_attr_data ) $class_attr .= 'evo_hidden_data';
				?><<?php echo $dom_element;?> <?php echo !empty($id) ? "id='{$id}'" :null;?> class='<?php echo $class_attr . $extra_classes;?>' <?php echo $processed_data[0];?>  style='<?php echo $styles;?>'><?php echo $title;?></<?php echo $dom_element;?>>
				<?php

			break;
			case 'trig_form_submit':
				/* easy copy
					'extra_classes'=>'',
					'styles'=> '',
					'title'=>'',
					'dom_element'=> 'span',
					'uid'=>'',
					'lb_class' =>'',
					'adata'=> array(),
					'lbdata'=> array(),
				*/

				
				$class_attr = empty($class_attr) ? 'evo_btn evolb_trigger_save ': $class_attr;
				?><<?php echo $dom_element;?> class='<?php echo $class_attr . $extra_classes;?>' <?php echo $processed_data[0];?> style='<?php echo $styles;?>'><?php echo $title;?></<?php echo $dom_element;?>>
				<?php
			break;
			case 'trig_ajax':
				/* easy copy
					'extra_classes'=>'',
					'class_attr'=>'',
					'styles'=> '',
					'title'=>'',
					'dom_element'=> 'span',
					'uid'=>'',
					'lb_class' =>'',
					'lb_load_new_content'=> false,			
					'load_new_content_id'=> '',	
					'ajax_data' =>array(),
					'adata'=> array(),
					'lbdata'=> array(),
				*/

				$class_attr = empty($class_attr) ? 'evo_btn evo_trigger_ajax_run ': $class_attr;

				?>
				<<?php echo $dom_element;?> class='<?php echo $class_attr . $extra_classes;?>' <?php echo $processed_data[0];?> style='<?php echo $styles;?>'><?php echo $title;?></<?php echo $dom_element;?>>
				<?php
			break;

			case 'trig_sp':

				$opt = extract( array_merge(array(
					'class_attr'=>'',
					'extra_classes'=>'',
					'styles'=> '',
					'title'=>'',
					'sp_title'=>'',
					'dom_element'=> 'span',
					'uid'=>'',
					'hide_sp'=> false,
					'hide_message'=> false,
					'content_id'=>'',
					'ajax'=>'no',			
					'ajax_data'=>'',			
					'end'=>'admin',// only for admin					
				), $args) );

				$class_attr = empty($class_attr) ? 'evo_admin_btn evosp_trigger ': $class_attr;

				$btn_data = array(
					'd'=> array('uid'=> $uid,
					'hide_sp'=> $hide_sp,
					'hide_message'=> $hide_message,
					'sp_title'=> $sp_title,
					'ajax'=> $ajax,
					'content_id'=>$content_id,
					'ajax_data'=>$ajax_data,
				));

				?>
				<<?php echo $dom_element;?> class='<?php echo $class_attr . $extra_classes;?>' <?php echo $help->array_to_html_data($btn_data);?> style='<?php echo $styles;?>'><?php echo $title;?></<?php echo $dom_element;?>>
				<?php

			break;
		}
	}
}  
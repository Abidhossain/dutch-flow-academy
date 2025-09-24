<?php
/**
 * All appearance additions
 * @version 2.3
 */
class evotx_appearance{
	function __construct(){

		add_filter('evo_elm_def_css', array($this, 'add_to_def_css'),10,1);
		
		add_filter( 'evo_appearance_button_elms', array($this, 'appearance_button' ), 10, 1);
		add_filter( 'evo_appearance_button_elms_hover',array($this, 'appearance_button_hover') , 10, 1);

		add_filter( 'eventon_inline_styles_array',array($this, 'evotx_dynamic_styles') , 10, 1);

		if(is_admin()){
			add_filter( 'eventon_appearance_add', array($this, 'evotx_appearance_settings' ), 10, 1);
		}
	}
	function add_to_def_css( $array){

		$array['evotx_error_color'] = 'f05f50';
		$array['evotx_good_color'] = '58af1f';

		return $array;
	}

	function evotx_appearance_settings($array){	

		extract( EVO()->elements->get_def_css() );

		$new[] = array('id'=>'evotx','type'=>'hiddensection_open',
			'name'=>__('Tickets Styles','evotx') ,'display'=>'none');
		$new[] = array('id'=>'evotx','type'=>'fontation','name'=> __('Notifications','evotx'),
			'variations'=>array(
				array('id'=>'evotx_1', 'name'=>__('Text Color','evotx'),'type'=>'color', 'default'=> $evo_color_1),
				array('id'=>'evotx_2', 'name'=>__('Success Background Color','evotx'),'type'=>'color', 'default'=> $evotx_good_color),
				array('id'=>'evotx_2e', 'name'=>__('Error Background Color','evotx'),'type'=>'color', 'default'=> $evotx_error_color),
				
			)
		);
		$new[] = array('id'=>'evotx','type'=>'hiddensection_close',);
		return array_merge($array, $new);
	}

	function evotx_dynamic_styles($_existen){

		extract( EVO()->elements->get_def_css() );

		$new= array(
			array(
				'item'=>'.evo_metarow_tix .evotx_success_msg.bad:before, .evotx_ticket_purchase_section .evotx_success_msg.bad:before',
				'css'=>'color:#$', 'var'=>'evotx_2',	'default'=> $evotx_error_color
			),
			array(
				'item'=>'.evo_metarow_tix .evotx_success_msg:before, .evotx_ticket_purchase_section .evotx_success_msg:before',
				'css'=>'color:#$', 'var'=>'evotx_2',	'default'=> $evotx_good_color
			),
			array(
				'item'=>'#evcal_list .eventon_list_event .evo_metarow_tix .tx_wc_notic p',
				'css'=>'color:#$', 'var'=>'evotx_1',	'default'=> $evo_color_1
			),	
		);			

		return (is_array($_existen))? array_merge($_existen, $new): $_existen;
	}
	function appearance_button($string){
		$string .= ',.evoTX_wc .variations_button .evcal_btn, .evo_lightbox.eventon_events_list .eventon_list_event .evoTX_wc a.evcal_btn';			
		return $string;
	}
	function appearance_button_hover($string){
		$string .= ',.evoTX_wc .variations_button .evcal_btn:hover, .evo_lightbox.eventon_events_list .eventon_list_event .evoTX_wc a.evcal_btn:hover';			
		return $string;
	}
}
new evotx_appearance();
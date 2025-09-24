<?php
/**
 * Admin settings class
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	eventon-slider/classes
 * @version     2.1.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class evosl_admin{
	
	public $optSL;
	function __construct(){
		add_action('admin_init', array($this,'_admin_init'));
		add_filter( 'evo_sc_keys_assoc',array($this,'block_sc_keys') , 10,1);
	}
	function _admin_init(){
		add_filter( 'eventon_appearance_add', array($this,'_appearance_settings') , 10, 1);
		add_filter( 'eventon_inline_styles_array',array($this,'_dynamic_styles') , 1, 2);
		
	}
	public function block_sc_keys($array)	{
		$array['add_eventon_slider'] = __('Event Slider','evosl');
		return $array;
	}
	function _appearance_settings($array){
	    extract(EVO()->elements->get_def_css());
	    $new = [
	        ['id'=>'evosl','type'=>'hiddensection_open','name'=>__('Slider Styles','evosl'),'display'=>'none'],
	        ['id'=>'evosl','type'=>'fontation','name'=>__('Circle arrow nav button','evosl'),'variations'=>[
	            ['id'=>'evosl1','name'=>__('Background Color','evosl'),'type'=>'color','default'=>'ffffff'],
	            ['id'=>'evosl1h','name'=>__('Background Color (on Hover)','evosl'),'type'=>'color','default'=>'ffffff'],
	            ['id'=>'evosl2','name'=>__('Arrow Color','evosl'),'type'=>'color','default'=>$evo_color_1],
	            ['id'=>'evosl3','name'=>__('Border Color','evosl'),'type'=>'color','default'=>$evo_color_1]
	        ]],
	        ['id'=>'evosl','type'=>'fontation','name'=>__('Arrow nav bar button','evosl'),'variations'=>[
	            ['id'=>'evosl7','name'=>__('Background Color','evosl'),'type'=>'color','default'=>'f1f1f1'],
	            ['id'=>'evosl8','name'=>__('Arrow Color','evosl'),'type'=>'color','default'=>'808080']
	        ]],
	        ['id'=>'evosl','type'=>'fontation','name'=>__('Nav Dots','evosl'),'variations'=>[
	            ['id'=>'evosl4','name'=>__('Outer Ring Color','evosl'),'type'=>'color','default'=>$evo_color_1],
	            ['id'=>'evosl5','name'=>__('Dot Color','evosl'),'type'=>'color','default'=>'a5a5a5'],
	            ['id'=>'evosl5h','name'=>__('Dot Color (on Hover)','evosl'),'type'=>'color','default'=>$evo_color_1]
	        ]],
	        ['id'=>'evoYV','type'=>'hiddensection_close']
	    ];
	    return array_merge($array, $new);
	}
	function _dynamic_styles($_existen, $CSS){
		extract($CSS);
		$new= array(
								
			array(
				'item'=>'.evoslider.cs_tb .evo_slider_outter .evoslider_nav, .evoslider.cs_lr .evo_slider_outter .evoslider_nav',
				'multicss'=>array(
					array('css'=>'background-color:#$', 'var'=>'evosl7','default'=>'f1f1f1'),
					array('css'=>'color:#$', 'var'=>'evosl8','default'=>'808080')
				)						
			),
			array(
				'item'=>'.evoslider .evoslider_dots span',
				'css'=>'background-color:#$', 'var'=>'evosl5','default'=>'a5a5a5'					
			),array(
				'item'=>'.evoslider .evoslider_dots span:hover',
				'css'=>'background-color:#$', 'var'=>'evosl5h','default'=> $evo_color_1					
			),
			array(
				'item'=>'.evoslider .evoslider_dots span.f em',
				'css'=>'border-color:#$', 'var'=>'evosl4','default'=> $evo_color_1					
			),array(
				'item'=>'.evoslider .evosl_footer_outter .nav:hover',
				'css'=>'background-color:#$', 'var'=>'evosl1h','default'=>'ffffff'					
			),
			array(
				'item'=>'.evoslider .evosl_footer_outter .nav',
				'multicss'=>array(
					array('css'=>'background-color:#$', 'var'=>'evosl1','default'=>'ffffff'),
					array('css'=>'border-color:#$', 'var'=>'evosl3','default'=> $evo_color_1),
					array('css'=>'color:#$', 'var'=>'evosl2','default'=> $evo_color_1)
				)						
			)
		);
		

		return (is_array($_existen))? array_merge($_existen, $new): $_existen;
	}
	
}

new evoSL_admin();
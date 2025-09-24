<?php
/**
 * EventON Settings Tab for addons and licensing
 * @version 4.9.8
 */


?>

<style type="text/css">
	.ajde_settings.evcal_4 .evo_settings_header{margin-left: -20px;}
</style>

<div id="evcal_4" class="postbox evcal_admin_meta curve" style='overflow: hidden'>	

	<div class='evodfx evofx_dr_r evofx_jc_sb evofx_ai_c evo_borderb evopad20'>
		<div class="">
			<h3 class=''><?php _e('Your EventON Product Licenses','eventon');?></h3>
		</div>
		<div class=''>
			
			<a href='https://docs.myeventon.com/documentations/can-download-addon-updates/' class='evo_admin_btn btn_triad' target='_blank' title='<?php _e('How to update EventON addons to latest version','eventon');?>'><?php _e('How to update?','eventon');?></a>
			<a style=''href='https://docs.myeventon.com/documentations/update-eventon/' target='_blank' class='evo_admin_btn btn_triad' title='<?php _e('How to update EventON Manually','eventon');?>'><?php _e('Manually Update?','eventon');?></a>
			
		</div>
	</div>

	<?php
		
		// UPDATE eventon addons list
		EVO_Prods()->update_addons();	
		EVO_Prods()->debug_remote_data();

	?>
	

	<div class='evo_addons_page addons'>	
		<?php // ADDONS 			
			global $wp_version; 
		?>				
			<div id='evo_addons_list'>
				<?php 
				EVO()->elements->get_preload_html(array(
					'pclass'=>'evo_addons_load',
					'styles'=>'',
					'animation_style'=>'blink',
					's'=> array(
						'multiply'=>'4',
						array( 'w'=>'25%','h'=>'350px', 'm'=>'4'),
					)
				));
				?>
			</div>
		<div class="clear"></div>
	</div>
	<?php
		// Throw the output popup box html into this page	
		EVO()->lightbox->admin_lightbox_content(array('content'=>"<p class='evo_loader'></p>", 'type'=>'padded'));
	?>
</div>
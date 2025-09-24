<?php
/**
 * HTML Views for eventon
 * @version 4.9.10
 */

class EVO_Views{

	function get_html($type, $args= ''){

		ob_start();
		switch($type){

		// FORMS
			case 'evo_activation_form':
				global $ajde;
				$style_input = "width:100%; margin-top:5px; display:block;border-radius:5px; font-size:20px";
				?>
				<div class='evo_license_section_form pad20'>
					<p style='padding-top:10px;'><?php _e('Enter Your EventON License Key','eventon');?>
						<input class='fields' name='key' type='text' style='<?php echo $style_input;?>'/>
						<input class='eventon_slug fields' name='slug' type='hidden' value='eventon' />
						<input class='eventon_license_div' type='hidden' value='evo_license_main' />
						<i style='opacity:0.6;padding-top:5px; display:block'><?php _e('More information on','eventon');?> <a href='https://docs.myeventon.com/documentations/how-to-find-eventon-license-key/' target='_blank'><?php _e('How to find eventON purchase key','eventon');?></a></i>
					</p>

					<p style='padding-top:10px;'>
						<label class='pea'><?php _e('Email Address used to purchase / Envato Username','eventon'); EVO()->elements->echo_tooltips('If you purchased eventON via myeventon.com please use purchase email address otherwise use envato/codecanyon username.');?></label>
						<input class='fields' name='envato_username' type='text' style='<?php echo $style_input;?>'/>
					</p>					
					<p style='text-align:center'><a class='eventon_submit_license evo_admin_btn btn_prime' data-type='main' data-slug='eventon'><?php _e('Activate Now','eventon');?></a></p>
				</div>
				<?php
			break;

			case 'evo_addon_activation_form':
				global $ajde;

				?>
				<div class='evo_license_section_form pad20'>
					<p>
						<label><?php _e('Addon License Key','eventon');?>*</label>
						<input class='eventon_license_key_val fields' name='key' type='text' style='width:100%' placeholder='Enter the addon license key'/>
						<input class='instance fields' name='instance' type='hidden' value='<?php echo md5(get_site_url());?>' />
						<input class='eventon_slug fields' name='slug' type='hidden' value='<?php echo $args['slug'];?>' />
						<input class='eventon_id fields' name='product_id' type='hidden' value='<?php echo $args['product_id'];?>' />
						<input class='eventon_license_div' type='hidden' value='evoaddon_<?php echo $args['slug'];?>' />
						<i style='opacity:0.6;padding-top:5px; display:block'><?php _e('Find addon license key from','eventon');?> <a href='http://www.myeventon.com/my-account/licenses/' target='_blank'><?php _e('My eventon > My licenses','eventon');?></a></i>
					</p>

					<p>
						<label class='pea'><?php _e('Email Address','eventon');?>* <?php EVO()->elements->echo_tooltips('The email address you have used to purchase eventon addon from myeventon.com.');?></label>
						<input class='eventon_email_val fields' name='email' type='text' style='width:100%' placeholder='Email address used for purchasing addon'/>
					</p>
							
					<p><a class='eventonADD_submit_license evo_admin_btn btn_prime' data-type='addon' data-slug='<?php echo $args['slug'];?>'>Activate Now</a></p>
				</div>
				<?php
			break;

		// EVO
			case 'evo_activated_box':				
				global $ajde;
				$EVO_prod = new EVO_Product_Lic('eventon');
				$has_update = $EVO_prod->has_update();
				//$has_update = true;
					
				$data = [
					'key'=> $EVO_prod->get_prop('key'),
					'email'=> $EVO_prod->get_prop('envato_username'),
					'slug'=> $EVO_prod->slug,//eventon
					'product_id'=> 'EVO',
				];
				?>
					<div id='evoaddon_eventon' class="addon main activated <?php echo ($has_update)? 'hasupdate':null;?>"  <?php echo EVO()->helper->array_to_html_data( $data) ;?>>
						<h2 class='evodfx evofx_ai_c evoff_1' style="font-size: 36px;">EventON <?php 
							if($has_update){
								echo "<b class='evo_admin_addons_newup_bar'>".__('New Update availale','eventon')."</b>";
							}
						?></h2>
						<p class='version'>v<?php echo EVO()->version;?><?php if($has_update):?><span>/<?php echo $EVO_prod->get_remote_version();?></span><?php endif;?></p>
						<p><?php _e('License Status','eventon');?> 
							<strong class='evomarl5 evottu'><?php _e('Activated','eventon');?></strong> 
							<a id='evoDeactLic' class='evomarl10 evocurp evotdu evocl1'><?php _e('Deactivate','eventon');?></a>
						</p>
						
						<?php if( !$EVO_prod->remotely_validated()): ?>
							<p><?php _e('Validatation Status','eventon');?>: <strong><?php _e('Locally','eventon');?></strong></p>
						<?php endif;?>

						<div class='evomart10'>
							<p class='links'>
							<?php 
							if( $has_update){
								echo "<a class='btn_primary' href='".admin_url()."update-core.php'>". __('Update Now','eventon') ."</a> | ";
								echo "<a class='thickbox btn_primary' href='".BACKEND_URL."plugin-install.php?tab=plugin-information&plugin=eventon&section=changelog&TB_iframe=true&width=600&height=400'>". __('Version Details','eventon')."</a> | ";
							}

							
							// License infor button
							EVO()->elements->print_trigger_element(array(
								'title'=>__('License Info','eventon'),
								'id'=>'evoaddons_lic_info',
								'dom_element'=> 'span',
								'styles'=>'color:#fff',
								'uid'=>'evoaddons_lic_info',
								'lb_class' =>'evoaddons_lic_info',
								'class_attr'=>'evolb_trigger evocurp',
								'lb_title'=> sprintf(__('License Info for %s','eventon'), 'Eventon' ),	
								'ajax_data'=>array(
									'a'=>'eventon_admin_lic_info',
									'slug'=> 'eventon',
								),
							),'trig_lb');
							

							echo " | <a class='evocurp evotdu' href='http://docs.myeventon.com' target='_blank'>". __('Docs','eventon')."</a> | ";
							echo "<a class='evocurp evotdu' href='http://www.myeventon.com/news/' target='_blank'>News</a>";
							?>								
							</p>
						</div>
					</div>
				<?php 
	
			break;

			case 'evo_not_activated_box':
				?>
				<div id='evoaddon_eventon' class="addon main">
					<h2 class='evoff_1'  style="font-size: 36px;">EventON</h2>
					<p class='version'>v<?php echo EVO()->version;?><span></span></p>
					<p class='status'><?php _e('License Status','eventon');?> <strong style='text-transform:uppercase'><?php _e('Not Activated','eventon');?></strong>
					</p>
					<p class='action'>
						<?php 
						EVO()->elements->print_trigger_element(array(
							'styles'=> '',
							'title'=>__('Activate','eventon'),
							'id'=>'evoaddons_active_license',
							'dom_element'=> 'span',
							'uid'=>'evoaddons_active_license',
							'lb_class' =>'evoaddons_active_license',
							'lb_title'=>__('Activate EventON License','eventon'),	
							'lb_hide'=> 4000,
							'ajax_data'=>array(
								'a'=>'eventon_admin_get_views',
								'type'=> 'evo_activation_form'
							),
						),'trig_lb');

						?>						
					</p>
					<p class='activation_text'><i><a href='https://docs.myeventon.com/documentations/how-to-find-eventon-license-key/' target='_blank'><?php _e('How to find activation key','eventon');?></a><?php EVO()->elements->echo_tooltips('EventON license you have purchased from Codecanyon, either regular or extended will allow you to install eventON in ONE site only. In order to install eventON in another site you will need a seperate license.','L');?></i>
					</p>
				</div>
				<?php
			break;

			case 'evo_box':
				$EVO_prod = new EVO_Product_Lic('eventon');
				if($EVO_prod->kriyathmakada()){
					echo $this->get_html('evo_activated_box');
				}else{
					echo $this->get_html('evo_not_activated_box');
				}

			break;

		// ADDONS
			case 'evo_addon_activated_box':
				extract($args);

				$ADDON = new EVO_Product($slug);
				$has_update = $ADDON->has_update();
				//$has_update = true;
			
				?>
				<div id='evoaddon_<?php echo $slug;?>' class="addon activated <?php echo ($has_update)? 'hasupdate':null;?>" data-slug='<?php echo $slug;?>' data-key='<?php echo $ADDON->get_prop('key');?>' data-email='<?php echo $ADDON->get_prop('email');?>' data-product_id='<?php echo $product['id'];?>'>
							

					<h2 class='evoff_1'><?php echo $product['name']?>
						<?php 
						if( $has_update ):
							echo "<b class='evo_admin_addons_newup_bar'>".__('New Update','eventon')."</b>";
						endif;
						?>
					</h2>
					<?php if(!empty($version)):?>
						<p class='version'><span><?php echo $version?></span> <?php echo $remote_version;?></p>
					<?php endif;?>

					<div class='evomart10 evomarb10'>
						<p class='status'><?php _e('License Status','eventon');?> <strong class='evodb evottu'><?php $ADDON->kriyathmaka_localda()? _e('Activated Locally','eventon'): _e('Activated','eventon');?> </strong></p>
						<?php if($ADDON->kriyathmaka_localda()):?>
							<p><a class='evo_admin_btn btn_triad evo_retry_remote_activation' >Try Remote Activate</a></p>
						<?php endif;?>
						<p><a class='evo_deact_adodn evocurp evotdu evocl1'>Deactivate</a></p>
					</div>
					
					<p class="links">
						<?php 
						if( $has_update ):
							echo "<a class='btn_primary' href='".admin_url()."update-core.php'>". __('Update Now','eventon') ."</a> | ";
						endif;
						if(!empty($guide_link)):
							echo "<span class='eventon_guide_btn ajde_popup_trig' ajax_url='{$guide_link}' data-t='How to use {$addon_name}'>Guide</span> | ";
						endif;
						?>
						<a href='<?php echo $addon_link;?>' target='_blank'><?php _e('Addon Page','eventon');?></a> | 
						<?php 
						// License infor button
						EVO()->elements->print_trigger_element(array(
							'title'=>__('License Info','eventon'),
							'id'=>'evoaddons_lic_info',
							'dom_element'=> 'span',
							'styles'=>'color:#fff',
							'uid'=>'evoaddons_lic_info',
							'lb_class' =>'evoaddons_lic_info',
							'class_attr'=>'evolb_trigger evocurp',
							'lb_title'=> sprintf(__('License Info for %s','eventon'), $addon_name ),	
							'ajax_data'=>array(
								'a'=>'eventon_admin_lic_info',
								'slug'=> $slug,
								'product_id'=> $addon_id
							),
						),'trig_lb');
						?>
					</p>
				</div>
				<?php
			break;

			// this is either has addon or dont have addon
			case 'evo_addon_not_activated_box':

				extract($args);
				?>
				<div id='evoaddon_<?php echo $slug;?>' class="addon <?php echo (!$has_addon)?'donthaveit':null;?>" data-slug='<?php echo $slug;?>' data-key='<?php echo !empty($this_addon['key'])? $this_addon['key']:'';?>' data-email='<?php echo !empty($this_addon['email'])?$this_addon['email']:'';?>' data-product_id='<?php echo !empty($product['id'])? $product['id']:'';?>'>

					<h2 class='evoff_1'><?php echo esc_html( $addon_name );?></h2>

					<?php 
					// has addon installed
					if( $has_addon):

						if(!empty($version)):
							echo "<p class='version'><span>{$version}</span> {$remote_version}</p>";
						endif;

						echo "<div class='evomart20 evomarb20'>";

						echo "<p class='status'>".__('License Status','eventon')." <strong class='evodb evottu'>".__('Not Activated','eventon')."</strong></p>";

						// activate button
						EVO()->elements->print_trigger_element(array(
							'title'=>__('Activate','eventon'),
							'id'=>'evoaddons_active_license',
							'dom_element'=> 'span',
							'styles'=>'color:#fff',
							'uid'=>'evoaddons_active_license',
							'lb_class' =>'evoaddons_active_license',
							'lb_title'=> sprintf(__('Activate %s License','eventon'), $addon_name ),	
							'ajax_data'=>array(
								'a'=>'eventon_admin_get_views',
								'type'=> 'evo_addon_activation_form',
								'data'=> array(
									'slug'=> $slug,
									'product_id'=> $addon_id
								)
							),
						),'trig_lb');

						echo "</div>";

						echo "<p class='links'>";

						// guide link
						if(!empty($guide_link)){
							echo "<span class='eventon_guide_btn ajde_popup_trig' ajax_url='{$guide_link}' data-t='How to use {$addon_name}'>Guide</span> | ";
						}

						// learn more link
						echo "<a href='{$addon_link}' target='_blank'>".__('Learn More','eventon')."</a>";
						echo "</p>";

					// dont have addon
					else:		
						echo "<p>". $addon_desc ."</p>";				
						echo "<p class=''><a class='evo_admin_btn btn_secondary evomart10' target='_blank' href='". $addon_link."'>". __('Get it now','eventon')."</a></p>";
					endif;

					?>					
				</div>
				<?php
			break;

			// input slug, activeplugins array
			case 'evo_addon_box':
				extract($args);
				$ADDON = new EVO_Product($slug);

				$_this_addon = $ADDON->get_product_array();

				$addons_list = new EVO_Addons_List();
				$product = $addons_list->addon($slug);
								
				$_has_addon = false;

				// check if the product is activated within wordpress
					$active_plugins = !empty($active_plugins)? $active_plugins : get_option( 'active_plugins' );
					if(!empty($active_plugins)){
						foreach($active_plugins as $plugin){
							// check if foodpress is in activated plugins list
							if(strpos( $plugin, $slug.'.php') !== false){
								$_has_addon = true;
							}
						}
					}
						
				$version = $ADDON->get_prop('version');
				$remote_version = $ADDON->get_remote_version();

				// initial variables
					$__remote_version = '<span title="Remote server version" style="opacity:0.2"> /'.$remote_version.'</span>';

					// HTML message when there are updates 
					$__has_updates = ($ADDON->has_update() && $_has_addon) ? "<span class='has_update'>".__('New version available','eventon') . "</span>":'';
					
				// get eventon addons views
				if( $ADDON->is_active() && $_has_addon):
										
					echo $this->get_html(
						'evo_addon_activated_box',
						array(
							'slug'				=>$slug,
							'version'			=>$version,
							'remote_version'	=>$__remote_version,
							'has_addon'			=>$_has_addon,
							'has_updates'		=>$__has_updates,
							'addon_link'		=> $product['download'],
							'addon_name'		=> $product['name'],
							'addon_id'			=> $product['id'],
							'guide_link'		=> ($_has_addon && $ADDON->get_prop('guide_file')) ? $ADDON->get_prop('guide_file') : null,
							'product'			=>$product,
							'this_addon'		=>$_this_addon,
						)
					);
				else:

					echo $this->get_html(
						'evo_addon_not_activated_box',
						array(
							'slug'				=>$slug,
							'version'			=>$version,
							'remote_version'	=>$__remote_version,
							'addon_link'		=> $product['download'],
							'addon_name'		=> $product['name'],
							'addon_id'			=> $product['id'],
							'has_addon'			=>$_has_addon,
							'has_updates'		=>$__has_updates,
							'product'			=>$product,
							'this_addon'		=>$_this_addon,
							'guide_link'		=> ($_has_addon && $ADDON->get_prop('guide_file')) ? $ADDON->get_prop('guide_file') : null,
							'addon_desc'	=> !empty($product['desc'])? $product['desc']: null,
						)
					);
					
				endif;
			break;
		
		}
		return ob_get_clean();
	}
}
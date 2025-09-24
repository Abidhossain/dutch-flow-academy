<?php
/**
 * EventON Various admin settings view designer
 * @version 4.9.10
 */

class EVO_Settings_Designer{

	// Print Main Settings Form Content
	public function print_ajde_customization_form($cutomization_pg_array, $data, $extra_tabs=''){
		$this->print_main_settings($cutomization_pg_array, $data, $extra_tabs );
	}
	function print_main_settings($cutomization_pg_array, $data, $extra_tabs=''){
		
		$textdomain = 'eventon';
		
		// initial variables
			$font_sizes = array('10px','11px','12px','13px','14px','16px','18px','20px', '22px', '24px','28px','30px','36px','42px','48px','54px','60px');
			$opacity_values = array('0.0','0.1','0.2','0.3','0.4','0.5','0.6','0.7','0.8','0.9','1',);
			$font_styles = array('normal','bold','italic','bold-italic');
			
			$__no_hr_types = array('begin_afterstatement','end_afterstatement','hiddensection_open','hiddensection_close','sub_section_open','sub_section_close');
		
			//define variables
			$leftside=$rightside='';
			$count=1;

		// icon selection
			$rightside.= EVO()->elements->get_icon_html();
		
		// different types of content
			/*
				notice, image, icon, subheader, note, checkbox, text. textarea, font_size, font_style, border_radius, color, fontation, multicolor, radio, dropdown, checkboxes, yesno, begin_afterstatement, end_afterstatement, hiddensection_open, hiddensection_close, customcode
			*/

		foreach($cutomization_pg_array as $cpa=>$cpav){								
			// left side tabs with different level colors
			$ls_level_code = (isset($cpav['level']))? 'class="'.$cpav['level'].'"': null;
			
			$leftside .= "<li ".$ls_level_code."><a class='".( ($count==1)?'focused':null)."' data-c_id='".$cpav['id']."' title='".$cpav['tab_name']."'><i class='fa fa-".( !empty($cpav['icon'])? $cpav['icon']:'edit')."'></i>".__($cpav['tab_name'],$textdomain)."</a></li>";								
			$tab_type = (isset($cpav['tab_type'] ) )? $cpav['tab_type']:'';
			if( $tab_type !='empty'){ // to not show the right side

				
				// RIGHT SIDE
				$display_default = (!empty($cpav['display']) && $cpav['display']=='show')?'':'display:none';
				//$display_default = 'display:none';

				$tab_icon = isset( $cpav['icon'] ) ? "<i class='fa fa-{$cpav['icon']} evofz24 evomarr10'></i>" :null;

				$rightside.= "<div id='setting_".$cpav['id']."' data-setting='".$cpav['id']."' style='".$display_default."' class='nfer'>
					<h3>". $tab_icon .__($cpav['name'],$textdomain)."</h3>";

					if(!empty($cpav['description']))
						$rightside.= "<p class='tab_description'>".$cpav['description']."</p>";
				
				$rightside.="<em class='hr_line'></em>";					
					

				// EACH field
				foreach($cpav['fields'] as $field){


					if( !isset($field['type'])) continue;

					// field value
					$_value = ( !empty( $field['id']) && !empty($data[$field['id']]) && !is_array($data[$field['id']])) ? 
							stripslashes( $data[$field['id']] ):null;

					if($field['type']=='text' || $field['type']=='textarea'){
						$FIELDVALUE = (!empty($data[ $field['id']]))? 
							htmlspecialchars( stripslashes($data[ $field['id']]) ): 
								null;

						// time format with slashes
						if( $field['id'] == 'evo_timeF_tf') $FIELDVALUE = empty($data[ $field['id']])? null: 
							htmlspecialchars( $data[ $field['id']] );
					}
					
					// LEGEND or tooltip
						$tooltip_content = '';
						if( !empty( $field['legend'] )) $tooltip_content = $field['legend'];
						if( !empty( $field['tooltip'] )) $tooltip_content = $field['tooltip'];
						
						$legend_code = !empty( $tooltip_content ) ? EVO()->elements->tooltips( $tooltip_content , 'L', false ) : null;

					// beta feature tag
						if( !empty($field['beta'])) $legend_code .= "<span class='evonewtag beta evotooltipfree L' data-d='".__('This feature is still in beta stage','eventon') ."'>".__('Beta','eventon')."</span>";
					
					// new label
						if (isset($field['ver'])) {
						    $version = (isset($field['compare_ver']) && $field['ver'] === $field['compare_ver']) 
						        ? $field['compare_ver'] : EVO()->version;
						    if ($field['ver'] === $version) {
						        $legend_code .= "<span class='new evonewtag evotooltipfree L' data-d='" . __('New in version', 'eventon') . " $version'>new</span>";
						    }
						}
					
					// switch statements	
					switch ($field['type']){

						// default field to use EVO Elements
						default:
							$field['value'] = $_value;
							$rightside .= EVO()->elements->get_element( $field );
						break;

						// notices
						case 'notice':
							$rightside .= EVO()->elements->get_element(array(
								'type'=>'notice','name'=> $field['name'] . $legend_code,
								'row_class'=>'ajdes_notice',
							));
						break;
						//IMAGE
						case 'image':
							$image = ''; 
							$meta = isset($data[$field['id']])? $data[$field['id']]:false;
							
							$preview_img_size = (empty($field['preview_img_size']))?'medium'
								: $field['preview_img_size'];
							
							$rightside.= "<div id='pa_".$field['id']."'><p>".$field['name'].$legend_code."</p>";
							
							if ($meta) { $image = wp_get_attachment_image_src($meta, $preview_img_size); $image = $image[0]; } 
							
							$display_saved_image = (!empty($image))?'block':'none';
							$opp = ($display_saved_image=='block')? 'none':'block';

							$rightside.= "<p class='ajde_image_selector'>";
							$rightside.= "<span class='ajt_image_holder' style='display:{$display_saved_image}'><b class='ajde_remove_image'>X</b><img src='{$image}'/></span>";
							$rightside.= "<input type='hidden' class='ajt_image_id' name='{$field['id']}' value='{$meta}'/>";
							$rightside.= "<input type='button' class='ajt_choose_image button' style='display:{$opp}' value='".__('Choose an Image','ajde')."'/>";
							$rightside.= "</p></div>";
							
						break;

						case 'icon_selection':

							$html = '';
							$html.= "<div class='row_faicons_collection evodfx evogap15 evofx_ww'>";

							
							// each icon
							foreach( $field['icons'] as $icon_ar ){
								$html.= "<div class='evo_settings_icon_box evodfx evobr15 evofx_dr_c evopad20 evocurp evo_transit_all'>";

								$field_value = (!empty($data[ $icon_ar['id']]) )? 
								$data[ $icon_ar['id']]:$icon_ar['default'];

							
								// code
								$html .= EVO()->elements->get_element(array(
									'type'=>'icon_select',
									'id'=> $icon_ar['id'],
									'value'=> $field_value,
									'close'=>false,
								));
								$html .= "<p class='fieldname'>".__($icon_ar['name'],$textdomain)."</p>";
								$html.= "</div>";
							}

							$html.= "</div>";

							$rightside.= $html;

						break;
						
						case 'icon':
							$field_value = (!empty($data[ $field['id']]) )? 
								$data[ $field['id']]:$field['default'];

							$rightside.= "<div class='row_faicons'><p class='fieldname'>".__($field['name'],$textdomain)."</p>";
							
							// code
							$rightside .= EVO()->elements->get_element(array(
								'type'=>'icon_select',
								'id'=> $field['id'],
								'value'=> $field_value,
								'close'=>false,
							));
							
							$rightside.= "<div class='clear'></div></div>";
						break;

						case 'subheader':
							$rightside.= "<h4 class='acus_subheader'>".__($field['name'],$textdomain)."</h4>";
						break;
						case 'note':
							$rightside.= "<p class='ajde_note'><span class='evodb evoop7'>".__($field['name'],$textdomain)."</span></p>";
						break;
						case 'hr': $rightside.= "<em class='hr_line'></em>"; break;
						case 'checkbox':
							$this_value= (!empty($data[ $field['id']]))? $data[ $field['id']]: null;						
							$rightside.= "<p><input type='checkbox' name='".$field['id']."' value='yes' ".(($this_value=='yes')?'checked="/checked"/':'')."/> ".$field['name']."</p>";
						break;
						case 'text':
							$placeholder = (!empty($field['default']) )? 'placeholder="'.$field['default'].'"':null;

							$show_val = false; $hideable_text = '';
							if(isset($field['hideable']) && $field['hideable'] && !empty($FIELDVALUE)){
								$show_val = true;
								$hideable_text = "<span class='evo_hideable_show' data-t='". __('Hide', $textdomain) ."'>". __('Show',$textdomain). "</span>";
							}
							
							$rightside.= "<p>".__($field['name'],$textdomain).$legend_code. $hideable_text. "</p><p class='field_container'><span class='nfe_f_width'>";

							if($show_val ){
								$rightside.= "<input type='password' style='' name='".$field['id']."'";
								$rightside.= 'value="'. $FIELDVALUE .'"';
							}else{
								$rightside.= "<input type='text' name='".$field['id']."'";
								$rightside.= 'value="'. $FIELDVALUE .'"';
							}
							
							$rightside.= $placeholder."/></span></p>";
						break;
						case 'password':
							$default_value = (!empty($field['default']) )? 'placeholder="'.$field['default'].'"':null;
							
							$rightside.= "<p>".__($field['name'],$textdomain).$legend_code."</p><p><span class='nfe_f_width'><input type='password' name='".$field['id']."'";
							$rightside.= 'value="'.$FIELDVALUE.'"';
							$rightside.= $default_value."/></span></p>";
						break;
						case 'textarea':
							
							$_value = isset($data[$field['id']])? stripslashes( $data[$field['id']] ):null;
							
							$rightside .= EVO()->elements->get_element(array(
								'type'		=>'textarea',
								'id'		=>$field['id'],
								'name'		=> __($field['name'],$textdomain),
								'tooltip'	=> $tooltip_content,
								'tooltip_position'=> 'L',
								'value'		=> $_value,
								'default'	=> ( (!empty($field['default']) )? $field['default'] :null ),
							));

						break;
						case 'wysiwyg':
							
							$_value = isset($data[$field['id']])? stripslashes( $data[$field['id']] ):null;

							$rightside .= EVO()->elements->get_element(array(
								'type'		=>'wysiwyg',
								'id'		=>$field['id'],
								'name'		=> __($field['name'],$textdomain),
								'tooltip'	=> $tooltip_content,
								'tooltip_position'=> 'L',
								'value'		=> $_value,
								'default'	=> ( (!empty($field['default']) )? $field['default'] :null ),
							));

						break;
						case 'font_size':
							$rightside.= "<p>".__($field['name'],$textdomain)." <select name='".$field['id']."'>";
								$ajde_fval = $data[ $field['id'] ];
								
								foreach($font_sizes as $fs){
									$selected = ($ajde_fval == $fs)?"selected='selected'":null;	
									$rightside.= "<option value='$fs' ".$selected.">$fs</option>";
								}
							$rightside.= "</select></p>";
						break;
						case 'opacity_value':
							$rightside.= "<p>".__($field['name'],$textdomain)." <select name='".$field['id']."'>";
								$ajde_fval = $data[ $field['id'] ];
								
								foreach($opacity_values as $fs){
									$selected = ($ajde_fval == $fs)?"selected='selected'":null;	
									$rightside.= "<option value='$fs' ".$selected.">$fs</option>";
								}
							$rightside.= "</select></p>";
						break;
						case 'font_style':
							$rightside.= "<p>".__($field['name'],$textdomain)." <select name='".$field['id']."'>";
								$ajde_fval = $data[ $field['id'] ];
								foreach($font_styles as $fs){
									$selected = ($ajde_fval == $fs)?"selected='selected'":null;	
									$rightside.= "<option value='$fs' ".$selected.">$fs</option>";
								}
							$rightside.= "</select></p>";
						break;
						case 'border_radius':
							$rightside.= "<p>".__($field['name'],$textdomain)." <select name='".$field['id']."'>";
									$ajde_fval = $data[ $field['id'] ];
									$border_radius = array('0px','2px','3px','4px','5px','6px','8px','10px');
									foreach($border_radius as $br){
										$selected = ($ajde_fval == $br)?"selected='selected'":null;	
										$rightside.=  "<option value='$br' ".$selected.">$br</option>";
									}
							$rightside.= "</select></p>";
						break;
						case 'color':

							// default hex color
							$hex_color = (!empty($data[ $field['id']]) )? 
								$data[ $field['id']]:$field['default'];
							$hex_color_val = (!empty($data[ $field['id'] ]))? $data[ $field['id'] ]: null;

							// RGB Color for the color box
							$rgb_color_val = (!empty($field['rgbid']) && !empty($data[ $field['rgbid'] ]))? $data[ $field['rgbid'] ]: null;
							$__em_class = (!empty($field['rgbid']))? ' rgb': null;

							$rightside.= "<p class='acus_line color'>
								<em><span class='colorselector{$__em_class} evotooltipfree' style='background-color:#".$hex_color."' hex='".$hex_color."' title='".$hex_color."'></span>
								<input name='".$field['id']."' class='backender_colorpicker evocolorp_val' type='hidden' value='".$hex_color_val."' default='".$field['default']."'/>";
							if(!empty($field['rgbid'])){
								$rightside .= "<input name='".$field['rgbid']."' class='rgb' type='hidden' value='".$rgb_color_val."' />";
							}
							$rightside .= "</em>".__($field['name'],$textdomain)." </p>";					
						break;					

						case 'fontation':

							$variations = $field['variations'];
							$rightside.= "<div class='row_fontation'><p class='fieldname'>".__($field['name'],$textdomain)."</p>";

							foreach($variations as $variation){
								switch($variation['type']){
									case 'color':
										// default hex color
										$hex_color = (!empty($data[ $variation['id']]) )? 
											$data[ $variation['id']]:$variation['default'];
										$hex_color_val = (!empty($data[ $variation['id'] ]))? $data[ $variation['id'] ]: null;
										
										$title = (!empty($variation['name']))? $variation['name']:$hex_color;
										$_has_title = (!empty($variation['name']))? true:false;

										// code
										$rightside.= "<p class='acus_line color'>
											<em><span id='{$variation['id']}' class='colorselector evotooltipfree ".( ($_has_title)? 'hastitle': '')."' style='background-color:#".$hex_color."' hex='".$hex_color."' title='".$title."' alt='".$title."'></span>
											<input name='".$variation['id']."' class='backender_colorpicker evocolorp_val' type='hidden' value='".$hex_color_val."' default='".$variation['default']."'/></em></p>";

									break;

									case 'font_style':
										$rightside.= "<p style='margin:0'><select title='".__('Font Style',$textdomain)."' name='".$variation['id']."'>";
												$f1_fs = (!empty($data[ $variation['id'] ]))?
													$data[ $variation['id'] ]:$variation['default'] ;
												foreach($font_styles as $fs){
													$selected = ($f1_fs == $fs)?"selected='selected'":null;	
													$rightside.= "<option value='$fs' ".$selected.">$fs</option>";
												}
										$rightside.= "</select></p>";
									break;

									case 'font_size':
										$rightside.= "<p style='margin:0'><select title='".__('Font Size',$textdomain)."' name='".$variation['id']."'>";
												
												$f1_fs = (!empty($data[ $variation['id'] ]))?
													$data[ $variation['id'] ]:$variation['default'] ;
												
												foreach($font_sizes as $fs){
													$selected = ($f1_fs == $fs)?"selected='selected'":null;	
													$rightside.= "<option value='$fs' ".$selected.">$fs</option>";
												}
										$rightside.= "</select></p>";
									break;
									
									case 'opacity_value':
										$rightside.= "<p style='margin:0'><select title='".__('Opacity Value',$textdomain)."' name='".$variation['id']."'>";
												
												$f1_fs = (!empty($data[ $variation['id'] ]))?
													$data[ $variation['id'] ]:$variation['default'] ;
												
												foreach($opacity_values as $fs){
													$selected = ($f1_fs == $fs)?"selected='selected'":null;	
													$rightside.= "<option value='$fs' ".$selected.">$fs</option>";
												}
										$rightside.= "</select></p>";
									break;
								}

								
							}
							$rightside.= "<div class='clear'></div></div>";
						break;

						case 'multicolor':

							$variations = $field['variations'];

							$rightside.= "<div class='row_multicolor' style='padding-top:10px'>";

							foreach($variations as $variation){
								// default hex color
								$hex_color = (!empty($data[ $variation['id']]) )? 
									$data[ $variation['id']]:$variation['default'];
								$hex_color_val = (!empty($data[ $variation['id'] ]))? $data[ $variation['id'] ]: null;

								$rightside.= "<p class='acus_line color'>
								<em data-name='".__($variation['name'],$textdomain)."'><span id='{$variation['id']}' class='colorselector evotooltipfree' style='background-color:#".$hex_color."' hex='".$hex_color."' title='".$hex_color."'></span>
								<input name='".$variation['id']."' class='backender_colorpicker evocolorp_val' type='hidden' value='".$hex_color_val."' default='".$variation['default']."'/></em></p>";
							}

							$rightside.= "<p class='multicolor_alt'></p></div>";

						break;

						case 'radio':
							$rightside.= "<p class='acus_line acus_radio'>".__($field['name'],$textdomain)."</br>";
							$cnt =0;
							foreach($field['options'] as $option=>$option_val){
								$this_value = (!empty($data[ $field['id'] ]))? $data[ $field['id'] ]:null;
								
								$checked_or_not = ((!empty($this_value) && ($option == $this_value) ) || (empty($this_value) && $cnt==0) )?
									'checked=\"checked\"':null;

								$option_id = $field['id'].'_'. (str_replace(' ', '_', $option_val));
								
								$rightside.="<em><input id='".$option_id."' type='radio' name='".$field['id']."' value='".$option."' "
								.  $checked_or_not  ."/><label class='ajdebe_radio_btn' for='".$option_id."'><span class='fa'></span>".__($option_val,$textdomain)."</label></em>";
								
								$cnt++;
							}						
							$rightside.= $legend_code."</p>";
							
						break;
						case 'dropdown':
							
							$dropdown_opt = (!empty($data[ $field['id'] ]))? $data[ $field['id'] ]
								:( !empty($field['default'])? $field['default']:null);
							
							$rightside.= "<p class='acus_line {$field['id']}'>".__($field['name'],$textdomain)." <select class='ajdebe_dropdown' name='".$field['id']."'>";
							
							if(is_array($field['options'])){
								foreach($field['options'] as $option=>$option_val){
									$rightside.="<option name='".$field['id']."' value='".$option."' "
									.  ( ($option == $dropdown_opt)? 'selected=\"selected\"':null)  ."> ".$option_val."</option>";
								}	
							}					
							$rightside.= "</select>";

								// description text for this field
								if(!empty( $field['desc'] )){
									$rightside.= "<br/><i style='opacity:0.6'>".$field['desc']."</i>";
								}
							$rightside.= $legend_code."</p>";						
						break;
						case 'checkboxes':
							
							$meta_arr= (!empty($data[ $field['id'] ]) )? $data[ $field['id'] ]: [];
							$default_arr= (!empty($field['default'] ) )? $field['default']: null;

							ob_start();

							echo EVO()->elements->get_element(array(
								'type'=> 'checkbox',
								'id'=> $field['id'],
								'name'=> __($field['name'],$textdomain),
								'options'=> $field['options'],
								'value'=> $meta_arr,
								'tooltip'=> ( !isset($field['tooltip'])?: $field['tooltip'] )
							));		

							$rightside.= ob_get_clean();
							
						break;

						/**
						 * Form fields builder
						 * @version 4.9
						 */
						case 'form_fields':

						break; 

						// rearrange field
							// fields_array - array(key=>var)
							// order_var
							// selected_var
							// title
							// (o)notes
						case 'rearrange':

							ob_start();
								$_ORDERVAR = $field['order_var'];
								$_SELECTEDVAR = $field['selected_var'];
								$_FIELDSar = $field['fields_array']; // key(var) => value(name)
								$_fields_order_string = '';
								$SAVED_ORDER = false;			

								// saved order
									if(!empty($data[$_ORDERVAR])){								
										
										$allfields_ = explode(',',$data[$_ORDERVAR]);
										$fieldsx = array();
										foreach($allfields_ as $fielders){									
											if(!in_array($fielders, $fieldsx)){
												$fieldsx[]= $fielders;
											}
										} 
										$_fields_order_string = implode(',', $fieldsx);
										$SAVED_ORDER = array_filter(explode(',', $_fields_order_string));	


									}	
								
								// already selected values from prev saved data
									$SELECTED = (!empty($data[$_SELECTEDVAR]))?
										( (is_array( $data[$_SELECTEDVAR] ))?
											$data[$_SELECTEDVAR]:
											array_filter( explode(',', $data[$_SELECTEDVAR]))):
										array();

								// unselectable fields
									$_unselectable_fields = !empty($field['unselectable_fields']) && is_array( $field['unselectable_fields'] ) ? $field['unselectable_fields']: false;
									if( $_unselectable_fields ){
										if( is_array($SELECTED) ){
											$SELECTED = array_merge( $SELECTED, $_unselectable_fields );
										}else{
											$SELECTED = $_unselectable_fields;
										}
									}


								// get a string value of selected field names
								$SELECTED_VALS = (is_array($SELECTED))? implode(',', $SELECTED): $SELECTED;
	

								echo "<div class='evosetting_rearrange_box'>";
								echo '<h4 class="acus_subheader">'.$field['title'].'</h4>';
								echo !empty($field['notes'])? '<p><i>'.$field['notes'].'</i></p>':'';
								echo '<input class="ajderearrange_order" name="'.$_ORDERVAR.'" value="'.$_fields_order_string.'" type="hidden"/>';
								echo '<input class="ajderearrange_selected" type="hidden" name="'.$_SELECTEDVAR.'" value="'.( (!empty($SELECTED_VALS))? $SELECTED_VALS:null).'"/>';


								// add secondary toggle data values
								$secondary_data_array = array();
								if( !empty($field['seondary_toggle_data']) && isset( $field['seondary_toggle_data']['key'])){

									$_secondary_toggle_data_key = esc_attr( $field['seondary_toggle_data']['key'] );
									$secondary_data_array = !empty( $data[ $_secondary_toggle_data_key ] ) && is_array( $data[ $_secondary_toggle_data_key ] ) ? 
										$data[ $_secondary_toggle_data_key ] : array();

									// create string of values if not empty
									$_secondary_data_saved_vals_string = '';
									if( count( $secondary_data_array ) > 0 )
										$_secondary_data_saved_vals_string = array_filter( explode(',', $secondary_data_array ) );

									echo '<input class="ajderearrange_secondary" type="hidden" name="'.$_secondary_toggle_data_key.'" value="'. $_secondary_data_saved_vals_string .'"/>';
								}
								
								echo '<div id="ajdeEVC_arrange_box" class="evosetting_arrange_box ajderearrange_box '.$field['id'].'">';


								// build the fields for the form
								$__new_form_data = array();

									// if an order array exists --> rearrange the order of the fields
										if($SAVED_ORDER){

											// Reorder $_FIELDSar to match $SAVED_ORDER
		    								$_FIELDSar = array_replace(array_flip($SAVED_ORDER), $_FIELDSar);

		    								/*
		    								 // Extract fields that are in $SAVED_ORDER (in the order of $SAVED_ORDER)
										    $orderedFields = array_intersect_key($_FIELDSar, array_flip($SAVED_ORDER));

										    // Extract fields that are NOT in $SAVED_ORDER (remaining fields)
										    $remainingFields = array_diff_key($_FIELDSar, array_flip($SAVED_ORDER));

										    // Merge the two arrays: ordered fields first, then remaining fields
										    $_FIELDSar = array_merge($orderedFields, $remainingFields);
										    */
										}

									foreach($_FIELDSar as $_slug => $field_val){

										$_field_name = (is_array($field_val))? $field_val[1] : $field_val;

										echo $this->_part_rearrange_field_html(array(
											'unselectable_fields'=> $_unselectable_fields,
											'selected_data'=> $SELECTED, 
											'slug'=> $_slug, 
											'name'=> $_field_name,
											'_field_data'=> $field,	
											'_saved_data'=> $data,	
											'_secondary_data_array'=> $secondary_data_array									
										));
									}

								
								echo "</div>";
								echo "</div>";

							$rightside .= ob_get_clean();

						break;
					
					// other
						case 'yesno':						
							$yesno_value = (!empty( $data[$field['id'] ]) )? 
								$data[$field['id']]:'no';
							
							$after_statement = (isset($field['afterstatement']) )?$field['afterstatement']:'';

							$__default = (!empty( $field['default'] ) && isset( $data[$field['id'] ]) && $data[$field['id'] ]!='yes' )? 
								$field['default']
								:$yesno_value;

							$rightside.= "<p class='yesno_row'>".
							EVO()->elements->yesno_btn(array('var'=>$__default,'attr'=>array('afterstatement'=>$after_statement) )).
							"<input type='hidden' name='".$field['id']."' value='".(($__default=='yes')?'yes':'no')."'/><span class='field_name'>".__($field['name'],$textdomain)."{$legend_code}</span>";

								// description text for this field
								if(!empty( $field['desc'] )){
									$rightside.= "<i style='opacity:0.6; padding-top:8px; display:block'>".$field['desc']."</i>";
								}
							$rightside .= '</p>';
						break;

						case 'yesnoALT':
						   $__default = (!empty( $field['default'] ) )?
						      $field['default']
						      :'no';

						   $yesno_value = (!empty( $data[$field['id'] ]) )?
						      $data[$field['id']]:$__default;
						   
						   $after_statement = (isset($field['afterstatement']) )?$field['afterstatement']:'';

						   $rightside.= "<p class='yesno_row'>".EVO()->elements->yesno_btn(array('var'=>$yesno_value,'attr'=>array('afterstatement'=>$after_statement) ))."<input type='hidden' name='".$field['id']."' value='".$yesno_value."'/><span class='field_name'>".__($field['name'],$textdomain)."{$legend_code}</span>";

						      // description text for this field
						      if(!empty( $field['desc'] )){
						         $rightside.= "<i style='opacity:0.6; padding-top:8px; display:block'>".$field['desc']."</i>";
						      }
						   $rightside .= '</p>';
						break;

						case 'begin_afterstatement': 
							
							$yesno_val = (!empty($data[$field['id']]))? $data[$field['id']]:'no';
							
							$rightside.= "<div class='backender_yn_sec' id='".$field['id']."' style='display:".(($yesno_val=='yes')?'block':'none')."'><div class='evosettings_field_child'>";
						break;
						case 'end_afterstatement': 
							$rightside.= "</div></div><em class='hr_line evosettings_end_field w1'></em>"; 
							break;
						
						// hidden section open
						case 'hiddensection_open':
							
							$__display = (!empty($field['display']) && $field['display']=='none')? 'style="display:none"':null;
							$__diclass = (!empty($field['display']) && $field['display']=='none')? '':'open';
							
							$rightside.="<div class='ajdeSET_hidden_open {$__diclass}'><h4>{$field['name']}{$legend_code}</h4></div>
							<div class='ajdeSET_hidden_body' {$__display}><div class='evo_in'>";
							
						break;					
						case 'hiddensection_close':	$rightside.="</div></div>";	break;

						case 'sub_section_open':
							$rightside.="<div class='evo_settings_subsection'><h4 class='acus_subheader'>".__($field['name'],$textdomain)."</h4><div class='evo_in'>";
						break;
						case 'sub_section_close': 
							$rightside.="</div></div>";
							if( isset($field['em']) && $field['em'])	$rightside.= "<em class='hr_line'></em>'";
						break;
						case 'line': // @4.9
							$rightside.= "<em class='hr_line'></em>";
						break;
						
						// custom code
						case 'customcode':						
							$rightside .= (!empty($field['code'])? $field['code']:'');						
						break;
					}
					if(!empty($field['type']) && !in_array($field['type'], $__no_hr_types) && !isset($field['afterstatement']) ){ 
						$rightside.= "<em class='hr_line'></em>";
					}
					
				}		
				$rightside.= "</div>";//<!-- nfer-->
			}
			$count++;
		}
		
		//built out the backender section
		ob_start();
		?>
		<table id='ajde_customization'>
			<tr><td class='backender_left' valign='top'>
				<div id='acus_left'>
					<ul><?php echo $leftside ?></ul>								
				</div>
				<div class="ajde-collapse-menu" id='collapse-button'>
					<span class="collapse-button-icon"></span>
					<span class="collapse-button-label" style='font-size:12px;'><?php _e('Collapse Menu','eventon');?></span>
				</div>
				</td><td class='evo_settings_right' width='100%'  valign='top'>
					<div id='acus_right' class='ajde_backender_uix'>
						<p id='acus_arrow' style='top:14px'></p>
						<div class='customization_right_in'>
							<div style='display:none;' id='ajde_color_guide'>Loading</div>
							<div id='ajde_clr_picker' class="cp cp-default" style='display:none'></div>							
							<?php echo $rightside.$extra_tabs;?>
						</div>
					</div>
				</td>
			</tr>
		</table>	
		<?php
		echo ob_get_clean();
		
	}


	// supportive functions
	private function _part_rearrange_field_html($args){
		extract( array_merge(array(
			'unselectable_fields'=> array(),
			'selected_data'=> array(), 
			'slug'=> '', 
			'name'=> '', 
			'_field_data'=> array(),
			'_secondary_data_array'=> array(),
			/*
			'right_content'=> '',
			'right_content_fields'=> '',
			'right_content_2'=> '',
			'right_content_2_fields'=> '',
			*/
		), $args));

		if( empty($name)) return null;

		// check if this field is unselectable
		$_is_unselectable = $unselectable_fields && in_array($slug, $unselectable_fields) ? true : false;

		// visible value
		$_visibility_val = ( !empty($selected_data) && in_array($slug, $selected_data)? 'circle-check on':'circle off');

		// check if this field has right content
		$_right_content = '';
		if( isset( $_field_data['right_content'] ) ){
			if( !empty($_field_data['right_content_fields']) && is_array($_field_data['right_content_fields']) && in_array( $slug, $_field_data['right_content_fields']) ){
				$_right_content =  $_field_data['right_content'];
			}
		}

		// secondary toggle data
		$_right_content_2 = '';
		if( isset( $_field_data[ 'seondary_toggle_data']) ){
			$secondary_toggle_fields = isset($_field_data[ 'seondary_toggle_data']['fields'] ) ? $_field_data[ 'seondary_toggle_data']['fields'] : false;
			if( $secondary_toggle_fields && in_array( $slug, $secondary_toggle_fields) ){

				// if this toggle value is selected or not
				$_icon_class = ( in_array($slug, $_secondary_data_array)) ? 'fa-toggle-on on': 'fa-toggle-off off';

				$_right_content_2 = "<i class='evosetting_tog_trig secondary fa {$_icon_class} evofz24 evomarr5 evocurp evohoop7'></i> ". $_field_data[ 'seondary_toggle_data']['label'];
			}
		}

		return "<div data-val='".$slug."' class='evo_data_item evomarb5 evodfx evofx_dr_r evofx_jc_sb evobr20 evoposr ". ($_is_unselectable? 'unsel':'') ."'>".
		"<span class='evosetting_left evodfx evofx_ai_c evogap10'>" .
			"<i class='evosetting_visibility evosetting_tog_trig evofz24 evocurp evo_transit_all evo_trans_sc1_1 fa fa-". $_visibility_val ."'></i>".
			$name .
		"</span>" .
		"<span class='evosetting_right evomarr30 evodfx evofx_ai_c'>" .
			$_right_content .
			$_right_content_2 .
		"</span>" . 			
		"</div>";
	}

} 
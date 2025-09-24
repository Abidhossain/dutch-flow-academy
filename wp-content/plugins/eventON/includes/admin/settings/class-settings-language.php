<?php
/**
 * EventON Language Settings Processing
 * @version 4.9.7
 */

 class EVO_Lang_Settings{

 	// interpret the language array information
		public function interpret_array($array){

			
			
			$output = '';

			if(!is_array($array)) return;

			$LNG_names = array();

			foreach($array as $item){
				$item_type = !empty($item['type'])? $item['type']: '';	
				$legend = (!empty($item['legend']))?  $item['legend']: '';
				$placeholder = (!empty($item['placeholder']))?  $item['placeholder']: $legend;

				// label
				$label = '';
				if( !empty( $item['label'])) $label = $item['label'];
				if( isset( $item[0]) && count( $item ) == 1) $label = $item[0];
				


				switch($item_type){
					case 'section':
						extract($item);
						$output .= "<div class='evoLANG_section_header evo_settings_toghead {$id}'>{$name}</div><div class='evo_settings_togbox'>";

						$output .= $this->interpret_array( $fields );						

						$output .= "</div>";
					break;
					case 'togheader':
						$output .= "<div class='evoLANG_section_header evo_settings_toghead'>{$item['name']}</div><div class='evo_settings_togbox'>";
					break;
					case 'multibox_open':
						if(!empty($item['items']) && is_array($item['items'])){
							
							$output .= "<div class='evoLANG_subsec'>";
						
							foreach($item['items'] as $slug => $__label){

								if( is_array( $__label)) $__label = $slug;

								$value = !empty( $this->lang_options[$slug] ) ? $this->lang_options[$slug] : '';

								$output .= "<div class='eventon_custom_lang_line'>";
								$output .= "<p class='evolang_string'><span>" . esc_html( $__label ) ."</span>";

								$output .= "<input class='evolang_input ' type='hidden' data-label='" . esc_html( $__label ) ."' name='{$slug}' value='{$value}'/>";
								$output .= "<em>{$value}</em>";

								$output .= "</p>";
								$output .= "</div>";
							}
							$output .= "</div>";
						}
						
					break;					
					case 'subheader':
						$output .= '<div class="evoLANG_subsec"><div class="evoLANG_subheader">'.$label.'</div>';
					break;

					case 'togend':
						$output .= "</div>";
					break;
					default:	

						// text slug @4.7
						$slug = isset( $item['name'] ) ? esc_html( $item['name'] ) : evo_lang_texttovar_filter($label);				
						$duplicate_string = in_array($slug, $LNG_names)? true:false;

						// field name processing															
							if($duplicate_string){
								if(!empty( $this->lang_options[$slug] )){
									$val = $this->lang_options[$slug];
								}elseif( !empty( $this->lang_options[$slug.'_v_']) ){
									$val = $this->lang_options[$slug.'_v_'];
								}
								//$slug = $slug.'_v_';
							}else{									
								$val = (!empty($this->lang_options[$slug]))?  $this->lang_options[$slug]: '';
							}	

							$LNG_names[] = $slug;
								

						$value = is_array($val)? $val[0]: stripslashes($val);

						$output .= "<div class='eventon_custom_lang_line ".($duplicate_string?'dup':'')."'>
							<p class='evolang_string'>";


							$output .= "<span>" . ( $duplicate_string ? "<i class='fa fa-clone' title='". __('Duplicate text string','eventon') ."'></i>":'' ) . esc_html( $label ) . "</span>";

							$output .= '<input class="evolang_input '. ($duplicate_string?'dup':'') .'" data-label="'. esc_html( $label ) .'" type="hidden" name="'.$slug.'" value="'.	esc_html( $value ) .'"/>';
							$output .= "<em>". esc_html( $value ) ."</em>";

							if($placeholder) $output .= EVO()->elements->tooltips($placeholder,'L');

						

						$output .= "</div>";
						//$output .= (!empty($legend))? "<p class='eventon_cl_legend'>{$legend}</p>":null;

					break;
				}
			}

			return $output;
		}

 } 
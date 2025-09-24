<?php 
/**
 * Event CMF settings
 * @version 4.8.1
 */

$EVENT = new EVO_Event( $post_data['event_id'] );
EVO()->cal->set_cur('evcal_1');
$metabox_array = array();

?>
<div class=''>
	<form class=''>
		<?php wp_nonce_field( 'evo_save_secondary_settings', 'evo_noncename' );?>
		<?php

		EVO()->elements->print_hidden_inputs(array(
			'event_id'=> $EVENT->ID,
		));


		// get each cmf
		$cmf_count = evo_calculate_cmd_count( EVO()->cal->get_op('evcal_1') );
		for($x =1; $x<=$cmf_count; $x++){	
			if(!eventon_is_custom_meta_field_good($x)) continue;

			$fa_icon_class = EVO()->cal->get_prop('evcal__fai_00c'.$x);		

			$visibility_type = (!empty($evcal_opt1['evcal_ec_f'.$x.'a4']) )? $evcal_opt1['evcal_ec_f'.$x.'a4']:'all' ;
			$__field_key = 'evcal_ec_f'.$x.'a';

			$metabox_array[] = array(
				'id'=>'evcal_ec_f'.$x.'a1',
				'variation'=>'customfield',
				'name'=>	EVO()->cal->get_prop('evcal_ec_f'.$x.'a1'),		
				'iconURL'=> $fa_icon_class,
				'iconPOS'=>'',
				'x'=>$x,
				'visibility_type'=>$visibility_type,
				'type'=>'code',
				'content'=>'',
				'slug'=>'evcal_ec_f'.$x.'a1',
				'field_key'=> $__field_key,
				'field_type'=> EVO()->cal->get_prop( $__field_key .'2')
			);
		}

		if( count($metabox_array)>0):	

			$html_fields = array();

			// print each cmf field
			foreach($metabox_array as $index=>$mBOX){
				
				extract( $mBOX );

				EVO()->cal->set_cur('evcal_1');

				$x = $mBOX['x'];
				$__field_key = 'evcal_ec_f'.$x.'a'; // evcal_ec_f1a
				$__field_id = '_'.$__field_key .'1_cus';
				$__field_type = EVO()->cal->get_prop( $__field_key .'2');

				// add to html data
				$html_fields[] = $__field_id;

				// field header data
				?>
				<div class='evo_border evobr20 evomarb20 ' data-id='<?php echo $__field_id;?>'>
					<div class='evodfx evofx_dr_r evofx_jc_sb evoborderb evopad20'>
						<span class='evofz18 evofw700 evoff_1 evodb'><i class='fa <?php echo $iconURL;?>'></i> <?php echo $name;?></span>
						<span class='evofz14 evoop5 evofsi'><?php _e('Visibility');?> <b><?php echo $visibility_type;?></b></span>
					</div>
					<div class='evopad30'>
				<?php
						
					// cmf image
						
						?><div class='evo_cmf_img_holder'>
							<?php
							EVO()->elements->get_element(array(
								'_echo'=>true,
								'type'=>'image',
								'id'=> '_'.$__field_key .'_img',
								'value'=> $EVENT->get_prop( '_'.$__field_key .'_img'),
								'name'=> sprintf(__('%s Image (Optional)', 'eventon'), $name ),
							));
							?>
						</div>
						<?php
					
										

					// FIELD
					$__saved_field_value = ($EVENT->get_meta_null( $__field_id ) );
					
					switch ($__field_type) {
						

						// work in progress 4.9
						case 'block_editor':
			                // Block editor container
			                echo "<div class='evo_block_editor_container' data-field-id='{$__field_id}'>";
			                echo "<label class='evomarb5 evodb'>" . __('Field Content', 'eventon') . "</label>";
			                echo "<div id='evo_block_editor_{$__field_id}' class='evo_block_editor' style='max-width: 100%;'></div>";
			                echo "<input type='hidden' name='{$__field_id}' id='{$__field_id}' value='" . esc_attr($__saved_field_value) . "' />";
			                echo "</div>";

			                // Inline script to render Block Editor
							    ?>
							    <script>
							        (function(wp) {
							            wp.domReady(function() {							               
							                if (!wp.blocks || !wp.blockEditor || !wp.element || !wp.data) {
									            console.error('Block editor scripts not loaded for field: ' + fieldId);
									            return;
									        }
							                const { blocks, blockEditor, element, data } = wp;
							                const fieldId = '<?php echo $__field_id; ?>';
							                const $hiddenInput = document.getElementById(fieldId);
							                let content = $hiddenInput.value || '';
							                const initialBlocks = blocks.parse(content);

							                element.render(
							                    element.createElement(
							                        blockEditor.BlockEditorProvider,
							                        {
							                            value: initialBlocks,
							                            onChange: (newBlocks) => {
									                        if ($hiddenInput) {
									                            $hiddenInput.value = blocks.serialize(newBlocks);
									                        }
									                    },
							                            settings: {
							                                hasFixedToolbar: true,
							                                alignWide: true,
							                                allowedBlockTypes: true,
							                                //allowedBlockTypes: ['core/paragraph', 'core/heading', 'core/html'],
							                                mediaUpload: wp.media?.editor?.upload || null,
							                                //__experimentalBlockInspector: true,
								                        	//__experimentalBlockInserter: true,
							                            }
							                        },
							                        // Include BlockList and BlockInserter components
									                [
									                    element.createElement(blockEditor.BlockList)
									                ]
							                    ),
							                    document.getElementById('evo_block_editor_' + fieldId)
							                );
							            });
							        })(window.wp);
							    </script>

			                <?php
			                break;

			            // support for classic wordpress editor @version 4.9.6
			            case 'wp_editor':

				            // Classic editor container
							echo "<div class='evo_classic_editor_container evomart15' data-field-id='{$__field_id}'>";
							echo "<label class='evomarb5 evodb'>" . __('Field Content', 'eventon') . "</label>";
							echo "<textarea name='{$__field_id}' id='{$__field_id}' class='evo_classic_editor_textarea evow100pi'>" . esc_textarea($__saved_field_value) . "</textarea>";
							echo "</div>";




			           	break;
						case 'textarea':
						case 'textarea_trumbowig':
							echo EVO()->elements->get_element(array(
								'type'=> 'wysiwyg',
								'id'=> $__field_id,
								'name'=> __('Field Content','eventon'),
								'value'=> $__saved_field_value,
								'row_class'=> 'evomart15'
							));	
							break;

						case 'textarea_basic':

							echo EVO()->elements->get_element(array(
								'type'=> 'textarea',
								'id'=> $__field_id,
								'name'=> __('Field Content','eventon'),
								'value'=> $__saved_field_value
							));	
							break;

						case 'button':
							$__saved_field_link = ($EVENT->get_meta_null("_" . $__field_key . "1_cusL")  );
							$input_value = ( !empty($__saved_field_value) ? addslashes($__saved_field_value ) :'' );

							echo EVO()->elements->get_element(array(
								'type'=>'textarea',
								'id'=> '_'.$__field_key .'_T',
								'value'=> $EVENT->get_meta_null( '_'.$__field_key .'_T'),
								'name'=>__('Above Button Content (Optional)','eventon')
							));
							echo EVO()->elements->get_element(array(
								'type'=>'input',
								'id'=> $__field_id,
								'value'=> $input_value,
								'name'=>__('Button Text','eventon')
							));
							echo EVO()->elements->get_element(array(
								'type'=>'input',
								'id'=> $__field_id.'L',
								'value'=> $__saved_field_link,
								'name'=>__('Button Link','eventon')
							));
												
							// open in new window
							$onw = ($EVENT->get_meta_null("_evcal_ec_f".$x."_onw") );
							?>

							<span class='yesno_row evo'>
								<?php 	
								echo EVO()->elements->yesno_btn(array(
									'id'=>'_evcal_ec_f'.$x . '_onw',
									'var'=> $EVENT->get_prop('_evcal_ec_f'.$x . '_onw'),
									'input'=>true,
									'label'=>__('Open in New window','eventon')
								));?>											
							</span>
						<?php
							break;
						default:
							echo EVO()->elements->get_element(array(
								'type'=> 'input',
								'id'=> $__field_id,
								'name'=> '',
								'value'=> $__saved_field_value
							));	
							break;	
					}
					

					echo "</div>";
				echo "</div>";
			}

			// pass html fields on the settings form
			EVO()->elements->print_hidden_inputs(array(
				'html_fields'=> json_encode( $html_fields ),
			));

			EVO()->elements->print_trigger_element(
				array(
					'title'=> __('Save Changes','eventon'),
					'uid'=> 'evo_save_secondary_settings',
					'lbdata'=> array(
						'class'=>'config_cmf_data',
						'hide'=> 3000
					),
					'adata'=> array(
						'a'=>'eventon_save_secondary_settings',
						'end'=>'admin',
						'loader_btn_el'=>'yes',
					)
				),'trig_form_submit');

			// Inline styles for classic editor
			echo "<style>
			    .evo_classic_editor_container .mce-tinymce, 
			    .evo_classic_editor_container .mce-edit-area, 
			    .evo_classic_editor_container .mce-edit-area iframe {
			        min-height: 200px !important;
			    }
			    .evo_classic_editor_container .mce-toolbar-grp {
			        display: flex !important;
			    }
			</style>";

			// inline scripts for classic editor
			?>
			<script>
			(function($) {
			    function initializeClassicEditor(fieldId, retryCount = 0, maxRetries = 5) {
			        if (typeof wp === 'undefined' || typeof wp.editor === 'undefined' || typeof $.ui === 'undefined') {
			            if (retryCount < maxRetries) {
			                setTimeout(function() {
			                    initializeClassicEditor(fieldId, retryCount + 1, maxRetries);
			                }, 500);
			            } else {
			                console.error('Failed to initialize Classic Editor for field: ' + fieldId);
			            }
			            return;
			        }

			        const $textarea = $('#' + fieldId);
			        if (!$textarea.length) {
			            console.error('Textarea not found for field: ' + fieldId);
			            return;
			        }

			        try {
			            wp.editor.remove(fieldId);
			        } catch (e) {}

			        wp.editor.initialize(fieldId, {
			            tinymce: {
			                wpautop: false,
			                plugins: 'charmap colorpicker compat3x directionality hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
			                toolbar1: 'bold italic underline strikethrough | bullist numlist | blockquote hr | alignleft aligncenter alignright | link unlink | image wp_more | spellchecker',
			                toolbar2: 'formatselect fontselect fontsizeselect | forecolor backcolor | pastetext removeformat | charmap | outdent indent | undo redo | wp_help',
			                content_css: '<?php echo includes_url('css/editor.min.css'); ?>,<?php echo includes_url('css/dashicons.min.css'); ?>',
			                height: 300,
			                setup: function(editor) {
			                    editor.on('init', function() {
			                        editor.setContent($textarea.val());
			                        editor.hide();
			                        setTimeout(function() {
			                            editor.show();
			                        }, 100);
			                    });
			                    editor.on('change', function() {
			                        editor.save();
			                    });
			                    editor.on('change keyup', function() {
								    $textarea.val(editor.getContent());
								});
			                    // Sync content when switching to Text mode
			                    editor.on('SetContent', function() {
			                        $textarea.val(editor.getContent());
			                        setTimeout(function() {
			                            const qtTextarea = $('#qt_' + fieldId + '_content');
			                            if (qtTextarea.length) {
			                                qtTextarea.val($textarea.val());
			                            }
			                        }, 100);
			                    });
			                },
			            },
			            quicktags: {
			                buttons: 'strong,em,link,block,del,ins,img,ul,ol,li,code,more,close',
			                id: fieldId
			            }
			        });

			        // Initialize Quicktags and sync content
			        if (typeof QTags !== 'undefined') {
			            QTags({ id: fieldId, buttons: 'strong,em,link,block,del,ins,ul,ol,li,code,more,close' });
			            QTags._buttonsInit();
			            setTimeout(function() {
			                const qtTextarea = $('#qt_' + fieldId + '_content');
			                if (qtTextarea.length) {
			                    qtTextarea.val($textarea.val());
			                } else {
			                    console.warn('Quicktags textarea not found for field: ' + fieldId);
			                    // Retry sync
			                    setTimeout(function() {
			                        const retryQtTextarea = $('#qt_' + fieldId + '_content');
			                        if (retryQtTextarea.length) {
			                            retryQtTextarea.val($textarea.val());
			                        }
			                    }, 2000);
			                }
			            }, 1500);
			        }

			        // Add media button above the editor
			        setTimeout(function() {
			            const $container = $('.evo_classic_editor_container[data-field-id="' + fieldId + '"]');
			            if ($container.find('.wp-media-buttons').length === 0) {
			            	$container.find('.wp-editor-tabs').before('<div class="wp-media-buttons"><button type="button" class="button insert-media add_media" data-editor="' + fieldId + '"><span class="wp-media-buttons-icon"></span> Add Media</button></div>');
			                // Bind media button click
			                $container.find('.add_media').on('click', function() {
			                    wp.media.editor.setContent = function(content) {
			                        var editor = tinymce.get(fieldId);
			                        if (editor) {
			                            editor.setContent(content);
			                        }
			                        $textarea.val(content);
			                        // Sync to Quicktags
			                        setTimeout(function() {
			                            const qtTextarea = $('#qt_' + fieldId + '_content');
			                            if (qtTextarea.length) {
			                                qtTextarea.val(content);
			                            }
			                        }, 100);
			                    };
			                    wp.media.editor.open(fieldId);
			                });
			            }
			        }, 1500);
			    }

			    $('.evo_classic_editor_textarea').each(function() {
		            const fieldId = $(this).attr('id');
		            initializeClassicEditor(fieldId);
		        });

			})(jQuery);
			</script>
			<?php 

		else:
			echo '<p class="pad20"><span class="evomarb10" style="display:block">' . __('You do not have any custom meta fields activated.') . '</span><a class="evo_btn" href="'. get_admin_url(null, 'admin.php?page=eventon#evcal_009','admin') .'">'. __('Activate Custom Meta Fields','eventon') . '</a></p>';
		endif;

		?>
	</form>
</div>
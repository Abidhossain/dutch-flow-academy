/*
*	Eventon Settings tab - addons and licenses
*	@version: 4.9.10
*/

jQuery(document).ready(function($){

	init();
	var BODY = $('body');

	// load addon details
		function init(){

			var obj = $('#evo_addons_list');
			var data_arg = {
				action:'eventon_get_addons_list',
				nn: evo_admin_ajax_handle.postnonce,
			};

			$.ajax({
				beforeSend: function(){	},
				type: 'POST',
				url:the_ajax_script.ajaxurl,
				data: data_arg,
				dataType:'json',
				success:function(data){				
					obj.html(data.content);
				}
			});
		}

	// load forms for lightbox
		$('body').on('click','#evo_license_form_trig', function(){
			var ajaxdataa = {};
			ajaxdataa['action'] = 'eventon_admin_get_views';
			ajaxdataa['type'] = 'evo_activation_form';
			ajaxdataa['nn'] = evo_admin_ajax_handle.postnonce,

			$.ajax({
				beforeSend: function(){		},
				type: 'POST',dataType:'json',url:the_ajax_script.ajaxurl,data: ajaxdataa,
				success:function(data){	
					if(data.status=='good'){
						$('.ajde_popup_text').html( data.html );
					}
				},complete: function(){
					hide_pop_loading();
				}
			});
		});
	// load addons license form
		$('#evo_addons_list').on('click','.evo_addon_license_form_trigger',function(){
			var ajaxdataa = {};
			OBJ = $(this);
			ajaxdataa['action'] = 'eventon_admin_get_views';
			ajaxdataa['type'] = 'evo_addon_activation_form';
			data = 'data';
			ajaxdataa[data] = {};
			ajaxdataa[data]['slug'] = OBJ.data('slug');
			ajaxdataa[data]['product_id'] = OBJ.data('product_id');
			ajaxdataa['nn'] = evo_admin_ajax_handle.postnonce,

			$.ajax({
				beforeSend: function(){		},
				type: 'POST',dataType:'json',url:the_ajax_script.ajaxurl,data: ajaxdataa,
				success:function(data){	
					if(data.status=='good'){
						$('.ajde_popup_text').html( data.html );
					}
				},complete: function(){
					hide_pop_loading();
				}
			});
		});


	// License Verification for EventON
		BODY.on('click','.eventon_submit_license',function(){
			validate( $(this) , 'main' );			
		});

		BODY.on('click','.eventonADD_submit_license',function(){	
			validate( $(this), 'addon' );
		});

		function validate(OBJ, TYPE){

			var LB = $('body').find('.evoaddons_active_license');

			LB.find('.message').removeClass('bad good');
			
			var ajaxdataa = {};
			var parent_pop_form = OBJ.parent().parent();
			
			// field validation
				errors = 0;
				parent_pop_form.find('input.fields').each(function(){
					if($(this).val()==''){
					 	errors++;
					}else{
						ajaxdataa[ $(this).attr('name')] = $(this).val();		
					}						
				});

				if(errors >0 ){
					show_pop_bad_msg('All fields are required! Please try again.');
					return false;
				}
							
			parent_pop_form.find('.message').hide();
			var slug = parent_pop_form.find('.eventon_slug').val();	
			var error = false;	
			if(TYPE == 'addon'){
				var id = parent_pop_form.find('.eventon_id').val();	
			}		
			
			// validate key format					
				ajaxdataa['action'] = 'eventon_validate_license';
				ajaxdataa['type'] = TYPE;
				ajaxdataa['nn'] = evo_admin_ajax_handle.postnonce,

				

			LB.evo_admin_get_ajax({
				lightbox_key:'evoaddons_active_license',
				ajaxdata: ajaxdataa,
				ajax_action: 'eventon_validate_license',
				uid: 'evo_submit_evo_licenseactivate_form',
				end:'admin',
				hide_lightbox: false,
				load_new_content: false,
			});
		}

		BODY.on('evo_ajax_success_evo_submit_evo_licenseactivate_form',function(event, OO, data, el){
			if(data.html!=''){
				SLUG = data.slug;
				BOX = $("#evoaddon_"+SLUG);
				BOX.replaceWith( data.html );
			}
			if( data.status == 'good'){
				LB = $('body').find('.evo_lightbox.'+ OO.lightbox_key);
				LB.evo_lightbox_close({delay:3000});
			}
		});

		// Reattempt remote activation
			$('#evo_addons_list').on('click','.evo_retry_remote_activation',function(){
				ADDON = $(this).closest('.addon');

				var ajaxdataa = {};
				ajaxdataa['action'] = 'eventon_revalidate_license';
				ajaxdataa['slug'] = ADDON.data('slug');
				ajaxdataa['product_id'] = ADDON.data('product_id');
				ajaxdataa['type'] = 'addon';
				ajaxdataa['nn'] = evo_admin_ajax_handle.postnonce,

				$(this).evo_admin_get_ajax({
					ajaxdata: ajaxdataa,
					ajax_action: 'eventon_revalidate_license',
					uid: 'evo_license_reactivate_formsubmit',
					end:'admin',
					hide_lightbox: false,
					load_new_content: false,
				});
			});

			BODY.on('evo_ajax_success_evo_license_reactivate_formsubmit',function(event, OO, data, el){
				if(data.html!=''){
					SLUG = data.slug;
					BOX = $("#evoaddon_"+SLUG);
					BOX.replaceWith( data.html );
				}
			});
	
	// deactivate eventon products
		// eventon
		$('body').on('click', '#evoDeactLic', function(){			
			deactivate_product( $(this) , 'main' );
		});
		$('body').on('click', '.evo_deact_adodn',function(){
			deactivate_product( $(this) , 'addon' );
		});

		function deactivate_product(OBJ, type){
			var data_arg = {
				action:'eventon_deactivate_product',
				type: type,
				nn: evo_admin_ajax_handle.postnonce,
			};
			var addon = OBJ.closest('.addon');
					
			data_arg['key']        = addon.data('key');
			data_arg['slug']       = addon.data('slug');
			data_arg['email']      = addon.data('email');
			data_arg['product_id'] = addon.data('product_id');
			
			$.ajax({
				beforeSend: function(){ addon.addClass('evoloading');	},
				type: 'POST',
				url:the_ajax_script.ajaxurl,
				data: data_arg,
				dataType:'json',
				success:function(data){
					if(data.html !=''){
						addon.replaceWith( data.html );
					}
					alert( data.msg);
				},complete:function(){	addon.removeClass('evoloading');	}
			});
		}

		
	// popup lightbox functions
		function show_pop_bad_msg(msg){
			$('.evolb_popup').find('.message').removeClass('bad good').addClass('bad').html(msg).fadeIn();
		}
		function show_pop_good_msg(msg){
			$('.evolb_popup').find('.message').removeClass('bad good').addClass('good').html(msg).fadeIn();
		}
		
		function show_pop_loading(){
			$('.evolnb_popup_text').css({'opacity':0.3});
			$('#evolb_loading').fadeIn();
		}
		function hide_pop_loading(){
			$('.evolnb_popup_text').css({'opacity':1});
			$('#evolb_loading').fadeOut(20);
		}
});
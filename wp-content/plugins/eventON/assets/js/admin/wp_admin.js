/*
 * EventON Back end scripts for general backend of wordpress
 * @version 4.9.8
 */

jQuery(document).ready(function($){	

	const BB = $('body');
	

// color picker
	if( $.isFunction($.fn.ColorPicker) ) { 
		BB.on('click','.colorselector',function(e){

			var EE = $(this);
			EE.ColorPicker({
				onBeforeShow: function(){
					const hex = $(this).attr('hex') || 'f3f3f3';
					$(this).ColorPickerSetColor( hex );					
				},	
				onChange:function(hsb, hex, rgb){
					//console.log(hex+' '+rgb);
					CIRCLE = $('body').find('.colorpicker_on');
					CIRCLE.css({'backgroundColor': '#' + hex}).attr({'title': '#' + hex, 'hex':hex});

					obj_input = CIRCLE.siblings('input.evocolorp_val');	
					obj_input.attr({'value':hex});
					
					$('body').trigger('evo_color_select_changed',[ hex, rgb]);
				},	
				onSubmit: function(hsb, hex, rgb, el) {
					var obj_input = $(el).siblings('input.evocolorp_val');

					if($(el).hasClass('rgb')){
						$(el).siblings('input.rgb').attr({'value':rgb.r+','+rgb.g+','+rgb.b});
					}

					obj_input.attr({'value':hex});

					$(el).css('backgroundColor', '#' + hex);
					$(el).attr({'title': '#' + hex, 'hex':hex});
					$(el).ColorPickerHide();

					$('body').find('.colorpicker_on').removeClass('colorpicker_on')
						.trigger('evo_color_select_submitted',[el, hex, rgb]);
				},
				onHide: function(colpkr){
					$('body').find('.colorpicker_on').removeClass('colorpicker_on');
				}
			}).bind('click',function(){
				$(this).addClass('colorpicker_on');
			});

			if( !EE.hasClass('clrO')){
				EE.trigger('click').addClass('clrO');
			}
		});


	}

	function colorPickMulti(itemid, cp){}

// get location cordinates
	BB.on('click','.evo_auto_gen_latlng', function(){
		// validation
		var O = $(this);

		BB.remove('.evo_auto_gen_latlng_m');

		var add = $('body').find('input[name="location_address"]').val();
		if(!add) 
			var add = $('body').find('input[name="term_meta[location_address]"]').val();


		if( !add){
			O.after('<p class="evo_auto_gen_latlng_m" style="padding-top:5px;">Address Missing</p>');
			return;
		}

		// run ajax
		var D = {
			'action': 'eventon_get_latlng',
			'address': add,
			'nn': evo_admin_ajax_handle.postnonce,
		};
		$.ajax({
			beforeSend: function(){
				O.parent().addClass('evo_loader');
			},
			url: evo_admin_ajax_handle.ajaxurl, type: 'POST',dataType:'json',
			data: D,
			success:function(data){

				if( data.status == 'good'){

					$('body').find('input[name="location_lat"]').val( data.lat );
					$('body').find('input[name="location_lon"]').val( data.lng );

					$('body').find('input[name="term_meta[location_lat]"]').val( data.lat );
					$('body').find('input[name="term_meta[location_lon]"]').val( data.lng );
				}else{
					if( 'm' in data){
						O.after('<p class="evo_auto_gen_latlng_m" style="padding-top:5px;">'+ data.m +'</p>');
					}
				}
				
			},complete:function(){	
				O.parent().removeClass('evo_loader');
			}
		});
	});

// Side Panel for admin
// @since 4.5.2
	$('body').on('click','.evosp_trigger', function(event){
		if( event !== undefined ){
			event.preventDefault();
			event.stopPropagation();
		}

		const el = $(this);

		el.addClass('evo_sp_trig_on');

		// spv = side panel values
		el.evo_open_sidepanel( el.data('d') , el.data('spv') );
	});
	$('body').on('click','.evosp_close', function(event){
		if( event !== undefined ){
			event.preventDefault();
			event.stopPropagation();
		}
		
		$(this).evo_close_sidepanel(  );
	});

	/**
	 * Side panel for eventON 
	 * @version 4.9
	 */ 
	$.fn.evo_open_sidepanel = function(opt, spv){
		var defs = {
			'uid':'',
			'end':'admin', // admin or client
			'hide_sp':false,
			'hide_message':false,
			'title':'',
			'sp_title':'',
			'content_id':'',  // only to load dynamic content
			'content_load_delay':0,
			'ajax':'no',
			'ajax_data':'',		
			'other_data':{},
			'adata':'',
			'size': 'sm',
		}
		var OO = $.extend({}, defs, opt);
		//console.log(OO);

		const SP = $('body').find('.evo_sp');

		// structure
			const load_html = "<div class='evo_loading_bar_holder h100'><div class='evo_loading_bar wid_50 hi_50'></div><div class='evo_loading_bar hi_100'></div><div class='evo_loading_bar'></div><div class='evo_loading_bar'></div><div class='evo_loading_bar hi_50'></div></div>";

			SP.html("<div class='evosp_in'><span class='evosp_close'><i class='fa fa-multiply'></i></span><div class='evosp_head evoff_1'>"+OO.sp_title+"</div><div id='evops_content' class='evosp_body'>"+load_html+"</div><div class='evosp_foot evo_bordert evodfx evofx_dr_r evofx_ai_c evogap10'><p class='message evomar0i'></p></div></div>");

			SP.addClass('show');
			SP.removeClass('med lar sm').addClass( OO.size );

			const SP_body = SP.find('.evosp_body');
			const SP_head = SP.find('.evosp_head');
			const SP_foot = SP.find('.evosp_foot');

		// load using content
		if( OO.content_id != ''){
			if( OO.content_load_delay > 0){
				setTimeout(function(){
					SP_body.html( BB.find('#'+ OO.content_id ).html() );
				}, OO.content_load_delay );
			}else{
				SP_body.html( BB.find('#'+ OO.content_id ).html() );
			}
			
		}

		// run ajax to load content for the lightbox inside
			if( ( OO.ajax == 'yes' && OO.ajax_data != '') || OO.adata != '' ){

				var passing_data = {
					adata: OO.adata,
					ajaxdata: OO.ajax_data, 
					uid: OO.uid ,
					end: OO.end,
					load_new_content: true,
					load_new_content_id: 'evops_content',
					lightbox_loader:false
				};

				SP.evo_admin_get_ajax( passing_data );
			}

		// populate new content with spv values
			if( spv != ''){
				BB.on('evo_ajax_success_'+ OO.uid , function(){
					$('#evops_content').find('input, select').each(function(){
						var fel = $(this);
						$.each(spv,function(index, val){
							if( fel.attr('name') == index){

								if( val == 'no' || val == 'yes') fel.evo_elm_change_yn_btn( val );
								fel.val( val );
							}
						});					
					});
				});
			}
  		
  		BB.trigger('evo_sp_opened_'+OO.uid, OO);
  		BB.trigger('evo_sp_opened', OO);
  	}

  	$.fn.evo_savevals_sidepanel = function(spv){
  		const el = BB.find('.evo_sp_trig_on');

  		el.data('spv', spv);
  		el.removeClass('evo_sp_trig_on');
  	}

  	// CLOSE sidepanel
  	$.fn.evo_close_sidepanel = function(opt){
  		SP = BB.find('.evo_sp');

  		var defaults = { 'delay':200};

		if( !(SP.hasClass('show')) ) return;

		var OO = $.extend({}, defaults, opt);
		
  		setTimeout(function(){	
  			BB.trigger('evo_sp_closed',[ SP ]);
  			SP.removeClass('show');
  		}, OO.delay );
  		setTimeout(function(){	SP.html('');	}, ( OO.delay + 100 ) );
  	}

  	$.fn.evo_showmsg_sidepanel = function(opt){
  		var defaults = { 
			'type':'good',
			'message':'',
			'hide_message': false,// hide message after some time pass time or false
			'hide_sp': false, // hide lightbox after some time of false
		}; var OO = $.extend({}, defaults, opt);

		const SP = $('body').find('#evo_sp');

		SP.find('.message').removeClass('bad good').addClass( OO.type ).html( OO.message ).fadeIn();

		if( OO.hide_message ) setTimeout(function(){  SP.evo_hidemsg_sidepanel() }, OO.hide_message );

		if( OO.hide_sp ) SP.evo_close_sidepanel({ delay: OO.hide_sp });
  	}
  	$.fn.evo_hidemsg_sidepanel = function(opt){
		SP = this;
		SP.find('p.message').hide();
	}

	
// LIGHTBOX function 
	// since 4.2 moved to funtctions


	// lightbox hide
		$('body').on('click',' .ajde_close_pop_trig',function(){
			hide_popupwindowbox( $(this).closest('.ajde_admin_lightbox') );
		});
		$('body').on('click',' .ajde_close_pop_btn',function(){
			var obj = $(this).parent();
			hide_popupwindowbox( $(this).closest('.ajde_admin_lightbox') );
		});
		
		$(document).mouseup( function(event){
			if ($(event.target).hasClass('evo_content_inin')) {
			 	CONTAIN =	$(event.target).find('.ajde_popup');
			 	if(!CONTAIN.hasClass('nooutside')){
			 		CONTAIN.find('.ajde_close_pop_btn').trigger('click');
			 	}
		  	}
		});

		$('body').on('click','.ajde_close_btn',function(){
			$(this).closest('.ajde_close_elm').hide();
			if( $(this).data('remove') !== undefined && $(this).data('remove') == 'yes')
				$(this).closest('.ajde_close_elm').remove();
		});
		
	// trigger hide popup
		$('body').on('evoadmin_lightbox_hide',function(event, lightboxclass, delay){
			lightboxELM = $('.ajde_admin_lightbox.'+lightboxclass);
			hide_popupwindowbox( lightboxELM , delay);
		});
		function hide_popupwindowbox( lightboxELM , delay){
			if(! lightboxELM.hasClass('show')) return false;
			Close = (lightboxELM.parent().find('.ajde_admin_lightbox.show').length == 1)? true: false;

			// if set to remove the lightbox HTML from dom
			var remove_from_DOM = ( lightboxELM.find('.ajde_close_pop_btn').hasClass('remove_from_DOM') )? true:false;

			// hide delays
			var hide_delay = (delay === null || delay == '' || delay === undefined) ?  500 : parseInt(delay);

			if( hide_delay > 500){
				setTimeout( function(){ 
					lightboxELM.removeClass('show');
				}, ( hide_delay - 500  ) );
			}else{
				lightboxELM.removeClass('show');
			}
			
			setTimeout( function(){ 
				if(Close){
					$('body').removeClass('evo_overflow');
					$('html').removeClass('evo_overflow');
				}
				// remove lightbox HTML from DOM
				if( remove_from_DOM){
					lightboxELM.remove();
				}
			}, hide_delay);			
		}

	// OPEN POPUP BOX		
		// everywhere in wp-admin
			$('body').on('click','.ajde_popup_trig', function(){			
				ajde_popup_open( $(this));
			});

	// Loading animations
		$('body').on('evo_show_loading_animation', function(event, lb_class, animation_type){
			show_loading_animation(lb_class, animation_type);
		});
		function show_loading_animation(lb_class, animation_type){
			LB = $('.ajde_admin_lightbox.'+lb_class).eq(0);

			var _animation_type = (animation_type !='' && animation_type !== undefined) ? 
				animation_type: 'initial';

			if( _animation_type == 'initial')
				LB.find('.ajde_popup_text').html('<div class="evo_loading_bar_holder"><div class="evo_loading_bar wid_40 hi_50"></div><div class="evo_loading_bar"></div><div class="evo_loading_bar"></div><div class="evo_loading_bar"></div><div class="evo_loading_bar wid_25"></div></div>');

			if( _animation_type == 'saving')
				LB.find('.ajde_popup_text').addClass('loading');
		}
		$('body').on('evo_hide_loading_animation', function(event, lb_class){
			hide_loading_aniation(lb_class);
		});
		function hide_loading_aniation( lb_class){
			LB = $('.ajde_admin_lightbox.'+lb_class).eq(0);
			LB.find('.ajde_popup_text').removeClass('loading');
		}

	// popup open
		// 2.6.9
		$('body').on('evo_open_admin_lightbox', function(event, lb_class){
			ajde_open_any_lightbox(lb_class);
		});

		function ajde_open_any_lightbox(lb_class){
			LIGHTBOX = $('.ajde_admin_lightbox.'+lb_class).eq(0);

			// if already open
			if(LIGHTBOX.is("visible")===true) return false;


			POPUP = LIGHTBOX.find('.ajde_popup');
			POPUP.find('.message').removeClass('bad good').hide();

			// open lightbox
			LIGHTBOX.addClass('show');	
			$('body').addClass('evo_overflow');
			$('html').addClass('evo_overflow');
		}

		// @version 4.1
		$('body').on('evo_open_dynamic_admin_lightbox', function(event, obj, obj_data){
			ajde_popup_open(obj, obj_data);
		});

		function ajde_popup_open(obj, obj_data){

			OD = obj_data !== undefined ? obj_data : obj.data();

			var popc = obj.data('popc');

			// check if specific lightbox requested
			var LIGHTBOX = ('popc' in OD && OD.popc !== false && OD.popc != '')?
				$('.ajde_admin_lightbox.'+ OD.popc).eq(0):$('.ajde_admin_lightbox.regular').eq(0);

			// create lightbox HTML and add to page
			if( OD.popc == 'print_lightbox'){
				lightbox_class_name = OD.lb_cl_nm;
				lightbox_size = 'lb_sz' in OD ? OD.lb_sz: '';
				$('.ajde_admin_lightboxes').append('<div id="" class="ajde_admin_lightbox '+lightbox_class_name+'"><div class="evo_content_in"><div class="evo_content_inin"><div class="ajde_popup '+lightbox_class_name+' '+lightbox_size+'"><div class="ajde_header"><a class="ajde_backbtn" style="display:none"><i class="fa fa-angle-left"></i></a><p id="ajde_title" class="ajde_lightbox_title"></p><a class="ajde_close_pop_btn remove_from_DOM">X</a></div><div class="ajde_popup_text"></div><p class="message"></p></div></div></div></div>');

				show_loading_animation( lightbox_class_name );

				LIGHTBOX = $('.ajde_admin_lightbox.'+lightbox_class_name);
			}

			var POPUP = LIGHTBOX.find('.ajde_popup');

			// alter lightbox title
			if( obj.data('t') !== undefined){				
				LIGHTBOX.find('.ajde_lightbox_title').html( obj.data('t') );
			}
			
			if(LIGHTBOX.is("visible")===true) return false;

			// append textbox id to popup if given
			if(obj.attr('data-textbox')!==''){
				POPUP.attr({'data-textbox':obj.attr('data-textbox')});
			}

			// dynamic content within the site
				var dynamic_c = obj.attr('data-dynamic_c');
				if(typeof dynamic_c !== 'undefined' && dynamic_c !== false){
					
					var content_id = obj.attr('data-content_id');
					var content = $('#'+content_id).html();
					
					LIGHTBOX.find('.ajde_popup_text').html( content);
				}
			
			// run ajax to load content for the lightbox inside
				if( obj.data('ajax') == 'yes' && obj.data('d') !== 'undefined'){

					var D = {};
					D = obj.data('d');

					$.ajax({
						beforeSend: function(){	},
						url:	evo_admin_ajax_handle.ajaxurl, type: 'POST',dataType:'json',
						data: D,
						success:function(data){
							LIGHTBOX.find('.ajde_popup_text').html( data.html);
						},complete:function(){	hide_pop_loading();	}
					});
				}

			// if content coming from a AJAX file			
				var attr_ajax_url = obj.attr('ajax_url');				
				if(typeof attr_ajax_url !== 'undefined' && attr_ajax_url !== false){
					$.ajax({
						beforeSend: function(){
							show_pop_loading();
						},
						url:attr_ajax_url,
						success:function(data){
							LIGHTBOX.find('.ajde_popup_text').html( data);
						},complete:function(){
							hide_pop_loading();
						}
					});
				}

			// change title if present		
				var poptitle = obj.attr('poptitle');
				if(typeof poptitle !== 'undefined' && poptitle !== false){
					LIGHTBOX.find('.ajde_header p').html(poptitle);
				}
						
			POPUP.find('.message').removeClass('bad good').hide();

			// open lightbox
			setTimeout( function(){ 
				LIGHTBOX.addClass('show');	
				$('body').addClass('evo_overflow');
				$('html').addClass('evo_overflow');
			},300);

		}
	
	// popup lightbox functions
		// lightbox messages
		$('body').on('ajde_lightbox_show_msg',function(event,message, boxclassname, type, donthide, hideMsg){
			LIGHTBOX = $('.'+boxclassname+'.ajde_admin_lightbox');
			type = (type!='bad')? 'good':'bad';
			LIGHTBOX.find('p.message').removeClass('bad good').addClass(type).html(message).fadeIn();
			
			// hide lightbox if good after 2 seconds
			dh = (donthide !='' && donthide == false ) ? true: false;
			
			if(type=='good' && dh)	setTimeout(function(){  hide_popupwindowbox(LIGHTBOX) }, 2000);

			// hide the message only after 2 seconds
			if( hideMsg)	setTimeout(function(){  LIGHTBOX.find('p.message').hide() }, 2000);
		});

		// hide lightbox message
		$('body').on('ajde_lightbox_hide_msg',function(event,boxclassname){
			LIGHTBOX = $('.'+boxclassname+'.ajde_admin_lightbox');
			LIGHTBOX.find('p.message').fadeOut();			
		});

		function show_pop_bad_msg(msg){
			$('.ajde_popup').find('.message').removeClass('bad good').addClass('bad').html(msg).fadeIn();
		}
		function show_pop_good_msg(msg){
			$('.ajde_popup').find('.message').removeClass('bad good').addClass('good').html(msg).fadeIn();
		}
		
		function show_pop_loading(){
			$('.ajde_popup_text').css({'opacity':0.3});
			$('#ajde_loading').fadeIn();
		}
		function hide_pop_loading(){
			$('.ajde_popup_text').css({'opacity':1});
			$('#ajde_loading').fadeOut(20);
		}

// TAXONOMY term form, NEW/ EDIT/ DELETE		
	$('body')

	// get term list
	.on('evo_ajax_success_evo_get_tax_list',function(event, OO, data){
		if(data.status=='good'){
			//console.log('dd');						
			$('.evo_config_term').find('select.field').select2();						
		}
	})
	// save term edit settings
	.on('evo_ajax_success_evo_save_tax_edit_settings', function (event, OO, data){
		if(data.status=='good'){	
			$('.evo_singular_tax_for_event.'+ data.tax ).html(data.htmldata);

			// when setting event location
			if(data.tax == 'event_location' && evo_admin_ajax_handle.setting_evo_gen_map){
				var inp = $('body').find('input[name="evcal_gmap_gen"]');
				if( inp.val() == 'no') inp.siblings('span').trigger('click');
			}
		}
	})
	// save term from list 
	.on('evo_ajax_success_evo_save_term_list_item', function (event, OO, data){
		if(data.status=='good'){	
			$('.evo_singular_tax_for_event.'+ data.tax ).html(data.htmldata);

			// when setting event location
			if(data.tax == 'event_location' && evo_admin_ajax_handle.setting_evo_gen_map){
				var inp = $('body').find('input[name="evcal_gmap_gen"]');
				if( inp.val() == 'no') inp.siblings('span').trigger('click');
			}
		}
	})

	// remove tax
	.on('evo_ajax_beforesend_evo_remove_tax_term', function (event, OO){
		$('.evo_singular_tax_for_event.'+ OO.ajaxdata.tax ).addClass('evoloading');
		
	})
	.on('evo_ajax_success_evo_remove_tax_term', function (event, OO, data){
		if(data.status=='good'){	
			$('.evo_singular_tax_for_event.'+ data.tax ).html(data.htmldata).removeClass('evoloading');;
		}
	})
	;	

	
// settings
	// themes section
		$('.evo_theme_selection select').on('change',function(){
			var theme = $(this).val();
			
			// switch to default
			if(theme =='default'){
				$('.colorselector ').each(function(){
					var item = $(this).siblings('input');
					item.attr({'value': item.attr('default') });
					$(this).attr({'style':'background-color:#'+item.attr('default'), 'hex':item.attr('default')});					
				});
				$('.evo_theme').find('span').each(function(){
					$(this).attr({'style':'background-color:#'+ $(this).attr('data-default')});
				});
	
			}else{
				themeSel = JSON.parse( $('#evo_themejson').html());

				// each theme array
				$.each(themeSel, function(i, item){			
					if(item.name== theme){
						$.each(item.content, function(key, value){
							var thisItem = $('body').find('input[name='+key+']');
							thisItem.val(value);

							if(!value.includes(','))
								thisItem.siblings('span.colorselector').attr({'style':'background-color:#'+value, 'hex':value});

							$('.evo_theme').find('span[name='+key+']').attr({'style':'background-color:#'+value});
						});
					}
				});

			}
		});
	
	// Import EventON settings
	// @updated 4.6.8
		$('body').on('evo_data_uploader_submitted', function(event, reader_event, msg_elm, upload_box){

			//console.log( reader_event);

			if( $(upload_box).data('id') != 'evo_settings_upload') return;

			var jsonData = reader_event.target.result ;
         
            $.ajax({
				beforeSend: function(){	},
				type: 'POST',
				url:evo_admin_ajax_handle.ajaxurl,
				data: {	
					action:'eventon_import_settings',
					nonce: evo_admin_ajax_handle.postnonce,
					jsondata: $.parseJSON( jsonData)
				},
				dataType:'json',
				success:function(data){
					msg_elm.html(data.msg);

					setTimeout(function(){
						location.reload();
					},2000);

				},complete:function(){	}
			});
		});
		
// Event Top Designer @version 4.1
	$.fn.evotop_designer = function (options){
		var designer = $(this);
		var fields = $(designer).find('.evotop_design_col');
		var selector = $(designer).find('#evotop_field_selector');
		
		var init = function(){
			interactions();
			field_selector();

			if( fields.length> 0 ){
				fields.sortable({
					update: function(e, ul){
						run_row_numbering();
					}
				});
			}			
		}

		var interactions = function(){	
			// remove a box
				$(designer).on('click','.ectd_act',function(){
					var box = $(this).closest('.evotop_design_field');
					
					add_item_to_selector( box.data('f') , box.find('em').html() );

					box.remove();
					run_row_numbering();	
				});				
		}

		
		var field_selector = function(){
			// add a field
			$(designer).on('click','.evotop_add_field_trig', function(){
				$(designer).find('.adding_field').removeClass('adding_field');
				$(this).parent().addClass('adding_field');
				selector.addClass('focus');
			});

			// selector > designer
			$('#evotop_field_selector_f').on('click','span',function(){
				if( !selector.hasClass('focus') ) return;

				var this_field = $(this).data('f');

				var html = "<span class='evotop_design_field' data-f='"+ this_field +"'><em>"+ $(this).html() +"</em><span class='ectd_act'><i class='fa fa-minus-circle'></i></span></span>";
				html += "<span class='evotop_add_field_trig'><b>+</b></span>";

				var box = $(designer).find('.adding_field');

				// append to the adding fields column
				box.find('.evotop_add_field_trig').remove();
				box.append( html ).removeClass('adding_field');

				// remove from selector and hide selector, show no fields message if
				$(this).remove();

				if( $('#evotop_field_selector_f').find('span').length == 0){
					selector.find('.nothing').show();
				}
				selector.removeClass('focus');

				// load new fields
				run_row_numbering();
			});

			// cancel selector
			$('#evotop_field_selector_c').on('click',function(){
				selector.removeClass('focus');
				$(designer).find('.adding_field').removeClass('adding_field');
			});
		}

		var add_item_to_selector = function(f, n){
			var HH = $('#evotop_field_selector_f').html();
			HH +=  "<span data-f='"+ f +"'>"+ n +"</span>";
			$('#evotop_field_selector_f').html( HH );
			
			// check if selector content to be shown
			if( $('#evotop_field_selector_f').html() == ''){
				selector.find('.nothing').show();
			}else{
				selector.find('.nothing').hide();
			}
		}
		
		var run_row_numbering = function(){
			
			var etl = {};

			var def_color = designer.data('dc');

			$(designer).find('.evotop_design_col').each(function(){
				col_key = $(this).data('c');
				etl[ col_key ] = {};
				var count = 1;
				$(this).find('.evotop_design_field').each(function(){
					if( $(this).data('f') === undefined ) return;
					etl[ col_key ][ count ] = {
						'f': $(this).data('f'),
						'v': 'y'
					};
					count++;
				});				
			});

			$('#evotop_fields').val( JSON.stringify(etl) );
		}

		init();
	}
	$('.evotop_designer').evotop_designer();

// settings event card designer
// EventON v4.0
	$.fn.evo_card_designer = function (options) {
		var designer = $(this);
		var fields = $(designer).find('.evocard_design_holder');
		var selector = $(designer).find('#evo_card_field_selector');
		var def_color = designer.data('dc');

		var init = function(){
			interactions();
			field_selector();

			if( fields.length> 0 ){
				fields.sortable({
					update: function(e, ul){
						run_row_numbering();
					}
				});
			}
			
		}

		var interactions = function(){
			// new row
			$('.ecd_add_rows').on('click',function(){
				var count = parseInt($(this).data('c'));
				var holder_boxes = parseInt($(this).data('hc'));
				var holder_location = $(this).data('hl');

				html = "<p class='ecd_row "+ ( holder_location ) +"' data-r=''><span class='ecd_row_in'>";

				// normal boxes
				for (var i = 1; i <= count; i++) {
					html += "<span class='ecd_row_box' data-b='"+ i +"'><span class='ecd_set_field'>+ Set Field</span></span>";
				}

				// before after boxes count
				if( holder_boxes ){
					html += "<span class='ecd_row_box_h'>";
					for (var i = 1; i <= holder_boxes; i++) {
						html += "<span class='ecd_row_box' data-b='"+ holder_location + i +"'><span class='ecd_set_field'>+ Set Field</span></span>";
					}
					html += "</span>";
				}


				html +="</span><i class='fa fa-minus-circle ecd_del_row'></i></p>";

				$(fields).append( html );
				run_row_numbering();
			});

			// remove row
				$(designer).on('click','.ecd_del_row', function(){
					// items in row		
					$(this).parent().find('.ecd_row_box').each(function(){
						if( $(this).data('n') !== undefined && $(this).data('n') != ''){
							add_item_to_selector( $(this).data('n') , $(this).find('em').html() );			
						}			
					});

					// remove row
					$(this).closest('.ecd_row').remove();
					run_row_numbering();
				});

			// remove a box
				$(designer).on('click','.ecdad_act .fa-minus-circle',function(){
					var row = $(this).closest('.ecd_row');
					var box = $(this).closest('.ecd_row_box');
					
					add_item_to_selector( box.data('n') , box.find('em').html() );

					box.html( "<span class='ecd_set_field'>+ Set Field</span>" )
						.data('n','').attr('data-n', '' )
						.data('h','').attr('data-h', '' )
						.data('c','').attr('data-c', '' );
					run_row_numbering();	
				});

			// move row up down
				$(designer).on('click','.fa-chevron-circle-up',function(){
					var RR = $(this).closest('.ecd_row');
					PR = RR.prev();
					RR.insertBefore( PR );
					run_row_numbering();
				});
				$(designer).on('click','.fa-chevron-circle-down',function(){
					var RR = $(this).closest('.ecd_row');
					PR = RR.next();
					RR.insertAfter( PR );
					run_row_numbering();
				});

			// toggle visibility
				$(designer).on('click','.fa.vis',function(){
					if($(this).hasClass('fa-eye')){
						$(this).removeClass('fa-eye').addClass('fa-eye-slash');
						$(this).closest('.ecd_row_box').data('h','y').addClass('hidden');
					}else{
						$(this).removeClass('fa-eye-slash').addClass('fa-eye');
						$(this).closest('.ecd_row_box').data('h','').removeClass('hidden');
					}
					run_row_numbering();
				});

			// color picker
			$('body').on('evo_color_select_submitted', function(event, el, hex, rgb){
				//console.log(hex);
				if( $(el).hasClass('clr')){
					$(el).closest('.ecd_row_box').data('c', hex).attr('data-c', hex);
					run_row_numbering();
				}
			});
			$('body').on('evo_color_select_changed',function(event, hex, rgb){
				var cO = $('body').find('.colorpicker_on');
				if( cO.hasClass('clr')){
					if( hex != def_color ) cO.siblings('.clr_reset').removeClass('dn');

					cO.closest('.ecd_row_box').data('c', hex).attr('data-c', hex);
					run_row_numbering();
				}
			});
			// reset to default color
			$(designer).on('click','.clr_reset',function(){
				$(this).siblings('.clr')
					.css('background-color', '#'+ def_color)
					.attr('hex', def_color);
				$(this).closest('.ecd_row_box').data('c', def_color)
					.attr('data-c', def_color);
				$(this).addClass('dn');
				run_row_numbering();
			});
			
		}

		
		var field_selector = function(){
			$(designer).on('click','.ecd_set_field', function(){
				$(designer).find('.adding_field').removeClass('adding_field');
				$(this).parent().addClass('adding_field');
				selector.addClass('focus');
			});
			// selector > designer
			$('#evo_card_field_selector_f').on('click','span',function(){
				if( !selector.hasClass('focus') ) return;

				var html = "<span class='ecd_act1'><i class='vis fa fa-eye'></i><span class='colorselector clr' hex='"+def_color+"' style='background-color:#"+def_color+"' title=''></span> <span class='clr_reset dn' data-hex='"+def_color+"' style='background-color:#"+def_color+"' title=''></span> </span>"+
					"<em>"+ $(this).html() +"</em><span class='ecdad_act'><i class='fa fa-minus-circle'></i></span>";

				var box = $(designer).find('.adding_field');

				// append to the designer box
				box.html( html )
					.data('n', $(this).data('n') )
					.attr('data-n', $(this).data('n') )
					.removeClass('adding_field');

				// remove from selector and hide selector, show no fields message if
				$(this).remove();

				if( $('#evo_card_field_selector_f').find('span').length == 0){
					selector.find('.nothing').show();
				}
				selector.removeClass('focus');

				// load new fields
				run_row_numbering();
			});

			// cancel selector
			$('#evo_card_field_selector_c').on('click',function(){
				selector.removeClass('focus');
				$(designer).find('.adding_field').removeClass('adding_field');
			});
		}

		var add_item_to_selector = function(f, n){
			var HH = $('#evo_card_field_selector_f').html();
			HH +=  "<span data-n='"+ f +"'>"+ n +"</span>";
			$('#evo_card_field_selector_f').html( HH );
			
			// check if selector content to be shown
			if( $('#evo_card_field_selector_f').html() == ''){
				selector.find('.nothing').show();
			}else{
				selector.find('.nothing').hide();
			}
		}
		
		var run_row_numbering = function(){
			var count = 1;
			var ecl = {};

			var def_color = designer.data('dc');

			$(designer).find('.ecd_row').each(function(){
				$(this).data('r', count).attr('data-r', count);

				ecl[ count ] = {};
				$(this).find('.ecd_row_box').each(function(){
					ecl[ count ][ $(this).data('b') ] = {
						'n': $(this).data('n'),
						'h': $(this).data('h'),
						'c': ( def_color == $(this).data('c') ? '' : $(this).data('c'))
					};
				});

				count++;
			});

			$('#evo_card_fields').val( JSON.stringify(ecl) );
		}


		init();
	};

	// RUN
	$('body').on('evo_ajax_success_evo_ecard_designer',function(){
		$('body').find('.evo_card_designer').evo_card_designer();
	});

// Settings > Support
	$('.evotrouble_qas').on('click','h5',function(){
		$(this).next('p').toggle();
	});

// Diagnose
	$('#evo_send_test_email').on('click', function(){
		var el = $(this);
		if($('#evo_admin_test_email_address').val() == undefined|| $('#evo_admin_test_email_address').val() ==''){
			$('.evo_diag_email_msg').removeClass('evodn').find('p').html('Email Address Missing!').addClass('bad');
		}else{
			$.ajax({
				beforeSend: function(){	
					el.addClass('evobtn_loader w');
					$('.evo_diag_email_msg').removeClass('evodn').find('p').html('Sending Email!').addClass('good');
				},
				type: 'POST',
				url:evo_admin_ajax_handle.ajaxurl,
				data: {	
					action:'eventon_admin_test_email',
					nonce: evo_admin_ajax_handle.postnonce,
					email: $('#evo_admin_test_email_address').val()
				},
				dataType:'json',
				success:function(data){
					el.removeClass('evobtn_loader w');
					$('.evo_diag_email_msg').removeClass('evodn');
					if( data.status == 'good'){
						
						$('.evo_diag_email_msg').find('p').html('Email action performed with no errors! - '+ data.msg ).addClass('good');
					}else{
						var error_val = (data.error != '') ? JSON.stringify( data.error ): '';
						$('.evo_diag_email_msg').find('p').html('Email action performed! - '+ data.msg +' '+error_val ).addClass('bad');
					}
					
				},complete:function(){	}
			});
		}		
	});



// LANGUAGE SETTINGS @4.7
	// language tab
		$('.eventon_cl_input').focus(function(){
			$(this).parent().addClass('onfocus');
		});
		$('.eventon_cl_input').blur(function(){
			$(this).parent().removeClass('onfocus');
		});


	
	// change language @4.7
		$('.evo_lang_selection').change(function(){
			var val = $(this).val();
			var url = $(this).data('url');
			window.location.replace(url+'?page=eventon&tab=evcal_2&lang='+val);
		});

	// duplicate editing
		if( $('body').find('.eventon_cl_input').length>0){
			$('.eventon_cl_input').on('change paste keyup',function(){
				const n = $(this).data('n');
				$('body').find('.eventon_cl_input.'+ n).val( $(this).val() );
			});
		}
	// select text string for translation
		$('body').on('click','.eventon_custom_lang_line',function(){
			$('.eventon_custom_lang_line').removeClass('select');
			$(this).addClass('select');

			var textarea = $('.evolang_translatable_textfield');
			var input = $(this).find('input');
			var slug = input.attr('name');
			var value = input.val();

			$('.evolang_translate_original').html( $(this).find('span').html() );

			console.log(slug);
			textarea.val( value ).data('slug',slug).attr('data-slug', slug );

			if( value == '' || value === undefined){
				textarea.attr('placeholder', textarea.data('t1'));
			}else{
				textarea.attr('placeholder', textarea.data('t0'));
			}
		});

		// Keyup event on translatable text fields
		$('.evolang_translatable_textfield').on('keyup', function() {
		    const $this = $(this);
		    const slug = $this.data('slug');		    
		    const value = $this.val();

		    const $input = $(`input[name="${slug.replace(/([.?*+^$[\]\\(){}|-])/g, '\\$1')}"]`);
		    $input.val(value).siblings('em').html(value);
		});

	// toggeling language subheaders
		$('.evo_settings_toghead').on('click',function(){
			$(this).next('.evo_settings_togbox').toggle();
			$(this).toggleClass('close');
		});
	// toggle all sections @4.7.2
		$('#evolang_tog_all_sections').click(function(){
			var iobt = $(this).find('i');
			if( iobt.hasClass('fa-toggle-off')){
				$('.evo_settings_toghead').addClass('close').next('.evo_settings_togbox').hide();
				iobt.attr('class','fa fa-toggle-on');
			}else{
				$('.evo_settings_toghead').removeClass('close').next('.evo_settings_togbox').show();
				iobt.attr('class','fa fa-toggle-off');
			}
			

		});

	// Search text strings in language @4.7
		$('.evo_lang_search_in').on('input change keyup paste',function(){
			var searchval = $(this).val();

			$('body').find('.evolang_input').each(function(){

				line = $(this).closest('.eventon_custom_lang_line ');
				box = $(this).closest('.evo_settings_togbox');
				bar = box.siblings('.evo_settings_toghead');

				if( searchval == ''){
					line.show();
				}else{
					line.hide();
					var thistext = $(this).data('label');
					if( thistext === undefined ) return;

					if( thistext.toLowerCase().indexOf( searchval.toLowerCase() ) != -1 ){
						line.show();
						//console.log(thistext+' '+searchval);
					}
				}
			});

			// if whole section have no lines showing hide the whole section
			$('body').find('.evoLANG_subsec').each(function(){
				// Check if all child elements with class 'eventon_custom_lang_line' are not visible
			    if ($(this).find('.eventon_custom_lang_line:visible').length === 0) {
			        // If none of the children are visible, hide the whole section
			        $(this).hide();
			    }else{
			    	$(this).show();
			    }
			    if( searchval == ''){
			    	$(this).show();
			    }
			});

			$('body').find('.evo_settings_togbox').each(function(){
				// Check if all child elements with class 'eventon_custom_lang_line' are not visible
			    if ($(this).find('.eventon_custom_lang_line:visible').length === 0) {
			        // If none of the children are visible, hide the whole section
			        $(this).prev('.evo_settings_toghead').hide();
			    }else{
			    	$(this).prev('.evo_settings_toghead').show();
			    }

			    if( searchval == ''){
			    	$(this).prev('.evo_settings_toghead').show();
			    }
			});
		});

	// export language
		$('body').on('click','.evo_lang_export_btn', function(){
			string = {};
			var tmpArr = [];
  			var tmpStr = '';
			var csvData = [];

			type = $(this).data('t');

			// Add column headers
    		csvData.push('Field,Value,Label');

			if(type == 'var'){
				$('#evcal_2').find('input').each(function(){
					var field = $(this).attr('name');
					if( field === undefined ) return;
		            var value = $(this).val();
		            var label = $(this).data('label') || ''; // Use empty string if data-label is undefined
		            csvData.push(field + ',' + value + ',' + label);
				});
			}else{
				$('#evcal_2').find('input').each(function(){
					var field = $(this).attr('for') || $(this).attr('name'); // Use for or name
					if( field === undefined ) return;
		            var value = $(this).val();
		            var label = $(this).data('label') || ''; // Use empty string if data-label is undefined
		            csvData.push(field + ',' + value + ',' + label);
				});
			}			

			var output = csvData.join('\n');
		  	var uri = 'data:application/csv;charset=UTF-8,' + encodeURIComponent(output);
		  	//window.open(uri);
		  	$(this).attr({
		  		'download':'evo_lang_'+$('.evo_lang_selection').val()+'.csv',
		  		'href':uri,
		  		'target':'_blank'
		  	});
		});

	// import language
		$('body').on('evo_data_uploader_submitted', function(event, reader_event, msg_elm, upload_box){

			if( $(upload_box).data('id') != 'evo_language_upload') return;

			var csvData = reader_event.target.result;

            var allTextLines = csvData.split(/\r\n|\n/);
            //console.log(allTextLines[0]);
            for (var i=0; i<allTextLines.length; i++) {
            	var data = allTextLines[i].split(',');

            	// update new values

            	$('#evcal_2').find('input[name="'+data[0]+'"]').val(data[1]).siblings('em').text( data[1] ); // for vars
            	$('#evcal_2').find('input[for="'+data[0]+'"]').val(data[1]); // for text strings
            	
            	msg_elm.html('Updating language values.');   
        	}

        	msg_elm.html('Language fields updated. Please save changes.');   
		});
		
		

		function processData(allText) {
		    var allTextLines = allText.split(/\r\n|\n/);
		    var headers = allTextLines[0].split(',');
		    var lines = [];

		    for (var i=1; i<allTextLines.length; i++) {
		        var data = allTextLines[i].split(',');
		        if (data.length == headers.length) {

		            var tarr = [];
		            for (var j=0; j<headers.length; j++) {
		                tarr.push(headers[j]+":"+data[j]);
		            }
		            lines.push(tarr);
		        }
		    }
		    console.log(lines);
		}

});
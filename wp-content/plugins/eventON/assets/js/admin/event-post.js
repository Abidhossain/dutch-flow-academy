/** 
 * Event Edit Post Scripts
 * @version  4.9.10
 */
jQuery(document).ready(function($){

	//return;

	const BB = $('body');

	var date_format = $('#evcal_dates').attr('date_format');
	var time_format = ($('body').find('input[name=_evo_time_format]').val()=='24h')? 'H:i':'h:i:A';
	var RTL = $('body').hasClass('rtl');

	// on page load run dynamic content		
		$.fn.load_eventedit_data = function(){
			$('body').evo_admin_get_ajax({
				ajax_type: 'ajax',
				ajax_action: 'eventon_eventedit_onload',
				ajaxdata:{
					'action':'eventon_eventedit_onload',
					'eid': $('body').find('input[name=evo_event_id]').val()
				},
				uid:'eventedit_onload'
			});
		}
		if( $('body').find('#evo_pageload_data').length > 0 ){
			$('body').load_eventedit_data();
		}
		

	// custom button to reload event details
		const eventMetaBox = $('#ajdeevcal_mb1');
		if (eventMetaBox.length) {
	        // Find the .handle-actions.hide-if-no-js div
	        const handleActions = eventMetaBox.find('.handle-actions.hide-if-no-js');
	        if (handleActions.length) {
	        	const dataEid = eventMetaBox.find('.eventon_mb').data('eid') || '';
	            // Create the custom button
	            const customButton = $('<button>', {
	                type: 'button',
	                style: 'width:44px;height:44px;color:#1d2327;margin:0;padding:0;border:0;background:transparent;cursor:pointer;',
	                'data-id':'evo',
	                'data-eid':dataEid,
	                class: 'handledivx evoadmin_eventedit_reloadbox ',
	                html: '<i class="fa fa-refresh evotooltipfree" data-d="Refresh Event Details"></i>',
	                click: function() {
	                    // Add your button's functionality here
	                    
	                }
	            });

	            // Append the button to the handle-actions div
	            handleActions.prepend(customButton);
	        }
	    }
		


	// load event edit page data
		$('body').on('evo_ajax_success_eventedit_onload', function(event, OO, data){
			// load html content to matching dom IDs
			if( 'dom_ids' in data){
				$.each(data.dom_ids, function(index, value){
					if( !( index in data.content_array)) return;
					$('body').find('#'+ value).html( data.content_array[ index ]);
					$('body').trigger('evo_eventedit_dom_loaded_'+index , [ index, value]);
				});	

				$('body').trigger('evo_eventedit_all_dom_loaded', [OO, data]);// 4.9
			}

			$('body').find('.evoloading').removeClass('evoloading');
		});

		// reload a meta box
		$('body').on('click','.evoadmin_eventedit_reloadbox',function(){
			$(this).closest('.eventon_mb, .postbox').addClass('evoloading');

			$('body').evo_admin_get_ajax({
				ajax_type: 'ajax',
				ajax_action: 'eventon_eventedit_onload',
				ajaxdata:{
					'action':'eventon_eventedit_onload',
					'eid': $(this).data('eid'),
					'id': $(this).data('id')
				},
				uid:'eventedit_onload'
			});
		});

	// event status
		$('body').on('evo_row_select_selected', function(event, P,V){
			if(P.hasClass('es_values')){
				P.siblings('div').hide();
				P.siblings('.'+ V +'_extra').show();
			}
		});

	
	// virtual event
	// 4.0.3
		// load virtual settings to light box
			$('body')
			.on('evo_ajax_success_evo_get_virtual_events',function(event, OO, data){
				if(data.status=='good'){		
					LB = $('body').find('.' + OO.lightbox_key );			
					LB.evo_lightbox_populate_content({content: data.content });	
					var vir_val = LB.find('.evo_eventedit_virtual_event').val();
					if( vir_val == 'zoom'){
						LB.find('.zoom_connect').show();
					}
				}
			})
			;

			// set user role > load users for the role
			$('body').on('change','.evo_virtual_moderator_role',function(){
				var ajaxdataa_ = {};
				ajaxdataa_['action']='eventon_get_virtual_users';
				ajaxdataa_['eid'] = $(this).data('eid');
				ajaxdataa_['_user_role'] = $(this).val();
				var LB = $('body').find('.sel_moderator');

				$.ajax({
					beforeSend: function(){ 
						LB.evo_lightbox_start_inloading();
					},
					url:	evo_admin_ajax_handle.ajaxurl,
					data: 	ajaxdataa_,	dataType:'json', type: 	'POST',
					success:function(data){
						LB.find('.evo_virtual_moderator_users').html( data.content );
					},
					complete:function(){ 
						LB.evo_lightbox_stop_inloading();
					}
				});
			});
			// save moderator
			$('body').on('click','.save_virtual_event_mod_config',function(){
				
				$(this).evo_ajax_lightbox_form_submit({
					'lightbox_key':'sel_moderator',
					uid:'save_virtual_moderator',
					hide_lightbox: 2000,
				});

			});
		

		// vritual type changed
			$('body').on('change','.evo_eventedit_virtual_event',function(){

				var V = $(this).val();
				var section = $('#evo_virtual_details_in');
				
				var L = $(this).find('option:selected').data('l');
				var P = $(this).find('option:selected').data('p');
				var O = $(this).find('option:selected').data('o');

				// zoom connect
				if(V == 'zoom'){
					section.find('.zoom_connect').show();
				}else{
					section.find('.zoom_connect').hide();
				}

				// jitsi connect
				if(V == 'jitsi'){ section.find('.jitsi_connect').show(); }
				else{section.find('.jitsi_connect').hide();			}

				section.find('p.vir_link label').html( L);
				section.find('p.vir_link').val();
				section.find('p.vir_pass label').html( P);
				if(O !== undefined)
					section.find('p.vir_link em').html( O );
			});

		// virtual event settings -> save changes
			$('body').on('click','.save_virtual_event_config',function(){

				$(this).evo_ajax_lightbox_form_submit({
					'lightbox_key':'config_vir_events',
					uid:'save_virtual_event_data',
					hide_lightbox: 2000,
				});

			});
		
		// virtual end time
			$('body').on('evo_blockbtn_trigged',function(e, newval, el, id, as){	
				if( id != '_evo_virtual_endtime') return;			
				$('.evo_date_time_virtual_end_row').toggle(newval !== 'no');
			});

	// jitsi
		$('body')
		// save jitsi meeting settings
		.on('evo_ajax_success_evo_jitsi_connect',function(event, OO, data){
			if(data.status=='good'){		
				$('body').find('input[name="_vir_url"]').val( data.join_url );
				$('body').find('input[name="_vir_pass"]').val( data.pass );

				// update meeting id
				if( 'id' in data){
					$('body').find('input[name="_evoz_mtg_id"]').val( data.id);
				}
			}
		});
		
		$('body').on('click','.evo_jitsi_save',function(){
			FORM = $(this).closest('form');
			if( $(this).hasClass('del')) FORM.find('input.form_type').val('delete');

			$(this).evo_ajax_lightbox_form_submit({
				'lightbox_key':'evo_jitsi_config',
				uid:'evo_jitsi_connect',
				hide_lightbox: 2000,
			});

		});

	// ZOOM
		$('body')
		// save zoom mettings settings lb
		.on('evo_ajax_success_evo_zoom_connect',function(event, OO, data){
			if(data.status=='good'){		
				
				$('body').find('.evoz_mtg_id').show().find('a').html( data.id )
							.attr('href','https://zoom.us/meeting/'+ data.id);

				$('body').find('input[name="_vir_url"]').val( data.join_url );
				$('body').find('input[name="_vir_pass"]').val( data.pass );

				// update meeting id
				if( 'id' in data){
					$('body').find('input[name="_evoz_mtg_id"]').val( data.id);
				}

				// delete meeting
				if(data.type == 'delete' ){
					$('body').find('.evoz_mtg_id').hide();
					$('body').find('input[name="_evoz_mtg_id"]').val('');
					$('body').find('input[name="_vir_url"]').val( '' );
					$('body').find('input[name="_vir_pass"]').val( '' );
				}

				// action html replace
				if( 'action_html' in data){
					$('body').find('.actions').html( data.action_html );
				}

			}
		});	

		// connect to the api
		$('body').on('click','.evoz_connect',function(){

			FORM = $(this).closest('form');
			if( $(this).hasClass('del')) FORM.find('input.form_type').val('delete');

			$(this).evo_ajax_lightbox_form_submit({
				'lightbox_key':'evo_zoom_config',
				uid:'evo_zoom_connect',
				hide_lightbox: 2000,
			});
		});

	// Related events	
		$('body')
		// trigger configure related events
		.on('click','.evo_configure_related_events',function(e){
			e.preventDefault();
			var el = $(this);
			var box = el.closest('.evo_rel_events_box');
			el.evo_lightbox_open({
				'uid':'evo_get_related_events',
				adata:{
					a:'eventon_rel_event_list',
					data:{
						EVs: box.find('input[name=ev_releated]').val(),
						eventid: box.find('input[name=ev_related_event_id]').val(),
					}					
				},
				lbdata:{
					class:'evo_related_events_lb',
					title: box.find('input[name=ev_related_text]').val(),
				}
			});
		})
		.on('evo_ajax_success_evo_get_related_events',function (event, OO, data){
			if(data.status=='good'){
				LB = $('body').find('.' + OO.lightbox_key );			
				LB.find('.evolb_content').html( data.content);					
			}
		})		
		.on('click','span.rel_event', function(e){
			e.preventDefault();
			O = $(this);
			O.toggleClass('select');
			const $i = O.find('i');
			$i.toggleClass('far fa-circle fa fa-circle-check');
		})

		// save related event select
		.on('click','.evo_save_rel_events', function(e){
			e.preventDefault();
			LB = $('body').find('.evo_related_events_lb');
			EV = {};
			HTML = '';

			$(this).closest('.evo_rel_events_form').find('.rel_event.select').each(function(){
				var O = $(this);
				EV[O.data('id')] = O.data('n');
				HTML += "<span class='l' data-id='"+ O.data('id') +"'><span class='t'>" + O.data('t') +"</span><span class='n'>"+ O.data('n') + "</span><i class='fa fa-close'></i></span>";
			});

			BOX = $('body').find('.evo_rel_events_box');


			BOX.find('.ev_rel_events_list').html( HTML );
			BOX.find('input[name=ev_releated]').val( JSON.stringify(EV) );

			LB.evo_lightbox_show_msg({
				'type':'good',
				'message':'Saved related events!',
				'hide_lightbox':2000
			});
			
		})
		// remove related events
		.on('click','.ev_rel_events_list i',function(){
			var rel_box = $(this).closest('.evo_rel_events_box');

			$(this).closest('.l').remove();

			EV = {};
			rel_box.find('span.l').each(function(){
				EV[ $(this).data('id') ] = $(this).find('.n').html();
			});
			rel_box.find('input[name=ev_releated]').val( JSON.stringify( EV ));
		})
		// search related events @4.5.5
		.on('keyup', '.evo_rel_search_input',function(event){
			var keycode = (event.keyCode ? event.keyCode : event.which);
			var typed_val = $(this).val().toLowerCase();

			var ev_count = 0;
			
			$(this).closest('.evo_rel_events_form').find('.rel_event').each(function(){
				const n = $(this).find('.n').html().toLowerCase();

				if( typed_val == ''){
					$(this).show();
					ev_count++;
				}else{
					if( n.includes(typed_val ) ){
						$(this).show(); ev_count++;
					}else{
						$(this).hide();
					}
				}				
			});	

			// update count
			const sp = $(this).siblings('span');
			sp.html(ev_count +' ' + sp.data('t') );	
		})
		
		;

		// after event details meta content loaded
		$('body').on('evo_eventedit_dom_loaded_evo',function(event, val){
			// related events draggable and sortable events
			$('body').find('.ev_rel_events_list').sortable({
				update: function(e, ul){
					BOX = $(this).closest('.evo_rel_events_box');
					update_rel_event_ids(BOX);
				}
			});
		});

		

		function update_rel_event_ids(obj){
	    	var EIDS={},
	    		INPUT = obj.find('input[name=ev_releated]');

	    	C= 1;
	    	obj.find('span.l').each(function(index){
	    		EIDS[ $(this).data('id') ] = $(this).find('.n').html();
	    	});
	    	INPUT.val( JSON.stringify(EIDS) );
    	
	    }

	// meta box sections
	// click hide and show
		$('body').on('click','.evomb_header',function(){			
			var box = $(this).siblings('.evomb_body');			
			if(box.hasClass('closed')){
				$(this).removeClass('closed');
				box.show().removeClass('closed');
			}else{
				$(this).addClass('closed');
				box.hide().addClass('closed');
			}
			update_eventEdit_meta_boxes_values();
		});
		
		function update_eventEdit_meta_boxes_values(){
			var box_ids ='';
			
			$('body').find('.evomb_body').each(function(){				
				if($(this).hasClass('closed'))
					box_ids+=$(this).attr('box_id')+',';
			});		
			$('body').find('#evo_collapse_meta_boxes').val(box_ids);
		}
	
	// Event Color - existing colors selection
		$('body').on('click','.evcal_color_box',function(){	
			const main_color_metabox = $('#color_selector_1');

			$(this).addClass('selected');
			var new_hex = $(this).attr('color');
			var new_hex_var = '#'+new_hex;
			
			// set rgb val
			rgb_val = $(this).evo_rgb_process({ data : new_hex_var, type:'hex',method:'rgb_to_val'});
			$(this).find('.evo_color_n').val( rgb_val );
		
			main_color_metabox.find('.evo_color_hex').val( new_hex );
			
			main_color_metabox.find('.evo_set_color').css({'background-color':new_hex_var});
			main_color_metabox.find('.evcal_color_hex').html(new_hex);

			$('body').trigger('evo_event_color_changed');
			
		})
		// on colorpicker 2 color is set -> set gradient if enable
		.on('evo_event_color_changed',function(event){

			el = $('body').find('.evo_mb_color_box');

			// if gradient colors set
			if( el.find('input[name="_evo_event_grad_colors"]').val() == 'yes' ){
				
				const grad_ang = parseInt(el.find('input[name="_evo_event_grad_ang"]').val());
				const color1 = el.find('input[name="evcal_event_color"]').val();
				const color2 = el.find('input[name="evcal_event_color2"]').val();

				const css = 'linear-gradient('+grad_ang+'deg, #' + color2+ ' 0%, #'+  color1 + ' 100%)';

				el.find('.evo_color_grad_prev').css({
					'background-image': css,
				});
			}

		})
		.on('evo_colorpicker_2_submit',function(event,el){
			if( $(el).closest('.evo_mb_color_box') ){
				$('body').trigger('evo_event_color_changed');
			}
		})
		.on('evo_angle_set',function(event,el, deg){
			if( $(el).closest('.evo_mb_color_box') ){
				$('body').trigger('evo_event_color_changed');
			}
		})
		;

		
				
	
		
	/** User interaction meta field 	 **/
		// new window
		$('body').on('click','#evo_new_window_io',function(){
			var curval = $(this).hasClass('selected');
			if(curval){
				$(this).removeClass('selected');
				$('#evcal_exlink_target').val('no');
			}else{
				$(this).addClass('selected');
				$('#evcal_exlink_target').val('yes');
			}
		});
		 
		$('body').on('click','.evo_eventedit_ui',function(){
			const box = $(this).closest('.evo_event_edit_ui_box');
			var new_value = $(this).attr('value');

			const input_field = box.find('input[name=_evcal_exlink_option]');
			var input_val = input_field.val( new_value );
			const evcal_exlink_input = box.find('input[name=evcal_exlink]');


			// if open in new window option is visible
			if( new_value == 2 || new_value == 3 || new_value == 4){
				box.find('.event_edit_ui_extra').show();
				evcal_exlink_input.show();
				if( new_value == 4){
					evcal_exlink_input.val( box.data('event_url'));
				}else{
					evcal_exlink_input.val( '');
				}
			}else{
				box.find('.event_edit_ui_extra').hide();
				evcal_exlink_input.hide();
			}

		});

		
		
	// repeating events UI	
		// frequency
		$('body').on('click','span.evo_repeat_type_val',function(){

			O = $(this);
			const rep_box = O.closest('#evo_editevent_repeatevents');
			
			json = O.closest('.evo_editevent_repeat_field').data('t');

			var raw_field = O.attr('value');

			rep_box.find('.evo_preset_repeat_settings').show();
			rep_box.find('.repeat_weekly_only').hide();
			rep_box.find('.repeat_monthly_only').hide();

			// monthly
			if(raw_field =='monthly'){
				$('.evo_rep_month').show();

				// show or hide day of week
				var field_x = $('.values.evp_repeat_rb').find("span.select").attr('value');
				var condition = (field_x=='dow');
				$('.repeat_monthly_modes').toggle(condition);
												
				$('.repeat_information').hide();
				$('.repeat_monthly_only').show();
			
			}else if(raw_field =='weekly'){
				$('.evo_rep_week').show();

				// show or hide day of week
				var field_x = $('.values.evp_repeat_rb_wk').find("span.select").attr('value');
				var condition = (field_x=='dow');
				$('.evo_rep_week_dow').toggle(condition);
				
				$('.repeat_information').hide();
				$('.repeat_weekly_only').show();
			
			}else if(raw_field=='custom'){// custom repeating patterns
				$('.evo_preset_repeat_settings').hide();
				$('.repeat_information').show();
			}else{
				$('.evo_rep_month').hide();				
				$('.repeat_monthly_modes').hide();
				$('.repeat_information').hide();
			}
			rep_box.find('#evcal_re').html(
				json[ raw_field ]
			);
		});

		
		// adding a new custom repeat interval
		// @since 2.2.24
		// @updated 4.9.9
			$('body').on('click','#evo_add_repeat_interval',function(){
				var el = $(this);
				var obj = $('body').find('.evo_repeat_interval_new');

				// if the add new RI form is not visible
				if(!obj.is(':visible')){
					obj.show();
				}else{

					if( obj.find('input.evo_dpicker.end').val() &&
						obj.find('input.evo_dpicker.start').val() 
					){		

						const container = el.closest('.repeat_information');
						const box = container.find('.evo_repeat_interval_new.evo_edit_field_box');
						var ajax_data = {}

						// gather repeat instance time data
						box.find('input, select').each(function(){
							ajax_data[ $(this).attr('name') ] = $(this).val();
						});

						// get index
						const ul = container.find('.evo_custom_repeat_list');
						ajax_data['new_index'] = ul.find('li:last').data('cnt');

						// 24h
						ajax_data['_evo_time_format'] = container.find('._evo_time_format').val();

						// editing index
						if( ul.hasClass('editing') ) ajax_data['edit_index'] = ul.find('li.editing').data('cnt');

						el.evo_admin_get_ajax({
							'adata':{
								a:'eventon_generate_custom_repeat_unix',
								data:ajax_data,
								show_snackbar:true,
								loader_class:'evo_repeat_interval_new'
							},
							uid:'generate_custom_repeat_unix',
							onSuccess: (OO, data) => {
								if( ul.hasClass('editing')){
									ul.find('li.editing').replaceWith( data.content );
								}else{
									ul.append( data.content );
								}								
							}
						});

						return;

					}else{
						el.evo_snackbar({message: "All fields are required!"});
					}
				}
			})
			// edit a custom repeat interval
			.on('click','.evo_rep_edit',function(){
				const $li = $(this).closest('li');
				const container = $(this).closest('.repeat_information');
				const box = container.find('.evo_repeat_interval_new.evo_edit_field_box');

				var ajax_data = {};
				$li.find('input').each(function(){
					var name = $(this).attr('name');
    				if (name) ajax_data[name] = $(this).val();
				});

				ajax_data['_evo_time_format'] = container.find('._evo_time_format').val();
				ajax_data['_evo_date_format'] = container.find('._evo_date_format').val();

				container.evo_admin_get_ajax({
					adata :{
						a:'eventon_edit_custom_repeat',
						data:ajax_data,
						show_snackbar:true,
						loader_class:'evo_custom_repeat_list'
					},
					uid:'edit_custom_repeat_unix',
					onBefore: (OO ) =>{
						$li.addClass('editing');
						$li.closest('ul').addClass('editing');
					},
					onSuccess: (OO, data) => {
						$.each(['start', 'end'], function(i, type) {
						    box.find(`input[name=event_new_repeat_${type}_date]`).val(data[`${type}_date`]);
						    box.find(`input[name=event_new_repeat_${type}_date_x]`).val(data[`${type}_date_x`]);
						    box.find(`select[name=_new_repeat_${type}_hour]`).val(data[`${type}_hour`]);
						    box.find(`select[name=_new_repeat_${type}_minute]`).val(data[`${type}_minute`]);
						    box.find(`select[name=_new_repeat_${type}_ampm]`).val(data[`${type}_ampm`]);
						});

						box.show();
					}
				});

			})

			// delete a repeat interval
			.on('click','em.evo_rep_del',function(){
				LI = $(this).closest('li');
				LI.hide(function(){
					LI.remove();
				});
			});

		// show all repeat intervals
			$('body').on('click','.evo_repeat_interval_view_all', function(){
				const section = $(this).closest('.repeat_information');

				if($(this).attr('data-show')=='no'){
					section.find('li.over').show();
					$(this).attr({'data-show':'yes'}).html('View Less');
				}else{
					section.find('li.over').hide();
					$(this).attr({'data-show':'no'}).html('View All');
				}
			});

		// repeat by value from select field
		// show correct info based on this selection
		$('body').on('evo_row_select_selected',function(e, P, val, vals){
			if(P.hasClass('repeat_mode_selection')){
				cond = (val == 'dow');
				P.parent().siblings('.repeat_modes').toggle( cond);				
			}
		});

		// repeat infinite additions 4.6.9
		$('body').on('evo_yesno_changed',function(event, newval, obj, key){
			if( key == 'evcal_rep_inf' && newval == 'yes'){
				$('body').find('.evcal_rep_num').find('.evo_plusminus_change_input').val('inf');
			}
		});
		
	
	$('body')
	// end time hide or not
		.on('evo_blockbtn_trigged', function(e, newval, el, id, as){
			if( id != 'evo_hide_endtime') return;

			// yes
			if( newval == 'yes' ){
				$('body').find('.evo_date_time_elem.evo_end').animate({'opacity':'0.5'});
			}else{
				$('body').find('.evo_date_time_elem.evo_end').animate({'opacity':'1'});
			}
		})
	// All day or not u4.5.9
		.on('click','span._time_ext_type span', function(){
			const v = $(this).attr('value');
			if( v == 'dl' || v == 'ml' || v == 'yl' ){
				$('.evo_datetimes .evo_time_edit').animate({'opacity':'0.5'});
			}else{
				$('.evo_datetimes .evo_time_edit').show().animate({'opacity':'1'});
			}
		});
		
	//date picker on	
		$('body').on('evo_elm_datepicker_onselect',function(event, OBJ, selectedDate, date){
			// regular event start end dates
			if( $(OBJ).attr('id') == 'evo_end_date_457973'){
				$('body').find( "#evo_start_date_457973" ).datepicker( "option", "maxDate", selectedDate );      	
			}

			// custom repeat new start end date
			if( $(OBJ).attr('id') == 'evo_new_repeat_end_date_478933'){
				$('body').find( "#evo_new_repeat_start_date_478933" ).datepicker( "option", "maxDate", selectedDate );      	
			}

			if( $(OBJ).attr('id') == 'evo_start_date_457973'){

				var dayOfWeek = date.getUTCDay();
				
				// save event year based off start event date
   				$('.evo_days_list').find('.opt[value='+dayOfWeek+']' ).addClass('select');
   				$('.evo_days_list').find('input').val(function(){
   					return  (this.value? this.value +',': '') + dayOfWeek;
   				});
			}
		});

	
	/* Event Images */
		var file_frame,
			BOX;
	  
	    $('body').on('click','.evo_add_more_images',function(event){

	    	var obj = $(this);
	    	BOX = obj.siblings('.evo_event_images');

	    	event.preventDefault();

			// If the media frame already exists, reopen it.
			if ( file_frame ) {	file_frame.open();		return;	}
			
			// Create the media frame.
			file_frame = wp.media.frames.downloadable_file = wp.media({
				title: 'Choose an Image',
				button: {text: 'Use Image',},
				multiple: true
			});

			// When an image is selected, run a callback.
			file_frame.on( 'select', function() {

				var selection = file_frame.state().get('selection');
		        selection.map( function( attachment ) {
		            attachment = attachment.toJSON();
		            loadselectimage(attachment, BOX);
		           
	            });

				//attachment = file_frame.state().get('selection').first().toJSON();
				//loadselectimage(attachment, BOX);
			});

			// Finally, open the modal.
			file_frame.open();
	    }); 

		function loadselectimage(attachment, BOX){
			imgURL = (attachment.sizes.thumbnail && attachment.sizes.thumbnail.url !== undefined)? attachment.sizes.thumbnail.url: attachment.url;

			caption = (attachment.caption!== undefined)? attachment.caption: 'na';

			imgEL = "<span class='evo_transit_all evo_trans_sc1_08 evocurp' data-imgid='"+attachment.id+"'><b class='remove_event_add_img'><i class='fa fa-times'></i></b><img title='"+caption+"' data-imgid='"+attachment.id+"' src='"+imgURL+"'></span>";

						
			BOX.find('.evo_event_image_holder').append(imgEL);
			update_image_ids(BOX);

			
			$('body').evo_snackbar({
				message: 'Image Added!', 
				visible_duration: 3000
			});
				
		}

	    // remove image from gallery
		    $('body').on('click', '.remove_event_add_img', function(){
		    	BOX = $(this).closest('.evo_event_images');
		    	$(this).parent().remove();
		    	update_image_ids(BOX);
		    });

		// drggable and sorting image order
			$('.evo_event_image_holder').sortable({
				update: function(e, ul){
					BOX = $(this).closest('.evo_event_images');
					update_image_ids(BOX);
				}
			});

		// update the image ids 
		    function update_image_ids(obj){
		    	var imgIDs='',
		    		INPUT = obj.find('input');

		    	C= 1;
		    	obj.find('img').each(function(index){
		    		imgid = $(this).attr('data-imgid');
		    		if(imgid){
		    			imgIDs = (imgIDs? imgIDs:'') + imgid+',';
		    			C++;
		    		}
		    	});
		    	INPUT.val(imgIDs);
	    	
		    }

	
		var upariam = 3;
});
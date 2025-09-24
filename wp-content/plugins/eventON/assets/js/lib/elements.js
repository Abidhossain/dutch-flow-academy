/**
 * EventON elements
 * @version: 4.9.8
 */
jQuery(document).ready(function($){

const BB = $('body');

// process element interactivity on demand
	$.fn.evo_process_element_interactivity = function(O){
		setup_colorpicker();
		_evo_elm_load_datepickers();

		if( $('body').find('.evoelm_trumbowyg').length > 0 ){
			$('body').find('.evoelm_trumbowyg').each(function(){
				if ( $.isFunction($.fn.trumbowyg) ) {
					$(this).trumbowyg({
						btnsDef: {
		                    // Customize the formatting dropdown
		                    formatting: {
		                        dropdown: ['p', 'blockquote','h1', 'h2', 'h3', 'h4', 'h5','h6'], 
		                        ico: 'p' // Default icon for the dropdown
		                    }
		                },
						btns: [
							['viewHTML'],
					        ['undo', 'redo'], // Only supported in Blink browsers
					        ['formatting', '|', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
					        ['strong', 'italic','underline'],
					        ['link'],
					        ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
					        ['unorderedList', 'orderedList'],
					        ['removeformat'],
					        ['fullscreen']
						]
					});
				}
			});	
		}
	}
	// on page load
	$('body').evo_process_element_interactivity();
	// on after elements load
	$('body').on('evo_elm_load_interactivity',function(){
		$(this).evo_process_element_interactivity();
	});


/* interactive wysiwyg 4.6*/
	BB.on('click','.evo_elm_act_on',function(){
		$(this).siblings('.evo_field_container').show();
		$(this).hide();
	});
	BB.on('click','.evo_field_preview',function(){
		$(this).siblings('.evo_field_container').show();
		$(this).hide();
	});

/* Multiple input field -- 4.9.2 */
	$('body').on('click', '.evo_elm_inputmulti_add', function(e) {
	    e.preventDefault();
	    const $input = $(this).siblings('input');
	    const val = $input.val()?.trim();
	    
	    if (!val) return;

	    const slug = val.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '') // Remove special characters
        .replace(/\s+/g, '-')         // Replace spaces with hyphens
        .replace(/-+/g, '-');

	    const $row = $(this).closest('.evo_elm_row');
	    const id = $row.find('.evo_elm_inputmulti_values').data('id');
	    
	    $row.find('.evo_elm_inputmulti_values').append(`
	        <span class='evo_btn grey evomarb5i'>
	            ${val}
	            <input type='hidden' name='${id}[${slug}]' value='${val}'/>
	            <i class='evo_elm_inputmulti_remove fa fa-times'></i>
	        </span>
	    `);
	    $input.val('');
	}).on('click', '.evo_elm_inputmulti_remove', function() {
	    $(this).parent().remove();
	});

// angle button
	var dragging = false;
	$('body').on('mousedown', '.evo_elm_ang_hold',function(){
		dragging = true
	}).on('mouseup','.evo_elm_ang_hold',function(){
		dragging = false
	}).on('mousemove','.evo_elm_ang_hold',function(e){
		if (dragging) {
			//console.log(e);
			tar = $(this).find('.evo_elm_ang_center');
			var mouse_x = e.offsetX;
            var mouse_y = e.offsetY;
            var radians = Math.atan2(mouse_x - 10, mouse_y - 10);
            var degree = parseInt( (radians * (180 / Math.PI) * -1) + 180 );
			//console.log(degree+ ' '+ mouse_x +' '+mouse_y);

			tar.css('transform', 'rotate(' + degree + 'deg)');
			$(this).siblings('.evo_elm_ang_inp').val( degree +'°');

			$('body').trigger('evo_angle_set',[$(this), degree]);
		}
	}).on('keyup','.evo_elm_ang_inp',function(){
		deg = parseInt($(this).val());
		$(this).val( deg +'°');
		tar.css('transform', 'rotate(' + deg + 'deg)');
		
		$('body').trigger('evo_angle_set',[$(this), deg]);
	});

// Attach an image

// Image Attachment @4.8.1
	var file_frame;	
	var __img_index;
	var __img_obj;
	var __img_box;
	var __img_type;
  
    BB.on('click','.evolm_img_select_trig',function(event) {
    	event.preventDefault();

    	__img_obj = $(this);
    	__img_box = __img_obj.closest('.evo_metafield_image');
    	__img_type = __img_box.hasClass('multi')? 'multi': 'single';

    	if( __img_type == 'single' &&  __img_box.hasClass('has_img') ) return;

    	if( __img_type == 'multi'){
    		__img_index = __img_obj.data('index');

    		// remove image
			if( __img_obj.hasClass('on')){
				__img_obj.css('background-image', '').removeClass('on');
				__img_obj.find('input').val( '' );
				return;
			}
    	}

    	// If the media frame already exists, reopen it.
			if ( file_frame ) {	file_frame.open();	return;			}

		// Create the media frame.
			file_frame = wp.media.frames.downloadable_file = wp.media({
				title: 'Choose an Image', button: {text: 'Use Image',},	multiple: false
			});

    	
		// When an image is selected, run a callback.
			file_frame.on( 'select', function() {
				const attachment = file_frame.state().get('selection').first().toJSON();

				if( __img_type == 'single'){
					__img_box.addClass('has_img');
					__img_box.find('input.evo_meta_img').val( attachment.id );
					__img_box.find('.evoelm_img_holder').css('background-image', 'url('+ attachment.url +')');
				}else{
					__img_obj.css('background-image', 'url('+ attachment.url +')').addClass('on');
					__img_obj.find('input').val( attachment.id );
				}

			});

		// Finally, open the modal.
		file_frame.open();
		
    });  
	// remove image
	BB.on('click','.evoel_img_remove_trig',function(){

		const field = $(this).closest('.evo_metafield_image');

		if( !(field.hasClass('has_img') ) ) return;
		
		field.removeClass('has_img');
		field.find('input').val('');
		field.find('button').addClass('chooseimg');
		field.find('.evoelm_img_holder').css('background-image', '' );
	});

// yes no button @4.6.9	
	$('body').on('click','.ajde_yn_btn', function(){

		var obj = $(this);
		var afterstatement = obj.attr('afterstatement');
		var newval = 'yes';
		var key = obj.attr('id');
		
		// yes
		if(obj.hasClass('NO')){
			obj.removeClass('NO');
			obj.siblings('input').val('yes');				
			
			// afterstatment
			if(afterstatement!=''){
				var type = (obj.attr('as_type')=='class')? '.':'#';
				$('body').find(type+afterstatement).show();
			}

		}else{//no
			obj.addClass('NO');
			obj.siblings('input').val('no');
			newval = 'no';

			
			if(afterstatement != ''){
				var type = (obj.attr('as_type')=='class')? '.':'#';
				$('body').find(type+afterstatement).hide();
			}
		}

		//console.log(newval);

		$('body').trigger('evo_yesno_changed',[newval, obj, key, afterstatement]);
	});

	// @since 4.5.2
	$.fn.evo_elm_change_yn_btn = function(val){
		el = this;
		el.val( val );
		if( val == 'no'){
			el.siblings('.evo_elm').addClass('NO');
		}else{
			el.siblings('.evo_elm').removeClass('NO');
		}
	}
	
// yes no button afterstatement hook @4.6.9
	BB.on('evo_yesno_changed', function(event, newval, obj, key, afterstatement){

		if(afterstatement === undefined) return;
		
		if(newval == 'yes'){
			obj.closest('.evo_elm_row').next().show();
		}else{
			obj.closest('.evo_elm_row').next().hide();
		}
	});
/* block button @4.9.2 */
	BB.on('click','.evoelm_blockbtn',function(e){
		e.preventDefault();
		const $el = $(this);
		var afterstatement = $el.attr('afterstatement');
		let afterstatementObj = afterstatement ? BB.find('.evo_elm_afterstatement.'+ afterstatement): false;
		let newVal = 'yes';	

		if( $el.find('input').val() == 'yes'){			
			$el.find('input').val('no');
			newVal = 'no';
		}else{
			$el.find('input').val('yes');
		}
		$el.find('i.evofz18i').toggleClass('far fa-circle fa fa-circle-check');
		$el.toggleClass('on');

		if( afterstatementObj ) afterstatementObj.toggle();


		$('body').trigger('evo_blockbtn_trigged',[newVal, $el, $el.data('id'), afterstatement]);
	});

// Side panel @4.5.1
	// move the sidepanel to body
		var SP = $('.evo_sidepanel');
		$('.evo_sidepanel').remove();
		BB.append(SP);


// ICON font awesome selector	
	BB.on('click','.evo_icons', function(){

		const el = $(this);
		
		el.evo_open_sidepanel({
			'uid':'evo_open_icon_edit',
			'sp_title':'Edit Icons',
			'content_id': 'evo_icons_data',
			'other_data': el.data('val')
		});
		BB.find('.evo_icons').removeClass('onfocus');
		el.addClass('onfocus');

		BB.find('.evo_settings_icon_box').removeClass('onfocus');
		el.closest('.evo_settings_icon_box').addClass('onfocus');

		return;
	})
	.on('evo_sp_opened_evo_open_icon_edit',function(event, OO){
		BB.evo_run_icon_selector({icon_val : OO.other_data} );
	});

	// when icons sidepanel closed
	BB.on('evo_sp_closed',function(event, SP ){
		if( $(SP).find('.evo_open_icon_edit')){
			BB.find('.evo_settings_icon_box').removeClass('onfocus');
		}
	});		




	$.fn.evo_run_icon_selector = function(options){
		const SP = BB.find('.evo_sp');
		var settings = $.extend({
            icon_val: "",
        }, options );

		var el = SP;
		var icon_on_focus = '';

		el.off('keyup','.evo_icon_search').off('search','.evo_icon_search');;

		var init = function(){
			scrollto_icon();
			icon_on_focus = BB.find('.evo_icons.onfocus');

			// move search to header
			el.find('.evo_icon_search_bar').appendTo( el.find('.evosp_head') );
		}

		var scrollto_icon = function(){
			if( settings.icon_val == '' ) return;
			const icon_in_list = el.find('li[data-v="' +settings.icon_val+ '"]');
				icon_in_list.addClass('selected');
			$('#evops_content').scrollTop( icon_in_list.position().top -100);
		}

		// select an icon
		el.on('click','li',function(){
			icon_on_focus = BB.find('.evo_icons.onfocus');
			var icon = $(this).find('i').data('name');

			el.find('li').removeClass('selected');
			el.find('li[data-v="'+ icon +'"]').addClass('selected');

			var extra_classes = '';
			if( icon_on_focus.hasClass('so')) extra_classes += ' so';

			//console.log(icon);

			icon_on_focus
				.attr({'class':'evo_icons ajde_icons default fa '+icon + extra_classes })
				.data('val', icon)
				.removeClass('onfocus');
			icon_on_focus.siblings('input').val(icon);

			BB.find('.evo_settings_icon_box').removeClass('onfocus');

			el.off('click','li');
			el.evo_close_sidepanel();
		});

		// search icon
		el.on('search','.evo_icon_search',function(){
			el.find('li').show();
			scrollto_icon();
		});
		el.on('keyup', '.evo_icon_search',function(event){
			var keycode = (event.keyCode ? event.keyCode : event.which);
			var typed_val = $(this).val().toLowerCase();

			console.log('e');
			
			el.find('li').each(function(){
				const nn = $(this).data('v');
				const n = nn.substr(3);

				if( typed_val == ''){
					$(this).show();
				}else{
					if( n.includes(typed_val ) ){
						$(this).show();
					}else{
						$(this).hide();
					}
				}				
			});	
		});

		init();
	}

	// remove icon
		$('body').on('click','i.evo_icons em', function(){
			$(this).parent().attr({'class':'evo_icons ajde_icons default'}).data('val','');
			$(this).parent().siblings('input').val('');
		});
	
// select2 dropdown field - 4.0.3 @updated 4.9.8
	// this is deprecating 4.9
	if ( $.isFunction($.fn.select2) ){
		$('.ajdebe_dropdown.evo_select2').select2();

		$('body').on('evo_ajax_complete_eventedit_onload', function(event, OO, data, el){
			$('body').find('.ajdebe_dropdown.evo_select2').each(function(){
				$(this).select2();
			});
		});
	}  

	// Helper to get row and list
    const getRowAndList = $el => ({
        $row: $el.closest('.evo_elm_row'),
        $list: $el.closest('.evo_elm_row').find('.evoelm_sel2_opt_list')
    });

    // Event handlers with event types in selector keys
    const handlers = {
        '.evoelm_sel2_cur_val:click': e => {
            e.stopPropagation();
            const { $list } = getRowAndList($(e.currentTarget));
            $('.evoelm_sel2_opt_list').not($list).hide();
            $list.toggle();
        },
        '.evoelm_sel2_opt:click': e => {
            e.stopPropagation();
            const $option = $(e.currentTarget);
            const { $row, $list } = getRowAndList($option);
            const isMulti = $list.hasClass('mul');
            const key = $option.data('value');
            const text = $option.text();

            if (isMulti) {
                // Multi-select: toggle selection
                $option.toggleClass('selected');
                
                // Collect all selected options
                const selectedKeys = $list.find('.evoelm_sel2_opt.selected')
                    .map((_, opt) => $(opt).data('value'))
                    .get();
                const selectedTexts = $list.find('.evoelm_sel2_opt.selected')
                    .map((_, opt) => $(opt).text())
                    .get();

                // Update hidden input with JSON array
                $row.find('.evoelm_sel2_val').val(selectedKeys.length ? JSON.stringify(selectedKeys) : '');
                
                // Update displayed text
                $row.find('.evoelm_sel2_cur_v').html(selectedTexts.length ? selectedTexts.join(', ') : '');
                
                // Keep dropdown open
            } else {
                // Single-select: existing behavior
                $row.find('.evoelm_sel2_val').val(key);
                $row.find('.evoelm_sel2_cur_v').html(text);
                $option.addClass('selected').siblings().removeClass('selected');
                $list.hide();
            }
        },
        '.evoelm_sel2_search:input search': e => {
            const $input = $(e.currentTarget);
            const { $list } = getRowAndList($input);
            const search = $input.val().toLowerCase();
            $list.find('.evoelm_sel2_opt').each((_, opt) => {
                const $opt = $(opt);
                $opt.toggle(search === '' || $opt.text().toLowerCase().includes(search));
            });
        },
        ':click': e => {
            const $target = $(e.target);
            if ($target.closest('#wp-content-wrap').length > 0) return;
            if (!$target.closest('.evoelm_sel2').length || $target.is('.evoelm_sel2_hide')) {
                $('.evoelm_sel2_opt_list').hide();
            }
        }
    };

    // Bind all events efficiently with multi-event support
    Object.entries(handlers).forEach(([key, handler]) => {
        const [selector, events] = key.split(/:(.+)/); // Split on first colon, events after
        const eventList = events ? events.split(' ') : ['click']; // Split events by space
        BB.on(eventList.join(' '), selector || null, handler); // Join events for .on()
    });

// select 3 - 4.9
	$('body')
	// create new item
		.on('click','.evoelm_sel3_new_item_trig',function(event){	event.preventDefault();
			const elm = $(this).closest('.evoelm_sel3');
			var _data = elm.find('.evoelm_sel3_container').data();
			
			$('body').trigger('evoelm_sel3_new_trig',[ $(this), elm, _data]);
		})
	// show items list
		.on('click','.evoelm_sel3_sel_trig',function(event){	event.preventDefault();
			const elm = $(this).closest('.evoelm_sel3');
			elm.find('.evoelm_sel3_list').toggleClass('show');	
		})
	
	// click on new item from list
		.on('click','.evoelm_sel3_item_trig',function(event){	event.preventDefault();
			const item_id = $(this).data('id');
			const elm = $(this).closest('.evoelm_sel3');			
			
			if( elm.hasClass('sin') ){
				elm.find('input.evoelm_sel3_val').val( item_id );

				let itemName = $(this).clone()    // Clone the element to work with its contents without altering the DOM
				    .children()                   // Select all children of the cloned element
				    .remove()                     // Remove all children from the clone
				    .end()                        // Go back to the clone itself
				    .text()                       // Get the text of what's left (the content before child elements)
				    .trim();

				elm.find('.evoelm_sel3_option_1').html(`
					<div class='evoelm_sel3_sel_trig evofx_1' data-id='`+item_id+`'>`+ itemName +`</div>
					<i class='evoelm_sel3_edit_trig fa fa-pencil evomarr10' data-id='`+item_id+`'></i>
					<i class='evoelm_sel3_del_trig fa fa-times' data-id='`+item_id+`'></i>
				`);
			}

			elm.find('.evoelm_sel3_list').toggleClass('show');
		})	

	// re-load items list
		.on('evoelm_sel3_reload_list', function(event, elm, data){
			const list = $(elm).find('.evoelm_sel3_list');
			if( !list.length ) return;
			
			list.html('');

			if( Object.keys(data).length == 0){

				$(elm).find('.evoelm_sel3_options').addClass('evodni');
				$(elm).find('.evoelm_sel3_sel_trig').addClass('evodni');
				return;
			}

			$(elm).find('.evoelm_sel3_options').removeClass('evodni');
			$(elm).find('.evoelm_sel3_sel_trig').removeClass('evodni');
			list.removeClass('show');

			$.each(data, function(index, val){
				list.append(`
					<span class='evoelm_sel3_item_trig evodfx evopad10-20 evocurp evo_borderb evofx_jc_sb evofx_ai_c ' data-id='`+index +`'>
					`+ val +`<i class='fa fa-trash evoelm_sel3_del_item_trig evocurp evohoop7'></i>
					</span>
				`);
			});
		})

	
	// edit an item
		.on('click','.evoelm_sel3_edit_trig',function(event){	event.preventDefault();
			let elm = $(this).closest('.evoelm_sel3');
			var data = elm.find('.evoelm_sel3_container').data();
			
			$('body').trigger('evoelm_sel3_edit_item',[ $(this), elm, data ]);
		})
	// delete an item from data
		.on('click','.evoelm_sel3_del_item_trig',function(event){	event.preventDefault();
			event.stopPropagation();

			var item_id = $(this).closest('.evoelm_sel3_item_trig').data('id');
			const elm = $(this).closest('.evoelm_sel3');	
			var _data = elm.find('.evoelm_sel3_container').data();
			
			$('body').trigger('evoelm_sel3_del_item_conf_trig',[ elm, item_id, _data]);
		})
	// remove item
		.on('click','.evoelm_sel3_del_trig',function(event){	event.preventDefault();
			let elm = $(this).closest('.evoelm_sel3');		
			
			if( elm.hasClass('sin') ){
				elm.find('.evoelm_sel3_val').val('');

				elm.find('.evoelm_sel3_option_1').html(`
					<div class='evofx_1 evoelm_sel3_sel_trig evohoop7'>`+ elm.find('.evoelm_sel3_options').data('t') +`<i class='fa fa-chevron-down evomarl20'></i></div>
				`);
			}	
		})
	
	;

// Checkbox @4.9
	$('body').on('click', '.evoelm_check_trig', function() {
	    var id = $(this).data('id');
	    var iObj = $(this).find('i');
	    
	    iObj.toggleClass('fa-circle-check fa-circle far fa');
	    $(this).find('input').val(iObj.hasClass('fa-circle-check') ? id : '');
	});


// self hosted tooltips
// deprecating
	$('body').find('.ajdethistooltip').each(function(){
		tipContent = $(this).find('.ajdeToolTip em').html();
		toolTip = $(this).find('.ajdeToolTip');
		classes = toolTip.attr('class').split('ajdeToolTip');
		toolTip.remove();
		$(this).append('<em>' +tipContent +'</em>').addClass(classes[1]);
	});

// @updated 4.9
// tooltips
	$.fn.evo_elm_show_tooltip = function( passed_content, hide_time, targetElement ){
		var el = this;

		if( el.hasClass('show')) return;

		var free = el.hasClass('free') || el.hasClass('evotooltipfree');
	    var content = passed_content !== undefined ? passed_content : el.data('d') || el.attr('title') || '';

	    if (!content) return;

	    var tooltipbox = $('.evo_tooltip_box');
	    var cor = getCoords(targetElement || el[0]);
	    tooltipbox.html(content).removeClass('show L evocenter'); // Reset classes

		var box_height = tooltipbox.height();
	    var box_width = tooltipbox.width();
	    var top = cor.top - 55 - box_height - (free ? 10 : 0);
	    var left;

	    // Base left position
	    if (el.hasClass('evocenter')) {
	        left = cor.left - parseInt(box_width / 2) - 9;
	        tooltipbox.addClass('evocenter');
	    } else if (el.hasClass('L')) {
	        left = cor.left - box_width - 15; // Left-aligned tooltip
	        tooltipbox.addClass('L');
	    } else {
	        left = cor.left + 5; // Default right-aligned
	    }

	    // Apply position and show
	    tooltipbox.css({ 'top': top, 'left': left }).addClass('show');
	    el.addClass('show');

	    // Hide after delay if specified
	    if (hide_time !== undefined) {
	        setTimeout(() => el.evo_elm_hide_tooltip(), hide_time);
	    }
	}
	$.fn.evo_elm_hide_tooltip = function(){
		this.removeClass('show');
		$('.evo_tooltip_box').removeClass('show L evocenter');
	}	

	$('body')
		.on('mouseover','.ajdeToolTip, .colorselector, .evotooltip, .evotooltipfree',function(event){
			event.stopPropagation();

			var relatedTarget = event.relatedTarget;
			var target = $(event.target);
			var target_ = $(this);

			if( target.hasClass('evotooltipfree') && !( target.hasClass('show') ) )  target.evo_elm_show_tooltip();
			if( target.hasClass('evotooltip') && !( target.hasClass('show')) )  target.evo_elm_show_tooltip();					
		})
		.on('mouseout','.ajdeToolTip, .colorselector, .evotooltip, .evotooltipfree',function(event){	
			event.stopPropagation();
			var relatedTarget = $(event.relatedTarget);
			var target = $(this);

			// Don’t hide if moving to tooltip box or its descendants
	        if (relatedTarget.closest('.evo_tooltip_box').length > 0 || 
	        	relatedTarget.closest('.evotooltip, .evotooltipfree').length > 0) {
	            return;
	        }

			//console.log('out');
			if( target.hasClass('evotooltipfree') && target.hasClass('show') ) 	target.evo_elm_hide_tooltip();
			if( target.hasClass('evotooltip') && target.hasClass('show') ) 	target.evo_elm_hide_tooltip();	        
		})
		.on('evoelm_hideall_tooltips',function(){
			$('.evo_tooltip_box').removeClass('show L evocenter');
			$('.evotooltipfree').removeClass('show');
			$('.evotooltip').removeClass('show');
		});



// get coordinates
	function getCoords(elem) { // crossbrowser version
	    var box = elem.getBoundingClientRect();
	    //console.log(box);

	    var body = document.body;
	    var docEl = document.documentElement;

	    var scrollTop = window.pageYOffset || docEl.scrollTop || body.scrollTop;
	    var scrollLeft = window.pageXOffset || docEl.scrollLeft || body.scrollLeft;

	    var clientTop = docEl.clientTop || body.clientTop || 0;
	    var clientLeft = docEl.clientLeft || body.clientLeft || 0;

	    var top  = box.top +  scrollTop - clientTop;
	    var left = box.left + scrollLeft - clientLeft;

	    return { top: Math.round(top), left: Math.round(left) };
	}

// Select in a row	 
	 $('body').on('click','span.evo_row_select_opt',function(){

	 	var O = $(this);
	    var P = O.closest('p.evo_row_select');
	    const multi = P.hasClass('multi') ? true : false;
	    const parentId = P.data('id');
				
		// Handle selection
	    if(multi){
	        O.toggleClass('select');
	    } else {
	        P.find('span.opt').removeClass('select');
	        O.addClass('select');
	    }

		// Update values
	    var val = '';
	    P.find('.opt.select').each(function(){
	        val += $(this).attr('value') + ',';
	    });
	    val = val.substring(0, val.length-1);
	    P.find('input').val(val);

	    // Handle CSF visibility
	    var csfContainer = P.next('.evoelm_CSF');
	    if(csfContainer.length) {
	        csfContainer.find('.evoelm_csf_section').each(function(){
	            var section = $(this);
	            var triggerValues = section.data('values');
	            var shouldShow = false;

	            if(multi) {
	                // For multi-select, show if any selected value matches
	                var selectedValues = val.split(',');
	                shouldShow = triggerValues.some(value => selectedValues.includes(value));
	            } else {
	                // For single select, show if exact match
	                shouldShow = triggerValues.includes(val);
	            }
	            console.log(shouldShow);

	            section.css('display', shouldShow ? 'block' : 'none');
	        });
	    }

	    $('body').trigger('evo_row_select_selected', [P, O.attr('value'), val]);		
	});

//* Date duration selector */
	$(document).on('click', '.evoelm_durationselect_item', function(e) {
        e.stopPropagation();
        
        const $this = $(this);
        const $parent = $this.closest('.evoelm_durationselect_box');
    	const $list = $parent.find('.evoelm_durationselect_list');
        
        // Close all other open lists
	    $('.evoelm_durationselect_list').not($list).hide();
	    $('.evoelm_durationselect_item').not($this).removeClass('evoboxsh1');
	    
	    // Toggle current list
	    if ($list.is(':visible')) {
	        $list.hide();
	        $this.removeClass('evoboxsh1');
	        return;
	    }
        
        var dataVals = $(this).data('vals') || {};
        var currentVal = $(this).data('val') || '0';
        
        var listHtml = `<div class="evoelm_durationselect_list evoposa evobgc3 evoscrollbh evoofh evobr10" style="max-height: 300px;overflow-y:scroll;z-index: 9990;box-shadow: 0px 3px 10px -5px #000; margin-top:10px;"><div class="">`;
        $.each(dataVals, function(key, value) {
            var selectedClass = (key == currentVal) ? ' evoclw evobgclp' : '';
            listHtml += '<div class="evoborderb evopad5-15 evocurp evohoop7' + selectedClass + '" data-val="' + key + '" style="white-space: nowrap;text-overflow: ellipsis;">' + value + '</div>';
        });
        listHtml += '</div></div>';

        if ($list.length === 0) {
            $parent.append(listHtml);
        } else {
            $list.replaceWith(listHtml);
        }
        
        var $newList = $parent.find('.evoelm_durationselect_list');
        $newList.show();
        $(this).addClass('evoboxsh1');
        
        var $selectedItem = $newList.find('.evoclw.evobgclp');
        if ($selectedItem.length) {
            $newList.scrollTop(
                $selectedItem.position().top + 
                $newList.scrollTop() - 
                $newList.height()/2 + 
                $selectedItem.height()/2
            );
        }
    });
    
    $(document).on('click', '.evoelm_durationselect_list div', function(e) {
        e.stopPropagation();
    
	    const $this = $(this);
	    const $parent = $this.closest('.evoelm_durationselect_box');
	    const $display = $parent.find('.evoelm_durationselect_item');
	    const $displayVal = $display.find('.evoelm_durationselect_item_val');
	    const $list = $parent.find('.evoelm_durationselect_list');
	    const $input = $parent.find('input[type="hidden"]');
	    
	    // Update display and input
	    $displayVal.text($this.text());
	    $display.data('val', $this.data('val')).removeClass('evoboxsh1');
	    $input.val($this.data('val'));
	    
	    $list.hide();
    });
    
    $(document).on('click', function(e) {
        const $target = $(e.target);
	    if (!$target.closest('.evoelm_durationselect_box').length) {
	        $('.evoelm_durationselect_list').hide();
	        $('.evoelm_durationselect_item').removeClass('evoboxsh1'); // Ensure shadow class is removed
	    }
    });



// Color picker @+4.5 @updated 4.9
	setup_colorpicker();
	$('body').on('evo_page_run_colorpicker_setup',function(){
		setup_colorpicker();
	});
	function setup_colorpicker(){
		$('body').find('.evo_elm_color').each(function(){
			var elm = $(this);
			const saved_color = elm.siblings('input').val();
			const color_to_use = saved_color || '#888888';


			if( typeof elm.ColorPicker ==='function'){
				elm.ColorPicker({
					onBeforeShow: function(){
						$(this).ColorPickerSetColor( color_to_use );
					},
					onChange:function(hsb, hex, rgb,el){
						elm.css({'background-color':'#'+hex});		
						elm.siblings('.evo_elm_hex').val( hex );
					},onSubmit: function(hsb, hex, rgb, el) {
						elm.css({'background-color':'#'+hex});		
						elm.siblings('.evo_elm_hex').val( hex );
						$(el).ColorPickerHide();

						var _rgb = get_rgb_min_value(rgb, 'rgb');
						elm.siblings('.evo_elm_rgb').val( _rgb );
					}
				});
			}
		});
	}

	function get_rgb_min_value(color,type){
			
		if( type === 'hex' ) {			
			var rgba = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(color);	
			var rgb = new Array();
			 rgb['r']= parseInt(rgba[1], 16);			
			 rgb['g']= parseInt(rgba[2], 16);			
			 rgb['b']= parseInt(rgba[3], 16);	
		}else{
			var rgb = color;
		}
		
		return parseInt((rgb['r'] + rgb['g'] + rgb['b'])/3);			
	}

	// color picker 2
	$.fn.evo_colorpicker_init = function(opt){
		var el = this;
		var el_color = el.find('.evo_set_color');

		var init = function(){
			el.ColorPicker({		
				color: get_default_set_color(),
				onChange:function(hsb, hex, rgb,el){
					set_hex_values(hex,rgb);
				},
				onSubmit: function(hsb, hex, rgb, el) {
					set_hex_values(hex,rgb);
					$(el).ColorPickerHide();

					// trigger
					$('body').trigger('evo_colorpicker_2_submit', [ el, hex, rgb]);
				}		
			});
		} 			

		var set_hex_values = function(hex,rgb){			
			el.find('.evcal_color_hex').html(hex);
			el.find('.evo_color_hex').val(hex);

			fcl = el.evo_is_hex_dark({hex: hex}) ? '000000':'ffffff';
			el_color.css({'background-color':'#'+hex, 'color':'#'+ fcl });		
			
			// set RGB val
			rgb_val = $('body').evo_rgb_process({ data : rgb, type:'rgb',method:'rgb_to_val'});
			el.find('.evo_color_n').val( rgb_val );
		}
		
		var get_default_set_color = function(){
			var colorraw = el_color.css("background-color");						
			var def_color = el.evo_rgb_process({data: colorraw, method:'rgb_to_hex'});	
			return def_color;
		}

		init();
	}
	$('body').on('evo_eventedit_dom_loaded_evo_color',function(event, val){
		$('body').find('.evo_color_selector').each(function(){
			$(this).evo_colorpicker_init();	
		});					
	});
	
// plus minus changer @updated 4.9
	$('body').on('click','.evo_plusminus_change', function(event){

        OBJ = $(this);

        QTY = parseInt(OBJ.siblings('input').val());
        MAX = OBJ.siblings('input').data('max');        
        if(!MAX) MAX = OBJ.siblings('input').attr('max');           

        NEWQTY = (OBJ.hasClass('plu'))?  QTY+1: QTY-1;

        NEWQTY =(NEWQTY <= 0)? 0: NEWQTY;

        // can not go below 1
        if( NEWQTY == 0 && OBJ.hasClass('min') ){    return;    }

        NEWQTY = (MAX!='' && NEWQTY > MAX)? MAX: NEWQTY;
        if( isNaN( NEWQTY ) ) NEWQTY = 0;

        OBJ.siblings('input').val(NEWQTY).attr('value',NEWQTY);

        if( QTY != NEWQTY) $('body').trigger('evo_plusminus_changed',[NEWQTY, MAX, OBJ]);
       
        if(NEWQTY == MAX){
            PLU = OBJ.parent().find('b.plu');
            if(!PLU.hasClass('reached')) PLU.addClass('reached');   

            if(QTY == MAX)   $('body').trigger('evo_plusminus_max_reached',[NEWQTY, MAX, OBJ]);                 
        }else{            
            OBJ.parent().find('b.plu').removeClass('reached');
        } 
    });

// date time picker @4.5.5
	var RTL = $('body').hasClass('rtl');

	// load date picker libs
	_evo_elm_load_datepickers();
	$('body').on('evo_elm_load_datepickers',function(){
		_evo_elm_load_datepickers();
	});
	$('body').on('click','.evo_dpicker',function(){	
		_evo_elm_load_datepickers( true, $(this).attr('id') );
	});

	function _evo_elm_load_datepickers( call = false, OBJ_id){

		
		$('body').find('.evo_dpicker').each(function(){

			var OBJ = $(this);
			if( OBJ.hasClass('dp_loaded')) return;

			const this_id = OBJ.attr('id');
			var rand_id = OBJ.closest('.evo_date_time_select').data('id');			
			var D = $('body').find('.evo_dp_data').data('d');
			var startDO, endDO;

			// set start and end date objects
			if( OBJ.hasClass('start') ){
				var startDO = OBJ;
				var endDO = $('body').find('.evo_date_time_select.end[data-id="'+rand_id+'"]').find('input.evo_dpicker.end');
			}else{
				var startDO = $('body').find('.evo_date_time_select.start[data-id="'+rand_id+'"]').find('input.evo_dpicker.start');
				var endDO = OBJ;
			}

			//console.log( endDO);

			OBJ.addClass('dp_loaded');

			const d = new Date( OBJ.val() );
			var highlightson = false;

			OBJ.datepicker({
				beforeShow: function( input , inst){
					$(inst.dpDiv).addClass('evo-datepicker');
					//console.log(rand_id);
					//console.log(startDO.val() +' '+ endDO.val());
				},
				beforeShowDay: function(date){

					var dates = [startDO.val(), endDO.val() ];

					// Convert start and end dates to Date objects
			        let startDate = new Date(dates[0]);
			        let endDate = new Date(dates[1]);

			        // If start and end dates are not set, return default
        			if (isNaN(startDate) || isNaN(endDate)) return [true, ''];

        			// if start and end are the same date
					if( new Date(dates[0]).toString() ==  new Date(dates[1]).toString())
						 return [true, ''];	


        			// Check if the current date is the start date
			        if (startDate.toDateString() === date.toDateString()) {
			            highlightson = true;
			        }

			        // Check if the current date is the day *after* the end date
			        let endDatePlusOne = new Date(endDate);
			        endDatePlusOne.setDate(endDatePlusOne.getDate() + 1);

			        if (endDatePlusOne.toDateString() === date.toDateString()) {
			            highlightson = false;
			        }

			        // Highlight if the date is within the range (including across months)
			        if (date >= startDate && date <= endDate) {
			            highlightson = true;
			        }



			        if( highlightson ) return [true, 'highlight','tt'];
			        return [true, ''];
				},
				onChangeMonthYear: function(year,month, inst){
					highlightson = false;
				},
				dateFormat: D.js_date_format,
				firstDay: D.sow,
				numberOfMonths: 2,
				altField: OBJ.siblings('input.alt_date'),
				altFormat: OBJ.siblings('input.alt_date_format').val(),
				isRTL: RTL,
				setDate: d,
				onSelect: function( selectedDate , ooo) {

					//var date = new Date(ooo.selectedYear, ooo.selectedMonth, ooo.selectedDay);
					var date = OBJ.datepicker('getDate');

					$('body').trigger('evo_elm_datepicker_onselect', [OBJ, selectedDate, date, rand_id]);

					// update end time					
					if( OBJ.hasClass('start') ){						
						if(endDO.length>0){
							
							endDO.datepicker( 'setDate', date);
							endDO.datepicker( "option", "minDate", date );
						}
					}
				}
			});


			var id_match = ( ( OBJ_id !== undefined && OBJ_id == this_id ) || OBJ_id === undefined )
				? true: false;

			if( call && id_match ) OBJ.datepicker('show');
		});
	}

	
// time picker
	$('body').on('change','.evo_timeselect_only',function(){
		var P = $(this).closest('.evo_time_edit');
		var min = 0;

		min += parseInt(P.find('._hour').val() ) *60;
		min += parseInt(P.find('._minute').val() );

		P.find('input').val( min );
	});

// Upload data files
// @version 4.6.9
	$('body').on('click','.evo_data_upload_trigger',function(event){
		if( event !== undefined ){
			event.preventDefault();
			event.stopPropagation();
		}
		OBJ = $(this);

		const upload_box = OBJ.closest('.evo_data_upload_holder').find('.evo_data_upload_window');
		upload_box.show();

		const msg_elm = upload_box.find('.msg');
		msg_elm.hide();		
	});

	$('body').on('click','.upload_settings_button',function(event){
		//event.preventDefault();
		OBJ = $(this);

		const upload_box = OBJ.closest('.evo_data_upload_window');

		// show form
		upload_box.show();

		const msg_elm = upload_box.find('.msg');
		const form = upload_box.find('form');
		var fileSelect = upload_box.find('input');
		const acceptable_file_type = fileSelect.data('file_type');
		msg_elm.hide();
		
		// when form submitted
		$(form).one('submit',function(event){
			
			event.preventDefault();
			msg_elm.html('Processing').show();

			var files = fileSelect.prop('files');

			if( !files ){
			 	msg_elm.html('Missing File.'); return;
			}
			
			var file = files[0];

			if (!window.File || !window.FileReader || !window.FileList || !window.Blob) {
		      	alert('The File APIs are not fully supported in this browser.');
		      	return;
		    }

		    if( file === undefined ){
		    	msg_elm.html('Missing File.'); return;
		    }
		    if( file.name.indexOf( acceptable_file_type ) == -1 ){
		  		msg_elm.html('Only accept '+acceptable_file_type+' file format.');
		  	}else{
		  		var reader = new FileReader();
			  	reader.readAsText(file);

	            reader.onload = function(reader_event) {
	            	$('body').trigger('evo_data_uploader_submitted', [reader_event, msg_elm, upload_box]);
	            };
	            reader.onerror = function() {
	            	msg_elm.html('Unable to read file.');
	            };
	        }	

	        return false;		
		});
		return true;
	});

	// close upload window
	$('body').on('click','.evo_data_upload_window_close',function(){
		$(this).parent().hide();
	});

// Show a snackbar message for eventON
	$.fn.evo_snackbar = function(options){
		var defaults = { 
			'message':'',
			'msg':'',
			'classnames':'',
			'visible_duration':5000,
		}; 
		const settings = $.extend({}, defaults, options);

		// Validate inputs
	    if (!settings.message && !settings.msg) {
	        console.warn('evo_snackbar: No message provided');
	        return this;
	    }

	    let snackbar = $('#evo_snackbar');
	    if (snackbar.length === 0) {
	        $('.evo_elms').append('<div id="evo_snackbar"></div>');
	        snackbar = $('#evo_snackbar');
	    }

	    // Clear existing timeout and handlers
	    clearTimeout(snackbar.data('timeoutId'));	    
	    snackbar.off('mouseenter.evo_snackbar mouseleave.evo_snackbar');
	    snackbar.removeClass('show hide');
	    snackbar.off('mouseenter mouseleave');

	    // Show snackbar
	    var displayMessage = settings.message || settings.msg;
	    snackbar.html( displayMessage ).attr('class',  settings.classnames);
	    setTimeout(function(){	snackbar.addClass('show');  },200);

	    // Hide function
	    const hideSnackbar = () => {
	        snackbar.addClass('hide').removeClass('show');
	        clearTimeout(snackbar.data('timeoutId'));
	    };

	    // Set initial timeout
	    var timeoutId = setTimeout(hideSnackbar, settings.visible_duration);
	    snackbar.data('timeoutId', timeoutId);

	    // Mouse events
	    snackbar.on({
	        'mouseenter.evo_snackbar': () => clearTimeout(snackbar.data('timeoutId')),
	        'mouseleave.evo_snackbar': () => {
	            const newTimeoutId = setTimeout(hideSnackbar, settings.visible_duration);
	            snackbar.data('timeoutId', newTimeoutId);
	        }
	    });

	    return this;

	}

// Click to copy content to clipboard -- @version 4.9.8
	$('body').on('click','.evo_copycontent',function(){
		$(this).evo_copycontent();
	});
	$.fn.evo_copycontent = function(){
		return this.each(function() {
	        const $el = $(this);
	        const link = decodeURIComponent($el.data('val') || '');

	        if (!link) {
	            console.warn('No content to copy: missing value');
	            return;
	        }

	        if (navigator.clipboard) {
	            navigator.clipboard.writeText(link)
	                .then(() => {
	                    $el.evo_snackbar({ msg: 'Content copied to clipboard' });
	                })
	                .catch(err => {
	                    console.error('Failed to copy: ', err);
	                    $el.evo_snackbar({ msg: 'Failed to copy content' });
	                });
	        } else {
	            // Fallback for older browsers
	            const $temp = $('<textarea>').val(link).appendTo('body').select();
	            try {
	                document.execCommand('copy');
	                $el.evo_snackbar({ msg: 'Content copied to clipboard' });
	            } catch (err) {
	                console.error('Fallback copy failed: ', err);
	                $el.evo_snackbar({ msg: 'Failed to copy content' });
	            }
	            $temp.remove();
	        }
	    });
	}


// lightbox select @updated 4.7.2
	$('body').on('click','.evo_elm_lb_field input',function(event){
		const O = $(this);
		const elm_row = O.closest('.evo_elm_row');

		$('body').find('.evo_elm_lb_on').removeClass('evo_elm_lb_on');
		O.addClass('evo_elm_lb_on');

		extra_class = '';

		POS = O.offset();
		pos_top = POS.top;
		pos_left = POS.left;

		// if menu to show above
		if( $(window).height() < ( POS.top + 220 ) ){
			extra_class = 'above';

			pos_top = pos_top - 260;
		}

		const list = O.closest('.evo_elm_lb_fields').data('d');
		const setvals = O.closest('.evo_elm_lb_fields').data('v');
		//console.log(list);

		lbhtml = "<div class='evo_elm_lb_window "+extra_class+"'><div class='eelb_in'><div class='eelb_i_i'>";

		// check if list has values
		if (typeof list === 'object' && list !== null && typeof list !== 'undefined') {

			$.each( list, function(index, val){
				select = setvals.includes(index) ? 'select':'';
				lbhtml += "<span class='"+select+"' value='"+index+"'>"+val+"</span>";
			});
		}else{
			lbhtml += "<span class='' value='all'>--</span>";
		}
		lbhtml += "</div></div></div>";

		const elm2 = $('body').find('.evo_elms2');

		elm2.html( lbhtml );

		elm2.find('.eelb_in').css({'top':pos_top,'left':pos_left});
		elm2.find('.evo_elm_lb_window').addClass('show');
		
	});

	// close lightbox
		$(window).on('click', function(event) {
			if( !($(event.target).hasClass('evo_elm_lb_field_input')) )
				$('body').find('.evo_elm_lb_window').removeClass('show above').fadeOut(300);
		});
		

	// selecting options in lightbox select field
	$('body')
		.on('click','.eelb_in span',function(){
			const field = $('body').find('.evo_elm_lb_on');
			
			if($(this).hasClass('select')){
				$(this).removeClass('select');
			}else{
				$(this).addClass('select');
			}

			var V = '', Vo = []; 

			$(this).parent().find('span.select').each(function(index){
				V += $(this).attr('value')+',';
				Vo.push( $(this).attr('value') );
			});

			field.val( V ).trigger('change');
			field.closest( '.evo_elm_lb_fields' ).data('v', Vo);

			console.log(Vo);

			$('body').trigger('evo_elm_lb_option_selected',[ $(this), V]);
		})
		.on('click','.evo_elm_lb_window',function(event){
			if( event !== undefined ){
				event.preventDefault();
				event.stopPropagation();
			}
		})
	;

});
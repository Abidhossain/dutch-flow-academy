/**
 * Javascript: Slider for eventon
 * @version  2.1.3
 */


jQuery(document).ready(function($){

	// get calendar shortcode data
	$.fn.evosl_shortcode_data = function() {		
        var ev_cal = $(this);
        return ev_cal.find('.evo_cal_data').data('sc');			
    };
	
// INIT
	  sliderfy_events();

// window resize
	$( window ).resize(function() {
		sliderfy_events();
	});



// EVO Slider code
	function sliderfy_events(){
		$('body').find('.evoslider').each(function(){
			var CAL = $(this);
			var SC = CAL.evosl_shortcode_data();
			//console.log( SC);

			var OUTTER = CAL.find('.evo_slider_slide_out');
			var slides = CAL.find('.eventon_list_event').length;
			var EL = CAL.find('.eventon_events_list');
			const sl_on = CAL.hasClass('evoslON') ? true: false;

			let all_slides_count = 0;
			var slider_move_distance = 0;
			var all_slides_w = 0;
			var slider_h = 0;
			var cal_width = CAL.width();


			// slides visible 
				slides_visible = ('slides_visible' in SC)? SC.slides_visible: 1;

			// slides count
				if( sl_on ) slides = CAL.find('.sl.slide').length;
				var __all_slides = slides + ( slides_visible * 2);
			
			// slider controls		
				if( SC.control_style == 'tb'|| SC.control_style == 'lr'|| SC.control_style == 'lrc'){
					if(CAL.find('.evoslider_dots').length == 0){
						var html = "<span class='evoslider_nav nav prev'><i class='fa fa-angle-left'></i></span>";
						CAL.find('.evo_slider_outter').prepend(html);
						var html = "<span class='evoslider_nav nav next'><i class='fa fa-angle-right'></i></span>";
						CAL.find('.evo_slider_outter').append(html);
						CAL.find('.evosl_footer').append( "<span class='evoslider_dots none'></span>" );
					}
				// bottom slide controls
				}else{

					var html = '';
					html += "<div class='evosl_footer_in'>";
					html += "<span class='evoslider_nav nav prev'><i class='fa fa-angle-left'></i></span>";
					html += "<span class='evoslider_dots none'></span>";
					html += "<span class='evoslider_nav nav next'><i class='fa fa-angle-right'></i></span>";
					html += "</div>";
					
					CAL.find('.evosl_footer').html( html );
				}

			slider_w = OUTTER.width();
			slider_w = cal_width;


			// wrap slides around each event
				if( !sl_on ){
					EL.find('.eventon_list_event').each(function(index){
						var el = $(this);

						var c = el.data('colr');
						el.css('background-color', c);					
						if(!el.parent().hasClass('slide')) 
							el.wrap('<div class="slide sl" data-index="' + ( index + 1) +'"></div>');
						if(!_hex_is_light( c )) el.addClass('sldark');
					});
				}

			// now count all slides including dup
				all_slides_count = CAL.find('.sl').length;
				
			// slide width setting
				var cur_slide_index = parseInt(EL.data('slideindex'));
				if( cur_slide_index === undefined || !cur_slide_index ) cur_slide_index = 1;

				// all verticals
				if( SC.slider_type == 'vertical'){
					
					EL.fadeIn().data('slideindex',1);
					var on_slide_h = 0;

					OUTTER.height(0);

					// set each slide to be as tall as the talleste slide
					var max_height = 0;
					EL.find('.slide').each(function(){
						const hh = $(this).outerHeight(true);
						if( max_height < hh ) max_height = hh;
					});
					EL.find('.sl').each(function(){
						$(this).height( max_height );
					});

					var visible_height = max_height * slides_visible;

					OUTTER.height( visible_height );
					
				
				// all horizontals
				}else{
					var one_slide_w = 0;
					var slider_move_distance = slider_w;

					// slides visible
						if( SC.slider_type == 'micro'){
							slv = parseInt( slider_w/ 125);
							slides_visible = slv;

						}else if( SC.slider_type == 'mini'){
							slv = parseInt( slider_w/ 200);
							slides_visible = slv;
						
						}else if( SC.slider_type == 'multi'){
							// set default slide visible count to 4 for multi slide
							if( SC.slides_visible == 1) SC.slides_visible = slides_visible = 4;
							if( slider_w < 400 && SC.slides_visible > 1)	
								slides_visible =  1;
							if( slider_w > 401 && slider_w < 600 && SC.slides_visible > 2)	
								slides_visible =  2;
							if( slider_w > 601 && slider_w < 800 && SC.slides_visible > 3)	
								slides_visible =  3;
							if( slider_w > 801 && slider_w < 1000 && SC.slides_visible > 4) 
								slides_visible =  4;
						}
					
					
					one_slide_w = parseInt(slider_w/ slides_visible);
					slider_move_distance = one_slide_w;		

					all_slides_w =  all_slides_count * one_slide_w;
					
					visible_width = one_slide_w * slides_visible;

					EL.width( all_slides_w ).fadeIn().data('slideindex', cur_slide_index);
					CAL.find('.sl').width( one_slide_w );
					OUTTER.width(visible_width);
				}

				CAL.find('.evo_loading_bar_holder').remove();
						

			// slider control dots 
				var dots_html = '';
				if( SC.slide_nav_dots == 'yes' ){

					dot_max = slides - 0 ;
					for(var dc = 1; dc <= dot_max; dc++){
						dots_html += "<span class='evosl_dot "+ (dc == cur_slide_index? 'f':'') +"' data-index='"+ dc+"'><em></em></span>";
					}	

					var extra_class = dot_max <1 ? 'none':'';

					CAL.find('.evoslider_dots').html( dots_html).addClass(extra_class);		
				}

			// slide looping
				let _create_duplicates = false;

				if(EL.find('.dup').length == 0 && slides > 1) _create_duplicates = true;

				if ( slides_visible < slides) _create_duplicates = true;

				if( _create_duplicates ){

					let duplicate_count = 0;

					// for each visible slide					
					for( y = 0; y < ( slides_visible ); y++){
						// add duplicated before
						var z = EL.find('.slide').clone().eq( slides - y -1 );
						z.addClass('dup sl').removeClass('slide').data('index', (0 - y) ).attr('data-index', ( 0- y) );
						EL.prepend( z ); duplicate_count++;

						// add duplicates after
						var v = EL.find('.slide').clone().eq( y  );
						v.addClass('dup sl').removeClass('slide').data('index', (slides + 1+y) ).attr('data-index', (slides + 1+y ) );
						EL.append( v ); duplicate_count++;
					}

					EL.data({'slides_visible': slides_visible});

					// adjust slide container width after adding duplicate slides
					all_slides_w =  ( all_slides_count + duplicate_count)  * one_slide_w;
					EL.width( all_slides_w );
				}

				
				go_to_slide_index( cur_slide_index , CAL, true, false, false );					
				

			// set slider data for interaction
				EL.data({
					'slider_move_distance': slider_move_distance,
					'all_slides_w': all_slides_w,
					'slides_visible': slides_visible
				});

				CAL.addClass('evoslON');

			// hide slider controls
				if( SC.slide_hide_control == 'yes')	CAL.find('.evosl_footer').hide();

			// if no events customize no events view
				if( CAL.find('.no_events').length > 0 ){
					CAL.addClass('no_slides');
				}

			// if only showing one slide with many slides visible adjust width
				if( CAL.find('.slide.sl').length == 1 && SC.slides_visible > 1){
					CAL.find('.sl').width( cal_width );
				}
			
		});
	}

// slider works
	$.fn.slider_work = function (options) {
		var slide = {},
		interval = null,
		$el = this;
		slide.$el = this;
		var SC = $el.evosl_shortcode_data();
		var EL = $el.find('.eventon_events_list');
		const slider_outter = $el.find('.evo_slider_outter');
		//var all_slides = $el.find('.eventon_list_event').length;
		var all_slides = EL.find('.slide').length;

		slide = {
			iv: SC.slider_pause,
			running: false,
			init: function(){

				// slider is set to run auto
				if( SC.slide_auto == 'yes'){
					slide.auto();

					// pause on hover
					if( SC.slide_pause_hover == 'yes'){
						$el.on('mouseover','.evo_slider_slide_out', function(){
							slide.pause();
						}).on('mouseout',function(){
							slide.auto();
						});	
					}				
				}

				all_slides -= 1;
			},
			auto: function (){
				clearInterval( interval );

				if( SC.slide_auto == 'yes'){ // if auto slide enabled via shortcode
					interval = setInterval(function(){
						slide.gotoNextSlides();
					}, this.iv );
				}
			},
			resetInterval: function(){
				slide.auto();
			},
			pause: function(){
				clearInterval(interval);
			},
			gotoNextSlides: function(){
				go_to_slide_index( 'next' , $el);
				slide.resetInterval();
			},
			interaction: function(){
				// click on nav arrows
				var slider_inter_area = $el.find('.evo_slider_outter');
				slider_inter_area.on('swiperight', function(event){
					if( !$(event.target).hasClass('evcal_list_a')) return;
					go_to_slide_index( 'prev', $el );
				});

				slider_inter_area.on('swipeleft', function(event, data){
					if( !$(event.target).hasClass('evcal_list_a')) return;
					go_to_slide_index( 'next', $el );			
				});
				

				$el.on('click','.evoslider_nav',function(){
					var direction = $(this).hasClass('next')? 'next':'prev';
					go_to_slide_index( direction, $el );
				});

				// click on control dots
				$el.on('click','.evosl_dot', function(){
					go_to_slide_index( $(this).data('index') , $el);
				});
			},
			
		};

		slide.init();
		slide.interaction();

		
	};
	
	$('body').find('.evoslider').each(function(){
		$(this).slider_work();
	});

// slider control interaction
	// hover over micro slides
		$('.ajde_evcal_calendar.microSlider').on('mouseover','.eventon_list_event', function(){
			O = $(this);
			OUT = O.closest('.evo_slider_outter');
			title = O.find('.evcal_event_title').html();

			p = O.position();

			OUT.append('<span class="evo_bub_box" style="">'+ title +"</span>");
			B = OUT.find('.evo_bub_box');

			l = p.left;
			t = p.top- B.height() -30;

			// adjust bubble to left if event on right edge
			LM = OUT.width();
			tl = p.left + B.width() + O.width();
			if(   tl > LM){
				l = l - B.width() +O.width()-20;
			}

			B.css({'top':t, 'left':l});

			OUT.find('.evo_bub_box').addClass('show');
		}).on('mouseout',function(){
			B = $(this).find('.evo_bub_box').remove();
		});

// go into a focused slide
	function go_to_slide_index(new_slide_index, CAL, instant = false, move_dots = true, initial_call = false ){

		
		var slider = CAL.find('.evo_slider_slide_out');
		var SC = CAL.evosl_shortcode_data();
		var EL = CAL.find('.eventon_events_list');
		

		var all_slides = CAL.find('.slide').length;
		var slides_visible = parseInt(EL.data('slides_visible')) ;
		var __all_slides = CAL.find('.sl').length; // including dup
		var slider_move_distance = EL.data('slider_move_distance');	
		var _do_merge = false;	

		// current slide dat
		var cur_slide_index = parseInt(EL.data('slideindex'));
		var cur_mart = parseFloat(EL.css('margin-top') );
		var cur_slider_height = slider.height();
		const current_first_visible_slide_elm = CAL.find('.slide[data-index="'+ cur_slide_index +'"]');
		var cur_marl = parseFloat(EL.css('margin-left'));

		// new slide data
		var new_marL = new_marT = 0;
		
		
		if( new_slide_index == 'next' || new_slide_index == 'prev'){

			if(  new_slide_index == 'next'){
				
				var new_slide_index = cur_slide_index + 1;
				if( new_slide_index > ( all_slides +1 ) ) new_slide_index = 1;

			}else{
				var new_slide_index = cur_slide_index - 1;
				if( new_slide_index < 0 ) new_slide_index = all_slides;
			}
		}

		const new_first_visible_slide_elm = CAL.find('.sl[data-index="'+ new_slide_index +'"]');
		var _prev_slides_count = new_first_visible_slide_elm.prevAll('.sl').length;
					

		// vertical
		if( SC.slider_type == 'vertical' ){
			
			for (var i = 0; i < (_prev_slides_count ); i++) {
				new_marT += CAL.find('.sl').eq( i ).outerHeight(true);
			}
			new_marT = -1 * new_marT;						
			
		
		// horizontal
		}else{

			
			// calculate width of hidden slides to left
			for (var i = 0; i < _prev_slides_count; i++) {
				//new_marL += CAL.find('.slide').eq( i )[0].getBoundingClientRect().width;
				var ww = CAL.find('.sl').eq( i ).width();
				new_marL += ww;
			}

			new_marL = -1 * new_marL;	
			
		}

		//console.log(  _prev_slides_count+ ' CS'+cur_slide_index +' NS'+ new_slide_index +' SV'+slides_visible+' '+ cur_marl +'/'+new_marL);
		

		EL.data('slideindex', new_slide_index);

		// animation
			if( instant){
				EL.css({
					marginLeft: new_marL,
					marginTop: new_marT,
				});
			}else{
				EL.animate({
					marginLeft: new_marL,
					marginTop: new_marT,
				}, parseInt(SC.slider_speed) , 'easeOutCirc');
			}


		// merge to looping
			setTimeout(function(){
				
				new_marL = new_marT = 0;

				const new_first_visible_slide_elm = CAL.find('.sl[data-index="'+ new_slide_index +'"]');
				var _prev_slides_count = new_first_visible_slide_elm.prevAll('.sl').length  - ( slides_visible - 1);
				var _next_slides_count = new_first_visible_slide_elm.nextAll('.sl').length - ( slides_visible - 1);


				// go to beginning
				//if(  new_slide_index == all_slides && _next_slides_count == 0){
				if( cur_slide_index == all_slides && new_slide_index == ( all_slides + 1) ){

					const new_first_visible_slide_elm = CAL.find('.sl[data-index="'+ 1 +'"]');
					var _prev_slides_count = new_first_visible_slide_elm.prevAll('.sl').length;

					for (var i = 0; i < _prev_slides_count; i++) {
						var ww = CAL.find('.sl').eq( i ).width();
						var hh = CAL.find('.sl').eq( i ).outerHeight(true);
						new_marL += ww;
						new_marT += hh;
					}

					if( SC.slider_type == 'vertical' ){
						new_marL = 0;
					}else{
						new_marT = 0;
					}

					EL.css({
						marginLeft: new_marL  * -1,
						marginTop: new_marT  * -1,
					});
					EL.data('slideindex', 1 );
					new_slide_index = 1;
				}

				// go to end
				if( new_slide_index  == 0 ){

					const new_first_visible_slide_elm = CAL.find('.sl[data-index="'+ all_slides +'"]');
					var _prev_slides_count = new_first_visible_slide_elm.prevAll('.sl').length;

					for (var i = 0; i < _prev_slides_count; i++) {
						var ww = CAL.find('.sl').eq( i ).width();
						var hh = CAL.find('.sl').eq( i ).outerHeight(true);
						new_marL += ww;
						new_marT += hh;
					}

					if( SC.slider_type == 'vertical' ){
						new_marL = 0;
					}else{
						new_marT = 0;
					}

					EL.css({
						marginLeft: new_marL * -1,
						marginTop: new_marT  * -1,
					});

					EL.data('slideindex', ( all_slides ) );
					new_slide_index = all_slides;
				}

			}, parseInt(SC.slider_speed) + 100 );

		// set dot focus
			setTimeout(function(){
				if( move_dots){
					//console.log(new_slide_index);
					CAL.find('.evosl_footer .evosl_dot').removeClass('f');
					CAL.find('.evosl_footer .evosl_dot').eq( new_slide_index -1 ).addClass('f');
				}
			}, parseInt(SC.slider_speed) + 101 );
			
		
	}

// whether an event color is lighter or dark
	function _hex_is_light(color) {
		if( color === undefined ) return false;
	    const hex = color.replace('#', '');
	    const c_r = parseInt(hex.substr(0, 2), 16);
	    const c_g = parseInt(hex.substr(2, 2), 16);
	    const c_b = parseInt(hex.substr(4, 2), 16);
	    const brightness = ((c_r * 299) + (c_g * 587) + (c_b * 114)) / 1000;
	    return brightness > 220;
	}

});
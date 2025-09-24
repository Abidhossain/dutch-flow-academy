/**
 * Admin Script
 * @version  1.0
 */
jQuery(document).ready(function($){
	// date and time picker
		var date_format = $('#evcal_dates').attr('date_format');
		var time_format = ($('body').find('input[name=_evo_time_format]').val()=='24h')? 'H:i':'h:i:A';
	
	// ajax trigs



	// enable disable special member prices
		$('#_evodp_member_pricing').on('click',function(){
			TD = $(this).closest('td');
			if(!$(this).hasClass('NO') ){
				TD.addClass('nomp');
			}else{
				TD.removeClass('nomp');
			}
		});

});
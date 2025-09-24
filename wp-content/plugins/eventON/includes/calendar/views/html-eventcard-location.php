<?php 
/**
 * EventCard location html content
 * @version 4.9.8
 */

$iconLoc = "<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__fai_003', 'fa-map-marker',$evOPT )."'></i></span>";
						
if(!empty($location_name) || !empty($location_address)){
	
	$locationLink = (!empty($location_link))? '<a target="'. ($location_link_target=='yes'? '_blank':'') .'" href="'. evo_format_link($location_link).'">':false;

	
	$btn_data = array(
		'lbvals'=> apply_filters('evotax_more_data', array(
			'lbdata'=> [
				'class'=> 'evo_location_lb_'.$location_term_id,
				'additional_class'=> 'lb_max',
				'title'=> $location_name
			],
			'adata'=>[
				'a'=> 'eventon_get_tax_card_content',
				'end'=> 'client',
				'data'=> [	
					'lang'=> EVO()->lang,		
					'term_id'=> $location_term_id,
					'tax'=>'event_location',
					'load_lbcontent'=>true,
				],
			],			
			'uid'=> 'eventon_get_tax_card_content'
		),'event_location', $location_term_id, $EVENT )
	);
	


	//$loc_more = "<span class='evo_expand_more_btn evo_trans_sc1_1 sm marl10' style='margin-top: -5px;'><i class='fa fa-plus'></i></span>";

	$loc_more_1 = "<div class='padt10'><span class='evo_btn_arr evolb_trigger' {$this->help->array_to_html_data($btn_data)}>". evo_lang('Other Events')  . "<i class='fa fa-chevron-right'></i></span></div>";
	
	echo "<div class='evcal_evdata_row evo_metarow_time_location evorow '>
		
			{$iconLoc}
			<div class='evcal_evdata_cell' data-loc_tax_id='{$EventData['location_term_id']}'>";

			// if location information is hidden
			if( $location_hide){
				echo "<h3 class='evo_h3'>".$iconLoc. evo_lang_get('evcal_lang_location','Location'). "</h3>";
				echo "<p class='evo_location_name'>". EVO()->calendar->helper->get_field_login_message() . "</p>";
			
			}else{
				
				echo "<h3 class='evo_h3 evodfx'>".$iconLoc.($locationLink? $locationLink:''). evo_lang_get('evcal_lang_location','Location').($locationLink?'</a>':'')."</h3>";

				if( !empty($location_name) && !$EVENT->check_yn('evcal_hide_locname') )
					echo "<p class='evo_location_name'>". $locationLink. $location_name . ($locationLink? '</a>':'') ."</p>";



				// for virtual location
				if( $location_type == 'virtual'){
					if( $locationLink) 
						echo "<p class='evo_virtual_location_url'>" . evo_lang('URL:'). $locationLink . ' '. $location_link."</a></p>";
				}else{

					if(!empty($location_address) && $location_address != $location_name ){

						// open address in google maps
						$gmap_link = '';
						if( $EVENT->check_yn('evcal_gmap_link')):
							$encoded_address = urlencode($location_address);
							$gmap_link = "<a href='https://www.google.com/maps?q={$encoded_address}' target='_blank'><i class='fa fa-arrow-up-right-from-square'></i></a>";
						endif;

						echo "<p class='evo_location_address evodfxi evogap10'>". $locationLink . stripslashes($location_address) . ($locationLink? '</a>':'') . $gmap_link . "</p>";
					}
					
				}	

				// location contacts
				if( !empty($loc_phone) || !empty($loc_email)){
					echo "<div class='evopadt5'><p class='evo_location_contact'>". (!empty($loc_phone) ? $loc_phone .' ' :'' ). (!empty($loc_email) ? "<a href='mailto:{$loc_email}'>$loc_email</a>" :'' ) ."</p></div>";
				}

				// location other events button
				if( !EVO()->cal->check_yn('evo_card_loc_btn') ) echo $loc_more_1;										
			}
			echo "</div>
		
	</div>";
}

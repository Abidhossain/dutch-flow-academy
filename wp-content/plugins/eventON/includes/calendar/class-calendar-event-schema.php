<?php
/**
 * Event Schema details
 * @version 4.8
 */

class EVO_Cal_Event_Structure_Schema extends EVO_Cal_Event_Structure_Top{
	// SEO Schema data
	function get_schema($EventData, $_eventcard){
		extract($EventData);
		$EVENT = $this->EVENT;

		//print_r($EventData);

		$__scheme_data = '<div class="evo_event_schema" style="display:none" >';

		$tz = strpos($this->timezone, '-') === false? '+'. $this->timezone : $this->timezone;


		// Start time 
			$_schema_starttime = $_schema_endtime = '';
			if(is_array($start_date_data))
				$_schema_starttime = $start_date_data['Y'].'-'.$start_date_data['n'].'-'.$start_date_data['j'].( !$EVENT->is_all_day()? 'T'.$start_date_data['H'].':'.$start_date_data['i']. $tz. ':00' :'');
			if(is_array($end_date_data))
				$_schema_endtime = $end_date_data['Y'].'-'.$end_date_data['n'].'-'.$end_date_data['j']. ( !$EVENT->is_all_day()? 'T'.$end_date_data['H'].':'.$end_date_data['i'].$tz. ':00':'');

		// Event Status
			$ES = array(
				'cancelled'=>'https://schema.org/EventCancelled',
				'movedonline'=>'https://schema.org/EventMovedOnline',
				'postponed'=>'https://schema.org/EventPostponed',
				'rescheduled'=>'https://schema.org/EventRescheduled',
			);

			$_ES = isset($ES[$_status])? $ES[$_status]: 'https://schema.org/EventScheduled';

		
		// Event details				
			$__schema_desc = !empty($event_excerpt_txt)? $event_excerpt_txt : (isset($EVENT->post_title)? '"'.$EVENT->post_title.'"':'');
			
			if(!empty($event_details)) $__schema_desc = $event_details;
			
			$__schema_desc = str_replace("'","'", $__schema_desc);
			$__schema_desc = str_replace('"',"'", $__schema_desc);
			$__schema_desc = preg_replace( "/\r|\n/", " ", $__schema_desc );

		// attendence mode
			$AM = ucfirst( $EVENT->get_attendance_mode() );
			$_AM = 'https://schema.org/'. $AM .'EventAttendanceMode';
		
		if(!empty($schema) && $schema){	
			// for each schema custom values
			$_schema_data_array = apply_filters('evo_event_schema',array(
				'url'=>array(
					'type'=>'a',
					'attr'=>'href',
					'attrcontent'=> $EVENT->get_permalink()
				),					
				'image'=>array(
					'type'=>'meta',
					'content'=> (!empty($img_src) &&!empty($img_src)? $img_src:'')
				),					
				'startDate'=>array(
					'type'=>'meta',
					'content'=> $_schema_starttime
				),
				'endDate'=>array(
					'type'=>'meta',
					'content'=> $_schema_endtime
				),
				'eventStatus'=>array(
					'type'=>'meta',
					'content'=>  $_ES
				),
				'eventAttendanceMode'=>array(
					'type'=>'meta',
					'itemtype'=> $_AM,
				),
			),$EVENT, $EVENT->ID);


			// for each schema data item
			foreach( $_schema_data_array as $key=>$value){

				$_item_prop = $key;


				$__scheme_data .= "<".(!empty($value['type'])?$value['type']:'meta') ." itemprop='{$_item_prop}' ".(!empty($value['content'])? 'content="'.$value['content'].'"':'') ." ". ( !empty($value['attr'])? $value['attr']."='". $value['attrcontent']."'":'');

				if(!empty($value['itemtype'])) $__scheme_data .= ' itemscope itemtype="'.$value['itemtype'].'"';
				
				$__scheme_data .= ($value['type'] =='meta')? "/>": ">";
				$__scheme_data .= (!empty($value['html'])?$value['html']:'');
				$__scheme_data .= (isset($value['type']) && $value['type'] == 'meta')? '': 
					( isset($value['type'])? "</".$value['type'] .">" :'' ); 
			}
			
			// location data
				if( !empty($location_type) && $location_type =='virtual'){
					$__scheme_data .= '<div style="display:none" itemprop="location" itemscope itemtype="http://schema.org/VirtualLocation">';
					if(!empty($location_link)) $__scheme_data .= '<span itemprop="url">'.$location_link.'</span>';
					$__scheme_data .= "</div>";					
				}

				if(!empty($location_address)){

					$__scheme_data .= '<div style="display:none" itemprop="location" itemscope itemtype="http://schema.org/Place">'. ( !empty($location_name)? '<span itemprop="name">'.$location_name.'</span>':'').'<span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"><item itemprop="streetAddress">'. stripslashes($location_address) .'</item></span></div>';					
				}


			// offer data
				if( $EVENT->get_prop('_seo_offer_price') && $EVENT->get_prop('_seo_offer_currency')){
					$__scheme_data .= '<div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
				        <div class="event-price" itemprop="price" content="'.$EVENT->get_prop('_seo_offer_price').'">'.$EVENT->get_prop('_seo_offer_price').'</div>
				        <meta itemprop="priceCurrency" content="'.$EVENT->get_prop('_seo_offer_currency').'">
				        <meta itemprop="url" content="'.$EVENT->get_permalink().'">
				        <meta itemprop="availability" content="http://schema.org/InStock">
				        <meta itemprop="validFrom" content="'.$_schema_starttime.'">
				    </div>';
				}

		    // organizer data
				if(!empty($event_organizer) ){
					$__scheme_data .= '<div itemprop="organizer" itemscope="" itemtype="http://schema.org/Organization">';
					foreach($event_organizer as $EO_id=>$EO):
						if( empty( $EO->name)) continue;
						$__scheme_data .= '<meta itemprop="name" content="'.$EO->name.'">
				    	'. (!empty($EO->organizer_link)? '<meta itemprop="url" content="'.$EO->organizer_link.'">':'');

					endforeach;
					$__scheme_data .= '</div>';									
				}

			// performer data using organizer data
				if( $EVENT->get_prop('evo_event_org_as_perf') && !empty($event_organizer) ){
					$__scheme_data .= '<div itemprop="performer" itemscope="" itemtype="http://schema.org/Person">';
					foreach($event_organizer as $EO_id=>$EO):
						if( empty( $EO->name)) continue;
						$__scheme_data .= '<meta itemprop="name" content="'.$EO->name.'">';

					endforeach;
					$__scheme_data .= '</div>';	
				}
		}else{
			$__scheme_data .= '<a href="'.$event_permalink.'"></a>';
		}

		// JSON LD
		if(!empty($schema_jsonld) && $schema_jsonld){
			$__scheme_data .= '<script type="application/ld+json">';				
			
			// location
				$_schema_location = ''; 
				
				if(!empty($location_type) && $location_type == 'virtual' || !empty($location_address)){
					$_schema_location .= ',"location":';
				}

				if(!empty($location_type) && $location_type == 'virtual' || !empty($location_address))
					$_schema_location .= '[';

				if(!empty($location_type) && $location_type == 'virtual'){
					$_schema_location .= '{"@type":"VirtualLocation"';
					if(!empty($location_link)) $_schema_location .= ',"url":"'.$location_link.'"';
					$_schema_location .= '}';
				}
				if(!empty($location_address)){

					if(!empty($location_type) && $location_type == 'virtual')
						$_schema_location .= ',';

					if( !empty($location_name) ) 
						$location_name = str_replace('"', "", $location_name);
					$_name = !empty($location_name)? '"name":"'.$location_name.'",':'';
					
					$_schema_location .= '{"@type":"Place",'.$_name.'"address":{"@type": "PostalAddress","streetAddress":"'. str_replace("\,",",", stripslashes($location_address) ).'"}}';
				}
				if(!empty($location_type) && $location_type == 'virtual' || !empty($location_address)){
					$_schema_location .= ']';
				}

			// organizer 
				$_schema_performer = $_schema_organizer = '';
				if(!empty($event_organizer) ){
					$_schema_organizer .= ',"organizer":[';

					$_schema_organizer_data = array();

					foreach($event_organizer as $EO_id=>$EO):
						if( empty( $EO->name)) continue;
						
						$_schema_organizer_content = '{"@type":"Organization","name":"'.$EO->name.'"';
						$_schema_organizer_content .= !empty($EO->organizer_link)? ',"url":"'.$EO->organizer_link. '"' :'';

						$_schema_organizer_content .= "}";
						$_schema_organizer_data[] = $_schema_organizer_content;

					endforeach;
					$_schema_organizer .= implode(',', $_schema_organizer_data);
					$_schema_organizer .= ']';									
				}


			// perfomer data using organizer
				if( $EVENT->check_yn('evo_event_org_as_perf') && !empty($event_organizer)  ){
					
					$_schema_performer .= ',"performer":[';
					$_schema_performer_data = array();

					foreach($event_organizer as $EO_id=>$EO):
						if( empty( $EO->name)) continue;
						$_schema_performer_data [] = '{"@type":"Person","name":"'.$EO->name.'"}';

					endforeach;
					$_schema_performer .= implode(',', $_schema_performer_data);
					$_schema_performer .= ']';
								
				}	

			// offers field
				$_schema_offers = '';
				if( $EVENT->get_prop('_seo_offer_price') && $EVENT->get_prop('_seo_offer_currency')){
					$_schema_offers = ',"offers":{"@type":"Offer","price":"'. $EVENT->get_prop('_seo_offer_price') .'","priceCurrency":"'.$EVENT->get_prop('_seo_offer_currency').'","availability":"http://schema.org/InStock","validFrom":"'.$_schema_starttime.'","url":"'.$EVENT->get_permalink().'"}';
				}

			$__scheme_data .= 
				'{"@context": "http://schema.org","@type": "Event",
				"@id": "event_'. $EVENT->get_event_uniqid().'",
				"eventAttendanceMode":"'. $_AM .'",
				"eventStatus":"'. $_ES .'",
				"name": '.(isset($EVENT->post_title)? '"'.htmlspecialchars( $EVENT->post_title, ENT_QUOTES ) .'"' :'').',
				"url": "'. $EVENT->get_permalink() .'",
				"startDate": "'.$_schema_starttime.'",
				"endDate": "'.$_schema_endtime.'",
				"image":'.(!empty($img_src) &&!empty($img_src)? '"'.$img_src.'"':'""').', 
				"description":"'.$__schema_desc.'"'.
			  	$_schema_location.
			  	$_schema_organizer.
			  	$_schema_performer.
			  	$_schema_offers.			  	
			  	apply_filters('eventon_event_json_schema_adds', '', $EVENT, $EVENT->ID).
			'}';
			$__scheme_data .= "</script>";
		}
		$__scheme_data .= "</div>";

		return $__scheme_data;
	}
} 
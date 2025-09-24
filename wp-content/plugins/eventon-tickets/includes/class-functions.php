<?php
/**
 * TIcket frontend and backend supporting functions
 * @version 2.4
 */
class evotx_functions{
	private $evohelper;
	public $EH;
	
	function __construct(){
		$this->EH = new evo_helper();
	}

// ORDER Related
	// get order status from order ID
		function get_order_status($order_id=''){
			if(empty($order_id)) return false;

			$order = wc_get_order( $order_id );

			if( !$order )return false;

			return $order->get_status();
		}
		function is_order_complete($orderid){
			return ($this->get_order_status($orderid)=='completed')? true: false;
		}
// CHECKING TICKET STATUS related
	// get proper ticket status name I18N
		function get_checkin_status($status, $lang=''){
			global $evotx;
			$evopt = $evotx->opt2;
			$lang = (!empty($lang))? $lang : 'L1';

			if($status=='check-in'){
				return (!empty($evopt[$lang]['evoTX_003x']))? $evopt[$lang]['evoTX_003x']: 'check-in';
			}else{
				return (!empty($evopt[$lang]['evoTX_003y']))? $evopt[$lang]['evoTX_003y']: 'checked';
			}
		}
		function get_statuses_lang($lang=''){
			global $evotx;
			$evopt = $evotx->opt2;
			$lang = (!empty($lang))? $lang : 'L1';

			return array(
				'check-in'=> ((!empty($evopt[$lang]['evoTX_003x']))? $evopt[$lang]['evoTX_003x']: 'check-in'),
				'checked'=> ((!empty($evopt[$lang]['evoTX_003y']))? $evopt[$lang]['evoTX_003y']: 'checked'),
			);
		}

	// check if an order have event tickets @2.2.10
		public function does_order_have_tickets($order_id){
			$order = is_integer($order_id) ? new WC_Order( $order_id ) : $order_id;
			$meta = $order->get_meta( '_tixids');
			return (!empty($meta))? true: false;
		}		

// TICKET related
		// get additional ticket holder array
		// **maybe deprecated
			function get_ticketholder_names($event_id, $ticketholder_array=''){
				if(empty($ticketholder_array)) return false;

				if(!isset($ticketholder_array[$event_id])) return false;

				$ticket_holder = array_filter($ticketholder_array[$event_id]);
				if(empty($ticket_holder)) return false;
				return $ticket_holder;
			}
		// GET product type by product ID
			public function get_product_type($product_id){

				$product = wc_get_product($product_id);
				return $product->get_type();
			}
		
		// alter initial WC order if they are event ticket orders
			function alt_initial_event_order( $order ){
				//$order = new WC_Order( $order_id );	
			    
			    $evtix_update = false;

			    foreach ($order->get_items() as $item) {	

			    	if( !$item ) continue;
			    	if( !is_callable( array( $item, 'get_product' ) )  ) continue;

					$product = $item->get_product();

					if ( ! $product ) continue;

			    	$event_id = $product->get_meta( '_eventid' );  	
			    	if(empty($event_id)) continue;

			    	if(!$evtix_update){
			    		$order->update_meta_data( '_order_type','evotix');
			    		$order->save();
			    		$evtix_update = true;	
			    	}			    	  
			    }
			}
		
		// get ticket item id from ticket id
			function get_tiid($ticket_id){
				$tix = explode('-', $ticket_id);
				return $tix[0];
			}
		
		// corrected ticket IDs
			function correct_tix_ids($t_pmv, $ticket_item_id){
				$tix = explode(',', $t_pmv['tid'][0]);
				foreach($tix as $tt){
					$ticket_ids[$tt] = 'check-in';
				}				
				update_post_meta($ticket_item_id, 'ticket_ids',$ticket_ids);
			}
	

// DEPREACTING 2.2.10

	// FOR an EVENT: return customer tickets array by event id and product id
	// Main function to get all attendees 
	// @ will be deprecating
	// @updated 2.2.5
		function get_customer_ticket_list($event_id, $wcid='', $ri='', $sep_by= 'event_time', $entries = -1, $WP_Arg=''){
			global $post;
			$existing_post = $post;

			$customer_ = array();

			$EVENT = new EVO_Event( $event_id );

			$e_pmv = $EVENT->get_data();
			
			if(empty($wcid)) 
				$wcid = (!empty($e_pmv['tx_woocommerce_product_id']))? $e_pmv['tx_woocommerce_product_id'][0]:null;

			if(!$wcid) return false;
			
			$w_pmv = get_post_custom($wcid);
			$ri_count_active = $this->is_ri_count_active($e_pmv, $w_pmv);

			// get all ticket items matching product id and event id			
			if(empty($WP_Arg)){
				// Meta query
					if(empty($wcid)){
						$meta_query = array(
							array('key' => '_eventid','value' => $event_id,'compare' => '=')
						);
					}else{
						$meta_query = array(
							'relation' => 'AND',
							array('key' => 'wcid','value' => $wcid,'compare' => '='),
							array('key' => '_eventid','value' => $event_id,'compare' => '='),
						);
					}

				// Separate output by order status default values
					if($sep_by=='order_status'){
						$customer_= array('completed'=> 0, 'pending'=>0,'refunded'=>0,'total'=>0,'checked'=>0);
					}
				//print_r($meta_query);
				
				$wp_arg = array(
					'posts_per_page'=> $entries,
					'post_type'=>'evo-tix',
					'meta_query' => $meta_query
				);
			}else{
				$wp_arg  = $WP_Arg;
			}

			$ticketItems = new WP_Query($wp_arg);

			if($ticketItems->have_posts()):
				while($ticketItems->have_posts()): $ticketItems->the_post();
					$tiid = $ticketItems->post->ID;
					$tii_meta = get_post_custom($tiid);

					$order_id = !empty($tii_meta['_orderid'])? $tii_meta['_orderid'][0]: false;
					$orderOK = false; 
					$order_status = $billing_address = $phone = $company = 'n/a';

					if( get_post_status($order_id) === false) continue;				

					if(
						(
							$ri_count_active && 
							((!empty($tii_meta['repeat_interval']) && $tii_meta['repeat_interval'][0]==$ri)
								|| ( empty($tii_meta['repeat_interval']) && $ri==0)
							)
						)
						|| !$ri_count_active 
						|| $ri=='all'
					){

						$evotx_tix = new evotx_tix();

						// return data arranged based on order status
						if($sep_by=='order_status'){
							if(!$order_id) continue;

							$order = new WC_Order( $order_id );
							$order_status = $order->get_status();

							$order_status = (in_array($order_status, array('on-hold','processing') )?'pending': $order_status);
							$customer_[$order_status] = (!empty($customer_[$order_status])? 
								$customer_[$order_status]+$tii_meta['qty'][0]: $tii_meta['qty'][0]);

							// checked tickets value

								$st_count = $evotx_tix->checked_count($ticketItems->post->ID);
								
								if( !empty($st_count['checked']) ){
									$customer_['checked'] = $customer_['checked'] + $st_count['checked'];
								}

							$customer_['total'] = !empty($customer_['total'])? $customer_['total']+$tii_meta['qty'][0]: $tii_meta['qty'][0];

						}elseif($sep_by=='customer_order_status'){
							if(!$order_id) continue;

							$order = new WC_Order( $order_id );
							$order_status = $order->get_status();

							$order_status = (in_array($order_status, array('on-hold','processing') )?'pending': $order_status);
							
							// Get ticket numbers for the post
							$ticketids = $evotx_tix->get_ticket_numbers_by_evotix($ticketItems->post->ID);
							
							$order_ticket_holders = $order->get_meta( '_tixholders' );
							if(!empty($order_ticket_holders))
								$order_ticket_holders = $this->get_ticketholder_names($event_id, $order_ticket_holders);

							// tickets
								$tix = array();
								$uu = 0;
								foreach($ticketids as $tixnumber=>$status){
									$tix[$tixnumber] = array(
										'status'=>$status,
										'name'=> isset($order_ticket_holders[$uu])? $order_ticket_holders[$uu]:''
									);	
									$uu++;
								}

							$customer_[$order_status][$tiid] = array(
								'name'=>$tii_meta['name'][0],
								'tiid'=>$tiid,
								'tids'=>$ticketids,
								'tickets'=>$tix,
								'email'=>$tii_meta['email'][0],
								'type'=>$tii_meta['type'][0],					
								'qty'=>$tii_meta['qty'][0],		
							);

						}else{// seprate by event time
						
							if($order_id){

								$order = new WC_Order( $order_id );
								$order_status = $order->get_status();
								$orderOK = ($order_status=='completed')? true:false;
								$billing_address = '"'.$order->get_billing_address_1().' '.
									$order->get_billing_address_2().' '.
									$order->get_billing_city().' '.
									$order->get_billing_state().' '.
									$order->get_billing_postcode().' '.
									$order->get_billing_country().'"';
								$phone = $order->get_billing_phone();
								$company = $order->get_billing_company();
							}

							// event time for the ticket
							$RI = !empty($tii_meta['repeat_interval'])? $tii_meta['repeat_interval'][0]:0;
							$EVENT->load_repeat();
							$event_time = $EVENT->get_formatted_smart_time();

							// get ticket numbers
							$ticketids = $evotx_tix->get_ticket_numbers_by_evotix($ticketItems->post->ID);

							// tickets			
								$TA = new EVOTX_Attendees();
								$order_ticket_holders = $TA->_get_tickets_for_order($order_id);
								

							$customer_[$event_time][$tiid] = array(
								'name'=>$tii_meta['name'][0],
								'tiid'=>$tiid,
								'tids'=>$ticketids,
								'tickets'=> (isset($order_ticket_holders[$event_id])? $order_ticket_holders[$event_id]:array()),
								'email'=>$tii_meta['email'][0],						
								'type'=>$tii_meta['type'][0],					
								'qty'=>$tii_meta['qty'][0],
								'order_status' =>	$order_status,
								'company'	=>$company,
								'address'=>$billing_address	,
								'phone'=>$phone,
								'postdata'=>get_the_date('Y-m-d'),
								'orderid'=>(!empty($order_id)? $order_id:'')
							);
						}
					}
				endwhile;
				wp_reset_postdata();
			endif;

			// reset wp query to existing post
				if($existing_post){
					$GLOBALS['post'] = $existing_post;
					setup_postdata($existing_post);
				}

			return (count($customer_)>0)? $customer_: false;
		}

		function is_ri_count_active($event_pmv, $woometa=''){
			 return (
				!empty($woometa['_manage_stock']) && $woometa['_manage_stock'][0]=='yes'
				&& !empty($event_pmv['_manage_repeat_cap']) && $event_pmv['_manage_repeat_cap'][0]=='yes'
				&& !empty($event_pmv['evcal_repeat']) && $event_pmv['evcal_repeat'][0] == 'yes' 
				&& !empty($event_pmv['ri_capacity']) 
			)? true:false;
		}

	
	
// SUPPORTIVE
		function get_author_id() {
			$current_user = wp_get_current_user();
	        return (($current_user instanceof WP_User)) ? $current_user->ID : 0;
	    }	
	    function get_event_post_date() {
	        return date('Y-m-d H:i:s', time());        
	    }

	    // get repeat interval of an order item from event time
	    	function get_ri_from_itemmeta($item){

	    		if( isset($item['_event_ri'])) return $item['_event_ri']; // since 1.6.9

	    		$item_meta = (!empty($item['Event-Time'])? $item['Event-Time']: false);
		    	$ri = 0;
		    	
		    	if($item_meta){
		    		if(strpos($item_meta, '[RI')!== false){
		    			$ri__ = explode('[RI', $item_meta);
				    	$ri_ = explode(']', $ri__[1]);
				    	$ri = $ri_[0];
		    		}
		    	}

		    	return $ri;
	    	}

	    // update capacity of repeat instance @2.2.10
			function update_repeat_capacity($adjust, $ri, $event ){


				if( !$event->is_repeating_event() ) return false;

				if( !$event->check_yn('_manage_repeat_cap')) return false;
				if( !$event->get_prop('ri_capacity')) return false;

				
				// repeat capacity values for this event
				$ri_capacity = $event->get_prop('ri_capacity');

				// repeat capacity for this repeat  interval
				$capacity_for_this_event = $ri_capacity[$ri];
				$new_capacity = $capacity_for_this_event + ( (int)$adjust );

				$ri_capacity[$ri] = ($new_capacity>=0)? $new_capacity:0;

				// save the adjusted repeat capacity
				$event->set_meta( 'ri_capacity',$ri_capacity);
				
				return true;
				
			}

	   
}
<?php
/**
 * Ticket Integration with Woocommerce
 * @version 2.4.12
 */

class EVOTX_WC extends EVOTX_Woo_Extrafields{
	public $current_user, $fnc, $opt2, $eotx;

	public function __construct(){

		$this->current_user = wp_get_current_user();

		$this->fnc = new evotx_functions();

		EVO()->cal->load_more( 'evcal_tx');
		$this->eotx = EVO()->cal->get_op('evcal_tx');
		$this->opt2 = EVO()->cal->get_op('evcal_2');

		include_once('class-int-wc-afterorder.php');		
		include_once('class-integration-woocommerce_myaccount.php');

		//parent::__construct();

		new EVOTX_WC_my_account();


		// Register ticket item data in Cart
			add_filter('woocommerce_add_cart_item_data',array($this,'add_item_data'),1,2);
			add_filter('woocommerce_get_cart_item_from_session', array($this,'get_cart_items_from_session'), 1, 3 );

		// ADDING to CART
			add_action('evotx_after_ticket_added_to_cart', array($this, 'add_ticket_data_tocart_session'), 10, 4);
			
			//add_action('woocommerce_after_cart_item_quantity_update', array($this, 'after_cart_q_update'), 20, 4);

		// cart item view
			add_filter('woocommerce_cart_item_product',array($this, 'cart_item_product'),10, 3);
			add_filter('woocommerce_cart_item_class',array($this, 'cart_item_class'),10, 3);
			
			// display custom date in cart
			add_action('woocommerce_get_item_data', array($this, 'WC_get_item_data'), 10, 2);
			add_filter('woocommerce_cart_item_name',array($this,'cart_item_name_box'),1,3);
			//add_filter('woocommerce_cart_item_permalink',array($this,'cart_item_permalink'),1,3);

			// display order details			
			add_filter('woocommerce_order_item_class', array($this, 'order_item_class_names'), 10,3);
			add_action('woocommerce_check_cart_items', array($this, 'cart_validation'), 10);
			add_action('woocommerce_before_calculate_totals', array($this, 'WC_before_total'), 10,1);
			add_action('woocommerce_after_calculate_totals', array($this, 'WC_after_total'), 10,1);
			
			add_filter('woocommerce_store_api_product_quantity_limit', array($this, 'WCAPI_quantity_limit'),10, 2);
			add_filter('woocommerce_cart_item_quantity',array($this,'cart_item_quantity'),15,3);

		// cart modification
			//add_action('woocommerce_after_cart_item_quantity_update',array($this,'remove_user_custom_data_options_from_cart'),1,1);
			//add_action('woocommerce_before_cart_item_quantity_zero',array($this,'remove_ticket_data'),1,1);
			add_filter('woocommerce_cart_emptied', array($this,'remove_ticket_data'), 10, 1 );
			add_filter('woocommerce_remove_cart_item', array($this,'remove_ticket_data'), 10, 2 );
			add_filter('woocommerce_cart_item_removed', array($this,'remove_ticket_data'), 10, 2 );

			// cart updates with quantity changes
			add_filter('woocommerce_update_cart_action_cart_updated', array($this, 'cart_tickets_updated'), 10, 1);

		// checkout
			add_action('woocommerce_checkout_create_order_line_item',array($this,'order_item_meta_update_new'),1,4);
			add_action('woocommerce_store_api_checkout_order_processed', array($this, 'create_evo_tickets_for_store_api'), 10, 1);
			
			add_action('woocommerce_checkout_order_processed', array($this, 'create_evo_tickets'), 10, 3);
			add_action('woocommerce_checkout_order_processed', array($this, 'reduce_stock_at_checkout'), 10, 1);
			add_action('woocommerce_reduce_order_stock', array($this, 'reduce_order_stock_action'), 10, 1);
			//add_action('woocommerce_restore_order_stock', array($this, 'restock_stock'), 10, 1);
	
			
		// Thank you page
			if( !evo_settings_val('evotx_hide_thankyou_page_ticket',$this->eotx) ){
				add_action('woocommerce_thankyou', array( $this, 'wc_order_tix' ), 10 ,1);
			}

			if( !evo_settings_check_yn($this->eotx,'evotx_hide_orderpage_ticket')){
				add_action('woocommerce_view_order', array( $this, 'wc_order_tix' ), 10 ,1);
			}

		// AFTER ORDER			
			// Restock refunded tickets
			foreach(array(
				array('old'=>'processing','new'=>'refunded'),
				array('old'=>'completed','new'=>'refunded'),
				array('old'=>'on-hold','new'=>'refunded'),
			) as $status){
				add_action('woocommerce_order_status_'.$status['old'] .'_to_'. $status['new'], 
					array($this, 'restock_stock_from_orderid'), 10,2);
			}

			// when orders are cancelled
			foreach(array(
				array('old'=>'processing','new'=>'cancelled'),
				array('old'=>'completed','new'=>'cancelled'),
				array('old'=>'on-hold','new'=>'cancelled'),
				array('old'=>'pending','new'=>'cancelled'),
			) as $status){
				add_action('woocommerce_order_status_'.$status['old'] .'_to_'. $status['new'], array($this, 'restock_cancelled_orders'), 10,2);
			}

			// when orders failed
			foreach(array(
				array('old'=>'processing','new'=>'failed'),
				array('old'=>'completed','new'=>'failed'),
				array('old'=>'on-hold','new'=>'failed'),
				array('old'=>'pending','new'=>'failed'),
			) as $status){
				add_action('woocommerce_order_status_'.$status['old'] .'_to_'. $status['new'], 
					array($this, 'restock_failed_orders'), 10,2);
			}

			// when failed orders get processed again
				foreach(array(
					array('old'=>'failed','new'=>'completed'),
					array('old'=>'cancelled','new'=>'completed'),
				) as $status){
					add_action('woocommerce_order_status_'.$status['old'] .'_to_'. $status['new'], 
						array($this, 're_process_order_items'), 10,2);
					add_action('woocommerce_order_status_'.$status['old'] .'_to_'. $status['new'], 
						array($this, 'reduce_stock_from_orderid'), 10,2);
				}

			// when refunded orders were repurchased or completed
			foreach(array(
				array('old'=>'refunded','new'=>'processing'),
				array('old'=>'refunded','new'=>'completed'),
			) as $status){
				add_action('woocommerce_order_status_'.$status['old'] .'_to_'. $status['new'], 
					array($this, 'reduce_stock_from_orderid'), 10,2);
			}

			// WC Action Handler for restocking failed orders
			add_action('evotx_restock_failed_order', array($this, 'process_scheduled_restock'), 10, 1);
			add_action('woocommerce_order_refunded', array($this, 'order_refunded'), 10, 2);

		// EMAILING
			if(empty($this->eotx['evotx_tix_email']) || (!empty($this->eotx['evotx_tix_email']) && $this->eotx['evotx_tix_email']!='yes') ){
				add_action('woocommerce_order_status_completed', array($this, 'send_ticket_email'), 15, 1);	
			}
			add_filter('woocommerce_order_item_name', array($this, 'order_item_name'), 10, 2);		
			add_action('woocommerce_new_order_item', array($this, 'new_order_item_name'), 10, 3);
			add_filter('woocommerce_email_order_meta_fields', array($this, 'order_item_meta_alt'), 10, 3);
			add_action( 'woocommerce_email_after_order_table', array( $this, 'order_details' ), 10, 4 );

		// Order item modified
			add_action('woocommerce_ajax_order_items_removed', array($this, 'order_items_removed'), 10, 4);

		// Auto complete function 
			add_filter('woocommerce_payment_complete_order_status', array( $this, 'autocomplete_orders'), -1 ,2);

			if( EVO()->cal->check_yn('evotx_autocomplete','evcal_tx')){
				$type = EVO()->cal->get_prop('evotx_autocomplete_type','evcal_tx');
				if( !empty($type) && is_array($type)){

					if( in_array('rpay', $type)){
						add_action( 'woocommerce_order_status_processing', array($this,'autocomplete_rpay'),10,3 );
					}
					
					if( in_array('bacs', $type) )
						add_filter('woocommerce_bacs_process_payment_order_status',array($this, 'autocomplete_bacs'), 10, 2);

					if( in_array('cheque', $type) )
						add_filter('woocommerce_cheque_process_payment_order_status',array($this, 'autocomplete_cheque'), 10, 2);

					if( in_array('cod', $type) )
						add_filter('woocommerce_cod_process_payment_order_status',array($this, 'autocomplete_cod'), 10, 2);
				}
			}

		add_filter('woocommerce_default_address_fields', array($this,'address_fields') );
	}

	public function address_fields($fields){
		
		$fields['address_1']['required']    = false;
    	$fields['address_2']['required']    = false;
		//print_r($fields);

		return $fields;
	}

	// CART INIT
		// add ticket item data from AJAX to session
		// deprecating
			function add_item_data($cart_item_data,$product_id){	        
		        
		        if( !empty($_REQUEST['add-to-cart']) &&	$_REQUEST['add-to-cart'] == $product_id && 
		        	isset($_REQUEST['ri']) &&
		        	!empty($_REQUEST['eid'])
		        ){
		        	$new_value = array();

		        	
		        	if(!isset($cart_item_data['evotx_repeat_interval_wc']))
		        		$new_value['evotx_repeat_interval_wc'] = (!empty($_REQUEST['ri'])? $_REQUEST['ri']:0);
		        	
		        	$new_value['evotx_event_id_wc'] = $_REQUEST['eid'];

		        	if(!empty($_REQUEST['eloc'])) $new_value['evotx_elocation'] = urldecode($_REQUEST['eloc']);

		        	// language
		        	if(!empty($_REQUEST['lang'])) $new_value['evotx_lang'] = urldecode($_REQUEST['lang']);

		        	$unique_cart_item_key = uniqid();
		        	$cart_item_data['unique_key'] = $unique_cart_item_key;

		        	//print_r($cart_item_data);

		        	return (empty($cart_item_data))? $new_value: array_merge($cart_item_data,$new_value);

		        }
		        return $cart_item_data;
		    }

	    // get ticket item from session and add to cart object
		    function get_cart_items_from_session($session_data, $values, $key){
			    
		        $cart_session_data = apply_filters('evotx_cart_session_item_values', array(
		        	'evotx_event_id_wc',
		        	'evotx_repeat_interval_wc',
		        	'evotx_elocation',
		        	'evotx_lang'
		        ));

		       	//print_r($values);
		        foreach($cart_session_data as  $meta_key){
		        	if (array_key_exists( $meta_key, $values ) ){
		        		$session_data[$meta_key] = $values[$meta_key];
		        	}
	        	}

	        	// set custom price
	        	// altered prices by ticket addons will be set using filtes in priority order
	        	if(!isset($values['line_total'])) return $session_data;
	        	$alter_ticket_price = apply_filters('evotx_ticket_item_price_for_cart',false, $values['line_total'], $session_data, $values);

	        	// name your price
	        		if( isset($values['evotx_yprice'])){
	        			$alter_ticket_price = $values['evotx_yprice'];
	        		}

	        	if( $alter_ticket_price === false) return $session_data;

	        	$session_data['data']->set_price( $alter_ticket_price );

		        return apply_filters('evotx_get_cart_item_from_session',$session_data,$values, $key);
		    }
	
	// Adding to CART
		function add_ticket_data_tocart_session($cart_item_key, $EVENT, $DATA, $cart_item_data){

			// add ticket data to cart session
			$data = (array)WC()->session->get( '_evotx_cart_data' );
			if ( empty( $data[$cart_item_key] ) ) {
				$data[$cart_item_key] = array();
			}

			if( !is_array($cart_item_data)) $cart_item_data = array();

			// add quantity to cart item data
			if(isset($DATA['qty'])) $cart_item_data['quantity'] = $DATA['qty'];
			if(isset($DATA['event_data']) && isset($DATA['event_data']['wcid'])) 
				$cart_item_data['wcid'] = $DATA['event_data']['wcid'];

			$data[$cart_item_key] = $cart_item_data;
			//print_r($data);

			WC()->session->set( '_evotx_cart_data', $data );
		}
		
	// CART item View
		private $cart_item_event = false;

		// cart product - initially load cart item product data
			public function cart_item_product($cart_item_data, $cart_item, $cart_item_key){

				//print_r($cart_item);
				if(empty($cart_item['evotx_event_id_wc'])) return $cart_item_data;

				$RI = isset($cart_item['evotx_repeat_interval_wc']) ? $cart_item['evotx_repeat_interval_wc']: 0;

				$this->cart_item_event = new EVO_Event( (int)$cart_item['evotx_event_id_wc'], '', $RI );

				return $cart_item_data;
				
			}
		// cart class name
			function cart_item_class($name, $cart_item, $cart_item_key){
				if(empty($cart_item['evotx_event_id_wc'])) return $name;

				return $name .' evo_event_ticket_item';
			}
			// ticekt item meta display			
			function order_item_class_names($name, $item, $order){
				$item_id = $item->get_ID();

				$event_id = wc_get_order_item_meta($item_id ,'_event_id'); 
				if(!$event_id) return $name;

				return $name.' evo_event_ticket_item';
			}

		// display event ticket cart item data @2.4
			public function WC_get_item_data( $item_data, $cart_item){

				if ( isset($cart_item['evotx_event_id_wc']) ) {

					//print_r($cart_item);

					$lang = isset($cart_item['evotx_lang'])? esc_attr( $cart_item['evotx_lang'] ):'L1';
					$event_id = isset($cart_item['evotx_event_id_wc']) ? intval($cart_item['evotx_event_id_wc']): 0;
					$RI = isset($cart_item['evotx_repeat_interval_wc']) ? $cart_item['evotx_repeat_interval_wc']: 0;

					$EVENT = new EVO_Event( $event_id, '', $RI );

					// all extra cart item data
						$extra_fields_array = array(
		            		'event_time' => array(
		            			$this->lang('Event Time'), 
		            			$EVENT->get_formatted_smart_time( ) 
		            		),
		            	);

						// if location present
						if( isset($cart_item['evotx_elocation']) && !empty( $cart_item['evotx_elocation']) ){
							$extra_fields_array['event_location'] = array(
		            			$this->langX('Event Location','evoTX_005c'), 
		            			stripslashes( $cart_item['evotx_elocation'] )
		            		);
						}

						$extra_fields_array = apply_filters('evotx_ticket_item_meta_data', $extra_fields_array , $cart_item, $EVENT);


					// add the extra item data
					foreach( $extra_fields_array as $field=>$val){
						
	            		if(empty($val)) continue;
	            		if(!isset($val[1])) continue;
	            		if(empty($val[1])) continue;
	            		            		
	            		$item_data[] = array( 'key' => $val[0],  'value' => $val[1]    );
	            	}
			    }


			    return $item_data;
			}

		// CART ticket item name
		    function cart_item_name_box($product_name, $values, $cart_item_key ) {

		    	if(!isset($values['evotx_repeat_interval_wc'])) return $product_name;
		    	if( empty($values['evotx_event_id_wc']) ) return $product_name;

		    	$event_id = $values['evotx_event_id_wc'];
		    	$ri = $values['evotx_repeat_interval_wc'];
		    	
		    	// Set global eventon lang
		    		$lang = isset($values['evotx_lang'])? esc_attr( $values['evotx_lang'] ):'L1';
		    		evo_set_global_lang($lang);

		    	// build event object
		    		if( $this->cart_item_event && !empty( $this->cart_item_event->ID ) && $this->cart_item_event->ID ==$event_id ){
		    			$EVENT = $this->cart_item_event;
		    		}else{
		    			$EVENT = new EVO_Event( $event_id, '',$ri);
		    		}
        		
        		$EVENT->set_lang( $lang);

	        	// get the correct event time
	        	$ticket_time = $EVENT->get_formatted_smart_time( );

	        	$event_name = sprintf( '<a href="%s">%s</a>', esc_url( $EVENT->get_permalink() ), get_the_title($EVENT->ID) );


	        	return $event_name;

	        	/*

		        	// legacy ticket meta
		        			        	
		            $return_string = $event_name;
		            $return_string .= "<p class='evotx_item_meta_data_p'><span class='item_meta_data'>";


		            // show other ticket item meta data in cart
		            	$extra_fields_array = apply_filters('evotx_ticket_item_meta_data', array(
		            		'event_time' => array($this->lang('Event Time'), $ticket_time ),
		            		'event_location' => (isset($values['evotx_elocation'])? array($this->langX('Event Location','evoTX_005c'), stripslashes($values['evotx_elocation']) ):''),
		            	), $values, $EVENT);


		            	// if WC based variations add those data
		            	if( !empty( $values['variation']) && is_array( $values['variation'] ) && !empty( $values['variation_id'] ) ){

		            		$variation = new WC_Product_Variation( $values['variation_id'] );

		            		$variation_attributes = $variation->get_attributes();

		            		foreach( $variation_attributes as $key => $value){
		            			$value = $values['variation']['attribute_'. esc_attr( $key ) ];
		            			$extra_fields_array[ $key ] = array( $key, $value );
		            		}
		            		
		            	}

		            	// print out the extra data value for the order item with event ticket
		            	foreach( $extra_fields_array as $field=>$val){
		            		if(empty($val)) continue;
		            		if(!isset($val[1])) continue;
		            		if(empty($val[1])) continue;

		            		$return_string .= '<span class="item_meta_data_'. esc_attr( $field ) .'"><b>'. $val[0]."</b> " . $val[1]. "</span>";
		            	}
		            		            
		            $return_string .= "</span></p>";  
		            
		            return apply_filters('evotx_cart_item_name', $return_string, $EVENT, $values, $cart_item_key);
	            */
		    }
		// Quantity
			function cart_item_quantity($product_quantity, $cart_item_key, $cart_item='' ){
				if(empty($cart_item)) return $product_quantity;
		   		if(empty($cart_item['evotx_event_id_wc']) ) return $product_quantity;
		   		if(!isset($cart_item['evotx_repeat_interval_wc']) ) return $product_quantity;

		   		$event_id = (int)$cart_item['evotx_event_id_wc'];

		   		$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

		   		// if set to sold individually
	   			if( $_product->is_sold_individually()  ) return $product_quantity;

	   			// pluggability
		   			$product_quantity_alt = apply_filters('evotx_cart_item_quantity', false, $_product, $cart_item_key, $cart_item);

		   			if( $product_quantity_alt !== false ) return $product_quantity_alt;


		   		$max_qty = $_product->backorders_allowed() ? '' : $_product->get_stock_quantity();

		   		if( $_product && $_product->is_type('simple')){

		   			$tEvent = new EVOTX_Event( $event_id ,'', $cart_item['evotx_repeat_interval_wc'], $_product->get_id() );

		   			$tix_inStock = $tEvent->has_tickets();

		   			// Set maximum quantity based on the ticket's stock values
		   			$max_qty = $tix_inStock;
		   			if($tix_inStock === false) $max_qty = 0;
		   			if($tix_inStock === true) $max_qty = '';
		   		}

		   		$product_quantity = woocommerce_quantity_input( array(
					'input_name'  => "cart[{$cart_item_key}][qty]",
					'input_value' => $cart_item['quantity'],
					'max_value'   => $max_qty,
					'min_value'   => '0',
				), $_product, false );

		   		return $product_quantity;
		   		
		   	}

		

		// Cart item value modification @2.4
		// woocommerce_before_calculate_totals
			public function WC_before_total( $cart){
				if ( is_admin() && ! defined( 'DOING_AJAX' ) )
			        return;

			    // Loop through cart items
			    foreach ( $cart->get_cart() as $cart_item ) {
			    	if( !isset($cart_item['evotx_event_id_wc'])) continue;
			    	if( !isset($cart_item['evotx_repeat_interval_wc'])) continue;

			    	$event_id = $cart_item['evotx_event_id_wc'];
		    		$ri = $cart_item['evotx_repeat_interval_wc'];

			    	$EVENT = $this->cart_item_event? $this->cart_item_event: 
			    		new EVO_Event( $event_id, '',$ri);


			    	do_action('evotx_cart_item_before_total', $EVENT, $cart_item, $cart);

			    	//print_r($cart_item);

			        $cart_item['data']->set_name( get_the_title($EVENT->ID) );
			    }
			}

			public function WC_after_total($cart){
				
			}

		// WC rest cart max quantity @2.4
			public	function WCAPI_quantity_limit($max, $product){

				if( !WC()->cart || empty( WC()->cart)) return $max;

				foreach ( WC()->cart->get_cart() as $cart_item ) {
					if( !isset($cart_item['evotx_event_id_wc'])) continue;
					if( !isset($cart_item['product_id'])) continue;
					if( $cart_item['product_id'] != $product->get_id() ) continue;

					return apply_filters('evotx_cart_item_max_qty', $max, $product, $cart_item);
				}

				return $max;
			}

		// cart item validation
			function cart_validation(){

				/*
				if (!is_user_logged_in()) {
			        wc_add_notice(__('You must be logged in to purchase tickets.', 'evotx'), 'error');
			        wp_redirect(wc_get_page_permalink('myaccount'));
			        exit;
			    }*/
				
				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

					//print_r($cart_item['evotx_event_id_wc']);
					// if event id and repeat interval missing skip those cart items
					if(empty($cart_item['evotx_event_id_wc'])) continue;
					if(!isset($cart_item['evotx_repeat_interval_wc'])) continue;

					if ( $cart_item['product_id'] > 0 ) {

						$E = new EVOTX_Event( (int)$cart_item['evotx_event_id_wc'],'', (int)$cart_item['evotx_repeat_interval_wc'] );
						$event_meta = get_post_custom($cart_item['evotx_event_id_wc']);
						$product_meta = get_post_custom($cart_item['product_id']);


						// if tickets disabled for events
						if(!$E->check_yn('evotx_tix')){
							WC()->cart->remove_cart_item($cart_item_key);
							wc_add_notice( __('Ticket is no longer for sale!','evotx') );
						
						}else{

							// check for stop selling tickets validation
							$stop_selling = $E->is_stop_selling_now();

							$stock = $E->has_tickets();

							// if there is no stocks or quantity is more than stock
							if(!$stock || $stop_selling){
								
								WC()->cart->remove_cart_item($cart_item_key);
								wc_add_notice( __('Ticket removed from cart, no longer available in stock!','evotx'), 'error' );

							}elseif( $stock < $cart_item['quantity']){
								// if quantity is more than stock update quantity and refresh total
								WC()->cart->set_quantity($cart_item_key, $stock, true);
								wc_add_notice( __('Ticket quantity adjusted to stock levels!','evotx') );
							}
						}
						

						// action hook 
						do_action('evotix_cart_item_validation', $cart_item_key, $cart_item, $cart_item['evotx_event_id_wc'],$event_meta);
					}
					
				}
			}

	// CHECKOUT

		// Woo store API checkout
			public function create_evo_tickets_for_store_api($order) {
			    if (!($order instanceof WC_Order)) {
			        return;
			    }

			    // Prevent duplicate ticket creation
			    if ($order->get_meta('_evotx_tickets_created') === 'yes') {
			        return;
			    }

			    // Call the existing ticket creation function
			    $ET = new evotx_tix();
			    $ET->create_tickets_for_order($order);

			    // Mark order as having tickets created
			    $order->update_meta_data('_evotx_tickets_created', 'yes');
			    $order->save();

			    // Reduce stock
			    if ($order->get_meta('evo_stock_reduced') !== 'yes') {
			        $this->adjust_ticket_var_stock($order->get_id(), 'reduce', 'cart');
			    }

			    // Auto-complete order if configured
			    if (EVO()->cal->check_yn('evotx_autocomplete', 'evcal_tx')) {
			        $order->update_status('completed', __('Order auto-completed for ticket generation (express checkout).', 'evotx'));
    			}

    			// Redirect to ticket holder form if additional data is needed
			    $needs_ticketholder_data = false;
			    foreach ($order->get_items() as $item) {
			        if ($item->get_product()->get_meta('_eventid')) {
			            $needs_ticketholder_data = true;
			            break;
			        }
			    }
			    if ($needs_ticketholder_data) {
			        $order->update_meta_data('_needs_ticketholder_data', 'yes');
			        $order->save();
			        add_filter('woocommerce_get_checkout_order_received_url', function($url, $order) {
			            if ($order->get_meta('_needs_ticketholder_data') === 'yes') {
			                return $order->get_view_order_url();
			            }
			            return $url;
			        }, 10, 2);
			    }
			}

		// add custom data to new order item 
		// this data can be used to access order item data later
		    public function order_item_meta_update_new($item, $cart_item_key, $values, $order){
		       	
		       	if( !isset($values['evotx_event_id_wc'])) return;

		       	// process event data for order item meta
        		$event_id = (int) $values['evotx_event_id_wc'];
        		$ri = (!empty($values['evotx_repeat_interval_wc']))? $values['evotx_repeat_interval_wc']: 0;

        		$EVENT = new EVO_Event( $event_id, '', $ri);
        		
        		$time = $EVENT->get_formatted_smart_time();
        		$ticket_time = ucwords($time); // capitalize the words			

        		$item->add_meta_data( '_event_id' , $values['evotx_event_id_wc'] , true); 
        		$item->add_meta_data( 'Event-Time' , $ticket_time , true); 
        	

        		// saving other order item data	
			        foreach(array(
			        	'evotx_repeat_interval_wc'=> '_event_ri',
			        	'evotx_elocation'=> 'Event-Location',
			        	'evotx_lang'=> '_evo_lang',
			        ) as $kk=>$vv){
			        	if(!isset($values[$kk]) ) continue;
			        	$item->add_meta_data( $vv , $values[$kk] , true); 
			        }

			        // pluggable
		   			do_action('evotx_checkout_create_order_line_item', $item, $cart_item_key, $values, $order);
			}

		// When cart item quantity was set to zero // AKA removed item from cart
			function remove_ticket_data($cart_item_key = null){

				$data = (array)WC()->session->get( '_evotx_cart_data' );

				// if no item is specified delete all item data
				if ( $cart_item_key == null ) {
					WC()->session->set( '_evotx_cart_data', array() );
					return;
				}

				// If item is specified, but no data exists, just return
				if(!isset( $data[$cart_item_key] )) return;

				// restock ticket
				do_action('evotx_cart_ticket_removed', $cart_item_key, $data[$cart_item_key] );
				

				// remove deleted cart item data from ticket cart session
				unset( $data[$cart_item_key] );
				WC()->session->set( '_evotx_cart_data', $data );
			}
		// cart ticket updates
			function cart_tickets_updated($cart_updated){

				// run through each item in cart that are event tickets
				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

					//print_r($cart_item['evotx_event_id_wc']);
					// if event id and repeat interval missing skip those cart items
					if(empty($cart_item['evotx_event_id_wc'])) continue;
					if(!isset($cart_item['evotx_repeat_interval_wc'])) continue;	

					// do action
					do_action('evotx_cart_tickets_updated', $cart_item_key, $cart_item);

				}

				return $cart_updated;
			}

		// create associate evo-tix post when order is completed
			public function create_evo_tickets($order_id, $posted_data, $order ){

				// Ensure we have a valid order
			    if (!$order)    $order = wc_get_order($order_id);
		   		if (!($order instanceof WC_Order))   return;

			    // Prevent duplicate ticket creation
			    if ($order->get_meta('_evotx_tickets_created') === 'yes') {
			        return;
			    }

				$ET = new evotx_tix();
				$ET->create_tickets_for_order( $order );	

				// Mark order as having tickets created
			    $order->update_meta_data('_evotx_tickets_created', 'yes');
			    $order->save();
	
			}

		
		// RESTOCK & REDUCE STOCK 
			function reduce_stock($order){// @dep
				$order_id = $order->get_id();
				$this->adjust_ticket_var_stock($order_id,'reduce');
			}
			function restock_stock_from_orderid($order_id, $order){
				$this->adjust_ticket_var_stock($order_id,'restock');
			}
			function reduce_stock_from_orderid($order_id){
				$order = new WC_Order($order_id);

			    // Check if a restock was scheduled and cancel it if pending
			    if ($order->get_meta('_restock_scheduled') === 'yes') {
			        $timestamp = wp_next_scheduled('evotx_restock_failed_order', array($order_id));
			        if ($timestamp) {
			            wp_unschedule_event($timestamp, 'evotx_restock_failed_order', array($order_id));
			            $order->add_order_note(__('Scheduled ticket restocking cancelled due to order completion.', 'evotx'));
			            $order->delete_meta_data('_restock_scheduled');
			        }
			    }

			    // Proceed with stock reduction (assuming tickets weren’t restocked yet)
			    $this->adjust_ticket_var_stock($order_id, 'reduce');

			}
			function restock_stock($order){// @dep
				$order_id = $order->get_id();
				$this->adjust_ticket_var_stock($order_id,'restock');
			}
			function reduce_stock_at_checkout($order_id){
				$order = wc_get_order($order_id);
			    if (!($order instanceof WC_Order)) {
			        return;
			    }

		    	// Only reduce stock if not already reduced
			    if ($order->get_meta('evo_stock_reduced') !== 'yes') {
			        $this->adjust_ticket_var_stock($order_id, 'reduce', 'cart');
			    }
			}
			function restock_cancelled_orders($order_id){
				$this->adjust_ticket_var_stock($order_id,'restock','cancelled');
			}
			function restock_failed_orders($order_id, $order){

				// Check if auto-restock for failed orders is enabled
				$restock_failed_val = EVO()->cal->check_yn('evotx_restock_failed', 'evcal_tx');

				// Get the grace period setting (in hours: 0, 24, 48, or 72)
			    $grace_period_hours = EVO()->cal->get_prop('evotx_restock_failed_gracetime', 'evcal_tx');
			    $grace_period_hours = $grace_period_hours !== false ? (int)$grace_period_hours : 0; // Default to 0 if not set

			    // If grace period is 0, restock immediately
			    if ($grace_period_hours === 0) {
			        $this->adjust_ticket_var_stock($order_id, 'restock', 'failed');
			        $order->update_meta_data('_tickets_restocked_from_failed', 'yes');
			        $order->save();
			    } else {
			        // Schedule restocking after the grace period
			        $restock_time = time() + ($grace_period_hours * 3600); // Convert hours to seconds
			        if (!wp_next_scheduled('evotx_restock_failed_order', array($order_id))) {
			            wp_schedule_single_event($restock_time, 'evotx_restock_failed_order', array($order_id));
			            $order->add_order_note(sprintf(
			                __('Ticket restocking scheduled in %d hours due to failed order grace period.', 'evotx'),
			                $grace_period_hours
			            ));
			            $order->update_meta_data('_restock_scheduled', 'yes');
			            $order->save();
			        }
			    }
			}

			// Process failed orders after grace period
			// @version 2.4.11
			function process_scheduled_restock($order_id) {
			    $order = wc_get_order($order_id);
			    if (!$order) return;

			    // Check if the order is still in 'failed' status to avoid restocking completed orders
			    if ($order->get_status() !== 'failed') {
			        $order->add_order_note(__('Scheduled ticket restocking skipped: Order status is no longer failed.', 'evotx'));
			        return;
			    }

			    // Perform the restock
			    $this->adjust_ticket_var_stock($order_id, 'restock', 'failed');
			    $order->update_meta_data('_tickets_restocked_from_failed', 'yes');
			    $order->add_order_note(__('Tickets restocked after grace period from failed order.', 'evotx'));
			    $order->save();
			}

		// Adjust ticket stock
		// this will not run for cancelled or failed orders
			function adjust_ticket_var_stock($order_id, $type='reduce', $stage='def', $order=''){
				$order = !empty($order) ? $order : new WC_Order( $order_id );	

				if(sizeof( $order->get_items() ) <= 0) return false;

				// if restocking tickets and auto restock ticket stock is disabled, bail
				// @updated 2.2.2
				if( $type == 'restock' && !EVO()->cal->check_yn('evotx_restock','evcal_tx') ) 
					return false;
				
				// check if the stock was reduced when order placed
					$evo_stock_reduced = $order->get_meta('evo_stock_reduced') =='yes' ? true : false;
					$restock_failed_val = EVO()->cal->check_yn('evotx_restock_failed', 'evcal_tx');
    				$restock_failed = ($stage == 'failed' && $restock_failed_val) ? true : false;

					$proceed = false;	
					
					if(!$evo_stock_reduced) $proceed = true;
					if( $type == 'restock' && $evo_stock_reduced ) $proceed = true;
					if( $type == 'restock' && !$evo_stock_reduced ) $proceed = false;
					if( $type == 'reduce' && $evo_stock_reduced ) $proceed = false;
					if( $type == 'reduce' && !$evo_stock_reduced ) $proceed = true;
					

				// BAIL
				if(!$proceed) return false;
			
				$stock_reduced = false;
				$_order_has_event_tickets = false;

				// each order item in the order
			    	foreach ( $order->get_items() as $item_id=>$item) {

			    		if ( $item['product_id'] > 0 ) {    			
				    		
				    		$event_id = ( isset($item['_event_id']) )? $item['_event_id']:'';
				    		$event_id = !empty($event_id)? $event_id: get_post_meta( $item['product_id'], '_eventid', true);				    		
				    		if(empty($event_id)) continue; // skip non ticket items

				    		$_order_has_event_tickets = true;

				    		$_product = $item->get_product();

				    		$EVENT = new EVOTX_Event($event_id);
				    		
				    		$qty   = (int)$item['qty']; // order ticket quantity
				    		$old_stock = $_product->get_stock_quantity(); // old total ticket quantity
				    		
				    		$item_name = $_product->get_sku() ? $_product->get_sku(): $item['product_id'];

				    		
				    		// REPEATING EVENT
				    		if( $EVENT->is_ri_count_active()){
				    			
				    			$ri = EVOTX()->functions->get_ri_from_itemmeta($item);

				    			// update repeat stock
					    			$qty_adjust = ($type == 'reduce')? $qty * -1: $qty * +1;
				    				EVOTX()->functions->update_repeat_capacity($qty_adjust, $ri, $EVENT );

				    				// NOTICE
									$order->add_order_note( __(sprintf( 
										'Event: (%s) repeat instance capacity changed by %s.', 
										$EVENT->get_title(), $qty_adjust 
									), 'evotx' ));

				    			
				    			// restock ONLY on def or failed stage
				    				if(($stage == 'def' || $restock_failed ) && $type == 'restock' && !empty($new_quantity)){
				    					// adjust product stock
				    					$new_quantity = wc_update_product_stock($_product, $qty, 'increase' );	
				    					
				    					$order->add_order_note( __(sprintf(
				    						'Event: %s ticket capacity increased from %s to %s.',  
				    						$EVENT->get_title(), $old_stock, $new_quantity
				    					),'evotx' ) 
				    					);
				    				}
				    			
								if($type=='reduce') $stock_reduced = true;
				    		// none repeating capacity activated events
				    		}else{

				    			// only for def stage
				    			if($stage == 'def' || $restock_failed ){
				    				
					    			// adjust product stock
					    			$new_quantity = wc_update_product_stock($_product, $qty, ($type == 'reduce')?'decrease':'increase' );	
									
									if(!empty($new_quantity)){
										if($type == 'reduce'){
											$order->add_order_note( __(sprintf( 
												'Event: (%s) ticket capacity reduced from %s to %s.',  
												$EVENT->get_title(), $old_stock, $new_quantity),'evotx') );
										}else{
											$order->add_order_note( __(sprintf( 
												'Event: (%s) ticket capacity increased from %s to %s.', 
												$EVENT->get_title(), $old_stock, $new_quantity),'evotx') );
				 						}
									}
								}							
				    		}
			    		
				    		// pluggable
				    		$stock_reduced = apply_filters('evotx_adjust_orderitem_ticket_stockother', $stock_reduced, $EVENT, $order, $item_id, $item, $type, $stage);			    		
				    	}
			    	}

			    
			    // plug to process ticket addon stocks
			    do_action('evotx_after_order_stock_adjusted', $order, $type, $stage, $_order_has_event_tickets);

			    $stock_reduced = ($type=='reduce')? true:false;

			    $order->update_meta_data( 'evo_stock_reduced',($stock_reduced?'yes':'no'));	
			    $order->save();			
			}

		// reduce order stock after WC action
			function reduce_order_stock_action($order){

			}

		// re process order with order items for tickets
		// updated @2.4.11
			public function re_process_order_items($order_id, $order, $tickets_resold = false){

				$TIXS = new evotx_tix();

				if ($tickets_resold) {
			        // Tickets were resold; mark existing tickets as invalid or adjust order
			        $order->add_order_note(__('Tickets from this order were resold after restocking. New tickets cannot be issued.', 'evotx'));
			        // Optionally, refund the order or notify the customer
			        do_action('evotx_failed_order_resold_tickets', $order);
			    } else {
			        // Normal reprocessing
			        $TIXS->re_process_order_items($order_id, $order);
			    }
			}

	// THANK YOU PAGE
		// show ticket in frontend customer account page, order received page
		public function wc_order_tix($order_id){
			
			$order = new WC_Order( $order_id );

			if(EVOTX()->functions->does_order_have_tickets($order)){
				
				do_action('evotx_checkout_fields_display_orderdetails', $order);

				?><section class='eventon-ticket-details wc_order_details'><?php
				
				// completed orders
				if ( in_array( $order->get_status(), array( 'completed' ) ) ) {

					$evotx_tix = new evotx_tix();
					
					$customer_id = $order->get_customer_id();
					$userdata = get_userdata( $customer_id );

					$order_tickets = $evotx_tix->get_ticket_numbers_for_order( $order );
					
					$email_body_arguments = array(
						'orderid'=>$order_id,
						'tickets'=>$order_tickets, 
						'customer'=>(isset($userdata->first_name)? $userdata->first_name:'').
							(isset($userdata->last_name)? ' '.$userdata->last_name:'').
							(isset($userdata->user_email)? ' '.$userdata->user_email:''),
						'email'=>''
					);

					$wrapper = "-webkit-text-size-adjust:none !important;margin:0;";
					$innner = "-webkit-text-size-adjust:none !important; margin:0;";
					
					?>
					<h2><?php echo evo_lang_get('evoTX_014','Your event Tickets','',$this->opt2);?></h2>

					<div class='evotx_event_tickets_section' style="<?php echo $wrapper; ?>">
					<div class='evotx_event_tickets_section_in' style='<?php echo $innner;?>'>
					<?php
						$email = new evotx_email();
						echo $email->get_ticket_email_body_only($email_body_arguments);

					echo "</div></div>";

					
				
				}elseif($order->get_status() == 'refunded'){
					?>
					<h2><?php echo evo_lang_get('evoTX_014','Your event Tickets','',EVOTX()->opt2);?></h2>
					<p><?php evo_lang_e('This order has been refunded!');?></p>
					<?php
						
				}else{
					?>
					<h2><?php echo evo_lang_get('evoTX_014','Your event Tickets','',EVOTX()->opt2);?></h2>
					<p><?php evo_lang_e('Once the order is processed your event tickets will show here or at my account!');?></p>
					<p><a href='<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>' class='evcal_btn'><?php evo_lang_e('My Account');?></a></p>
					<?php
				}	

				// PLUG
				do_action('evotx_wc_thankyou_page_end', $order);

				?></section><?php		
			}
		}

	// AUTO complete @2.2.9
		function autocomplete_bacs($order_status, $order){
			if( EVOTX()->functions->does_order_have_tickets( $order ) ){
				$order_status = 'completed';
			}
			return $order_status;
		}
		function autocomplete_cod($order_status, $order){
			if( EVOTX()->functions->does_order_have_tickets( $order ) ){
				$order_status = 'completed';
			}
			return $order_status;
		}

		function autocomplete_cheque( $order_status, $order){
			if( EVOTX()->functions->does_order_have_tickets( $order ) ){
				$order_status = 'completed';
			}
			return $order_status;
		}
		function autocomplete_rpay($order_id, $order, $order_status){
			if( EVOTX()->functions->does_order_have_tickets( $order ) ){
				$order->update_status( 'completed' );
			}
		}
		function autocomplete_orders($order_status, $order_id){
			if( EVO()->cal->check_yn('evotx_autocomplete','evcal_tx')){
				
				$type = EVO()->cal->get_prop('evotx_autocomplete_type','evcal_tx');
				if( !empty($type) && is_array($type)){

					
					$order = wc_get_order( $order_id );

					// make sure orders have tickets in them
					if( EVOTX()->functions->does_order_have_tickets( $order ) ){

						if( in_array('bacs', $type) && $order && $order->get_payment_method() =='bacs'){
							$order_status = 'completed';
						}

						if( in_array('cheque', $type) && $order && $order->get_payment_method() =='cheque'){
							$order_status = 'completed';
						}

						if( in_array('cod', $type) && $order && $order->get_payment_method() =='cod'){
							$order_status = 'completed';
						}

						if( in_array('rpay', $type) ){
							if ( $order && 'processing' === $order_status && in_array( $order->get_status(), array( 'pending', 'on-hold', 'failed' ), true ) ) {
								$order_status = 'completed';
							}
						}
					}					
				}

			}

			return $order_status;
		}

	// when order is refunded partially change ticket number status
	// Updated 2.0
		function order_refunded($order_id, $refund_id){

			if(empty($order_id)) return false;

			$order = new WC_Order( $order_id );	
			$items = $order->get_items();

			if ( count( $items ) <= 0 ) return false;

			$order_status = $order->get_status();

			//$ET = new evotx_tix();
			$EA = new EVOTX_Attendees();

			$DD = '';

			$tickets_for_order = $EA->get_tickets_for_order($order_id);

			// save order_item_id => refunded ticket count
			$refunded_tickets = array();

			foreach($tickets_for_order as $ticket_number => $ticket_data){

				$TIX = new EVO_Ticket( $ticket_number );

				
				// if the whole order was refunded = mark every ticket as refunded
				if( $order->get_status() == 'refunded'){

					$TIX->refund();

				}else{

					
					if(!isset($ticket_data['oDD']['_order_item_id'])) continue;

					$order_item_id = (int)$ticket_data['oDD']['_order_item_id'];
					$refunded_qty = -1 * $order->get_qty_refunded_for_item($order_item_id);

					
					// order item id associated to this ticket was refunded
					if( $refunded_qty ){
						
						if(isset($refunded_tickets[ $order_item_id]) ){

							// total refunded order items is less than already marked as refunded tickets
							if( $refunded_tickets[ $order_item_id] < $refunded_qty ){
								$DD .= $ticket_number.'/';
								$DD .= $refunded_qty.'/';
								$refunded_tickets[ $order_item_id] = $refunded_tickets[ $order_item_id] + 1;
								
								$TIX->refund();

							}else{
								$DD .= $ticket_number.'/';
								// set other tickets to ticket status
								$TIX->restock();
							}							

						// not marked as refunded
						}else{
							$refunded_tickets[ $order_item_id] = 1;
							
							$TIX->refund();
						}

					}
				}
			}


		}

	// EMAILING
		function send_ticket_email($order_id){
			$email = new evotx_email();
			// initial ticket email
			$email->send_ticket_email($order_id, false, true);
		}

		// order event link and name
		public function order_item_name($item_name, $item){

			if(!isset($item['product_id'])) return $item_name;	

			// if linking to event page is disabled in settings
			if( EVO()->cal->check_yn('evotx_wc_prodname_link','evcal_tx')) return $item_name;

			$event_id = $item->get_meta('_event_id');	
			$repeat_interval = $item->get_meta('_event_ri');	

			if(!$event_id) return $item_name;

			$EVENT = new EVO_Event($event_id, '', $repeat_interval);

			return sprintf( '<a href="%s">%s</a>', $EVENT->get_permalink(), $EVENT->get_title() );
		}
		public function new_order_item_name($item_id, $item, $order_id){

		}

		function order_item_meta_alt($array){
			$updated_array = $array;
			foreach($array as $index=>$field){
				if( isset($field['label'])){
					if( strpos($field['label'], 'Event-Time') !== false){
						$updated_array[$index]['label'] = str_replace('Event-Time', 
							$this->lang('Event Time') , $field['label']);						
					}
					if( strpos($field['label'], 'Event-Location') !== false){
						$updated_array[$index]['label'] = str_replace('Event-Location', 
							$this->langX('Event Location','evoTX_005c') , $field['label']);						
					}
				}
			}
			return $updated_array;
		}

		// show additional ticket holders in WC email_body_arguments
		function order_details($order, $sent_to_admin = false, $plain_text = false, $email = ''){

			// if set to not show ticekt holder details on woo emails @2.4.11
			if( EVO()->cal->check_yn('evotx_hide_email_tix_holder','evcal_tx')) return;


			$TA = new EVOTX_Attendees();
			$ticket_holders = $TA->_get_tickets_for_order($order->get_id(), 'event');

			if(!$ticket_holders) return false;
			if(sizeof($ticket_holders) < 1 ) return false;

				// print styles
				$TA->__print_ticketholder_styles();
			?>
			<div style='margin-bottom:40px'>
			<h2><?php evo_lang_e('Ticket Holder Details');?></h2>
			<table class="shop_table ticketholder_details" style='width:100%; border:1px solid #e5e5e5' cellpadding="0" cellspacing="0">
				<?php 

				foreach($ticket_holders as $e=>$dd){
        			?><tr><td style='border:1px solid #e5e5e5; padding:0;'><?php
        			foreach($dd as $tn=>$nm){ 
						echo $TA->print_one_ticketholder_foremail($tn, $nm, array(
							'orderStatus'=>$order->get_status(),								
						));
					}
        			?></td></tr><?php
        		}?>					
			</table>
			</div>

			<?php
		}

	// Order item modified
		public function order_items_removed($item_id, $item, $changed_stock, $order){

			$event_id = $item->get_meta('_event_id');
			if(!$event_id) return;

			$TIXS = new evotx_tix();
			$evotix_post_id = $TIXS->get_evotix_id_by_order_item_id($item_id);

			// if evo-tix post exists for the order item id > trash the post
			if($evotix_post_id){
				wp_trash_post( $evotix_post_id );
			}

		}


	// get language fast for evo_lang
		function lang($text){	return evo_lang($text, '', EVOTX()->opt2);}
		function langE($text){ echo $this->lang($text); }
		function langX($text, $var){	return eventon_get_custom_language(EVOTX()->opt2, $var, $text);	}
		function langEX($text, $var){	echo eventon_get_custom_language(EVOTX()->opt2, $var, $text);		}

}
new EVOTX_WC();
<?php
/**
 * Ticket extension of the event
 * @version 2.4.6
 */

//evotx_event

class EVOTX_Event extends EVO_Event{

	public $wcid, $wcmeta, $product, $ri, $product_id;
	public function __construct($event_id, $event_pmv='', $RI=0, $wcid='', $load_product_meta = true ){
		parent::__construct($event_id, $event_pmv, $RI );

		// setting up wc product and data
		if( empty($wcid)){
			$this->wcid = $this->get_wcid();
			if( !$this->wcid) return;
		}else{
			$this->wcid = $wcid;
		}

		if( is_numeric( $this->wcid ) && $this->wcid > 0 ){
			$this->product = wc_get_product( $this->wcid );
		}else{
			$this->product = $this->wcid;
			$this->wcid = $this->product->get_id();
		}

		$this->product_id = $this->wcid;
			
		//$GLOBALS['product'] = $this->product;
	}

	function get_wcid(){
		return $this->get_prop('tx_woocommerce_product_id')? (int)$this->get_prop('tx_woocommerce_product_id'):false;
	}
	
	// get repeat with stocks available
	function next_available_ri($current_ri_index, $cut_off = 'start'){
		$current_ri_index = empty($current_ri_index)? 0:$current_ri_index;

		if(!$this->is_ri_count_active()) return false;
		
		// if all stocks are out of stock
		$stock_status = $this->get_ticket_stock_status();
		if($stock_status=='outofstock') return false;


		// check repeats
		$repeats = $this->get_repeats();
		if(!$repeats) return false;
	
		$current_time = EVO()->calendar->get_current_time();

		$return = false;
		foreach($repeats as $index=>$repeat){
			if($index<= $current_ri_index) continue;

			$ri_start = $this->__get_tz_based_unix( $repeat[0] );
			$ri_end = $this->__get_tz_based_unix( $repeat[1] );

			// check if start time of repeat is current
			if($cut_off == 'start' && ( $ri_start ) >=  $current_time) $return = true;
			if($cut_off != 'start' && ( $ri_end ) >=  $current_time) $return = true;

			if($return){

				$ri_stock = $this->get_repeat_stock($index);
				if($ri_stock>0) return array('ri'=>$index, 'times'=>$repeat);
			}				
		}
		
		return false;
	}

// wc product meta
	
	function is_product_manage_stock(){
		return $this->product ? $this->product->get_manage_stock() : false;
	}

	function get_product_stock_status(){
		return $this->product ? $this->product->get_stock_status() : false;
	}
	function get_product_stock(){
		return $this->product ? ( $this->product->get_stock_quantity() ? $this->product->get_stock_quantity() : false ) : false;
	}
	function get_product_sku(){
		return $this->product ? $this->product->get_sku() : false;
	}
	function get_product_total_sales(){
		return $this->product ? $this->product->get_total_sales() : false;
	}
	function get_product_price(){ return $this->product ? $this->product->get_price() : false; }
	
	function get_product_regular_price(){
		if( !$this->product ) return false;

		$price = strip_tags( wc_price( $this->product->get_regular_price() , array('currency'=>'x')) );
		
		return $price;
	}
	function get_product_sale_price(){ 
		if( !$this->product ) return null;
		$sale_price = $this->product->get_sale_price();
		return $sale_price !== '' ? floatval($sale_price) : null;

		$price = strip_tags( wc_price( $this->product->get_sale_price() , array('currency'=>'x')) );		
		return $price;
	}
	public function get_product_type(){
		return ( $this->product ) ? $this->product->get_type() : false;
	}
	// @2.4
	function get_product_description(){
		return ( $this->product ) ? $this->product->get_description() : false; 
	}
	function wc_is_type($type){
		return $this->product ? $this->product->is_type($type) : false;
	}

	function get_product_meta( $field ){
		return $this->product ? $this->product->get_meta( $field ): false;
	}

	function get_wc_prop($field, $def = false){

		if( !$this->product) return false;

		switch ($field) {
			case '_sku':	return $this->product->get_sku() ? $this->product->get_sku() : $def;		break;
			case '_manage_stock':	return $this->product->managing_stock() ? $this->product->managing_stock() : $def;		break;
			case '_stock':	return $this->product->get_stock_quantity() ? $this->product->get_stock_quantity() : $def;		break;
			case '_stock_status':	return $this->product->get_stock_status() ? $this->product->get_stock_status() : $def;		break;
			case '_sold_individually':	return $this->product->is_sold_individually() ? $this->product->is_sold_individually() : $def;		break;
			
		}

		$value = $this->get_product_meta( $field );

		return $value ? $value : $def;
	}

	// @since 2.4.6
	function get_product_adjusted_stock(){
		$setCap = $this->get_wc_prop('_manage_stock');
		$setCapVal = $this->get_wc_prop('_stock');
		$managRIcap = $this->check_yn('_manage_repeat_cap');
		$riCap = $this->get_prop('ri_capacity');

		$ri = $this->ri;

		// if managing capacity per each ri
		if($managRIcap && $riCap){
			return isset( $riCap[$ri] ) ? $riCap[$ri] : 0;
		// if total capacity limit for event
		}elseif($setCap && $setCapVal){
			return $setCapVal ?: 0;
		}else{
			return 0;
		}
	}
	

// Cart - output - array
// @1.7.2
	function add_ticket_to_cart($DATA){
		if(!isset($DATA)) return false;

		$default_ticket_price = $this->product->get_price();

		$cart_item_keys = false;
		$status = 'good'; $output = '';

		$qty = isset($DATA['qty']) ? intval($DATA['qty']) : 1; // Sanitize quantity

		// hook for ticket addons
		$plug = apply_filters('evotx_add_ticket_to_cart_before',false, $this,$DATA);
		if($plug !== false){	return $plug;	}

		// load location information
			$loc_data = $this->get_location_data();
			$location_name = isset($loc_data) && isset($loc_data['location_name']) ? $loc_data['location_name']: '';
		
		// gather cart item data before adding to cart
			$_cart_item_data_array = array(
				'evotx_event_id_wc'			=> $this->ID,
				'evotx_repeat_interval_wc'	=> $this->ri,
				'evotx_elocation'			=> $location_name,
				'evotx_lang'              => isset($DATA['l']) ? sanitize_text_field($DATA['l']) : 'L1'
			);

			 // Handle "Name Your Price" functionality
			if( isset($DATA['nyp'])){

				if( $this->check_yn('_name_yprice')){
					$min_nyp = $this->get_prop('_evotx_nyp_min');

					if( $DATA['nyp'] < $min_nyp ){
						wp_send_json( array(
							'msg'=> __('Your price is lower than minimum price','evotx'), 
							'status'=> 'bad'
						));
						wp_die();
					}
				}else{
					wp_send_json( array(
						'msg'=> __('Name your price is not enabled','evotx'), 
						'status'=> 'bad' 
					));
					wp_die();
				}

				$_cart_item_data_array['evotx_yprice'] = floatval($DATA['nyp']);;
			} 
			
			$cart_item_data = apply_filters('evotx_add_cart_item_meta', $_cart_item_data_array, $this, $default_ticket_price, $DATA);

		// Add ticket to cart
			if( is_array($cart_item_data)  ){
				$cart_item_keys = WC()->cart->add_to_cart(
					$this->wcid,
					apply_filters('evotx_add_cart_item_qty',$qty, $this, $default_ticket_price, $DATA),
					0,
					array(),
					$cart_item_data
				);
			// if filter pass cart item keys
			}else{
				$cart_item_keys = $cart_item_data;
			}

			if($cart_item_keys){
				
				// get total cart quantity for this item
				$DATA['cart_qty'] = WC()->cart->cart_contents[ $cart_item_keys ]['quantity'];
				do_action('evotx_after_ticket_added_to_cart', $cart_item_keys, $this, $DATA, $cart_item_data);
			}


		// Successfully added to cart
		if($cart_item_keys !== false){
			$tx_help = new evotx_helper();
			$output = $tx_help->add_to_cart_html();
			$msg = evo_lang('Ticket added to cart successfully');
		}else{
			$status = 'bad';
			$msg = evo_lang('Could not add ticket to cart, please try later!');
		}

		wp_send_json( apply_filters('evotx_ticket_added_cart_ajax_data', array(
			'msg'=>$msg, 
			'status'=> $status,
			'html'=>$output,
			't'=>$DATA
		), $this, $DATA));
		wp_die();

	}

	// check if an event ticket is already in cart 
	function is_ticket_in_cart_already(){

		if( !function_exists('WC')) return;
		if( empty( WC()->cart )) return;

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

			// if event id and repeat interval missing skip those cart items
			if(empty($cart_item['evotx_event_id_wc'])) continue;
			if(!isset($cart_item['evotx_repeat_interval_wc'])) continue;

			if ( $cart_item['product_id'] > 0 ) {

				if( $cart_item['evotx_event_id_wc'] == $this->ID && $cart_item['evotx_repeat_interval_wc'] == $this->ri) return true;
			}
		}

		return false;
	}

// Event Repeat & Stock
	function get_repeat_stock($repeat_index = 0){
		if(!$this->is_ri_count_active()) return false;

		$ri_capacity = $this->get_prop('ri_capacity');
		if(!isset( $ri_capacity[$repeat_index] )) return 0;
		return $ri_capacity[$repeat_index];
	}

// tickets	
	
	// return is there are tickets for sale remaining @2.2.10
	function has_tickets(){
		// check if tickets are enabled for the event
			if( !$this->check_yn('evotx_tix')) return false;

		if( !$this->product ) return false;


		// if tickets set to out of stock 
			if( $this->product->get_stock_status() && $this->product->get_stock_status() == 'outofstock') return false;
		
		// if manage capacity separate for Repeats
		if( $this->is_ri_count_active() ){
			$ri_capacity = $this->get_prop('ri_capacity');
				$capacity_of_this_repeat = 
					(isset($ri_capacity[ $this->ri ]) )? 
						$ri_capacity[ $this->ri ]
						:0;
				return ($capacity_of_this_repeat==0)? false : $capacity_of_this_repeat;
		}else{
			// check if overall capacity for ticket is more than 0
			$manage_stock = $this->product->managing_stock() ? true:false;
			$stock = $this->product->get_stock_quantity();
			$stock_count = $stock > 0 ? $stock : false;
			
			// return correct
			if($manage_stock && !$stock_count){
				return false;
			}elseif($manage_stock && $stock_count){	return $stock_count;
			}elseif(!$manage_stock){ return true;}
		}
	}

	function is_ticket_active(){
		if( !$this->check_yn('evotx_tix')) return false;
		if(!$this->wcid) return false;
		return true;
	}
		
	// check if tickets can be sold based on event start/end time with current time
	// @2.2.9
	function is_stop_selling_now(){
		$stop_sell = $this->get_prop('_xmin_stopsell');
		
		EVO()->cal->set_cur('evcal_tx');
		$stopsellingwhen = EVO()->cal->get_prop('evotx_stop_selling_tickets');
		$stopsellingwhen = $stopsellingwhen && $stopsellingwhen == 'end'? 'end':'start';

		
		$event_unix = $this->get_event_time( $stopsellingwhen );			
		$timeBefore = $stop_sell ? (int)($this->get_prop('_xmin_stopsell'))*60 : 0;	

		$cutoffTime = $event_unix -$timeBefore;

		return ($cutoffTime < time() )? true: false;
		
	}

	// check if the stock of a ticket is sold out
	// @added 1.7
	function is_sold_out(){

		return $this->get_product_stock_status() && $this->get_product_stock_status() == 'outofstock' ? true : false;
	}

	// check if ticket is sold individually - @since 2.2
	function is_sold_individually(){
		$val = $this->get_wc_prop( '_sold_individually');
		return ($val == 'yes') ? true : false;		
	}

	// show remaining stop or not
	// @added 1.7 @~ 1.7.2
		function is_show_remaining_stock($stock = ''){

			$tickets_in_stock = $this->has_tickets();

			if(!$this->wc_is_type('simple')) return false;
			if(is_bool($tickets_in_stock) && !$tickets_in_stock) return false;


			if(
				$this->check_yn('_show_remain_tix') &&
				$this->get_wc_prop('_manage_stock') &&
				$this->get_wc_prop('_stock') &&
				$this->get_product_stock_status() == 'instock'
			){


				// show remaining count disabled
				if(!$this->get_prop('remaining_count')) return true;

				// show remaing at set but not managing cap sep for repeats
				if( $this->get_prop('remaining_count') && !$this->check_yn('_manage_repeat_cap') && (int)$this->get_prop('remaining_count') >= $this->get_wc_prop('_stock') ) return true;

				if( $this->get_prop('remaining_count') && $this->check_yn('_manage_repeat_cap') && (int)$this->get_prop('remaining_count') >= $stock ) return true;

				return false;
			}
			return false;
		}

// Attendees
	function has_user_purchased_tickets($user_id =''){
		if( !is_user_logged_in()) return false;
		if(!$this->wcid) return false;

		// if user id is not provided
		if(empty($user_id)){
			$current_user = wp_get_current_user();
			$user_id = $current_user->ID;
		}

		// check customer tickets
		$AA = array(
			'posts_per_page'=>-1,
			'post_type'=>'evo-tix',
			'meta_query'=> array(
				'relation'=>'AND',
				array(
					'key'=>'_eventid',
					'value'=> $this->ID,
				),array(
					'key'=>'wcid',
					'value'=> $this->wcid,
				),array(
					'key'=>'_customerid',
					'value'=> $user_id,
				)
			)
		);

		// if manage repeat stock by repeat count active
		if( $this->is_ri_count_active() ){
			$AA['meta_query'][] = array(
				'key'=>'repeat_interval',
				'value'=> $this->ri,
			);
		}

		$TT = new WP_Query($AA);

		$bought = false;

		if( $TT->have_posts()){

			foreach($TT->posts as $P){

				$order_id = get_post_meta($P->ID, '_orderid',true);
				$order = new WC_Order( $order_id );
				
				if( $order->get_status() != 'completed') continue;

				$bought = true;
			}
		}

		if($bought) return true;

		return false;
		//wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product->get_id() )
	}

	// get ticket post id by user id
		public function get_ticket_post_id_by_uid($uid){
			if(!$uid) return false;
			$uid = (int)$uid;

			$II = new WP_Query(array(
				'posts_per_page'=>1,
				'post_type'=>'evo-tix',
				'meta_query'=>array(
					'relation' => 'AND',
					array(	'key'	=> '_eventid','value'	=> $this->ID	),					
					array(	'key'	=> '_customerid','value'	=> $uid	),
					array(
						'relation' => 'OR',
						array(	'key'	=> 'repeat_interval','value'	=> $this->ri	),
						array(	'key'	=> 'repeat_interval','compare'	=> 'NOT EXISTS'	),
					)
				)
			));

			if(!$II->have_posts()) return  false;

			return $II->posts[0]->ID;
		}


	// check if a user has rsvped and has signed in
		public function is_user_signedin($uid){
			if(!$uid) return false;
			$uid = (int)$uid;

			$II = new WP_Query(array(
				'posts_per_page'=>1,
				'post_type'=>'evo-tix',
				'meta_query'=>array(
					'relation' => 'AND',
					array(	'key'	=> '_eventid','value'	=> $this->ID	),		
					array(	'key'	=> 'signin','value'	=> 'y'	),
					array(	'key'	=> '_customerid','value'	=> $uid	),
					array(
						'relation' => 'OR',
						array(	'key'	=> 'repeat_interval','value'	=> $this->ri	),
						array(	'key'	=> 'repeat_interval','compare'	=> 'NOT EXISTS'	),
					)
				)
			));

			return $II->have_posts() ? true: false;
		}

	public function get_guest_list(){
		$EA = new EVOTX_Attendees();
		$TH = $EA->get_tickets_for_event($this->ID);
		$total_tickets = 0;
		$output = '';

		if(!$TH || count($TH)<1) return false;

		ob_start();
		$cnt = $checked_count = 0;
		$tix_holders = array();
		$guests = array();

		//print_r($TH);
		foreach($TH as $tn=>$td){

			// validate
			if(empty($td['name'])) continue;
			if(trim($td['name']) =='') continue;

			// check for RI
			if($td['ri'] != $this->ri) continue;
			//if(in_array($td['name'], $guests)) continue;

			// skip refunded tickets
			if($td['s'] == 'refunded') continue;
			if($td['oS'] != 'completed') continue;

			// get checked count
			if($td['s']== 'checked')  $checked_count++;

			$tix_holders[ $td['name'] ] = array_key_exists( $td['name'] , $tix_holders) ? 
				$tix_holders[ $td['name'] ] + 1 : 1;		
			
			$cnt++;
		}


		foreach($tix_holders as $name=>$count){
			$guests[] = $name;
			echo apply_filters('evotx_guestlist_guest',"<span class='fullname' data-name='".$name."' >". $name . ( $count >1 ? ' ('. $count .')':'') . "</span>", $td);
		}


		$output = ob_get_clean();			

		return array(
			'guests'=>	$output,
			'count'=>	$cnt,
			'checked'=> $checked_count
		);
	}
	
// stock
	function get_ticket_stock_status(){
		return $this->get_wc_prop( '_stock_status');
	}
	function is_ri_count_active(){
		return (
			$this->get_wc_prop('_manage_stock')
		&& ($this->get_prop('_manage_repeat_cap')) && $this->get_prop('_manage_repeat_cap')=='yes'
		&& ($this->get_prop('ri_capacity'))
		&& $this->is_repeating_event()
		)? true: false;
	}

// WC Ticket Product
	function create_wc_product($args){

		// check nonce
        // create empty instance of WC_Product
        //$product = wc_get_product_object_type( 'simple' );

        $product = new WC_Product_Simple();

        if( ! $product )    return false;

        $product->set_name( $this->get_wc_product_title() );
        $product->set_status( isset($args['status']) ? $args['status'] : 'publish' );
        $product->set_catalog_visibility( isset($args['visibility']) ? $args['visibility'] : 'hidden' );
        $product->set_virtual( isset($args['virtual']) ? $args['virtual'] : true );
        $this->set_wc_product_meta( $product , $args );
        $this->assign_wc_product_cat( $product );

        $product_id = $product->save();


        // after product is created
        // save new wc product id to event for reference
        $this->set_prop( 'tx_woocommerce_product_id', $product_id);

	}

	// update WC product data
	public function update_wc_product( $product_id, $args = '' ){
		
		$product = wc_get_product( $product_id );

		if( !$product ) return;

		// update WC Product title
		$this->update_wc_product_title(false, $product );

		// save args is passed
		if( !empty( $args) ){
			if( isset($args['_tx_desc'])) $product->set_description( $args['_tx_desc'] );

			$product = $this->set_wc_product_meta( $product , $args, false );
		}

		
		$product_id = $product->save();

		$this->set_prop( 'tx_woocommerce_product_id', $product_id);
		
	}

	// update the product title with new event name information
		public function update_wc_product_title( $save = true, $product = '' ){
			if( empty( $product ) )
				$product = !empty( $this->product) ? $this->product : wc_get_product( $this->wcid );

			if( !$product ) return;

			// Update WC product title with event title if set
				if( EVO()->cal->check_yn('evotx_wc_prodname_update','evcal_tx')){
					
					$title = $this->get_wc_product_title();
					if($title) $product->set_name( $title );
				}

			if( $save ) $product->save();
		}

	// set product meta values
	private function set_wc_product_meta( $product, $args , $new = true){

		// stock modification
		if( isset( $args['_stock_status']) && $args['_stock_status'] == 'yes' ){
			$args['_manage_stock'] = 'no';
		}


		// stock 
		// sku generation or use
		if( isset( $args['_sku'] ) && !empty( $args['_sku']) ){
			$_sku = $args['_sku'];

			// if new WC product
			if( $new ){

				// check if SKU already exists
	            $productID = wc_get_product_id_by_sku($_sku);
	            
	            // if product exists with SKU generate a new SKU
	            if( !empty($productID)) $_sku = $_sku. '-'. rand(200,999);
	        }

		}else{			
			$_sku = $this->generate_product_sku( $product );
		}
        $product->set_sku( $_sku );

        $product->set_description( isset($args['_tx_desc']) ? $args['_tx_desc'] :' ' );
        $product->set_manage_stock( isset($args['_manage_stock']) && $args['_manage_stock'] == 'yes' ? true: false );
        $product->set_stock_status( isset($args['_stock_status']) && $args['_stock_status'] =='yes' ? 'outofstock':'instock' );
        $product->set_sold_individually( isset($args['_sold_individually']) && $args['_sold_individually'] =='yes' ? true: false );
        if( isset( $args['_stock'])) $product->set_stock_quantity( (int)$args['_stock'] );

        
        // Save repeat capacity
		if(!empty($args['ri_capacity']) && evo_settings_check_yn($args, '_manage_repeat_cap')){

			// get total
			$count = 0; 
			foreach($args['ri_capacity'] as $cap){
				$count = $count + ( (int)$cap);
			}
			
			// update product capacity
			$product->set_stock_quantity( $count);
			$this->set_prop('ri_capacity',$args['ri_capacity']);
		}

        // price
        if( isset($args['_regular_price'])){
        	$product->set_regular_price( str_replace('$', '', $args['_regular_price']) );
        	//$product->set_price( str_replace('$', '', $args['_regular_price']) );
        }
        if (isset($args['_sale_price'])) {
	        if (!empty($args['_sale_price'])) {
	            $sale_price = str_replace('$', '', $args['_sale_price']);
	            $product->set_sale_price($sale_price);
	        } else {
	            $product->set_sale_price(''); // Explicitly remove sale price if empty
	        }
	    }

        // image
        if( isset( $args['_tix_image_id'])) $product->set_image_id( $args['_tix_image_id'] );


        // other meta data --> saved to wc product
	        foreach( array( 
	        	'_tx_text', 
	        	'_tx_subtiltle_text',
	        	'event_id'
	        ) as $key ){

	        	if( !isset( $args[ $key ]) ) continue;

	        	if( $new){
	        		$product->add_meta_data( $key, $args[ $key ]);
	        	}else{
	        		$product->update_meta_data( $key, $args[ $key ]);
	        	}
	        }

        // save event id - legacy
        	if( $new){
        		$product->add_meta_data( '_eventid', $this->ID );
        	}else{
        		$product->update_meta_data( '_eventid', $this->ID );
        	}

        // save event post meta data
        	$woo_data = array(
        		'_manage_stock','_stock','_stock_status','_sold_individually',
				'tx_woocommerce_product_id',
				'_tx_desc'
			);

	        $skip_sanitize_fields = array('_tx_add_info','_tx_subtiltle_text', '_tx_text');

	        foreach($args as $key=>$val){
				if( in_array($key, $woo_data)) continue;

				// none sanitize fields
				if( in_array($key, $skip_sanitize_fields) && isset( $_POST[ $key ])){
					$val = $_POST[ $key ];
				}

				$this->set_prop( $key, $val);
			}


        return $product;
	}

	function generate_product_sku($product){
		return $product->get_id().'_tix'. ( rand(2000,9999));
	}

	private function get_wc_product_title(){

    	$_date_addition = '';

    	// wc prodduct name structure
    	$structure = "Ticket: {event_name} {event_start_date} - {event_end_date}";

    	if( EVO()->cal->check_yn('evotx_wc_prodname_update','evcal_tx') && EVO()->cal->get_prop('evotx_wc_prodname_structure','evcal_tx')){
    		$structure = EVO()->cal->get_prop('evotx_wc_prodname_structure','evcal_tx');
    	}
    	
    	$__sku = !empty($_REQUEST['_sku'])? '('. $_REQUEST['_sku'].') ':'';
    	$structure = str_replace('{sku}', $__sku, $structure);

    	// event name
    	$structure = str_replace('{event_name}', $this->get_title(), $structure);

    	// start date
    	if( strpos($structure, '{event_start_date}') !== false){
    		$this->DD->setTimestamp( $this->start_unix );
    		$event_start = $this->DD->format( EVO()->calendar->date_format  );
    		$structure = str_replace('{event_start_date}', $event_start, $structure);
    	}

    	// end time
    	if( strpos($structure, '{event_end_date}') !== false){
    		$this->DD->setTimestamp( $this->end_unix );
    		$event_end = $this->DD->format( EVO()->calendar->date_format );
    		$structure = str_replace('{event_end_date}', $event_end, $structure);
    	}
			
		return $structure;
	}

	private function assign_wc_product_cat( $product ){
		$terms = term_exists('Ticket', 'product_cat');
		if(!empty($terms) && $terms !== 0 && $terms !== null && isset( $terms['term_id'] )){

			$product->set_category_ids( array( 0=>$terms['term_id']) );
			//wp_set_post_terms( $post_id, $terms, 'product_cat' );
		}else{
			// create term
			$new_termid = wp_insert_term(
			  	'Ticket', // the term 
			  	'product_cat',
			  	array(	'slug'=>'ticket')
			);

			// assign term to woo product
			$product->set_category_ids( array( 0=>$new_termid ) );
			//wp_set_post_terms( $post_id, $new_termid, 'product_cat' );
		}
	}

}



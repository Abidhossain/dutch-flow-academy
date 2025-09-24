<?php
/**
 * Ticket Integration with Woocommerce
 * @version 2.4.2
 */

class EVOTX_WC_afterorder{
	public function __construct(){
		// hide some order item meta from showing in order edit page
		add_filter('woocommerce_hidden_order_itemmeta', array($this,'hide_order_itemmeta'),10,1);
		add_action('woocommerce_after_order_itemmeta',array($this, 'display_admin_order_itemdata'), 10, 3);

		add_filter('woocommerce_order_items_meta_display', array($this, 'ordermeta_display'), 10,2);
		add_filter('woocommerce_order_item_display_meta_key', array($this, 'ordermeta_display_key'), 10,3);
		add_filter('woocommerce_display_item_meta', array($this, 'order_item_meta'), 10, 3);	

		add_action('woocommerce_before_order_item_line_item_html',array($this, 'before_orderitem_admin'), 10, 3);	
	}

	public function hide_order_itemmeta($array){
		$array[]= '_event_id';
		$array[]= '_event_ri';
		$array[]= '_evo_lang';
		$array[]= 'Event-Time';
		$array[]= 'Event-Location';
		return apply_filters('evotx_hidden_order_itemmeta', $array);
	}


	// add order item meta in admin
	public function display_admin_order_itemdata($item_id, $item, $product){
		// only for line items in backend order pages
		if( ! ( is_admin() && $item->is_type('line_item') ) )    return;

		global $theorder;
		$order = $theorder;

		$print_meta_data = array();

		// ticket number
		if( !empty( $item->get_meta('_event_id'))){

			$TA = new EVOTX_Attendees();
			$ticket_numbers = $TA->get_ticket_number_by_orderitemid( $item_id  );
			
			if( count($ticket_numbers)> 0){
				$print_meta_data['event_ticket'] = array( __('Event Ticket','evotx'), $ticket_numbers[0] );
			}
		}

		if( !empty( $item->get_meta('Event-Time'))){
			$print_meta_data['event_time'] = array( 
				$this->lang('Event Time'), $item->get_meta('Event-Time') 
			);
		}
		if( !empty( $item->get_meta('Event-Location'))){
			$print_meta_data['event_location'] = array( 
				$this->langX('Event Location','evoTX_005c'), $item->get_meta('Event-Location') 
			);
		}

		foreach($print_meta_data as $key=> $val){

			if( $key == 'event_ticket'){
				$parts = explode('-', $val[1]);
				$post_id = $parts[0];

				echo "<div class='". $key."'><strong class='evomarr10'>". $val[0] ."</strong><a href='". get_edit_post_link( $post_id ) ."' class=''>". $val[1] ."</a></div>";
				continue;
			}

			echo "<div class='". $key."'><strong class='evomarr10'>". $val[0] ."</strong><span>". $val[1] ."</span></div>";
		}

	}

	// admin order item name
	public function before_orderitem_admin( $item_id, $item, $order){
		if( !empty( $item->get_meta('_event_id'))){
			$item->set_name( get_the_title(  $item->get_meta('_event_id') )  );
		}
	}

	// FORMAT ticekt item meta date
		function ordermeta_display($output, $obj){
			$output = str_replace('Event Time', $this->lang('Event Time'), $output);
			$output = str_replace('Event Location', $this->langX('Event Location','evoTX_005c'), $output);
			return $output;
		}
		// change order edit meta key text @since 2.2.5
		function ordermeta_display_key( $display_key, $meta, $class){

			if( $display_key == 'Event-Time') $display_key = $this->lang('Event Time');
			if( $display_key == 'Event-Location') $display_key = $this->langX('Event Location','evoTX_005c');
			return $display_key;
		}

		function order_item_meta($html, $item, $args){
			//print_r($item);

			// set the language for order item
				$lang = $item->get_meta('_evo_lang');
				$lang = $lang ? $lang : 'L1';
				if( EVO()->lang != $lang) evo_set_global_lang($lang);

			$html = $this->_format_ticket_item_meta($html);							
			return $html;
		}

		function _format_ticket_item_meta($html){
			foreach(apply_filters('evotx_order_item_meta_slug_replace',array(
				'Event-Time'=>$this->lang('Event Time'),
				'Event-Location'=>$this->langX('Event Location','evoTX_005c'),
			)) as $slug=>$name){

				if( strpos($html, $slug) == false) continue;

				$html = str_replace($slug, $name , $html);
			}			
			return $html;
		}

	// get language fast for evo_lang
		function lang($text){	return evo_lang($text, '', EVOTX()->opt2);}
		function langE($text){ echo $this->lang($text); }
		function langX($text, $var){	return eventon_get_custom_language(EVOTX()->opt2, $var, $text);	}
		function langEX($text, $var){	echo eventon_get_custom_language(EVOTX()->opt2, $var, $text);		}
}
new EVOTX_WC_afterorder();
<?php
/**
 * Woocommerce block based API integration - in progress
 * @version 2.2.10
 */

class EVOTX_WC_API {

	public function __construct(){
		add_action( 'woocommerce_store_api_checkout_order_processed', array($this,'process_ticket_order'),10, 1 );
	}


	function process_ticket_order( $order){
		$ET = new evotx_tix();
		$ET->create_tickets_for_order( $order );		
	}
}

new EVOTX_WC_API();
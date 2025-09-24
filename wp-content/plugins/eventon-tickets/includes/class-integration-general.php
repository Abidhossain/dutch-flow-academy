<?php
/**
 * General integration parts with other components
 * @version 2.4.7
 */

class evotx_int{
	public function __construct(){
		if(is_admin()){
			add_filter('evo_csv_export_fields', array($this,'export_field_names'), 10,1);
			add_filter('evo_export_events_row_field_value', array($this, 'export_row_field_val'), 10, 4);
			
			add_filter('evo_export_events_csv_eventdata', array($this, 'export_csv_eventdata'), 10, 3);
			
			// CSV importing
			add_filter('evocsv_additional_csv_fields', array($this,'csv_importer_fields'), 10,1);
			add_action('evocsv_save_additional_event_metadata', array($this,'csv_save_customdata'), 10,2);
		}

		// confirmation email additional information
		add_action('evotix_confirmation_email_data_after_tr', array($this, 'additional_info'), 10,4);

		// virtual event link
		add_filter('evodata_vir_url', array($this, 'event_virtual_link'),10,2);
	}

	// virtual link to event
		function event_virtual_link( $link, $EV){

			if( $EV->check_yn('_vir_after_tix') ){

				$TEV = new EVOTX_Event($EV->ID);

				return ( $TEV->has_user_purchased_tickets()) ? $link : $EV->get_permalink();

			}
			return $link;
		}

	// Confirmation email
		function additional_info($EVENT, $TIX_CPT, $order, $styles){
			if( $EVENT->get_prop('_tx_add_info') && $order->get_status() == 'completed'){
				?>
				 <tr><td colspan='3' style='border-top:1px solid #e0e0e0; padding:8px 20px; text-transform: none'>
				 	<div style='font-size: 14px;font-style: italic;font-family: "open sans",helvetica; padding: 0px; margin: 0px;font-weight: bold;line-height: 100%;word-break:break-word'><?php echo $EVENT->get_prop('_tx_add_info');?></div>
				 	<p style="<?php echo $styles['004'];?>"><?php evo_lang_e('Additional Ticket Information');?></p>
				 </td></tr>
				<?php
			}
		}
	
	// include ticket event meta data fields for exporting events as CSV
		function export_field_names($array){
			
			foreach($this->get_event_cpt_meta_fields() as $ad){
				$array[$ad] = $ad;
			}
			return $array;
		}
		public function export_csv_eventdata( $event_csv_data, $EVENT, $csv_fields){

			if( $EVENT->check_yn('evotx_tix')){
				$tx_event = new EVOTX_Event( $EVENT->ID );
				
				foreach($csv_fields as $ff => $vv){
					if( $ff == '_regular_price') $event_csv_data[ $ff ] = $tx_event->get_product_regular_price();
					if( $ff == '_sale_price') $event_csv_data[ $ff ] = $tx_event->get_product_sale_price();
					if( $ff == '_sku') $event_csv_data[ $ff ] = $tx_event->get_product_sku();
					if( $ff == '_tx_desc') $event_csv_data[ $ff ] = $tx_event->get_product_description();
					if( $ff == '_stock') $event_csv_data[ $ff ] = $tx_event->get_product_stock();
					if( $ff == '_manage_stock') $event_csv_data[ $ff ] = $tx_event->is_product_manage_stock() ?'yes':'no';
					if( $ff == '_stock_status') $event_csv_data[ $ff ] = $tx_event->get_product_stock_status() ?'yes':'no';
				}
			}

			return $event_csv_data;
		}

		

	// for CSV Importer
		public function csv_importer_fields($array){
			$adds = $this->get_event_cpt_meta_fields();

			return array_merge($array, $adds);
		}

		public function get_event_cpt_meta_fields(){
			return array(
					'evotx_tix', 
					'_regular_price',
					'_sale_price',
					'_sku',
					'_manage_stock', 
					'_stock_status',// place ticket on out of stock
					'_stock',// total ticket in stock
					'_tx_desc',
					'_tx_img_text',					
					'_show_remain_tix', 
					'remaining_count', 
					'_manage_repeat_cap', 
					'_tix_image_id', '_tx_img_text',// imge and image text					
					'_tx_inq_email',
					'_tx_inq_subject',
					'_xmin_stopsell',
					'_tx_show_guest_list',
					'_name_yprice','_evotx_nyp_min',// name your price yes/no and price
					'_tx_text', // ticket section subtitle
					'_tx_subtiltle_text',// ticket field description

					'_tx_add_info',// additional info visible to customer after purchase

					'_allow_inquire','_tx_inq_email','_tx_inq_subject', // allow customer inqueries

				);
		}
		public function csv_save_customdata( $post_id, $post_data ){

			// check if the ticket price and sku present
			if( isset($post_data['_regular_price']) && isset($post_data['_sku'] )){
				$product_id = get_post_meta( $post_id, 'tx_woocommerce_product_id', true);
				$ticket = new EVOTX_Event( $post_id);

				// woo product exists
				if( $product_id){					
					$ticket->update_wc_product( $product_id, $post_data );
				}else{
					$ticket->create_wc_product( $post_data );
				}
			}

		}
	
}

new evotx_int();
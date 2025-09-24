<?php
/**
 * Ticket meta boxes for event page
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	EventON/Admin/evo-tix
 * @version     2.4.6
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class EVOTX_post_meta_boxes{
	public function __construct(){
		add_action( 'add_meta_boxes', array($this, 'evotx_meta_boxes'), 10, 2 );
		add_action('eventon_save_meta',  array($this, 'evotx_save_ticket_info'), 10, 4);
		add_action('save_post',array($this, 'save_evotix_post'), 10, 2);
		add_action('save_post', array($this, 'evotx_new_ticket_order_save'), 20,2);
		

		// event repeat additions
		//add_filter('evo_repeats_admin_notice', array($this, 'repeat_notice'), 10,2);
		add_action('evo_eventedit_repeat_metabox_top', array($this, 'repeat_metabox_adds'), 10, 1);

		// event edit ajax content		
		add_filter('evo_eventedit_pageload_dom_ids', array($this, 'eventedit_dom_ids'), 12,4);
		add_filter('evo_eventedit_pageload_dom_data',array($this, 'eventedit_pageload_data'), 12, 4);
		add_filter('evo_eventedit_pageload_data', array($this, 'legacy_eventedit_content'), 12,4);

		// order details additions
		add_action('woocommerce_admin_order_data_after_payment_info', array($this, 'order_details_add'), 10, 1);
		add_action('woocommerce_admin_order_data_after_payment_info', array($this, 'evotx_metabox_003x'), 10, 1);
	}

	/** Init the meta boxes. */
		function evotx_meta_boxes($screen_id, $post){
			
			add_meta_box('evotx_mb1', __('Event Tickets','evotx'), array($this,'evotx_metabox_content'),'ajde_events', 'normal', 'high');
							

			// Event Ticket CPT
			add_meta_box('evo_mb1',__('Event Ticket Data','evotx'), array($this,'evotx_metabox_002'),'evo-tix', 'normal', 'high');
			add_meta_box('evotx_mb3',__('Ticket Data','evotx'), array($this,'metabox_evotix_ticket_data'),'evo-tix', 'side', 'default');
			add_meta_box('evotx_mb2',__('Event Ticket Confirmation','evotx'), array($this,'evoTX_notifications_box'),'evo-tix', 'side', 'default');

			// meta box for WC Product
			add_meta_box('evotx_metaboxes_wcproduct',__('Associated Event Data','evotx'), array($this,'metaboxes_wc_product'),'product', 'side', 'default');

			// Plug to add meta boxes on tickets init
			do_action('evotx_add_meta_boxes');	
		}

	// WC Product meta box
		public function metaboxes_wc_product(){
			include_once('view-meta_boxes-product.php');
		}

	// Event edit ajax content		
		function eventedit_dom_ids( $array, $postdata, $EVENT, $id){

			$array['evotx'] = 'evotx_content';
			return $array;
		}
		function eventedit_pageload_data( $array, $postdata, $EVENT, $dom_id_array){

			if( !array_key_exists( 'evotx', $dom_id_array )) return $array;

			$array['evotx'] = $this->get_event_edit_settings_content( $postdata, $EVENT );
			return $array;

		}

		// remove after 4.8.3 eventon
		function legacy_eventedit_content($array, $postdata, $EVENT, $id){

			// if id is passed and its not evotx -> skip
			if( $id && $id != 'evotx') return $array;	
			if( isset($array['evotx'])) return $array;		

			$array['evotx'] = $this->get_event_edit_settings_content( $postdata, $EVENT );

			return $array;
		}

		private function get_event_edit_settings_content( $postdata, $EVENT){
			ob_start();
			include_once 'metabox-event.php';
			return ob_get_clean();
		}
		// on page load content
		public function evotx_metabox_content(){
			EVO()->admin_elements->print_loading_metabox_skeleton(
				array(
					'id'=> 'evotx_content',
					'class'=>'evotx_content',
					'nonce_key'=> '',
				)
			);
		}
		

	// repeat notice on event edit post
		function repeat_notice($string, $EVENT){
			if( $EVENT->check_yn('_manage_repeat_cap') )
				$string .= "<em style='background-color: #fb3131;color: #fff;'>". __('IMPORTANT: Ticket stock for each repeating instances is enabled, changes made to repeating instances may effect the stock for each repeat instance!','evotx') . "</em>";
			return $string;
		}
		function repeat_metabox_adds($EVENT ){
			if( $EVENT->check_yn('_manage_repeat_cap')){
				echo "<p style='margin-bottom:10px;background-color: #f76a6a; color: #fff; padding: 10px; border-radius: 10px;'>". __('IMPORTANT: Ticket stock for each repeating instances is enabled, changes made to repeating instances may effect the stock for each repeat instance & already sold ticket dates!','evotx') . "</p>";
			}
		}

	// META box on WC Order post
		// order details adds
			function order_details_add( $order){
				
				include_once('view-meta_boxes-order.php');

			}

		// adding manual order from backend
			function evotx_metabox_003x( $order ){

				global $pagenow;

				$__show = false;

				if( $pagenow && ( $pagenow == 'post-new.php' || $pagenow == 'new.php')  &&  isset($_GET['post_type']) && $_GET['post_type'] == 'shop_order'
				) $__show = true;

				if( isset($_GET['action']) && $_GET['action'] == 'new') $__show = true;

				if( $__show):
				
				?><div id='evotx_new_order'>					
					<p class='yesno_row evo'><?php echo EVO()->elements->yesno_btn(array(
						'id'=>'_order_type',
						'default'=>'',
						'label'=> __('Is this a ticket order ?','evotx'),
						'guide'=> __('Check this if the order contain event tickets. The order must NOT contain repeating events as repeating event tickets are not fully compatible for adding from backend, and they must be added from frontend only.','evotx'),
						'input'=>true
					));
					?></p>
				</div>
				<?php
				endif;

			}
			// save value
			// Manually adding ticket orders from backend @2.2.10
			function evotx_new_ticket_order_save($post_id, $post){


				if($post->post_type!='shop_order')	return;
				if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
				if (defined('DOING_AJAX') && DOING_AJAX) return;

				// if order type custom value is passed 
				if(isset($_POST['_order_type']) && $_POST['_order_type'] == 'yes'){

					$ET = new evotx_tix();

					$order = new WC_Order( $post_id );	

					$ET->create_tickets_for_order( $order );
					
					EVOTX()->functions->alt_initial_event_order( $order );
					
				}
			}
	
	// EVO-TIX POST
		function evotx_metabox_002(){
			include_once('view-meta_boxes-evo-tix.php');
		}

		// ticket data
			public function metabox_evotix_ticket_data(){

				global $post;

				$TIX_CPT = new EVO_Ticket( $post->ID );
				$event_id 			= $TIX_CPT->get_event_id();	
				$repeat_interval 	= $TIX_CPT->get_repeat_interval();
				$EVENT 				= new EVO_Event( $event_id, '', $repeat_interval );

				$order_id = $TIX_CPT->get_order_id();
				$order = new WC_Order( $order_id);
				$order_status 	= $order->get_status();

				$product_id = $TIX_CPT->get_wcid();
				$product = wc_get_product( $product_id );

				?>
				<div class=''>
					<p class='evodfx evofx_jc_sb evogap10'>
						<span><?php _e('Event','evotx');?></span>
						<a class='evotar' href='<?php echo get_edit_post_link($event_id);?>'><?php echo $EVENT->get_title().' ('. $EVENT->ID .')';?></a>
					</p>
					<p class='evodfx evofx_jc_sb evogap10'>
						<span><?php _e('WC Product','evotx');?></span>
						<a class='evotar' href='<?php echo get_edit_post_link($product_id);?>'><?php echo $product_id;?></a>
					</p>
					<p class='evodfx evofx_jc_sb'>
						<span><?php _e('WC Order','evotx');?></span>
						<a href='<?php echo $order->get_edit_order_url();?>'><?php echo $order_id;?></a>
					</p>
					<p class='evodfx evofx_jc_sb evofx_ai_c'>
						<span><?php _e('Order Status','evotx');?></span>
						<span class="evotx_wcorderstatus <?php echo $order_status;?>" style="line-height: 20px; padding: 5px 20px;"><?php  __(printf('%s',$order_status),'evotx') ;?></span>
					</p>
					<p class='evodfx evofx_jc_sb evofx_ai_c'>
						<span><?php _e('Other Actions','evotx');?></span>
					</p>
					<p>
						<a id='evotix_sync_with_order' data-oid='<?php echo $order_id;?>' class='evo_admin_btn' ><?php _e('Sync with WC Order','evotx');?></a><?php echo EVO()->elements->tooltips('This will sync order item ids of woocommerce order with this ticket!','L');?>
						<span class='evodb evomart5'></span>
					</p>
					<p class=''>
						<?php 

						EVO()->elements->print_trigger_element( array(
							'title'=> __('Preview Ticket Email'),
							'id'=>'evotx_email_preview',
							'dom_element'=> 'span',
							'uid'=>'evotx_email_preview',
							'lb_class' =>'evotx_email_preview',
							'lb_title'=> __('Ticket Confirmation Email Preview'),	
							'ajax_data'=>array(
								'a'=>'evotx_email_preview',
								'evo_ticket_id'=> $post->ID 
							),
						),'trig_lb');

						?>
						<?php echo EVO()->elements->tooltips('Preview how the ticket email content look for this ticket order.','L');?>
					</p>
				</div>	
				<?php
			}

		// ticket email  information
			function evoTX_notifications_box(){
				global $post;

				$tix_post = new EVO_Ticket( $post->ID );

				$order_id = $tix_post->get_order_id();

				$order = new WC_Order( $order_id );	
				$order_status = $order->get_status();

				?>
				<div class='evoTX_resend_conf'>
					<div class='evoTX_rc_in'>
						<?php
						if($order_status != 'completed'):
						?>
							<p><?php _e('Ticket Order is not completed yet!','evotx');?></p>
						<?php
						else:
						?>
							<p><i><?php _e('You can re-send the Event Ticket confirmation email to customer if they have not received it. Make sure to check spam folder.','evotx');?></i></p>
							
							<a id='evoTX_resend_email' class='evoTX_resend_email evo_btn' data-orderid='<?php echo $order_id;?>'><?php _e('Re-send Ticket(s) Email','evotx');?></a>
							
							<p class='message' style='display:none; text-align:center;' data-s='<?php _e('Ticket Email Re-send!','evotx');?>' data-f='<?php _e('Could not send email.','evotx');?>'></p>
						<?php endif;?>
					</div>
				</div>
				<?php

				do_action('evotx_ticketpost_confirmation_end', $order_id, $order);
			}

		// save evo-tix post values
			function save_evotix_post($post_id, $post){			

				if($post->post_type!='evo-tix')	return;				
				if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
				if (defined('DOING_AJAX') && DOING_AJAX)	return;
				

				// verify this came from the our screen and with proper authorization,
				// because save_post can be triggered at other times
				if( isset($_POST['evo_noncename_tix']) && !wp_verify_nonce( $_POST['evo_noncename_tix'], 'evotx_edit_post' ) ) return;


				// Check permissions
				if ( !current_user_can( 'edit_post', $post_id ) )	return;	


				global $pagenow;
				$_allowed = array( 'post-new.php', 'post.php' );
				if(!in_array($pagenow, $_allowed)) return;

				foreach(array(
					'_admin_notes'
				) as $variable){
					if(!empty($_POST[$variable])){
						update_post_meta( $post_id, $variable,$_POST[$variable]);
					}elseif(empty($_POST[$variable])){
						delete_post_meta($post_id, $variable);
					}
				}


				// instance index
				if(isset($_POST['_ticket_number_instance']) ){
					update_post_meta($post_id, '_ticket_number_instance', (int)$_POST['_ticket_number_instance']);
				}




				// update ticket holder data
				if(!empty($_POST['_ticket_holder']) ){
					$EA = new EVOTX_Attendees();

					$EA->_update_ticket_holder( 
						array(
							'order_id'=>$_POST['order_id'],
							'event_id'=>$_POST['event_id'],
							'ri'=>$_POST['ri'],
							'Q'=>(int)$_POST['Q'],
							'event_instance'=>(int)$_POST['event_instance']
						),
						$_POST['_ticket_holder']
					);
				}
				
			}

	// save new ticket and create matching WC product @u 2.2.7
		public function evotx_save_ticket_info($fields_ar, $post_id, $EVENT, $post_data){			

			foreach(apply_filters('evotx_save_eventedit_page', array(
				'evotx_tix'
			)) as $variable){
				if(!empty($post_data[$variable])){
					update_post_meta( $post_id, $variable,$post_data[$variable]);
				}elseif(empty($post_data[$variable])){
					delete_post_meta($post_id, $variable);
				}
			}

			// Update product title if event name changed
			if( isset($post_data['evotx_tix']) && $post_data['evotx_tix'] == 'yes'){
				$ticket = new EVOTX_Event( $EVENT->ID );
				$ticket->update_wc_product_title();
			}

			


			
			// after saving event tickets data
			do_action('evotx_after_saving_ticket_data', $post_id);			
		}

}
new EVOTX_post_meta_boxes();
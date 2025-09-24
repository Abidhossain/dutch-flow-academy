<?php
/**
 * Woocommerce extra checkout fields
 * @version 2.4.11
 */

class EVOTX_Woo_Extrafields{

	public function __construct(){
		
		// Additional order fields - guest names
		if( !EVO()->cal->check_yn('evotx_hideadditional_guest_names', 'evcal_tx') ):
			// show additional fields in checkout
			add_filter( 'woocommerce_checkout_fields', array($this,'filter_checkout_fields') );
			add_action( 'woocommerce_after_order_notes' ,array($this,'extra_checkout_fields') );
			add_action( 'woocommerce_after_checkout_validation' ,array($this,'extra_fields_process'), 10,2 );

			// save extra information
			add_action( 'woocommerce_checkout_update_order_meta', array($this,'save_extra_checkout_fields') );

			// display in order details section
			add_action( 'woocommerce_order_details_after_order_table', array($this,'display_orderdetails'),10,1 );

			add_action('init', array($this, 'handle_ticketholder_submission'));
		endif;
	}


    // Helper to gather fields data from cart
    private function get_fields_data_for_cart() {
        $cart = WC()->cart->get_cart();
        $fields_data = [];
        foreach ($cart as $item => $values) {
            $event_id = $values['evotx_event_id_wc'] ?? '';
            $RI = $values['evotx_repeat_interval_wc'] ?? 0;
            if ($event_id) {
                $fields_data[$event_id] = $this->_supportive_checkout_additional_fiels($event_id, $item, $values);
            }
        }
        return $fields_data;
    }


	function filter_checkout_fields($fields){
	    $fields['evotx_field'] = array(
            'evotx_field' => array(
                'type' => 'text',
                'required' => false,
                'label' => __('Event Ticket Data')
            ),
        );
        return $fields;
	}
	function extra_checkout_fields(){ 

	    $checkout = WC()->checkout();
        $required = EVO()->cal->get_prop('evotx_reqadditional_guest_names', 'evcal_tx');
	   
	    // there will only be one item in this array - just to pass these values only for tx
	    foreach ( $checkout->checkout_fields['evotx_field'] as $key => $field ) : 

	    	global $woocommerce;
            $items = $woocommerce->cart->get_cart();
            $output = '';
            $datetime = new evo_datetime();
            $_event_instance = 1;
            $_cart_events = array();


	    	// foreach item in the cart
	        foreach($items as $item => $values) { 

	        	$event_id = !empty($values['evotx_event_id_wc'])? $values['evotx_event_id_wc']:
	        		(!empty($values['evost_eventid'])? $values['evost_eventid']: false);

	        	$RI = !empty($values['evotx_repeat_interval_wc'])? (int)$values['evotx_repeat_interval_wc']:0;

	        	if(!$event_id) continue;

	        	$EVENT = new EVO_Event( $event_id, '', $RI );

	        	// add event to cart events array
	        	// same event with different item meta values 
	        	// @+ 1.7.6
	        		if(in_array($event_id, $_cart_events)){	
	        			// if once instance of event exists in cart items	        			
	        			$_event_instance++;		      
	        		}else{
	        			$_event_instance=1;
	        			$_cart_events[] = $event_id;
	        		}


	        	// set language
	        		if(isset($values['evotx_lang']))	evo_set_global_lang($values['evotx_lang']);
	        	
	        	// get event time
		        	$event_time = $EVENT->get_formatted_smart_time();
		        	

	        	$_product = wc_get_product($values['variation_id'] ? $values['variation_id'] : $values['product_id']);

	        	$product_id = $_product->get_id();

	        	
	        	$output.= "<div class='evotx_ticket_additional_info'>";
	        	$output.= "<p class='evo_event_information'>";
	        	$output .= "<span style='display:block'><b>". evo_lang('Event Name').':</b> '. get_the_title($event_id) . "</span>";
	        	
	        	$output .= "<span style='display:block'><b>". evo_lang('Event Time').':</b> '.apply_filters('evotx_cart_add_field_eventtime', $event_time, $values) ."</span>";


	        	// for WC based variations
	            	if( !empty( $values['variation']) && is_array( $values['variation'] ) && !empty( $values['variation_id'] ) ){

	            		$variation = new WC_Product_Variation( $values['variation_id'] );

	            		$variation_attributes = $variation->get_attributes();

	            		foreach( $variation_attributes as $key => $value){
	            			$value = $values['variation']['attribute_'. $key ];
	            			$output .= "<span style='display:block'><b>". ucfirst( esc_html( $key ) ) .':</b> '. $value ."</span>";
	            		}		            		
	            	}

	        	$output = apply_filters('evotx_checkout_addnames_other_vars', $output, $values, $EVENT);
                $output .= "</p>";

                if ($values['quantity'] > 0) {
                    for ($x = 0; $x < $values['quantity']; $x++) {
                        $Q = $x;
                        $output .= "<div class='evotx_tai_oneholder'>";
                        $output .= "<span class='evotx_tai_oneholder_title'>" . evo_lang('Ticket Holder') . " #" . ($Q + 1) . "</span>";

                        foreach ($this->_supportive_checkout_additional_fiels($event_id, $item, $values) as $key => $data) {
                            $placeholder = isset($data['placeholder']) ? $data['placeholder'] : $data['label'];
                            $result = woocommerce_form_field(
                                'tixholders[' . $event_id . '][' . $RI . '][' . $Q . '][' . $_event_instance . '][' . $key . ']',
                                array(
                                    'type' => $data['type'],
                                    'class' => array('my-field-class form-row'),
                                    'label' => $data['label'],
                                    'placeholder' => $placeholder,
                                    'required' => $data['required'],
                                    'return' => true
                                ),
                                $checkout->get_value('tixholders[' . $event_id . '][' . $RI . '][' . $Q . '][' . $_event_instance . '][' . $key . ']')
                            );
                            $output .= apply_filters('evotx_checkout_fields', $result, $event_id, $x);
                        }
                        $output .= "</div>";
                    }
                }
                $output .= "</div>"; 
	        } 

	        echo !empty($output) ? "<div class='extra-fields'><div class='evotx_checkout_additional_names'><h3>" . evo_lang('Additional Ticket Information') . "</h3>" . $output . '</div></div>' : '';
        endforeach;
    }

	// supportive
	private function _supportive_checkout_additional_fiels($event_id, $item, $values){
		$required = EVO()->cal->get_prop('evotx_reqadditional_guest_names','evcal_tx');	

		$fields = array();
		$fields['name'] =array(
			'type'=>'text',
			'label'=> apply_filters('evotx_checkout_addnames_label',evo_lang('Full Name'),$item, $values, $event_id),
			'required'=> $required,
		);

		// additional fields
		$ad_fields = EVO()->cal->get_prop('evotx_add_fields','evcal_tx');	
		if($ad_fields){
			foreach($ad_fields as $field){
				switch($field){
					case 'phone':
						$fields['phone'] =array(
	    					'type'=>'tel',
	    					'label'=> evo_lang('Phone Number'),
	    					'required'=> $required,
	    				);
					break;
					case 'email':
						$fields['email'] =array(
	    					'type'=>'email',
	    					'label'=> evo_lang('Email Address'),
	    					'required'=> $required,
	    				);
					break;
				}
			}
		}

		return apply_filters('evotx_additional_ticket_info_fields', $fields);
	}

	function extra_fields_process($data, $errors) {
	    if (!empty($_POST['tixholders'])) {
	        $required = EVO()->cal->get_prop('evotx_reqadditional_guest_names', 'evcal_tx');

	        // Skip validation if additional fields are not required
	        if (!$required) return;

	        // Loop through cart items to get event-specific fields and validate ticket holders
	        foreach (WC()->cart->get_cart() as $item => $values) {
	            $event_id = !empty($values['evotx_event_id_wc']) ? $values['evotx_event_id_wc'] : false;
	            $RI = !empty($values['evotx_repeat_interval_wc']) ? (int)$values['evotx_repeat_interval_wc'] : 0;

	            if (!$event_id) continue;

	            // Get the fields defined for this event, including required ones
	            $fields = $this->_supportive_checkout_additional_fiels($event_id, $item, $values);
	            $required_fields = array_filter($fields, function($field) {
	                return !empty($field['required']);
	            });

	            // Validate ticket holder data for this event and repeat interval
	            if (isset($_POST['tixholders'][$event_id][$RI])) {
	                foreach ($_POST['tixholders'][$event_id][$RI] as $qty => $instances) {
	                    foreach ($instances as $instance => $ticket_data) {
	                        foreach ($required_fields as $key => $field) {
	                            $field_label = $field['label'];
	                            $value = isset($ticket_data[$key]) ? trim($ticket_data[$key]) : '';

	                            // Generic validation: Check if the required field is empty
	                            if (empty($value)) {
	                                $errors->add(
	                                    'tixholder_validation',
	                                    sprintf(
	                                        _x('%s for Ticket Holder #%d is a required field.', 'FIELDNAME for Ticket Holder #NUMBER is a required field.', 'evotx'),
	                                        '<strong>' . $field_label . '</strong>',
	                                        $qty + 1
	                                    )
	                                );
	                                continue; // Skip further validation if empty
	                            }

	                            // Specific validation for certain field types
	                            switch ($key) {
	                                case 'name':
	                                    if (strlen($value) < 2) {
	                                        $errors->add(
	                                            'tixholder_validation',
	                                            sprintf(
	                                                _x('%s for Ticket Holder #%d must be at least 2 characters long.', 'FIELDNAME for Ticket Holder #NUMBER must be at least 2 characters long.', 'evotx'),
	                                                '<strong>' . $field_label . '</strong>',
	                                                $qty + 1
	                                            )
	                                        );
	                                    }
	                                    break;
	                                case 'email':
	                                    if (!is_email($value)) {
	                                        $errors->add(
	                                            'tixholder_validation',
	                                            sprintf(
	                                                _x('%s for Ticket Holder #%d must be a valid email address.', 'FIELDNAME for Ticket Holder #NUMBER must be a valid email address.', 'evotx'),
	                                                '<strong>' . $field_label . '</strong>',
	                                                $qty + 1
	                                            )
	                                        );
	                                    }
	                                    break;
	                                case 'phone':
	                                    if (!preg_match('/^[0-9\-\(\) ]+$/', $value)) {
	                                        $errors->add(
	                                            'tixholder_validation',
	                                            sprintf(
	                                                _x('%s for Ticket Holder #%d must be a valid phone number.', 'FIELDNAME for Ticket Holder #NUMBER must be a valid phone number.', 'evotx'),
	                                                '<strong>' . $field_label . '</strong>',
	                                                $qty + 1
	                                            )
	                                        );
	                                    }
	                                    break;
	                                default:
	                                    // For all other dynamic fields, no additional validation beyond emptiness
	                                    // Optionally, you could add a filter here for custom validation
	                                    do_action('evotx_validate_custom_checkout_field', $key, $value, $field, $qty, $errors);
	                                    break;
	                            }
	                        }
	                    }
	                }
	            }
	        }
	    }
	}

	function save_extra_checkout_fields( $order_id ){
		if (!empty($_POST['tixholders'])) {
            $order = new WC_Order($order_id);
            $order->add_meta_data('_tixholders', $_POST['tixholders']);
            $order->save();
            do_action('evotx_checkout_fields_saving', $order_id, $order);
        }
	}

	// ticket holder information is displayed on order received page, and wc emails		
	public function display_orderdetails($order){
		$order_id = $order->get_id();
		$TA = new EVOTX_Attendees();
        $ticket_holders = $TA->_get_tickets_for_order($order->get_id(), 'event');
        
        if (!$ticket_holders) return $order;

        $TA->__print_ticketholder_styles();
        ?>
        <h2><?php evo_lang_e('Ticket Holder Details'); ?></h2>
        <table class="shop_table ticketholder_details evomart20i" cellspacing="0" style='width:100%;'>
            <?php 
            foreach ($ticket_holders as $e => $dd) {
                ?><tr><td><?php
                foreach ($dd as $tn => $nm) { 
                    echo $TA->__display_one_ticket_data($tn, $nm, array('showExtra' => false));
                }
                ?></td></tr><?php
            } ?>                    
        </table>
        <?php 
       	// Display ticketholder form if needed
        if ($order->get_meta('_needs_ticketholder_data') === 'yes') {
            $cart_items = [];
            foreach ($order->get_items() as $item_id => $item) {
                $event_id = $item->get_product()->get_meta('_eventid');
                if ($event_id) {
                    $cart_items[$item_id] = [
                        'evotx_event_id_wc' => $event_id,
                        'evotx_repeat_interval_wc' => 0, // Assume RI=0; adjust if needed
                        'quantity' => $item->get_quantity(),
                    ];
                }
            }

            $_event_instance = 1;
            $_cart_events = [];

            ?>
            <div class="ticketholder-form">
                <h3 class='evofwb'><?php evo_lang_e('Enter Ticket Holder Information'); ?></h3>
                <?php if (isset($_GET['ticketholder_success']) && $_GET['ticketholder_success'] === '1'): ?>
                    <p class="woocommerce-message"><?php evo_lang_e('Ticket holder information submitted successfully.'); ?></p>
                <?php endif; ?>
                <form method="post" action="">
                    <?php wp_nonce_field('evotx_save_ticketholder_data', 'evotx_nonce'); ?>
                    <input type="hidden" name="order_id" value="<?php echo esc_attr($order_id); ?>">
                    <?php
                    foreach ($cart_items as $item_id => $values) {
                        $event_id = $values['evotx_event_id_wc'];
                        $RI = $values['evotx_repeat_interval_wc'];

                        if (in_array($event_id, $_cart_events)) {
                            $_event_instance++;
                        } else {
                            $_event_instance = 1;
                            $_cart_events[] = $event_id;
                        }

                        $EVENT = new EVO_Event($event_id, '', $RI);
                        $event_time = $EVENT->get_formatted_smart_time();
                        ?>
                        <div class="evotx_ticket_additional_info">
                            <p class="evo_event_information">
                                <span style="display:block"><b><?php evo_lang_e('Event Name'); ?>:</b> <?php echo esc_html(get_the_title($event_id)); ?></span>
                                <span style="display:block"><b><?php evo_lang_e('Event Time'); ?>:</b> <?php echo esc_html($event_time); ?></span>
                            </p>
                            <?php
                            for ($x = 0; $x < $values['quantity']; $x++) {
                                $Q = $x;
                                ?>
                                <div class="evotx_tai_oneholder">
                                    <span class="evotx_tai_oneholder_title"><?php echo esc_html(evo_lang('Ticket Holder') . ' #' . ($Q + 1)); ?></span>
                                    <?php
                                    foreach ($this->_supportive_checkout_additional_fiels($event_id, $item_id, $values) as $key => $data) {
                                        $placeholder = isset($data['placeholder']) ? $data['placeholder'] : $data['label'];
                                        $result = woocommerce_form_field(
                                            'tixholders[' . $event_id . '][' . $RI . '][' . $Q . '][' . $_event_instance . '][' . $key . ']',
                                            array(
                                                'type' => $data['type'],
                                                'class' => array('my-field-class form-row'),
                                                'label' => $data['label'],
                                                'placeholder' => $placeholder,
                                                'required' => $data['required'],
                                                'return' => true,
                                            )
                                        );
                                        echo apply_filters('evotx_checkout_fields', $result, $event_id, $x);
                                    }
                                    ?>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                    <button class='evomart20i' type="submit" name="submit_ticketholder_data"><?php evo_lang_e('Submit Ticket Holder Information'); ?></button>
                </form>
            </div>
            <style>
                .ticketholder-form { max-width: 600px; margin: 20px 0; padding: 20px; border: 1px solid #eee; border-radius: 4px; }
                .ticketholder-form label { display: block; margin-bottom: 5px; font-weight: bold; }
                .ticketholder-form input[type="text"], .ticketholder-form input[type="email"], .ticketholder-form input[type="tel"] {
                    width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;
                }
                .ticketholder-form button { background-color: #0073aa; color: #fff; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
                .ticketholder-form button:hover { background-color: #005177; }
                .evotx_tai_oneholder { margin-bottom: 20px; }
                .evotx_tai_oneholder_title { display: block; font-weight: bold; margin-bottom: 10px; }
                .evo_event_information { margin-bottom: 15px; }
            </style>
            <?php
        }
        do_action('evotx_checkout_fields_display_orderdetails', $order);
	}

	public function handle_ticketholder_submission() {
        if (isset($_POST['submit_ticketholder_data'], $_POST['order_id'], $_POST['evotx_nonce']) && wp_verify_nonce($_POST['evotx_nonce'], 'evotx_save_ticketholder_data')) {
            $order_id = absint($_POST['order_id']);
            $order = wc_get_order($order_id);

            if ($order && $order->get_meta('_needs_ticketholder_data') === 'yes') {
                if (isset($_POST['tixholders']) && is_array($_POST['tixholders'])) {
                    $tixholders_data = [];
                    foreach ($_POST['tixholders'] as $event_id => $ris) {
                        foreach ($ris as $ri => $qtys) {
                            foreach ($qtys as $qty => $instances) {
                                foreach ($instances as $instance => $ticket_data) {
                                    foreach ($ticket_data as $key => $value) {
                                        $tixholders_data[$event_id][$ri][$qty][$instance][$key] = sanitize_text_field($value);
                                    }
                                }
                            }
                        }
                    }

                    $order->update_meta_data('_tixholders', $tixholders_data);
                    $order->update_meta_data('_needs_ticketholder_data', 'no');
                    $order->save();

                    do_action('evotx_checkout_fields_saving', $order_id, $order);

                    // Redirect back to order details page with success message
                    wp_redirect(add_query_arg(['ticketholder_success' => '1'], $order->get_view_order_url()));
                    exit;
                }
            }
        }
    }
}
new EVOTX_Woo_Extrafields();
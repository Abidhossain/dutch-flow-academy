<?php
/**
 * Ticket Addon Helpers for ticket addon extensions
 * @updated 2.4.15
 */

class evotx_helper extends evo_helper{

	// select data html content
		function print_select_data_element( $args){
			$dd = array_merge(array(
				'class'=>'evotx_other_data',
				'data'=>array()
			), $args);
			extract($dd);

			echo "<div class='{$class}' ". $this->array_to_html_data( $data ) ."></div>";
		}
		
	// convert a value to proper currency	
		public function process_price($price) {
			if (is_array($price) ) return [     'decimal' => $price,  'formatted' => $price,  'plain' => $price  ];

		    // Step 1: Parse input to a float
		    $price_str = (string) $price;
		    $price_str = trim($price_str);
		    $price_str = preg_replace('/[^0-9.,-]/', '', $price_str); // Remove non-numeric except .,,-

		    // Handle negative numbers
		    $is_negative = strpos($price_str, '-') === 0;
		    $price_str = str_replace('-', '', $price_str);

		    // Detect decimal separator based on last occurrence of . or ,
		    $dot_pos = strrpos($price_str, '.');
		    $comma_pos = strrpos($price_str, ',');

		    if ($dot_pos !== false && $comma_pos !== false) {
		        // Last one is decimal separator
		        if ($dot_pos > $comma_pos) {
		            $price_str = str_replace(',', '', $price_str); // Comma is thousand separator
		        } else {
		            $price_str = str_replace('.', '', $price_str); // Dot is thousand separator
		            $price_str = str_replace(',', '.', $price_str); // Comma to decimal point
		        }
		    } elseif ($comma_pos !== false) {
		        $parts = explode(',', $price_str);
		        if (isset($parts[1]) && strlen($parts[1]) <= 2) {
		            $price_str = str_replace(',', '.', $price_str); // Comma as decimal
		        } else {
		            $price_str = str_replace(',', '', $price_str); // Comma as thousand
		        }
		    } elseif ($dot_pos !== false) {
		        $parts = explode('.', $price_str);
		        if (isset($parts[1]) && strlen($parts[1]) > 2) {
		            $price_str = str_replace('.', '', $price_str); // Dot as thousand
		        }
		    }

		    $decimal_number = floatval($price_str);
		    if ($is_negative) {
		        $decimal_number = -$decimal_number;
		    }

		    // Step 2: Use WooCommerce's wc_price for formatting
		    $formatted_with_symbol = wc_price($decimal_number);

		    // Step 3: Format without symbol using WooCommerce settings
		    $decimals = wc_get_price_decimals();
		    $decimal_separator = wc_get_price_decimal_separator();
		    $thousand_separator = wc_get_price_thousand_separator();
		    $formatted_without_symbol = number_format(
		        abs($decimal_number),
		        $decimals,
		        $decimal_separator,
		        $thousand_separator
		    );
		    if ($is_negative) {
		        $formatted_without_symbol = '-' . $formatted_without_symbol;
		    }

		    // Return array
		    $output =  array(
		        'decimal' => $decimal_number,          // 12.65 or 1332.88
		        'formatted' => $formatted_with_symbol, // "€12,65" or "€1.332,88"
		        'plain' => $formatted_without_symbol,   // "12,65" or "1.332,88"
		        'raw'=> $price,		        
		    );

		    // debug
		    $debug = false;
		    if( $debug){
		    	$output['settings' ]= [
		        	'decimals'=> $decimals,
		        	'decimal_separator'=> $decimal_separator,
		        	'thousand_separator'=> $thousand_separator,
		        ];
		        EVO_Debug($output);
		    }

		    return $output;
		}
		// @since 2.4.11
		public function convert_price_to_decimal( $price){
			if (is_array($price) || is_null($price) || $price === '') return null;
			return $this->process_price( $price)['decimal'];
		}
		// @since 2.4.15
		public function convert_price_to_plain($price){
			if (is_array($price) || is_null($price) || $price === '') return null;
			return $this->process_price( $price)['plain'];
		}
		public function convert_price_to_format($price, $symbol = true, $tax_label = false )	{
			if (is_array($price) || is_null($price) || $price === '') return null;
			$return = $symbol ? $this->process_price( $price)['formatted'] : $this->process_price( $price)['plain'];
			if ($tax_label && wc_tax_enabled()) {
		        $return .= ' <small class="woocommerce-Price-taxLabel tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
		    }			
			return $return;
		}

		function convert_to_currency($price, $symbol = true) { 
			return $this->convert_price_to_format( $price, $symbol);

		    extract(apply_filters('wc_price_args', 
		        wp_parse_args(array(), 
		            array(
		                'ex_tax_label'       => false,
		                'currency'           => '', // € from WC
		                'decimal_separator'  => wc_get_price_decimal_separator(), // ','
		                'thousand_separator' => wc_get_price_thousand_separator(), // '.'
		                'decimals'           => wc_get_price_decimals(), // 2
		                'price_format'       => get_woocommerce_price_format(), // '%1$s%2$s' (€12,65)
		            )
		        )
		    ));

		    // Convert price to string to handle input correctly
		    $price = (string) $price; // Input: "12.65"

		    // Normalize input: if input uses '.', but WC expects ',' as decimal, adjust it
		    if (strpos($price, '.') !== false && $decimal_separator === ',') {
		        $price = str_replace('.', ',', $price); // "12.65" -> "12,65"
		    }

		    // Remove thousand separator if present
		    if (!empty($thousand_separator) && strpos($price, $thousand_separator) !== false) {
		        $price = str_replace($thousand_separator, '', $price); // e.g., "1.234,56" -> "1234,56"
		    }

		    // Convert WC decimal separator to '.' for float conversion
		    if ($decimal_separator !== '.' && strpos($price, $decimal_separator) !== false) {
		        $price = str_replace($decimal_separator, '.', $price); // "12,65" -> "12.65"
		    }

		    // Convert to float
		    $price = floatval($price); // "12.65" -> 12.65
		    $original_price = $price;
		    $negative = $price < 0;

		    // Filter raw price
		    $price = apply_filters('raw_woocommerce_price', $negative ? $price * -1 : $price, $original_price);

		    // Format with WC separators
		    $price = number_format($price, $decimals, $decimal_separator, $thousand_separator); // 12.65 -> "12,65"

		    // Remove trailing zeros if enabled
		    if (apply_filters('woocommerce_price_trim_zeros', false) && $decimals > 0) {
		        $price = wc_trim_zeros($price);
		    }

		    // Apply currency symbol and format
		    $return = ($negative ? '-' : '') . sprintf(
		        $price_format, 
		        ($symbol ? get_woocommerce_currency_symbol($currency) : ''), // € 
		        $price // "12,65"
		    );

		    if ($ex_tax_label && wc_tax_enabled()) {
		        $return .= ' <small class="woocommerce-Price-taxLabel tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
		    }

		    return $return; // "€12,65"
		}

	// HTML: remaining stock
	// @added 1.7
		function remaining_stock_html($stock, $text='', $visible=true){
			$remaining_count = apply_filters('evotx_remaining_stock', (int)$stock);

			// text string
			if(empty($text)){
				$text = $remaining_count>1? 
					EVO()->frontend->lang('','evoTX_013','Tickets Remaining!') : 
					evo_lang('Ticket Remaining!');
			} 

			echo "<p class='evotx_remaining' data-count='{$remaining_count}' style='display:". ($visible?'block':'none')."'>
				<span class='evotx_remaining_stock'>";
			echo "<span>" . $remaining_count . "</span> ";
			echo $text;
			echo "</span></p>";
		}

	// HTML Price 
	// @updated: 1.7.3
		function base_price_html($price, $unqiue_class='', $striked_price = '', $label_additions='', $is_name_yp=false){

			if(empty($price)) $price = 0;

			// get all processed price data
			$reg_price_data = $this->process_price( $price );
			

			// if there is sales price set
			$strike_ = '';
			if( !empty( $striked_price) && $striked_price != $price){
				$sale_price_data = $this->process_price( $striked_price );
				$strike_ = "<span class='strikethrough' style='text-decoration: line-through'>". $sale_price_data['formatted'].'</span> ';
			}
			

			//EVO_Debug($this->process_price( '12,65'));
			//EVO_Debug($price);

			$label_addition  = !empty($label_additions)? " <span class='label_add' style='font-style:italic; text-transform:none;opacity:0.6'>". $label_additions.'</span> ':'';
			?>
			<div itemprop='offers' itemscope itemtype='http://schema.org/Offer'>
				<p itemprop="price" class='price tx_price_line <?php echo $unqiue_class;?> <?php echo $is_name_yp? 'nyp':''?>' content='<?php echo $price;?>'>
					<meta itemprop='priceCurrency' content='<?php echo get_woocommerce_currency_symbol();?>'/>
					<meta itemprop='availability' content='http://schema.org/InStock'/>
					<span class='evo_label evomarr10'><?php echo $is_name_yp ? evo_lang('Name your price'): evo_lang('Price');?><?php echo $label_addition;?></span> 

					<?php

					// Name your own price
					if($is_name_yp){
						EVO_Debug($nyp_price_data);
						?>
						<span class='nyp_val value evodfx evofx_ai_c' data-sp='<?php echo $nyp_price_data['decimal'];?>'>
							<?php echo get_woocommerce_currency_symbol();?>
							<input class='nyp' name='nyp' data-minnyp='<?php echo $nyp_price_data['decimal'];?>' value='<?php echo $nyp_price_data['plain'];?>'/>
						</span>
						<?php
					}else{?>
					<span class='value' data-sp='<?php echo $reg_price_data['decimal'];?>'><?php echo $strike_;?><?php echo $reg_price_data['formatted'];?></span>
					<?php }?>
					<input type="hidden" data-prices=''>
				</p>
			</div> 
			<?php
		}

	// nonce field
		function print_nonce_field($var='evotx_add_tocart'){
			wp_nonce_field($var);
		}

	function custom_item_meta($name, $value, $unqiue_class=''){
		?>
		<p class='evotx_ticket_other_data_line <?php echo $unqiue_class;?>'>
			<span class='evo_label'><?php echo $name;?></span> 
			<span class='value' ><?php echo $value;?></span>
		</p>
		<?php
	}
	function ticket_qty_html($max='', $unqiue_class=''){
		$max = empty($max)? '':$max;
		?>
		<p class="evotx_quantity dfxi evofx_jc_sb evodfxi">
			<span class='evo_label'><?php evo_lang_e('How many tickets?');?></span>
			<span class="qty evotx_qty_adjuster">
				<b class="min evotx_qty_change <?php echo $unqiue_class;?>">-</b><em>1</em>
				<b class="plu evotx_qty_change <?php echo $unqiue_class;?> <?php echo (!empty($max) && $max==1 )? 'reached':'';?>">+</b>
				<input type="hidden" name='quantity' value='1' data-max='<?php echo $max;?>'/>
			</span>
		</p>
		<?php
	}
	// @+1.7.2
	function ticket_qty_one_hidden(){
		?>
		<p class="evotx_quantity" style='display:none'>
			<span class="qty evotx_qty_adjuster">
				<input type="hidden" name='quantity' value='1' data-max='1'/>
			</span>
		</p>
		<?php
	}
		
	function total_price_html($price, $unqiue_class='', $wcid=''){
		?>
		<h4 class='evo_h4 evotx_addtocart_total <?php echo $unqiue_class;?>'>
			<span class="evo_label"><?php evo_lang_e('Total Price');?></span>
			<span class="value"  data-wcid='<?php echo $wcid;?>'><?php echo $this->convert_price_to_format($price);?></span>
		</h4>
		<?php
	}
	function add_to_cart_btn_html($btn_class='', $data_arg = array(), $cancel_btn_data = array() ){
		
		if(!isset($data_arg['green'])) $data_arg['green'] = 'y';
		
		$data_addition = $this->array_to_html_data( $data_arg );

		$can_btn_html = '';

		if( count($cancel_btn_data)> 0 ){
			$cancel_btn = array_merge(array(
				'name'=>__('Cancel'),
				'class'=>'evcal_btn',
				'style'=>'',
				'data'=> array()
			), $cancel_btn_data);
  
			extract($cancel_btn);

			$can_btn_html = "<span class='{$class}' style='{$style}' data-d='". json_encode($data) ."'>{$name}</span>";
		}
		?>
		<p class='evotx_addtocart_button'>
			<?php echo $can_btn_html;?>
			<button class="evcal_btn <?php echo $btn_class;?>" style='margin-top:10px' <?php echo $data_addition;?>><?php evo_lang_e('Add to Cart')?></button>
		</p>
		<?php
	}

	// Return price formatting values
		function get_price_format_data(){
			return array(
				'currencySymbol'=>get_woocommerce_currency_symbol(),
				'thoSep'=> htmlentities( get_option('woocommerce_price_thousand_sep'), ENT_QUOTES ),
				'curPos'=> get_option('woocommerce_currency_pos'),
				'decSep'=> get_option('woocommerce_price_decimal_sep'),
				'numDec'=> get_option('woocommerce_price_num_decimals')
			);
		}
		public function get_text_strings(){
			$R = array();
			foreach( apply_filters('evotx_addtocart_text_strings',array(
				't1'=> evo_lang('Added to cart'),
				't2'=> evo_lang('View Cart'),
				't3'=> evo_lang('Checkout'),
				't4'=> evo_lang('Ticket could not be added to cart, try again later!'),
				't5'=> evo_lang('Quantity of Zero can not be added to cart!'),
				't6'=> evo_lang('Price must be higher than minimum!'),
			)) as $t=>$tt){
				$R[ $t ] = htmlspecialchars( $tt, ENT_QUOTES);
			}
			return $R;
		}

	// success or fail message HTML after adding to cart	
	function add_to_cart_html($type='good', $msg=''){
		$newWind = (evo_settings_check_yn(EVOTX()->evotx_opt,'evotx_cart_newwin'))? 'target="_blank"':'';
		ob_start();
		if( $type =='good'):
			?>
			<p class='evotx_success_msg'><b><?php evo_lang_e('Added to cart');?>!</b></p>
			<?php
		else:
			if(empty($msg)) $msg = evo_lang('Ticket could not be added to cart, try again later');
			?>
			<p class='evotx_success_msg bad'><b><?php echo $msg;?>!</b></p>
			<?php
		endif;
		return ob_get_clean();
	}	

	function __get_addtocart_msg_footer($type='', $msg=''){
		?>
		<div class='tx_wc_notic evotx_addtocart_msg marb20'>
		<?php
			if( !empty($type) ){
				echo $this->add_to_cart_html($type, $msg);
			}
		?>
		</div>
		<div class='evotx_cart_actions' style='display:<?php echo $type == 'standalone' ? 'block':'none';?>'>
			<?php 
			$new_window = EVO()->cal->check_yn('evotx_cart_newwin','evcal_tx') ?  'target="_blank"':'';
			?>
			<a class='evcal_btn' href="<?php echo wc_get_cart_url();?>" <?php echo $new_window;?>><?php evo_lang_e('View Cart');?></a> 
			<a class='evcal_btn' href="<?php echo wc_get_checkout_url();?>" <?php echo $new_window;?>><?php evo_lang_e('Checkout');?></a></span>
		</div>
		<?php
	}

// deprecating functions
	public function convert_currency_to_number($price) {
	    extract(apply_filters('wc_price_args', 
	        wp_parse_args(array(), 
	            array(
	                'ex_tax_label'       => false,
	                'currency'           => '',
	                'decimal_separator'  => wc_get_price_decimal_separator(), // ','
	                'thousand_separator' => wc_get_price_thousand_separator(), // '.'
	                'decimals'           => wc_get_price_decimals(), // 2
	                'price_format'       => get_woocommerce_price_format(),
	            )
	        )
	    ));

	    // Start with price as string
	    $price = (string) $price; // "12.65"

	    // Check for decimal separator first
	    $decimal_pos = strpos($price, $decimal_separator); // Check for ','
	    $dot_pos = strpos($price, '.');

	    if ($decimal_pos !== false) {
	        // If WC decimal separator (',') is present, convert it to dot
	        $price = str_replace($decimal_separator, '.', $price); // "12,65" -> "12.65"
	    } elseif ($dot_pos !== false && $decimal_separator === ',') {
	        // If dot is present and WC expects comma, treat dot as decimal (no change needed)
	        // "12.65" stays "12.65"
	    }

	    // Remove thousand separator only if it’s not the decimal
	    if (!empty($thousand_separator) && strpos($price, $thousand_separator) !== false && $thousand_separator !== '.') {
	        $price = str_replace($thousand_separator, '', $price);
	    }

	    // Convert to float
	    $price = floatval($price); // "12.65" -> 12.65

	    return $price;
	}

	// convert price string to decimal number @since 2.2.5
	// this is expecting the price to be in WC price format
		function convert_price_to_number( $price_string){

			$decSep = wc_get_price_decimal_separator();
			$thosSep = wc_get_price_thousand_separator();

			$price_string = str_replace( $thosSep , '', $price_string);
			$price_string = str_replace( $decSep , '.', $price_string);

			return floatval($price_string);
		}	

}
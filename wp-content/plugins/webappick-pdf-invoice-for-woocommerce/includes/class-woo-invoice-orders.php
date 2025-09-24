<?php
/**
 * Used to get formatted order information
 *
 * @link  https://webappick.com
 * @since 1.0.0
 *
 * @package    Woo_Invoice_Orders
 * @subpackage Woo_Invoice_Orders/includes
 */

/**
 * User: Md Ohidul Islam
 * Email: wahid0003@gmail.com
 * Date: 4/7/20
 * Time: 8:58 PM
 */
class Woo_Invoice_Orders
{

	/**
	 *  Init Helper Class
	 *
	 * @var Woo_Invoice_Helper  Helper Class.
	 */
	private $helper;
	/**
	 * Hold Order Ids
	 *
	 * @var array $order_ids Contain order Ids.
	 */
	private $order_ids;
	/**
	 * Template Type
	 *
	 * @var string $template
	 */
	private $template;

	/**
	 * Woo_Invoice_Orders constructor.
	 *
	 * @param array  $order_ids Order Ids.
	 * @param string $template  Template Type.
	 */
	public function __construct( $order_ids, $template = 'invoice' ) {
		$this->order_ids = $order_ids;
		$this->template  = $template;
		$this->helper    = woo_invoice_helper();
	}

	/**
	 * Get Orders info
	 *
	 * @return mixed
	 */
	public function get_orders_info() {
		if ( empty($this->order_ids) ) {
			return false;
		}
		$r      = 0;
		$orders = array();
		foreach ( $this->order_ids as $key => $order_id ) {
			$order                  = wc_get_order($order_id);
			$orders[ $r ]['status'] = '';
			if ( $order->is_paid() || 'completed' === $order->get_status() ) {
				$orders[ $r ]['status'] = 'completed';
			}

			$orders[ $r ]['ID']            = $order_id;
			$orders[ $r ]['order_info']    = $this->get_order_info($order);
			$orders[ $r ]['billing_info']  = $this->helper->get_address($order, 'billing', $this->template);
			$orders[ $r ]['shipping_info'] = $this->helper->get_address($order, 'shipping', $this->template);
			$orders[ $r ]['order_note']    = $order->get_customer_note();
			$orders[ $r ]['items']         = $this->get_order_items($order);
			$orders[ $r ]['bank_accounts'] = $this->helper->get_bank_accounts($order);

			$totals = array(
				'subtotal'          => $this->get_subtotal($order, $orders[ $r ]['items']),
				'discount_total'    => $this->get_discount_total($order),
				'tax_total'         => $this->get_tax_total($order),
				'shipping_total'    => $this->get_shipping_total($order),
				'total_without_tax' => $this->get_total_without_tax($order),
				'fees'              => $this->get_order_total_fees($order),
				'grand_total'       => $this->get_order_total($order),
				'total_refund'      => $this->get_refunded_total($order),
				'net_total'         => $this->get_net_total($order),
			);

			$packing_total = array(
				'quantity' => $this->get_product_total_quantity($order),
				'weight'   => $this->get_product_total_weight($order),
			);

			// Invoice Total Filter.
			if ( has_filter('woo_invoice_order_total') ) {
				$totals = apply_filters('woo_invoice_order_total', $totals, $this->template, $order);
			}

			// Packing total filter.
			if ( has_filter('woo_invoice_packing_total') ) {
				$packing_total = apply_filters('woo_invoice_packing_total', $packing_total, $this->template, $order);
			}

			$orders[ $r ]['totals']        = $totals;
			$orders[ $r ]['packing_total'] = $packing_total;

			$r++;
		}

		return $orders;
	}

	/**
	 * Get Porduct total quantity.
	 *
	 * @param WC_Order $order Order Object.
	 *
	 * @return array
	 */
	private function get_product_total_quantity( $order ) {
		$item_quantity = 0;
		foreach ( $order->get_items() as $item_id => $item_data ) {
			$item_quantity += $item_data->get_quantity(); // Get the item quantity.
		}
		return $item_quantity;
	}

	/**
	 * Get product total weight
	 *
	 * @param WC_Order $order Order Object.
	 *
	 * @return array
	 */
	private function get_product_total_weight( $order ) {
		$items                = $order->get_items();
		$products             = array();
		$product_total_weight = 0;
		foreach ( $items as $key => $item ) {
			$item_info  = $item->get_data();
			$product_id = $item_info['product_id'];
			$product    = wc_get_product($product_id);
			if ( 'packing_slip' === $this->template ) { // Packing Slip Item Data.
				$quantity = $item->get_quantity();
				$products[ $key ]['weight'] = wc_format_weight($this->get_item_weight($product, $quantity));
			}
			if ( ! empty($products[ $key ]['weight']) ) {
				$product_total_weight += (float) $products[ $key ]['weight'];
			}
		}

		return apply_filters('woo_invoice_packing_total_weight', $product_total_weight, $items, $order, $this->template );
	}


	/**
	 * Get Order Items.
	 *
	 * @param WC_Order $order Order Object.
	 *
	 * @return array
	 */
	private function get_order_items( $order ) {
		$items    = $order->get_items();
		$products = array();

		foreach ( $items as $key => $item ) {
			$item_info  = $item->get_data();
			$item_quantity = $item->get_quantity();
			$product_id = $item_info['product_id'];

			if ( $item_info['variation_id'] ) {
				$product_id = $item_info['variation_id'];
			}

			$product                          = wc_get_product( $product_id );
			$products[ $key ]['id']           = $product_id;
			$products[ $key ]['raw_total']    = $item_info['total'];
			$products[ $key ]['raw_price']    = $item_info['subtotal'] / $item_info['quantity'];
			$products[ $key ]['raw_quantity'] = $item->get_quantity();
			$products[ $key ]['raw_title']    = $item->get_name();
			$products[ $key ]['raw_weight']   = $this->get_item_weight( $product, $item_quantity );

			$products[ $key ]['product-img'] = $this->get_item_image( $product );
			$products[ $key ]['product']     = $this->get_item_title( $order, $product, $item->get_name(), $item );

			if ( 'packing_slip' === $this->template ) { // Packing Slip Item Data.
				$products[ $key ]['dimension'] = wc_format_dimensions( $this->get_item_dimension( $product ) );
				$products[ $key ]['weight']    = wc_format_weight( $this->get_item_weight( $product, $item_quantity ) );
				$products[ $key ]['quantity']  = $item->get_quantity();
			} elseif ( 'invoice' === $this->template ) { // Invoice Item Data.
				$products[ $key ]['price']          = $this->helper->format_price( $order, $item_info['subtotal'] / $item_info['quantity'] );

				if ( is_a( $product, 'WC_Product' ) ) {
					$products[ $key ]['regular_price'] = $this->helper->format_price( $order, $product->get_regular_price() );
				} else {
					// Product doesn't exist, set regular price to price
					$products[ $key ]['regular_price'] = $this->helper->format_price( $order, $item_info['subtotal'] / $item_info['quantity'] );
				}

				$products[ $key ]['quantity']       = $this->get_item_quantity( $order, $item->get_id(), $item->get_quantity() );
				$products[ $key ]['total']          = $this->get_item_total_price( $order, $item->get_id(), $item_info['total'] );
				$products[ $key ]['tax']            = $this->get_item_tax( $order, $item, $item_info['total_tax'] );
				$products[ $key ]['discount']       = $this->helper->format_price( $order, $item->get_subtotal() - $item->get_total() );
			}
		}

		if ( has_filter( 'woo_invoice_product_data' ) ) {
			$products = apply_filters( 'woo_invoice_product_data', $products, $this->template, $order );
		}

		return $products;
	}

	/**
	 * Get Order Item Price
	 *
	 * @param  WC_Order $order   Order Object.
	 * @param  int      $item_id Item Id.
	 * @param  float    $price   Item Price.
	 * @return mixed|string
	 */
	private function get_item_total_price( $order, $item_id, $price ) {
		// Get the refunded amount for a line item.
		$price = $this->helper->format_price($order, $price);

		return $price;
	}

	/**
	 * Get Order Item Quantity
	 *
	 * @param  WC_Order $order   Order Object.
	 * @param  int      $item_id Item Id.
	 * @param  int      $qty     Item Quantity.
	 * @return mixed|string
	 */
	private function get_item_quantity( $order, $item_id, $qty ) {
		// Get the refunded quantity for a line item.
		$item_qty_refunded = $order->get_qty_refunded_for_item($item_id);
		if ( $item_qty_refunded < 0 ) {
			$qty = $qty . "<br/><span class='refund'>" . $item_qty_refunded . '</span>';
		}

		return '<small class="times">&times;</small> ' . $qty;
	}


	/**
	 * Get Order Item Tax
	 *
	 * @param WC_Order      $order Order Object.
	 * @param WC_Order_Item $item  Order Item Object.
	 * @param float         $tax   $item tax.
	 *
	 * @return mixed|string
	 */
	private function get_item_tax( $order, $item, $tax ) {
		// Get the refunded tax amount for a line item.
		$item_tax_refunded = $order->get_tax_refunded_for_item($item->get_id(), $this->tax_rate_id($order));
		$tax               = $this->helper->format_price($order, $tax);
		if ( $item_tax_refunded > 0 ) {
			$item_tax_refunded = $this->helper->format_price($order, $item_tax_refunded);
			$tax               = $tax . "<br/><span class='refund'>-" . $item_tax_refunded . '</span>';
		}

		return $tax;
	}

	/**
	 * Get Tax rate id
	 *
	 * @param WC_Order $order Order Object.
	 *
	 * @return mixed
	 */
	private function tax_rate_id( $order ) {
		foreach ( $order->get_items('tax') as $item_id => $item_tax ) {
			return $item_tax->get_rate_id();
		}
	}

	/**
	 * Get items subtotal without tax
	 *
	 * @param  WC_Order $order Order Object.
	 * @param  array    $items Order Items array.
	 * @return mixed|string
	 */
	private function get_subtotal( $order, $items ) {
		// return $order->get_subtotal_to_display(); phpcs:ignore.
		// $item_subtotal = array_sum( array_column( $items, 'raw_total' ) ); phpcs:ignore.
		// return $this->helper->format_price( $order, $item_subtotal ); phpcs:ignore.
		return $this->helper->format_price($order, $order->get_subtotal());
	}

	/**
	 * Get Order data for order info section
	 *
	 * @param WC_Order $order Order Object.
	 *
	 * @return array
	 */
	private function get_order_info( $order ) {
		$payment_method_text = ! empty(get_option('wpifw_payment_method_text')) ? get_option('wpifw_payment_method_text') : 'Payment Method';
		$invoice_number_text = ! empty(get_option('wpifw_INVOICE_NUMBER_TEXT')) ? get_option('wpifw_INVOICE_NUMBER_TEXT') : 'Invoice No';
		$order_number_text   = ! empty(get_option('wpifw_ORDER_NUMBER_TEXT')) ? get_option('wpifw_ORDER_NUMBER_TEXT') : 'Order No';
		$order_date_text     = ! empty(get_option('wpifw_ORDER_DATE_TEXT')) ? get_option('wpifw_ORDER_DATE_TEXT') : 'Order Date';
		$order_info          = array();

		if ( '1' === get_option('wpifw_payment_method_show') || '' === get_option('wpifw_payment_method_show') ) {
			$order_info['payment_method'] = $order->get_payment_method_title();
		}

		$order_no   = $this->helper->get_order_number($order);
		$order_date = $this->helper->get_formatted_date($order);
		$invoice_no = woo_invoice_get_invoice_number($order->get_id());

		$order_info['order_number']   = apply_filters('woo_invoice_order_number', $order_no, $this->template, $order);
		$order_info['order_date']     = apply_filters('woo_invoice_order_date', $order_date, $this->template, $order);
		$order_info['invoice_number'] = apply_filters('woo_invoice_invoice_number', $invoice_no, $this->template, $order);

		// Add Order metas according to settings.
		$get_order_metas = $this->get_order_post_meta($order->get_id());
		if ( ! empty($get_order_metas) ) {
			$order_info = $order_info + $get_order_metas;
		}

		if ( has_filter('woo_invoice_order_data') ) {
			$order_info = apply_filters('woo_invoice_order_data', $order_info, $this->template, $order);
		}

		return $order_info;
	}

	/**
	 * Get Order Post Meta Value
	 *
	 * @param  int $order_id Order Id.
	 * @return array|string
	 */
	private function get_order_post_meta( $order_id ) {
		if ( 'invoice' === $this->template && get_option('wpifw_custom_order_meta') && ! empty(get_option('wpifw_custom_order_meta')) ) {
			$post_meta = array();
			foreach ( get_option('wpifw_custom_order_meta') as $key => $value ) {
				if ( '' !== get_post_meta($order_id, $key, true) ) {
					$post_meta[ $value ] = get_post_meta($order_id, $key, true);
				}
			}

			return $post_meta;
		}

		return '';
	}

	/**
	 * Get total tax
	 *
	 * @param WC_Order $order Order Object.
	 *
	 * @return mixed
	 */
	private function get_tax_total( $order ) {
		return ( $order->get_total_tax() > 0 ) ? $this->helper->format_price($order, $order->get_total_tax()) : '';
	}

	/**
	 * Get total without tax
	 *
	 * @param WC_Order $order Order Object.
	 *
	 * @return mixed|string
	 */
	private function get_total_without_tax( $order ) {
		$total_without_tax = (float) $order->get_total() - (float) $order->get_total_tax();

		return $this->helper->format_price($order, $total_without_tax);
	}

	/**
	 * Get grand total without tax
	 *
	 * @param WC_Order $order Order Object.
	 *
	 * @return mixed|string
	 */
	private function get_order_total( $order ) {
		return $this->helper->format_price($order, $order->get_total());
	}

	/**
	 * Get order fees
	 *
	 * @param WC_Order $order Order Object.
	 *
	 * @return mixed|string
	 */
	private function get_order_total_fees( $order ) {
		if ( method_exists($order, 'get_total_fees') ) {
			$raw_fees = $order->get_total_fees();
			$fees     = $this->helper->format_price($order, $raw_fees);
			return ! empty($raw_fees) ? $fees : '';
		}
		return false;
	}

	/**
	 * Get Shipping total
	 *
	 * @param WC_Order $order Order Object.
	 *
	 * @return mixed|string
	 */
	private function get_shipping_total( $order ) {
		if ( $order->get_shipping_total() > 0 ) {
			if ( $order->get_shipping_tax() > 0 ) {
				if ( 'wpifw_invoice_display_shipping_total_without_tax' === get_option('wpifw_invoice_display_shipping_total') ) {
					$shipping_without_tax = $order->get_shipping_total();
				}elseif ( 'wpifw_invoice_display_shipping_total_with_tax' === get_option('wpifw_invoice_display_shipping_total') ) {
					$shipping_without_tax = $order->get_shipping_total() + $order->get_shipping_tax();
				}
			} else {
				$shipping_without_tax = $order->get_shipping_total();
			}
			return $this->helper->format_price($order, $shipping_without_tax);
		}
		return '';
	}

	/**
	 * Get Discount total
	 *
	 * @param WC_Order $order Order Object.
	 *
	 * @return mixed|string
	 */
	private function get_discount_total( $order ) {
		if ( ! $order->get_discount_total() ) {
			return false;
		}

		return $this->helper->format_price($order, $order->get_discount_total());
	}

	/**
	 * Get Discount total
	 *
	 * @param WC_Order $order Order Object.
	 *
	 * @return mixed|string
	 */
	private function get_refunded_total( $order ) {
		if ( ! $order->get_total_refunded() ) {
			return false;
		}

		return $this->helper->format_price($order, $order->get_total_refunded());
	}

	/**
	 * Get Discount total
	 *
	 * @param WC_Order $order Order Object.
	 *
	 * @return mixed|string
	 */
	private function get_net_total( $order ) {
		if ( ! $order->get_total_refunded() ) {
			return false;
		}

		$net_total = $order->get_total() - $order->get_total_refunded();

		return $this->helper->format_price($order, $net_total);
	}

	/**
	 * Get Product Title
	 *
	 * @param WC_Order   $order      Order Object.
	 * @param WC_Product $product    Product Object.
	 * @param string     $item_title Item Title.
	 *
	 * @return mixed|string
	 */
	private function get_item_title( $order, $product, $item_title, $item ) {
		if ( $product && $product instanceof WC_Product ) {
			return $this->get_product_title($order, $product->get_id(), $item_title, $this->template, $item);
		}

		return '<b>'.$item_title.'</b>';
	}

	/**
	 * Get Product information
	 *
	 * @param WC_Order $order      Order Object.
	 * @param integer  $product_id Product id.
	 * @param string   $title      Order Item title.
	 * @param string   $template   Template Type.
	 *
	 * @return string
	 */
	private function get_product_title( $order, $product_id, $title, $template , $item ) {
		$name    = '';
		$product = wc_get_product($product_id);

        // Check if the setting to remove invalid characters is enabled
        $remove_invalid_characters = get_option( 'remove_invalid_characters_from_title', false );

        // If enabled, remove invalid characters from the title
        if ( $remove_invalid_characters ) {
            $title = preg_replace( '/[^\p{L}\p{N}\s\-_]/u', '', $title );
        }

		$sku = $product->get_sku();
		// Product Title Length Setting.
		$product_title_length = get_option('wpifw_invoice_product_title_length');

		if ( ! empty($product_title_length && strlen($title) > $product_title_length) ) {
			$name .= '<p><b>' . substr($title, 0, $product_title_length) . '</b>...';
		} else {
			$name .= '<p><b>' . $title . '</b>';
		}
		$name .= "<span class='product-meta'>";
		// Action Before the item meta.
		$name .= woo_invoice_before_item_meta($product, $order, $template);

		// Show SKU or ID.
		$display_info = get_option('wpifw_disid');

		if ( ! empty($display_info) ) {
			if ( 'ID' === $display_info ) {
				$name .= "<br/><span class='product-meta'>Id:" . $product_id . '</span>';
			} elseif ( 'SKU' === $display_info ) {
				if ( ! empty($sku) ) {
					$name .= "<br/><span class='product-meta'>sku: " . $sku . '</span>';
				}
			}
		}

        if ( 'invoice' === $template ) {
            $item_meta_label = get_option('_winvoice_order_item_meta_label');
            $item_meta_name = get_option('_winvoice_order_item_meta_name');
            if ( $item_meta_name ) {
                $item_meta_val = wc_get_order_item_meta($item->get_id(), $item_meta_name, true);
            }
            if ( ! empty($item_meta_val) && ! is_array($item_meta_val) ) {
                $name .= "<br/><span class='product-meta'>$item_meta_label: " . $item_meta_val . '</span>';
            }
        }

        if ( 'invoice' === $template ) {
            $post_meta_label = get_option('_winvoice_post_meta_label');
            $post_meta_name = get_option('_winvoice_post_meta_name');
            if ( $post_meta_name ) {
				$product_type = $product->get_type();
				if ( 'variation' === $product_type ) {
					$post_meta_val = apply_filters( 'woo_invoice_meta_value_filter', get_post_meta( $item->get_variation_id() , $post_meta_name, true), $post_meta_name);
				} else {
					$post_meta_val = apply_filters( 'woo_invoice_meta_value_filter', get_post_meta( $item->get_product_id() , $post_meta_name, true), $post_meta_name);
				}
            }
            if ( ! empty($post_meta_val) && ! is_array($post_meta_val) ) {
                $name .= "<br/><span class='product-meta'>$post_meta_label: " . $post_meta_val . '</span>';
            }
        }

		// Action After the item meta.
		$name .= woo_invoice_after_item_meta($product, $order, $template);

		$name .= '</p></span>';

		return $name;
	}

	/**
	 * Get Product Weight
	 *
	 * @param  WC_Product $product  Product Object.
	 * @param  WC_Product $quantity Product Quanity.
	 * @return mixed|string
	 */
	private function get_item_weight( $product, $quantity ) {
		if ( $product instanceof WC_Product ) {
			$product_weight = $product->get_weight();
			if ( $product_weight ) {
				$product_weight = ( $quantity > 0 ) ? $product_weight * $quantity : $product_weight;
				return $product_weight;
			}
		}
		return '';
	}

	/**
	 * Get Product Dimension
	 *
	 * @param WC_Product $product Product Object.
	 *
	 * @return mixed|string
	 */
	private function get_item_dimension( $product ) {
		if ( $product && $product instanceof WC_Product ) {
			return array(
				( $product->get_width() ) ? $product->get_width() : '',
				( $product->get_height() ) ? $product->get_height() : '',
				( $product->get_length() ) ? $product->get_length() : '',
			);
		}

		return array();
	}

	/**
	 * Get Product Image
	 *
	 * @param WC_Product $product Product Object.
	 *
	 * @return mixed|string
	 */
	private function get_item_image( $product ) {
		if ( $product && $product instanceof WC_Product ) {
			return $product->get_image(
				'woocommerce_gallery_thumbnail',
				array(
					'width'  => 50,
					'height' => 50,
					'crop'   => 0,
				)
			);
		}

		return '';
	}
}

/**
 * Initialize Woo_Invoice_Orders class
 *
 * @param  array  $order_ids Order Ids.
 * @param  string $template  Template Type.
 * @return Woo_Invoice_Orders
 */
function woo_invoice_orders( $order_ids, $template ) {
	return new Woo_Invoice_Orders($order_ids, $template);
}

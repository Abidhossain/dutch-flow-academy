<?php
/**
 * Used to get formatted order information
 *
 * @link  https://webappick.com
 * @since 1.0.0
 *
 * @package    Woo_Invoice_Helper
 * @subpackage Woo_Invoice_Helper/includes
 */

/**
 * User: Md Ohidul Islam
 * Email: wahid0003@gmail.com
 * Date: 4/7/20
 * Time: 8:58 PM
 */
class Woo_Invoice_Helper
{


	/**
	 * Get WooCommerce Country Object.
	 *
	 * @var WC_Countries
	 */
	private $countries;

	/**
	 * Woo_Invoice_Helper constructor.
	 */
	public function __construct() {
		$this->countries = new WC_Countries();
	}

	/**
	 * Get formatted order date according to plugin settings
	 *
	 * @param WC_Order $order Order Object.
	 *
	 * @return mixed
	 */
	public function get_formatted_date( $order ) {
		// Set formatted order date.
		$format     = '';
		$get_format = get_option('wpifw_date_format') ? get_option('wpifw_date_format') : 'd/m/Y';
		if ( ! empty($get_format) ) {
			$format = $get_format;
		}

		return $order->get_date_created()->date_i18n($format);
	}

	/**
	 * Get Order Number
	 *
	 * @param WC_Order $order Order Object.
	 *
	 * @return mixed|string
	 */
	public function get_order_number( $order ) {
		$order_no = '';

		if ( empty($order_no) ) {
			$order_no = $order->get_order_number();
		}

		// Process order number macros.
		$order_no = woo_invoice_process_date_macros($order->get_id(), $order_no);

		return $order_no;
	}

	/**
	 * Get Billing Address
	 *
	 * @param WC_Order $order    Order Object.
	 * @param string   $type     Value: billing or shipping.
	 * @param string   $template Value: invoice or packing_slip.
	 * @param string   $column   Used for Delivery Address.
	 *
	 * @return string
	 */
	public function get_address( $order, $type, $template, $column = null ) {

		if ( 'billing' === $type ) { // Get Billing Address.

			$fname = $order->get_billing_first_name();
			$lname = $order->get_billing_last_name();
			$name = $fname .' '. $lname;
			$company_name = $order->get_billing_company();
			$billing_address_1 = $order->get_billing_address_1();
			$billing_address_2 = $order->get_billing_address_2();
			$billing_city = $order->get_billing_city();
			$billing_state_code = $order->get_billing_state(); //print billing state code
			$billing_post_code = $order->get_billing_postcode();
			$billing_country = WC()->countries->countries[ $order->get_billing_country() ];
			$billing_phone = $order->get_billing_phone();
			$billing_email = $order->get_billing_email();

			//Apply a filter for billing state name
			$billing_state_name = apply_filters( 'woo_invoice_state_name', $billing_state_code,  $order->get_billing_country(), $order, WC()->countries);

			ob_start();
			if ( isset( $name ) ) {
				echo esc_html( $name );
			}
			if ( isset( $company_name ) ) {
				echo '<p>'. esc_html( $company_name ).'<p>';
			}
			if ( isset( $billing_address_1 ) ) {
				echo '<p>' . esc_html( $billing_address_1 ).'<p>';
			}
			if ( isset( $billing_address_2 ) ) {
				echo '<p>'.esc_html( $billing_address_2 ) .'<p>';
			}
			if ( isset( $billing_city ) && ! empty( $billing_city ) ) {
				echo '<p>' . esc_html( $billing_city );
				if ( isset( $billing_post_code ) && ! empty( $billing_post_code ) ) {
					echo ', ' . esc_html( $billing_post_code );
				}
				echo '</p>';
			} elseif ( isset( $billing_post_code ) && ! empty( $billing_post_code ) ) {
				echo '<p>' . esc_html( $billing_post_code ) . '</p>';
			}
			if ( isset( $billing_state_name ) ) {
				echo '<p>'.esc_html( $billing_state_name ) . '<p>';
			}
			if ( isset( $billing_country ) ) {
				echo '<p>' . esc_html( $billing_country ).'<p>';
			}
			if ( empty( get_option('wpifw_display_phone' ) ) && 1 != get_option('wpifw_display_phone' ) ) {
				echo '<p>' . woo_invoice_free_filter_label('Phone', $order, $template) . ' : ' . $order->get_billing_phone() . '<p>'; //phpcs:ignore
			}
			if ( empty( get_option('wpifw_display_email' ) ) && 1 != get_option('wpifw_display_email' ) ) {
				echo '<p>' . woo_invoice_free_filter_label('Email', $order, $template) . ' : ' . $order->get_billing_email() . '<p>'; //phpcs:ignore
			}

			$billing = ob_get_contents();
			ob_end_clean();
			return $billing;

//            if (! empty(get_option('wpifw_buyer')) ) {
//                return $this->get_custom_address($order, $type, $template, $column = null);
//            } else {
//                return $order->get_formatted_billing_address();
//            }

		} elseif ( 'shipping' === $type ) { // Get SHipping Address.
			return $order->get_formatted_shipping_address();

		}

		if ( 'label' === $template ) {
			// return $this->get_custom_address($order, $type, $template);
			$delivery_address_data = '';
			$order_id = $order[0]['ID'];
			$order_data = wc_get_order( $order_id );
			$delivery_address_data .= $order_data->get_formatted_shipping_address();
			if ( empty( get_option('wpifw_display_phone' ) ) && 1 != get_option('wpifw_display_phone' ) ) {
				$delivery_address_data .= '<br>' . $order_data->get_billing_phone();
			}
			if ( empty( get_option('wpifw_display_phone' ) ) && 1 != get_option('wpifw_display_phone' ) ) {
				$delivery_address_data .= '<br>' . $order_data->get_billing_email();
			}
			return $delivery_address_data;

		}
	}


	/**
	 * Get Custom Formatted Billing/Shipping Address
	 *
	 * @param WC_Order $order    Order Object.
	 * @param string   $type     Value: billing or shipping.
	 * @param string   $template Value: invoice or packing_slip.
	 * @param string   $column   Used for Delivery Address.
	 *
	 * @return string|bool
	 */
	private function get_custom_address( $order, $type, $template ) {
		$order_id = $order[0]['ID'];
		$details  = '';
		if ( 'billing' === $type ) {
			$details = get_option( 'wpifw_buyer' );
		} elseif ( 'shipping' === $type ) {
			$details = get_option( 'wpifw_buyer_shipping_address' );
		} elseif ( 'label' === $type ) {
			$details = $details = "
				{{shipping_first_name}}	{{shipping_last_name}}
				{{shipping_company}}	{{shipping_address_1}}
				{{shipping_address_2}}	{{shipping_city}}
				{{shipping_state}}	{{shipping_postcode}}
				{{shipping_country}}
				{{billing_phone}}
				{{billing_email}} ";

			if ( ! empty( $details ) ) {
				preg_match_all( '/{{(.*?)}}/', $details, $matches );
				$to_replace   = $matches[0];
				$replace_with = array();

				if ( 'shipping' === $type ) {
					$country_code = get_post_meta( $order_id, '_shipping_country', true );
				} elseif ( 'billing' === $type ) {
					$country_code = get_post_meta( $order_id, '_billing_country', true );
				} else {
					$country_code = get_post_meta( $order_id, '_shipping_country', true );
					if ( empty( $country_code ) ) {
						$country_code = get_post_meta( $order_id, '_billing_country', true );
					}
				}

				foreach ( $matches[1] as $key => $meta_key ) {
					$is_type_meta = substr( "$meta_key", 0, 1 );

					$get_meta = get_post_meta( $order_id, $meta_key, true );

					// If meta not found then add underscore and try again.
					if ( empty( $get_meta ) ) {
						if ( '_' !== $is_type_meta ) {
							$meta_key = '_' . $meta_key;
						}
						$get_meta = get_post_meta( $order_id, $meta_key, true );
					}

					if ( is_array( $get_meta ) ) {
						$get_meta = implode( '-', $get_meta );
					}

					if ( strpos( $meta_key, 'billing_state' ) !== false || strpos( $meta_key, 'shipping_state' ) !== false ) {
						//$get_meta = $this->get_state_label( $country_code, $get_meta );
					}

					if ( strpos( $meta_key, 'shipping_country' ) !== false || strpos( $meta_key, 'billing_country' ) !== false ) {
						$get_meta = $this->get_country_label( $get_meta );
					}

					$get_meta = ! empty( $get_meta ) ? $get_meta : '';
					array_push( $replace_with, $get_meta );
				}

				// Replace Billing information according to customers settings.
				$address = str_replace( $to_replace, $replace_with, $details );

				// Remove Empty Line.
				$address = preg_replace( "/\n\n/", "\n", $address );
				$address = preg_replace( "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", '<br>', $address );
				$address = str_replace( '<br><br>', '<br>', $address );

				return $address;
			}

			return false;
		}
	}

	/**
	 * Get Billing Address
	 *
	 * @param WC_Order $order    Order Object.
	 * @param string   $type     Value: billing or shipping.
	 * @param string   $template Value: invoice or packing_slip.
	 * @param string   $column   Used for Delivery Address.
	 *
	 * @return string
	 */
	public function get_address2( $order, $type, $template, $column = null ) {

		if ( 'billing' === $type ) {
			$from    = '';
			$details = get_option('wpifw_buyer');
		} elseif ( 'shipping' === $type ) {

			// Return empty if Billing and Shipping Address Same.
			if ( get_option('wpifw_display_shipping_address') ) {
				if ( get_option('wpifw_hide_for_same_address') ) {
					if ( $order->get_billing_address_1() === $order->get_shipping_address_1() ) {
						return '';
					}
				}
			}

			$from    = '';
			$details = get_option('wpifw_buyer_shipping_address');
		} elseif ( 'label' === $type ) {
			$from    = get_option('wpifw_delivery_address_block_title_to');
			$details = get_option('wpifw_delivery_address_buyer');
		}

		$order_id = $order->get_id();
		$address  = '';

		if ( ! empty($details) ) {
			preg_match_all('/{{(.*?)}}/', $details, $matches);
			$to_replace   = $matches[0];
			$replace_with = array();

			if ( 'shipping' === $type ) {
				$country_code = get_post_meta($order_id, '_shipping_country', true);
			} elseif ( 'billing' === $type ) {
				$country_code = get_post_meta($order_id, '_billing_country', true);
			} else {
				$country_code = get_post_meta($order_id, '_shipping_country', true);
				if ( empty($country_code) ) {
					$country_code = get_post_meta($order_id, '_billing_country', true);
				}
			}

			foreach ( $matches[1] as $key => $meta_key ) {
				$is_type_meta = substr("$meta_key", 0, 1);

				$get_meta = get_post_meta($order_id, $meta_key, true);

				// If meta not found then add underscore and try again.
				if ( empty($get_meta) ) {
					if ( '_' !== $is_type_meta ) {
						$meta_key = '_' . $meta_key;
					}
					$get_meta = get_post_meta($order_id, $meta_key, true);
				}

				if ( is_array($get_meta) ) {
					$get_meta = implode('-', $get_meta);
				}

				if ( strpos($meta_key, 'billing_state') !== false || strpos($meta_key, 'shipping_state') !== false ) {
					$get_meta = $this->get_state_label($country_code, $get_meta);
				}

				if ( strpos($meta_key, 'shipping_country') !== false || strpos($meta_key, 'billing_country') !== false ) {
					$get_meta = $this->get_country_label($get_meta);
				}

				$get_meta = ! empty($get_meta) ? $get_meta : '';
				array_push($replace_with, $get_meta);
			}

			// Replace Billing information according to customers settings.
			$address = str_replace($to_replace, $replace_with, $details);

			// Remove Empty Line.
			$address = preg_replace("/\n\n/", "\n", $address);
			$address = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", '<br>', $address);
			$address = str_replace('<br><br>', '<br>', $address);

		}

		// Add SSN and VAT ID if billing address.
		if ( 'invoice' == $template && 'billing' == $type ) {
			if ( get_option('wpifw_display_vat_id') ) {
				// Get VAT Label.
				$vat = get_option('wpifw_VAT_ID');
				$vat = ( false !== $vat || ! empty($vat) ) ? $vat : 'VAT ID';
				// Get VAT Number.
				if ( ! empty(get_post_meta($order_id, 'wpifw_vat_id', true)) ) {
					$address .= '<br>' . __('VAT ID', 'webappick-pdf-invoice-for-woocommerce') . ': ' . get_post_meta($order_id, 'wpifw_vat_id', true);
				} else {
					if ( get_user_meta($order->get_user_id(), 'wpifw_vat', true) != '' ) {
						$address .= '<br>' . __('VAT ID', 'webappick-pdf-invoice-for-woocommerce') . ': ' . get_user_meta($order->get_user_id(), 'wpifw_vat', true);
					}
				}
			}

			if ( get_option('wpifw_display_ssn') ) {
				// Get SSN Label.
				$ssn = get_option('wpifw_SSN');
				$ssn = ( false !== $ssn || ! empty($ssn) ) ? $ssn : 'SSN';
				// Get SSN Number.
				if ( ! empty(get_post_meta($order_id, 'wpifw_ssn_id', true)) ) {
					$address .= '<br>' . esc_attr__('SSN', 'webappick-pdf-invoice-for-woocommerce') . ': ' . get_post_meta($order_id, 'wpifw_ssn_id', true);
				} else {
					if ( ! empty(get_user_meta($order->get_user_id(), 'wpifw_ssn', true)) ) {
						$address .= '<br>' . esc_attr__('SSN', 'webappick-pdf-invoice-for-woocommerce') . ': ' . get_user_meta($order->get_user_id(), 'wpifw_ssn', true);
					}
				}
			}
		}
		if ( 'label' === $template && ! empty($from) ) {
			$address = '<div style="float:left;width:' . $column . '%"><p><b>' . $from . '</b><br>' . $address . '</p></div>';
		}

		return $address;
	}



	/**
	 * Get tax rate by product id
	 *
	 * @param string $id Product id.
	 *
	 * @return float|mixed
	 */
	public function product_tax_rate( $id ) {
		$product        = wc_get_product($id);
		$tax            = new WC_Tax();
		$tax_rate_class = $product->get_tax_class();
		if ( ! empty($tax_rate_class) ) {
			$tax_rate = $tax->get_rates($tax_rate_class);
			$tax_rate = reset($tax_rate);
		} else {
			$tax_rate = round(reset(WC_Tax::get_rates())['rate']);
		}

		return $tax_rate;
	}

	/**
	 * Get Country label by country code
	 *
	 * @param string $country_code Country Code.
	 *
	 * @return mixed
	 */
	private function get_country_label( $country_code ) {
		if ( empty($country_code) ) {
			return false;
		}

		$countries = $this->countries->get_countries();

		return $countries[ $country_code ];
	}

	/**
	 * Get State label by Country code and State code
	 *
	 * @param string $country_code Country Code.
	 * @param string $state_code   State Code.
	 *
	 * @return mixed
	 */
	private function get_state_label( $country_code, $state_code ) {
		if ( empty($country_code) || empty($state_code) ) {
			return false;
		}

		$states = $this->countries->get_states($country_code);

		return $states[ $state_code ];
	}

	/**
	 * Format price with WooCommerce number format and order currency
	 *
	 * @param WC_Order $order Order Object.
	 * @param integer  $price Product Price.
	 *
	 * @return mixed|string
	 */
	public function format_price( $order, $price ) {
		$missing_currencies = array(
			'BDT' => '&#2547;&nbsp;',
			'BTC' => '&#3647;',
			'CRC' => '&#x20a1;',
			'GEL' => '&#x20be;',
			'ILS' => '&#8362;',
			'KPW' => '&#x20a9;',
			'KRW' => '&#8361;',
			'LAK' => '&#8365;',
			'MNT' => '&#x20ae;',
			'MUR' => '&#x20a8;',
			'MVR' => '.&#x783;',
			'NPR' => '&#8360;',
			'PKR' => '&#8360;',
			'PYG' => '&#8370;',
			'RUB' => '&#8381;',
			'SCR' => '&#x20a8;',
			'THB' => '&#3647;',
			'TRY' => '&#8378;',
			'VND' => '&#8363;',
		);
		if ( get_option('wpifw_currency_code') ) {
			$price = number_format(
				$price,
				wc_get_price_decimals(),
				wc_get_price_decimal_separator(),
				wc_get_price_thousand_separator()
			);
			if ( 'left' === get_option('woocommerce_currency_pos') || 'left_space' === get_option('woocommerce_currency_pos') ) {
				$price = $order->get_currency() . ' ' . $price;
			}
			if ( 'right' === get_option('woocommerce_currency_pos') || 'right_space' === get_option('woocommerce_currency_pos') ) {
				$price = $price . ' ' . $order->get_currency();
			}
		} else {
			$price = wc_price($price, array( 'currency' => $order->get_currency() ));
			if ( ! get_option('wpifw_currency_code') ) {
				if ( array_key_exists($order->get_currency(), $missing_currencies) ) {

					$price = str_replace('woocommerce-Price-currencySymbol"', 'woocommerce-Price-currencySymbol" style="font-family: currencies;font-size:15px"', $price);
				}
			}
		}

		return $price;
	}

	/**
	 * Resize & Get Invoice logo according to plugin settings
	 *
	 * @return string
	 */
    public function get_invoice_logo() {
        $logo_html = '';

        if (false !== get_option('wpifw_logo_attachment_id')) {
            $image_id = 0;
            $image_data = get_option('wpifw_logo_attachment_id');

            if (is_string($image_data) && (strpos($image_data, 'http://') === 0 || strpos($image_data, 'https://') === 0)) {
                $image_id = attachment_url_to_postid($image_data);
                if ($image_id) {
                    update_option('wpifw_logo_attachment_id', get_attached_file($image_id));
                    update_option('wpifw_logo_attachment_image_id', $image_id);
                }
            } else {
                $image_id = get_option('wpifw_logo_attachment_image_id');
            }

            if ($image_id) {
                $logo_html = $this->get_clean_image($image_id);
            }
        } elseif (has_custom_logo()) {
            $custom_logo_id = get_theme_mod('custom_logo');
            if ($custom_logo_id) {
                $logo_html = $this->get_clean_image($custom_logo_id);
            }
        }

        return $logo_html ? apply_filters('woo_invoice_store_logo', $logo_html) : '';
    }

    /**
     * Generate a clean image without width and height.
     */
    private function get_clean_image($attachment_id) {
        $img_html = wp_get_attachment_image($attachment_id, 'full', false, [
            'class' => 'logo',
            'style' => 'width:' . esc_attr(get_option('wpifw_logo_width', '20%')),
        ]);

        // Remove width and height attributes
        $img_html = preg_replace('/(width|height)="\d*"\s/', "", $img_html);

        return $img_html;
    }

    /**
	 * Seller Info according to plugin settings
	 *
	 * @return string
	 */
	public function get_seller_info() {
		$company = get_option('wpifw_cname');
		$address  = str_replace("\n", '<br>', stripslashes(get_option('wpifw_cdetails')));

		return "<b>$company</b><br>$address";
	}

	/**
	 * Get Direct Bank Transfer accounts info
	 *
	 * @param WC_Order $order Order Object.
	 *
	 * @return array|bool
	 */
	public function get_bank_accounts( $order ) {
		if ( get_option('wpifw_display_bank_account') && 'bacs' === $order->get_payment_method() ) {
			$bank_accounts = get_option('woocommerce_bacs_accounts');
			if ( ! empty($bank_accounts) ) {
				return $bank_accounts;
			}
		}

		return false;
	}
}

/**
 * Initialize Helper class into this function
 *
 * @return Woo_Invoice_Helper
 */
function woo_invoice_helper() {
	return new Woo_Invoice_Helper();
}

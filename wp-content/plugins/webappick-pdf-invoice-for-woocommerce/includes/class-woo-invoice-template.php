<?php
/**
 * Used to generate template according to template type.
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
class Woo_Invoice_Template {


	/**
	 * Helper class variable
	 *
	 * @var Woo_Invoice_Helper
	 */
	public $helper;
	/**
	 * Orders Class variable
	 *
	 * @var Woo_Invoice_Orders
	 */
	public $orders;
	/**
	 * Order Object
	 *
	 * @var WC_Order
	 */
	private $order;
	/**
	 * Template Type
	 *
	 * @var string
	 */
	private $template_type;
	/**
	 * Arabic RTL
	 *
	 * @var string
	 */
	private $rtl = '';

	/**
	 * Woo_Invoice_Template constructor.
	 *
	 * @param array $order_ids Order Id or Ids.
	 * @param string $template_type Template Type.
	 */
	public function __construct( $order_ids, $template_type = 'invoice' ) {
		$this->helper        = woo_invoice_helper();
		$this->orders        = woo_invoice_orders( $order_ids, $template_type );
		$this->template_type = $template_type;

	}

	/**
	 * Set RTL status for arabic languages.
	 *
	 * @return string
	 */
	private function set_rtl() {
		global $locale;
		if ( false !== strpos( $locale, 'ar' )
		     || false !== strpos( $locale, 'he' )
		     || false !== strpos( $locale, 'he_IL' )
		) {
			$this->rtl = 'rtl';
		} else {
			$this->rtl = '';
		}

		return $this->rtl;
	}

	/**
	 * Get Invoice Template
	 *
	 * @return string
	 */
	public function get_invoice_template() {
		$template         = '';
		$page_break       = 'pageBreak';
		$product_per_page = ( get_option( 'wpifw_invoice_product_per_page' ) ) ? get_option( 'wpifw_invoice_product_per_page' ) : 6;
		$product_per_page = apply_filters( 'wpifw_invoice_product_per_page', $product_per_page );
		$orders           = $this->orders->get_orders_info();
		$total_page       = count( $orders );
		$page_loop        = 0;
		$template         .= $this->get_css();
		foreach ( $orders as $o_key => $order ) {
			$this->set_rtl();
			$this->order   = ( $order['ID'] ) ? wc_get_order( $order['ID'] ) : null;
			$product_chunk = array_chunk( $order['items'], $product_per_page );
			$total_chunk   = count( $product_chunk );
			if ( $total_chunk > 1 ) {
				$total_page += ( $total_chunk - 1 );
			}
			$chunk = 0;
			$page_loop ++;
			$product_loop = 1;
			foreach ( $product_chunk as $p_key => $page ) {
				// Calculate Page Break.
				if ( $chunk > 0 ) {
					$page_loop ++;
				}
				if ( $total_page === $page_loop ) {
					$page_break = '';
				}

				// $page_break = $page_break . " " . $page_loop . " " . $total_page;
				// $product_loop = $page_break . " " . $product_loop . " " . $total_chunk;

				$template .= $this->get_html_start( $page_break );
				$template .= $this->get_header_section();
				$template .= $this->get_order_section( $order );
				$template .= $this->get_product_section( $page );

				if ( $product_loop === $total_chunk ) {
					$template .= $this->get_product_total_section( $order );
					$template .= $this->get_order_note_section( $order['order_note'] );
				}

				$template .= $this->get_footer_section();

				$product_loop ++;
				$chunk ++;
			}
		}
		$template .= $this->get_html_end();

		return $template;
	}

	/**
	 * Get Packing Slip Template
	 *
	 * @return string
	 */
	public function get_packing_template() {
		$template         = '';
		$page_break       = 'pageBreak';
		$product_per_page = ( get_option( 'wpifw_packingslip_product_per_page' ) ) ? get_option( 'wpifw_packingslip_product_per_page' ) : 6;
		$orders           = $this->orders->get_orders_info();
		$total_page       = count( $orders );
		$page_loop        = 0;
		$template         .= $this->get_css();
		foreach ( $orders as $o_key => $order ) {
			$this->set_rtl();
			$this->order   = ( $order['ID'] ) ? wc_get_order( $order['ID'] ) : null;
			$product_chunk = array_chunk( $order['items'], $product_per_page );
			$total_chunk   = count( $product_chunk );
			if ( $total_chunk > 1 ) {
				$total_page += ( $total_chunk - 1 );
			}
			$chunk = 0;
			$page_loop ++;
			$product_loop = 1;
			foreach ( $product_chunk as $p_key => $page ) {
				// Calculate Page Break.
				if ( $chunk > 0 ) {
					$page_loop ++;
				}
				if ( $total_page === $page_loop ) {
					$page_break = '';
				}

				// $page_break = $page_break . " " . $page_loop . " " . $total_page;
				// $product_loop = $page_break . " " . $product_loop . " " . $total_chunk;

				$template .= $this->get_html_start( $page_break );
				$template .= $this->get_header_section();
				$template .= $this->get_order_section( $order );
				$template .= $this->get_product_section( $page );
				$template .= $this->get_packing_total_section( $order );
				$template .= $this->get_footer_section();

				$product_loop ++;
				$chunk ++;
			}
		}
		$template .= $this->get_html_end();

		return $template;
	}

	/**
	 * Get Delivery Address Template
	 *
	 * @param int $column Page Column.
	 * @param int $row Label per page.
	 * @param int $font_size Font Size.
	 *
	 * @return string
	 */
	public function get_delivery_address_template() {
		$rtl     = $this->set_rtl();
		$content = '';
		$content .= $this->get_css();
		$orders  = $this->orders->get_orders_info();
		$content .= "<div dir='$rtl'>";
		// get single shipping label.
		$content .= $this->helper->get_address( $orders, 'label', $this->template_type );
		$content .= '</div>';

		return $content;

	}


	/**
	 * Load Style for Template
	 *
	 * @return false|string
	 */
	public function get_css() {
		$font_size = ( get_option( 'wpifw_invoice_font_size' ) ) ? get_option( 'wpifw_invoice_font_size' ) : '11';
		$rtl       = ( get_option( 'wpifw_rtl' ) ) ? 'rtl' : '';

		ob_start();

		// Load CSS File for template.
		$file  = '';
		$title = '';
		if ( 'invoice' === $this->template_type ) {
			$template = get_option( 'wpifw_templateid' );
			$file     = plugin_dir_path( __FILE__ ) . "templates/$template.css";
			$title    = 'Invoice';
			$title    = apply_filters( 'woo_invoice_invoice_title', $title );
		} elseif ( 'packing_slip' === $this->template_type ) {
			$file  = plugin_dir_path( __FILE__ ) . 'templates/packing_slip.css';
			$title = 'Packing Slip';
			$title = apply_filters( 'woo_invoice_packing_slip_title', $title );
		} elseif ( 'label' === $this->template_type ) {
			$file  = plugin_dir_path( __FILE__ ) . 'templates/delivery_address.css';
			$title = 'Delivery Address';
			$title = apply_filters( 'woo_invoice_delivery_address_title', $title );
		}
		// Default css.
		$default_css      = '';
		$default_css_file = apply_filters( 'woo_invoice_default_css_file', plugin_dir_path( __FILE__ ) . 'templates/default.css' );
		if ( file_exists( $default_css_file ) ) {
			$default_css = file_get_contents( $default_css_file ); //phpcs:ignore
		}
		// template css.
		$template_css = '';
		$file         = apply_filters( 'woo_invoice_template_css_file', $file );
		if ( file_exists( $file ) ) {
			$template_css = file_get_contents( $file );//phpcs:ignore
		}

		?>
        <html>
        <title><?php echo esc_attr__( $title, 'webappick-pdf-invoice-for-woocommerce' ); //phpcs:ignore ?></title>
        <head>
            <style>
                <?php echo $default_css; //phpcs:ignore ?>
                <?php echo $template_css; //phpcs:ignore ?>
            </style>
            <style>
                <?php
				$custom_css = woo_invoice_custom_style($this->template_type); // Load custom css from action hook.
				echo esc_attr($custom_css);
				echo( ! empty(get_option('wpifw_custom_css')) ? esc_html(get_option('wpifw_custom_css')) : '' );
				?>
            </style>
        </head>
        <body style="font-size:<?php echo esc_attr( $font_size ) . 'px'; ?>">
		<?php
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Start Template Body
	 *
	 * @param string $page_break Page break counter for new page.
	 *
	 * @return false|string
	 */
    public function get_html_start( $page_break = '' ) {
        ob_start();
        $rtl_css = '';
        if ( 'rtl' === $this->rtl && file_exists( plugin_dir_path( __FILE__ ) . 'templates/invoice-rtl.css' ) ) {
            $rtl_css = file_get_contents( plugin_dir_path( __FILE__ ) . 'templates/invoice-rtl.css' );
        }
        ?>

            <style><?php echo esc_attr( $rtl_css ); ?></style>
            <div dir="<?php echo $this->rtl; ?>" class='invoice-box <?php echo $page_break; //phpcs:ignore?>'>
                <?php
                echo woo_invoice_before_document( $this->order, $this->template_type );//phpcs:ignore
                $html = ob_get_contents();
                ob_end_clean();

        return $html;
    }

	/**
	 * Load Header
	 */
	public function get_header_section() {
		ob_start();
		$logo   = $this->helper->get_invoice_logo();
		$seller = $this->helper->get_seller_info();
		$seller = apply_filters( 'woo_invoice_seller_info', $seller, $this->template_type );
		// Action Before the seller address.
		$seller_before = woo_invoice_before_seller_info( $this->order, $this->template_type );
		// Action After the seller address.
		$seller_after = woo_invoice_after_seller_info( $this->order, $this->template_type );
		?>
        <table class="header-table" border="">
            <tbody>
            <tr>
                <td class="site-logo">
					<?php echo $logo; //phpcs:ignore?>
                </td>
                <td class="seller">
					<?php echo "<span class='seller-info-before'>$seller_before</span>"; //phpcs:ignore?>
					<?php echo "<span class='seller-info'>$seller</span>"; //phpcs:ignore?>
					<?php echo "<span class='seller-info-after'>$seller_after</span>"; //phpcs:ignore?>
                </td>
            </tr>
            </tbody>
        </table>
        <br/><br/>
		<?php
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Load Order info section
	 *
	 * @param array $order Order Info.
	 *
	 * @return mixed
	 */
	public function get_order_section( $order ) {
		$billing  = apply_filters( 'woo_invoice_billing_info', $order['billing_info'], $this->order, $this->template_type );
		$shipping = apply_filters( 'woo_invoice_shipping_info', $order['shipping_info'], $this->order, $this->template_type );

		// Action Before the billing address.
		$billing_before = woo_invoice_before_billing_address( $this->order, $this->template_type );
		// Action After the billing address.
		$billing_after = woo_invoice_after_billing_address( $this->order, $this->template_type );
		// Action Before the shipping address.
		$shipping_before = woo_invoice_before_shipping_address( $this->order, $this->template_type );
		// Action After the shipping address.
		$shipping_after = woo_invoice_after_shipping_address( $this->order, $this->template_type );
		// Action Before the order data.
		$before_order_data = woo_invoice_before_order_data( $this->order, $this->template_type );
		// Action After the order data.
		$after_order_data = woo_invoice_after_order_data( $this->order, $this->template_type );
		$order_data       = $order['order_info'];
		ob_start();

		$billing  = ! empty( $billing ) ? '<b>' . woo_invoice_free_filter_label( 'Billing', $this->order, $this->template_type ) . '</b><br/>' . $billing_before . $billing : ''; //phpcs:ignore
		$shipping = ! empty( $shipping ) ? '<b>' . woo_invoice_free_filter_label( 'Shipping', $this->order, $this->template_type ) . '</b><br/>' . $shipping_before . $shipping : ''; //phpcs:ignore
		?>

        <table border="" class="order-table">
            <tbody>
            <tr>
                <td class="billing-td">
					<?php echo "<span class='billing-address'>" . $billing . '</span>'; //phpcs:ignore ?>
					<?php echo "<span class='billing-address-after'>" . $billing_after . '</span>'; //phpcs:ignore ?>
                </td>
				<?php if ( ! empty( $shipping ) ) : ?>
                    <td class="shipping-td">
						<?php echo "<span class='shipping-address'>" . $shipping . '</span>'; //phpcs:ignore ?>
						<?php echo "<span class='shipping-address-after'>" . $shipping_after . '</span>';  //phpcs:ignore ?>
                    </td>
				<?php endif; ?>
                <td class="order-data-td">
                    <table class="order-data-table">
                        <tbody>
                        <tr>
                            <td style="font-weight: bold;"
                                class="order-data-label"><?php echo esc_html( woo_invoice_free_filter_label( 'Invoice Number', $this->order, $this->template_type ) ); ?></td>
                            <td style="font-weight: bold;"
                                class="order-data-value"><?php echo ': ' . $order_data['invoice_number']; //phpcs:ignore?></td>
                        </tr>
						<?php
						// String before order data.
						if ( ! empty( $before_order_data ) ) {
							echo "<tr><td class='before-order-data'>" . $before_order_data . '</td></tr>'; //phpcs:ignore
						}
						?>
                        <tr>
                            <td class="order-data-label"><?php echo esc_html( woo_invoice_free_filter_label( "Order Number", $this->order, $this->template_type ) ); ?></td>
                            <td class="order-data-value"><?php echo ': ' . $order_data['order_number']; //phpcs:ignore?></td>
                        </tr>
                        <tr>
                            <td class="order-data-label"><?php echo esc_html( woo_invoice_free_filter_label( "Order Date", $this->order, $this->template_type ) ); ?></td>
                            <td class="order-data-value"><?php echo ': ' . $order_data['order_date']; //phpcs:ignore?></td>
                        </tr>
						<?php
						if ( ! empty( $order_data['payment_method'] ) ) {
							?>
                            <tr>
                                <td class="order-data-label"><?php echo esc_html( woo_invoice_free_filter_label( "Payment Method", $this->order, $this->template_type ) ); ?></td>
                                <td class="order-data-value"><?php echo ': ' . $order_data['payment_method']; //phpcs:ignore?></td>
                            </tr>
							<?php
						}

						unset( $order_data['payment_method'] );
						unset( $order_data['order_number'] );
						unset( $order_data['order_date'] );
						unset( $order_data['invoice_number'] );

						if ( ! empty( $order_data ) ) :
							foreach ( $order_data as $key => $value ) :
								?>
                                <tr>
                                    <td class="order-data-label"><?php echo esc_attr__( $key, 'webappick-pdf-invoice-for-woocommerce' ); //phpcs:ignore
										?></td>
                                    <td class="order-data-value"><?php echo ': ' . $value; ?></td> <?php //phpcs:ignore
									?>
                                </tr>
							<?php
							endforeach;
						endif;

						// String After order data.
						if ( ! empty( $after_order_data ) ) {
							echo "<tr><td class='after-order-data'>$after_order_data</td></tr>";//phpcs:ignore
						}

						?>
                        </tbody>
                    </table>
                </td>
            </tr>
            </tbody>
        </table>
        <br/>
		<?php
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Load Products table
	 *
	 * @param array $products Order Items.
	 *
	 * @return false|string
	 */
	public function get_product_section( $products ) {
		ob_start();
		// Action Before the product table.
		$before_product_list = woo_invoice_before_product_list( $this->order, $this->template_type );
		echo esc_html( $before_product_list );
		?>
        <table border="" class="product-table">
            <thead>
            <tr class="product-list-header">
                <th class="product-column"><?php echo esc_html( woo_invoice_free_filter_label( "Item", $this->order, $this->template_type ) ); ?></th>
				<?php if ( 'invoice' === $this->template_type ) : ?>
                <?php
                    $options = [
                        'price' => woo_invoice_free_filter_label('Cost', $this->order, $this->template_type),
                        'quantity' => woo_invoice_free_filter_label('Qty', $this->order, $this->template_type),
                        'total' => woo_invoice_free_filter_label('Total', $this->order, $this->template_type),
                        'discount' => woo_invoice_free_filter_label('Discount', $this->order, $this->template_type),
                    ];
                    $selected_option = get_option('wpifw_select_product_column', []);
                    if (empty($selected_option)) {
                        $selected_option = ['price', 'quantity', 'total'];
                    }
                    array_unshift($selected_option, 'product');

                    foreach ($selected_option as  $column) {
                        if(array_key_exists( $column, $options)){ ?>
                            <th class="<?php echo esc_attr( $column . '-column' ); ?>"><?php echo esc_html( $options[ $column ] ); ?></th>
                        <?php }
                    } ?>
				<?php elseif ( 'packing_slip' === $this->template_type ) :
                    $selected_option = ['product', 'weight', 'quantity'];
                    ?>
                    <th class="weight-column"><?php echo esc_html( woo_invoice_free_filter_label( 'Weight', $this->order, $this->template_type ) ); ?></th>
                    <th class="quantity-column"><?php echo esc_html( woo_invoice_free_filter_label( 'Quantity', $this->order, $this->template_type ) ); ?></th>
				<?php endif; ?>
            </tr>
            </thead>
            <tbody class="product-list-tbody">
            <?php
            foreach ($products as $key => $product) :
                unset($product['id']);
                unset($product['raw_total']);
                unset($product['raw_title']);
                unset($product['raw_quantity']);
                unset($product['raw_weight']);
                unset($product['product_meta']);

                ?>
                <tr class="product-list">
                    <!-- Available classes for td row -->
                    <!-- [ .product-img, .product, .price .quantity, .total] -->
		            <?php
		            foreach ($selected_option as $key) :
			            if (in_array($key, $selected_option)) :
				            ?>
                            <td class="<?php echo esc_html($key); ?>">
					            <?php echo $product[$key]; // phpcs:ignore ?>
                            </td>
			            <?php
			            endif;
		            endforeach; ?>
                </tr>
            <?php
            endforeach;
            ?>
            </tbody>
        </table>
        <br>
		<?php
		// Action After the product table.
		$after_product_list = woo_invoice_after_product_list( $this->order, $this->template_type );
		echo esc_html( $after_product_list );
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Load Order total table
	 *
	 * @param array $order Order Total info.
	 *
	 * @return string
	 */
	public function get_product_total_section( $order ) {
		$total    = $order['totals'];
		$row_span = 9;
		if ( ! $total['discount_total'] ) {
			-- $row_span;
		}

		if ( ! $total['tax_total'] || empty( $total['tax_total'] ) ) {
			-- $row_span;
		}

		if ( ! $total['shipping_total'] || empty( $total['shipping_total'] ) ) {
			-- $row_span;
		}

		if ( ! $total['total_refund'] ) {
			$row_span -= 2;
		}

		if ( empty( $total['fees'] ) ) {
			-- $row_span;
		}

		$product_total = '<table border="" class="order-total-table"><tbody>';
		$product_total .= "<tr><td rowspan='$row_span' class='paid-stamp'></td> <td class='order-total-label subtotal-label'>" . esc_html( woo_invoice_free_filter_label( 'Items Subtotal', $this->order, $this->template_type ) ) . " :</td><td class='order-total-value subtotal-value'>" . $total['subtotal'] . '</td></tr>';

		if ( ! empty( $total['fees'] ) ) {
			$product_total .= "<tr><td class='order-total-label fees-label'>" . esc_html( woo_invoice_free_filter_label( 'Fees', $this->order, $this->template_type ) ) . " :</td><td class='order-total-value fees-value'>" . $total['fees'] . '</td></tr>'; //phpcs:ignore
		}

		if ( ! empty( $total['shipping_total'] ) ) {
			$product_total .= "<tr><td class='order-total-label shipping-label'>" . esc_html( woo_invoice_free_filter_label( 'Shipping', $this->order, $this->template_type ) ) . " :</td><td class='order-total-value shipping-value'>" . $total['shipping_total'] . '</td></tr>'; //phpcs:ignore
		}

		if ( ! empty( $total['tax_total'] ) ) {
			$product_total .= "<tr><td class='order-total-label tax-label'>" . esc_html( woo_invoice_free_filter_label( 'Tax', $this->order, $this->template_type ) ) . " :</td><td class='order-total-value tax-value'>" . $total['tax_total'] . '</td></tr>';
		}

		$order_total = $total['grand_total'];
		$refund      = '';
		$net_total   = '';
		if ( isset( $total['total_refund'] ) && ! empty( $total['total_refund'] ) ) {
			$refund    = $total['total_refund'];
			$net_total = $total['net_total'];
		}
		// Run loop for filtered value.
		unset( $total['subtotal'] );
		unset( $total['shipping_total'] );
		unset( $total['tax_total'] );
		unset( $total['total_without_tax'] );
		unset( $total['grand_total'] );
		unset( $total['total_refund'] );
		unset( $total['net_total'] );
		unset( $total['fees'] );

        if(!$total['discount_total']){
            unset($total['discount_total']);
        }

		if ( ! empty( $total ) ) {
			foreach ( $total as $key => $value ) {
				if( 'discount_total' === $key ) {
					$product_total .= "<tr><td class='order-total-label'>".esc_html( woo_invoice_free_filter_label( 'Discount :', $this->order, $this->template_type ) ) ."</td><td class='order-total-value'>-$value</td></tr>";
				}else{
					$product_total .= "<tr><td class='order-total-label'>$key :</td><td class='order-total-value'>$value</td></tr>";
				}
			}
		}

		$product_total .= "<tr><td class='order-total-label'>" . esc_html( woo_invoice_free_filter_label('Order Total', $this->order, $this->template_type) ) . " :</td><td class='order-total-value'>" . $order_total . '</td></tr>'; //phpcs:ignore
		if ( ! empty( $refund ) ) {
			$product_total .= "<tr><td class='order-total-label '>" . esc_html( woo_invoice_free_filter_label( 'Refund', $this->order, $this->template_type ) ) . " :</td><td class='order-total-value  refund'>-" . $refund . '</td></tr>'; //phpcs:ignore
			$product_total .= "<tr><td class='order-total-label '>" . esc_html( woo_invoice_free_filter_label( 'Net Payment', $this->order, $this->template_type ) ) . " :</td><td class='order-total-value'>" . $net_total . '</td></tr>'; //phpcs:ignore
		}
        $product_total .= apply_filters('wpifw_before_last_tr_free', '', $this->order, $this->template_type); // apply a filter before last table row
		$product_total .= "<tr class='total-last-tr'><td class='order-total-label total-last-td-label'></td><td class='order-total-value total-last-td-value'></td></tr>";

		$product_total .= '</tbody></table><br/><br/>';
        $product_total .= apply_filters('wpifw_after_last_tr_free', '', $this->order, $this->template_type); // apply a filter after last table row

		return $product_total;
	}

	/**
	 * Load order note
	 *
	 * @param string $order_note Customer note.
	 *
	 * @return false|string
	 */
	public function get_order_note_section( $order_note ) {
		$wpifw_show_order_note = get_option( 'wpifw_show_order_note' ) === '' ? 1 : get_option( 'wpifw_show_order_note' );
		// Action before order note.
		$before_order_note = woo_invoice_before_customer_notes( $this->order, $this->template_type );
		// Action after order note.
		$after_order_note = woo_invoice_after_customer_notes( $this->order, $this->template_type );

		if ( ! empty( $order_note ) ) {
			$order_note = '<b>' . esc_html( woo_invoice_free_filter_label( 'Customer Note', $this->order, $this->template_type ) ) . ':</b> ' . $order_note;
		}

		if ( 'invoice' === $this->template_type && $wpifw_show_order_note ) {
			if ( has_filter( 'woo_invoice_customer_notes' ) ) {
				$order_note = apply_filters( 'woo_invoice_customer_notes', $order_note, $this->template_type, $this->order );
			}
		} elseif ( 'packing_slip' === $this->template_type && $wpifw_show_order_note ) {
			if ( has_filter( 'woo_invoice_customer_notes' ) ) {
				$order_note = apply_filters( 'woo_invoice_customer_notes', $order_note, $this->template_type, $this->order );
			}
		} else {
			$order_note = '';
		}

		ob_start();
		?>
        <table dir="<?php echo esc_html( $this->rtl ); ?>" class="order-note-table">
            <tbody>
			<?php echo ( ! empty( $before_order_note ) ) ? "<tr><td class='order-note-before'>" . $before_order_note . '</td></tr>' : ''; // phpcs:ignore ?>
			<?php echo ( ! empty( $order_note ) ) ? "<tr><td class='order-note'>" . $order_note . '</td></tr>' : ''; //phpcs:ignore ?>
			<?php echo ( ! empty( $after_order_note ) ) ? "<tr><td class='order-note-after'>" . $after_order_note . '</td></tr>' : ''; // phpcs:ignore ?>
            </tbody>
        </table>
		<?php

		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Get packing total info
	 *
	 * @param string $order Order.
	 *
	 * @return string
	 */
	public function get_packing_total_section( $order ) {
		$product_total = '';
		$packing_total = $order['packing_total'];
		$product_total .= '<table border="" class="packing-total-table" dir="' . $this->rtl . '"><tbody>';
		if ( ! empty( $packing_total ) ) {
			$rowspan                = count( $packing_total );
			$product_total_quantity = $packing_total['quantity'];
			$product_total_weight   = $packing_total['weight'];
			$weight_weight          = $product_total_weight . ' ' . get_option( 'woocommerce_weight_unit' );
			$product_total          .= "<tr><td class='order-total-label'>" . esc_html( woo_invoice_free_filter_label( 'Total Quantity', $this->order, $this->template_type ) ) . " :&nbsp;&nbsp;</td><td class='order-total-value'>" . $product_total_quantity . '</td></tr>';
			if ( $product_total_weight > 0 ) {
				$product_total .= "<tr><td class='order-total-label'>" . esc_html( woo_invoice_free_filter_label( 'Total Weight', $this->order, $this->template_type ) ) . " :&nbsp;&nbsp;</td><td class='order-total-value'>" . $weight_weight . '</td></tr>';
			}
			// Remove weight and quantity from array.
			unset( $packing_total['weight'] );
			unset( $packing_total['quantity'] );
			foreach ( $packing_total as $key => $value ) {
				$product_total .= "<tr><td class='order-total-label'>" . esc_html__( $key, 'webappick-pdf-invoice-for-woocommerce' ) . " :</td><td class='order-total-value'>" . $value . '</td></tr>'; //phpcs:ignore
			}
		}
		$product_total .= '</table>';

		return $product_total;
	}


	/**
	 * Load Footer Section
	 *
	 * @return false|string
	 */
public function get_footer_section() {
if ( 'invoice' === $this->template_type ) {
	ob_start();
	// Footer 1.
	$terms_and_condition = stripslashes( get_option( 'wpifw_terms_and_condition' ) );
	if ( has_filter( 'woo_invoice_footer_1' ) ) {
		$terms_and_condition = apply_filters( 'woo_invoice_footer_1', $terms_and_condition, $this->template_type, $this->order );
	}
	// Footer 2.
	$other_information = stripslashes( get_option( 'wpifw_other_information' ) );
	if ( has_filter( 'woo_invoice_footer_2' ) ) {
		$other_information = apply_filters( 'woo_invoice_footer_2', $other_information, $this->template_type, $this->order );
	}
	$footer_font_size = ( get_option( 'wpifw_invoice_footer_font_size' ) ) ? get_option( 'wpifw_invoice_footer_font_size' ) : '9';
	?>
    <htmlpagefooter name="invoiceFooter">
		<?php
		if ( '' != $terms_and_condition || '' != $other_information ) : ?>
            <hr class='invoice-footer-hr'>
		<?php
		endif;
		?>

        <div class="invoice-footer">
            <table border="0" class="invoice-footer-table">
                <tbody>
                <tr>
                    <td class="order-term-condition">
						<?php if ( ! empty( $terms_and_condition ) ) : ?>
                            <p style="font-size:<?php echo esc_attr( $footer_font_size ) . 'px'; ?>"><?php echo esc_html( $terms_and_condition ); ?></p>
                            <br>
						<?php endif;
						if ( ! empty( $other_information ) ) : ?>
                            <p style="font-size:<?php echo esc_attr( $footer_font_size ) . 'px'; ?>"><?php echo esc_html( $other_information ); ?></p>
						<?php endif; ?>
                    </td>
                </tr>
                </tbody>
            </table>
			<?php
			// Footer content added by action hook.
			$footer_content = woo_invoice_after_document( $this->order, $this->template_type );
			echo esc_html( $footer_content );
			?>
        </div>
    </htmlpagefooter>
    <sethtmlpagefooter name="invoiceFooter" value="1"/>
</div>
	<?php
	$html = ob_get_contents();
	ob_end_clean();

	return $html;
} else {
	return '</div>';
}

}

	/**
	 * Close HTML Tags
	 *
	 * @return false|string
	 */
	public function get_html_end() {
		ob_start();
		?>
        </body>
        </html>
		<?php
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}
}

/**
 * Load template class into function for reuse.
 *
 * @param array $order_ids Order Id or Ids.
 * @param string $template Template Type.
 *
 * @return Woo_Invoice_Template
 */
function woo_invoice_template( $order_ids, $template ) {
	return new Woo_Invoice_Template( $order_ids, $template );
}

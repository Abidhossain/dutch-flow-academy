<?php
/**
 * YITH_Report_Sales_By_Date Class
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Report_Sales_By_Date' ) ) {
	/**
	 * YITH_Report_Sales_By_Date class.
	 */
	class YITH_Report_Sales_By_Date extends WC_Admin_Report {

		/**
		 * The array of char data colors.
		 *
		 * @var array
		 */
		public $chart_colours = array();

		/**
		 * Report data array.
		 *
		 * @var object stdClass
		 */
		private $report_data;

		/**
		 * Get report data
		 *
		 * @return object
		 */
		public function get_report_data() {
			if ( empty( $this->report_data ) ) {
				$this->query_report_data();
			}

			return $this->report_data;
		}

		/**
		 * Get all data needed for this report and store in the class
		 *
		 * @return void
		 */
		private function query_report_data() {
			$vendor = yith_wcmv_get_vendor( 'current', 'user' );

			if ( ! $vendor || ! $vendor->is_valid() || ! $vendor->has_limited_access() ) {
				return;
			}

			$allowed_order_status = apply_filters( 'yith_wcmv_sales_by_date_allowed_order_status', array( 'completed', 'processing', 'on-hold' ) );

			$this->report_data = new stdClass();

			$date_query = YITH_Reports()->get_wp_query_date_args( $this->start_date, $this->end_date );
			$query_args = array_merge( array( 'vendor_id' => $vendor->get_id(), 'number' => 0  ), $date_query );
			$products   = $vendor->get_products();

			$commission_ids                              = yith_wcmv_get_commissions( array_merge( $query_args, array( 'status' => 'all' ) ) );
			$orders                                      = array();
			$amount                                      = array();
			$this->report_data->orders_all_count         = 0;
			$this->report_data->orders_all_product_total = 0;
			$this->report_data->orders_refunded_total    = 0;
			$this->report_data->orders_gross_amount      = 0;
			$this->report_data->orders_net_amount        = 0;
			$orders_all_product                          = array();

			foreach ( $commission_ids as $commision_id ) {
				$commission = yith_wcmv_get_commission( $commision_id );
				$order      = $commission ? $commission->get_order() : false;

				if ( ! $commission || ! $order || ! in_array( $order->get_status(), $allowed_order_status, true ) ) {
					continue;
				}

				$line_items = $order->get_items( 'line_item' );
				$order_id   = $order->get_id();
				// Order placed.
				if ( ! isset( $this->report_data->orders_count[ $order_id ] ) ) {
					$this->report_data->orders_count[ $order_id ]             = new stdClass();
					$this->report_data->orders_count[ $order_id ]->order_date = date_i18n( wc_date_format(), $order->get_date_created()->getTimestamp() );
					$this->report_data->orders_count[ $order_id ]->count      = 0;
					$orders_all_product[ $order_id ]                          = 0;
				}

				if ( ! isset( $amount[ $order_id ] ) ) {
					$amount[ $order_id ] = 0;
				}

				if ( 'shipping' !== $commission->get_type() ) {
					$current_currency = apply_filters( 'wc_aelia_cs_base_currency', '' );

					if ( ! empty( $_GET['report_currency'] ) && 'base' !== sanitize_text_field( wp_unslash( $_GET['report_currency'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
						$current_currency = sanitize_text_field( wp_unslash( $_GET['report_currency'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
					}

					$commission_amount   = apply_filters( 'wc_aelia_cs_convert', $commission->get_amount(), $order->get_currency(), $current_currency );
					$amount[ $order_id ] = $amount[ $order_id ] + $commission_amount;
				}

				if ( ! in_array( $order_id, $orders, true ) ) {
					$orders[] = $order_id;
					$this->report_data->orders_all_count++;
					foreach ( $line_items as $line_item_id => $line_item ) {
						if ( in_array( $line_item['product_id'], $products, true ) ) {
							$this->report_data->orders_all_product_total += $line_item['qty'];
						}
					}
				} else {
					continue;
				}

				$this->report_data->orders_count[ $order_id ]->count += 1;

				foreach ( $line_items as $line_item_id => $line_item ) {
					$this->report_data->orders_all_product[ $order_id ] = new stdClass();
					$this->report_data->orders_net[ $order_id ]         = new stdClass();
					if ( in_array( $line_item['product_id'], $products, true ) ) {
						// Net sales.
						$this->report_data->orders_net[ $order_id ]->order_date = yit_get_prop( $order, 'date_created' );

						// Items purchased.
						$orders_all_product[ $order_id ]                                += absint( $line_item['qty'] );
						$this->report_data->orders_all_product[ $order_id ]->order_date = date_i18n( wc_date_format(), $order->get_date_created()->getTimestamp() );
						$this->report_data->orders_all_product[ $order_id ]->count      = $orders_all_product[ $order_id ];
					} else {
						$this->report_data->orders_all_product[ $order_id ]->order_date = 0;
					}
				}
				if ( ! isset( $this->report_data->orders_all_product[ $order_id ] ) ) {
					$this->report_data->orders_all_product[$order_id] = new stdClass();
					$this->report_data->orders_all_product[ $order_id ]->count = $orders_all_product[ $order_id ];
					
				} else {
					$this->report_data->orders_all_product[ $order_id ]->count = $orders_all_product[ $order_id ];
				}
			}

			foreach ( $amount as $order_id => $line_total ) {
                if( ! isset( $this->report_data->orders_net[$order_id] ) ) {
					$this->report_data->orders_net[ $order_id ]         = new stdClass();
				}
				if ( ! isset( $this->report_data->orders_net[ $order_id ]->order_date ) ) {
					$this->report_data->orders_net[ $order_id ]->order_date = 0;
				}
				$this->report_data->orders_net[ $order_id ]->amount = $this->round_chart_totals( floatval( $line_total ) );
				$this->report_data->orders_net_amount               += floatval( $line_total );
			}
		}

		/**
		 * Get the legend for the main chart sidebar
		 *
		 * @return array
		 */
		public function get_chart_legend() {
			$legend = array();
			$data   = $this->get_report_data();

			$legend[] = array(
				// translators: %s stand for the commissions net amount in period.
				'title'            => sprintf( __( '%s net commissions in this period', 'yith-woocommerce-product-vendors' ), '<strong>' . wc_price( $data->orders_net_amount ) . '</strong>' ),
				'placeholder'      => __( 'This is the sum of the orders total after any refunds and excluding shipping and taxes.', 'yith-woocommerce-product-vendors' ),
				'color'            => $this->chart_colours['orders_net'],
				'highlight_series' => 2,
			);

			$legend[] = array(
				// translators: %s stand for the order number placed.
				'title'            => sprintf( __( '%s orders placed', 'yith-woocommerce-product-vendors' ), '<strong>' . $data->orders_all_count . '</strong>' ),
				'color'            => $this->chart_colours['orders_count'],
				'highlight_series' => 1,
			);

			$legend[] = array(
				// translators: %s stand for the item number purchased.
				'title'            => sprintf( __( '%s items purchased', 'yith-woocommerce-product-vendors' ), '<strong>' . $data->orders_all_product_total . '</strong>' ),
				'color'            => $this->chart_colours['products_count'],
				'highlight_series' => 0,
			);

			return $legend;
		}

		/**
		 * Output the report
		 */
		public function output_report() {

			$this->chart_colours = array(
				'orders_net'     => '#3498db',
				'orders_count'   => '#95a5a6',
				'products_count' => '#b1d4ea',
			);

			$current_range = YITH_Reports()->get_current_date_range();

			$this->calculate_current_range( $current_range );

			$args = array(
				'report'        => $this,
				'current_range' => $current_range,
				'ranges'        => YITH_Reports()->get_ranges(),
			);

			yith_wcmv_get_template( 'sales-by-date', $args, 'woocommerce/admin/reports' );
		}

		/**
		 * Round our totals correctly
		 *
		 * @param mixed $amount The chart totals amount.
		 * @return mixed
		 */
		private function round_chart_totals( $amount ) {
			if ( is_array( $amount ) ) {
				return array_map( array( $this, 'round_chart_totals' ), $amount );
			} else {
				return wc_format_decimal( $amount, wc_get_price_decimals() );
			}
		}

		/**
		 * Get the main chart
		 *
		 * @return void
		 */
		public function get_main_chart() {
			global $wp_locale;

			// Encode in json format.
			$chart_data = wp_json_encode(
				array(
					'orders_net'     => isset( $this->report_data->orders_net ) ? array_values( $this->prepare_chart_data( $this->report_data->orders_net, 'order_date', 'amount', $this->chart_interval, $this->start_date, $this->chart_groupby ) ) : false,
					'orders_count'   => isset( $this->report_data->orders_count ) ? array_values( $this->prepare_chart_data( $this->report_data->orders_count, 'order_date', 'count', $this->chart_interval, $this->start_date, $this->chart_groupby ) ) : false,
					'products_count' => isset( $this->report_data->orders_all_product ) ? array_values( $this->prepare_chart_data( $this->report_data->orders_all_product, 'order_date', 'count', $this->chart_interval, $this->start_date, $this->chart_groupby ) ) : false,
				)
			);

			?>
			<div class="chart-container">
				<div class="chart-placeholder main"></div>
			</div>
			<script type="text/javascript">

				var main_chart;

				jQuery(function () {
					var orders_data = jQuery.parseJSON('<?php echo $chart_data; ?>');
					var drawGraph = function (highlight) {
						var series = [
							{
								label: "<?php echo esc_js( __( 'Items purchased', 'yith-woocommerce-product-vendors' ) ); ?>",
								data: orders_data.products_count,
								color: "<?php echo esc_attr( $this->chart_colours['products_count'] ); ?>",
								points: {show: false},
								bars: {
									fillColor: '<?php echo esc_attr( $this->chart_colours['products_count'] ); ?>',
									fill: true,
									show: true,
									lineWidth: 0,
									barWidth: <?php echo esc_attr( $this->barwidth ); ?> * 0.5, align: 'center'
								},
								shadowSize: 0,
								hoverable: true,
								yaxis: 1
							},
							{
								label: "<?php echo esc_js( __( 'Orders count', 'yith-woocommerce-product-vendors' ) ); ?>",
								data: orders_data.orders_count,
								color: "<?php echo esc_attr( $this->chart_colours['orders_count'] ); ?>",
								points: {
									show: false,
									radius: 0
								},
								bars: {
									fillColor: '<?php echo esc_attr( $this->chart_colours['orders_count'] ); ?>',
									fill: true,
									show: true,
									lineWidth: 0,
									barWidth: <?php echo esc_attr( $this->barwidth ); ?> *0.5,
									align: 'center'
								},
								shadowSize: 0,
								hoverable: true,
								yaxis: 1
							},
							{
								label: "<?php echo esc_js( __( 'Net sales', 'yith-woocommerce-product-vendors' ) ); ?>",
								data: orders_data.orders_net,
								color: "<?php echo esc_attr( $this->chart_colours['orders_net'] ); ?>",
								points: {
									show: true
								},
								lines: {
									show: true,
									lineWidth: 2,
									fill: false
								},
								shadowSize: 0,
								hoverable: true,
								prepend_tooltip: "<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?>",
								yaxis: 2
							},
						];

						if ( highlight !== 'undefined' && series[highlight] ) {
							highlight_series = series[highlight];

							highlight_series.color = '#9c5d90';

							if ( highlight_series.bars )
								highlight_series.bars.fillColor = '#9c5d90';

							if ( highlight_series.lines ) {
								highlight_series.lines.lineWidth = 5;
							}
						}

						main_chart = jQuery.plot(
							jQuery('.chart-placeholder.main'),
							series,
							{
								legend: {
									show: false
								},
								grid: {
									color: '#aaa',
									borderColor: 'transparent',
									borderWidth: 0,
									hoverable: true
								},
								xaxes: [{
									color: '#aaa',
									position: "bottom",
									tickColor: 'transparent',
									mode: "time",
									timeformat: "<?php echo ( 'day' === $this->chart_groupby ) ? '%d %b' : '%b'; ?>",
									monthNames: <?php echo wp_json_encode( array_values( $wp_locale->month_abbrev ) ); ?>,
									tickLength: 1,
									minTickSize: [1, "<?php echo esc_attr( $this->chart_groupby ); ?>"],
									font: {
										color: "#aaa"
									}
								}],
								yaxes: [
									{
										min: 0,
										minTickSize: 1,
										tickDecimals: 0,
										color: '#d4d9dc',
										font: {color: "#aaa"}
									},
									{
										position: "right",
										min: 0,
										tickDecimals: 2,
										alignTicksWithAxis: 1,
										color: 'transparent',
										font: {color: "#aaa"}
									}
								],
							}
						);

						jQuery('.chart-placeholder').resize();
					}

					drawGraph();

					jQuery('.highlight_series').hover(
						function () {
							drawGraph(jQuery(this).data('series'));
						},
						function () {
							drawGraph();
						}
					);
				});
			</script>
			<?php
		}
	}
}


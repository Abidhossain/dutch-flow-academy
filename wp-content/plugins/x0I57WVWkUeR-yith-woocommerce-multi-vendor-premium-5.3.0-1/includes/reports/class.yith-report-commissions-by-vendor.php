<?php
/**
 * YITH_Report_Commissions_By_Vendor class
 *
 * @author      YITH
 * @package     YITH WooCommerce Multi Vendor
 * @version     2.1.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Report_Commissions_By_Vendor' ) ) {
	/**
	 * YITH_Report_Commissions_By_Vendor class
	 */
	class YITH_Report_Commissions_By_Vendor extends WC_Admin_Report {

		/**
		 * Report data array.
		 *
		 * @var array
		 */
		private $report_data;

		/**
		 * The array of char data colors.
		 *
		 * @var array
		 */
		public $chart_colours = array();

		/**
		 * The vendor id.
		 *
		 * @var integer
		 */
		public $vendor_id = 0;

		/**
		 * The array of vendors names.
		 *
		 * @var array
		 */
		public $vendor_ids_name = array();

		/**
		 * Class construct
		 *
		 * @return void
		 */
		public function __construct() {
			$this->prepare_report_data();
		}

		/**
		 * Get report data
		 *
		 * @param boolean $series (Optional) Include or not series to data array. Default is true.
		 * @return array
		 */
		public function get_report_data( $series = true ) {
			$data = $this->report_data;

			if ( false === $series ) {
				unset( $data['series'] );
			}

			return ! empty( $data ) ? $data : array();
		}

		/**
		 * Set report data
		 *
		 * @param array $report_data Reported data array to set.
		 * @return void
		 */
		public function set_report_data( $report_data ) {
			$this->report_data = $report_data;
		}

		/**
		 * Output the report
		 */
		public function output_report() {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( empty( $this->vendor_id ) && ! empty( $_GET['vendor_ids'] ) ) {
				$this->vendor_id = absint( $_GET['vendor_ids'] );
			}

			$this->chart_colours = array(
				'vendor_sales'  => '#d4d9dc',
				'vendor_amount' => '#3498db',
			);

			$args = array(
				'report'        => $this,
				'current_range' => YITH_Reports()->get_current_date_range(),
				'ranges'        => YITH_Reports()->get_ranges(),
			);

			yith_wcmv_get_template( 'commissions-by-vendor', $args, 'woocommerce/admin/reports' );
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Get the legend for the main chart sidebar
		 *
		 * @return array
		 */
		public function get_chart_legend() {

			$vendor_id = ! empty( $_GET['vendor_ids'] ) ? absint( $_GET['vendor_ids'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
			if ( ! $vendor_id ) {
				return array();
			}

			$report_data = $this->get_report_data();
			if ( ! isset( $report_data[ $vendor_id ] ) ) {
				$report_data[ $vendor_id ]['sales']        = 0;
				$report_data[ $vendor_id ]['items_number'] = 0;
			}

			$legend = array();

			$legend[] = array(
				// translators: %s stand for the vendor commissions amount.
				'title'            => sprintf( __( '%s commissions for the selected vendor', 'yith-woocommerce-product-vendors' ), '<strong>' . wc_price( $report_data[ $vendor_id ]['sales'] ) . '</strong>' ),
				'color'            => $this->chart_colours['vendor_amount'],
				'highlight_series' => 1,
			);

			$legend[] = array(
				// translators: %s stand for the vendor commissions amount.
				'title'            => sprintf( __( '%s commissions for the selected vendor', 'yith-woocommerce-product-vendors' ), '<strong>' . $report_data[ $vendor_id ]['items_number'] . '</strong>' ),
				'color'            => $this->chart_colours['vendor_sales'],
				'highlight_series' => 0,
			);

			return $legend;
		}

		/**
		 * [get_chart_widgets description]
		 *
		 * @return array
		 */
		public function get_chart_widgets() {

			$widgets = array();

			if ( ! empty( $this->vendor_id ) ) {
				$widgets[] = array(
					'title'    => __( 'Showing reports for:', 'yith-woocommerce-product-vendors' ),
					'callback' => array( $this, 'current_filters' ),
				);
			}

			$widgets[] = array(
				'title'    => '',
				'callback' => array( $this, 'vendors_widget' ),
			);

			return $widgets;
		}

		/**
		 * Show current filters
		 */
		public function current_filters() {

			$this->vendor_ids_name = array();

			$vendor = yith_wcmv_get_vendor( $this->vendor_id );

			if ( $vendor && $vendor->is_valid() ) {
				$this->vendor_ids_name[] = $vendor->get_name();
			} else {
				$this->vendor_ids_name[] = '#' . $vendor->get_id();
			}

			echo '<p><strong>' . esc_html( implode( ', ', $this->vendor_ids_name ) ) . '</strong></p>';
			echo '<p><a class="button" href="' . esc_url( remove_query_arg( 'vendor_ids' ) ) . '">' . esc_html__( 'Reset', 'yith-woocommerce-product-vendors' ) . '</a></p>';
		}

		/**
		 * Product selection
		 */
		public function vendors_widget() {
			// phpcs:disable WordPress.Security.NonceVerification
			$limit = get_option( 'yith_wpv_reports_limit', 10 );
			?>
			<h4 class="section_title">
				<span><?php esc_html_e( 'Search Vendors', 'yith-woocommerce-product-vendors' ); ?></span>
			</h4>
			<div class="section">
				<form method="GET">
					<div>
						<?php yit_add_select2_fields( YITH_Reports()->get_select2_args() ); ?>
						<input type="submit" class="submit button" value="<?php esc_html_e( 'Show', 'yith-woocommerce-product-vendors' ); ?>"/>
						<input type="hidden" name="range" value="<?php echo ! empty( $_GET['range'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['range'] ) ) ) : ''; ?>"/>
						<input type="hidden" name="start_date" value="<?php echo ! empty( $_GET['start_date'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['start_date'] ) ) ) : ''; ?>"/>
						<input type="hidden" name="end_date" value="<?php echo ! empty( $_GET['end_date'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['end_date'] ) ) ) : ''; ?>"/>
						<input type="hidden" name="page" value="<?php echo ! empty( $_GET['page'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) : ''; ?>"/>
						<input type="hidden" name="tab" value="<?php echo ! empty( $_GET['tab'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) : ''; ?>"/>
						<input type="hidden" name="report" value="<?php echo ! empty( $_GET['report'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['report'] ) ) ) : ''; ?>"/>
					</div>
				</form>
			</div>
			<h4 class="section_title">
				<span><?php esc_html_e( 'Top Sellers', 'yith-woocommerce-product-vendors' ); ?></span>
			</h4>
			<div class="section">
				<table cellspacing="0">
					<?php
					$top_sellers = $this->get_report_data( false );
					if ( $top_sellers ) {
						uasort( $top_sellers, array( $this, 'item_sort' ) );
						$limit = ! empty( $limit ) ? $limit : count( $top_sellers );
						foreach ( array_slice( $top_sellers, 0, $limit ) as $top_seller ) {
							echo '<tr class="' . ( $top_seller['vendor']->get_id() === $this->vendor_id ? 'active' : '' ) . '">
                                <td class="count">' . intval( $top_seller['items_number'] ) . '</td>
                                <td class="name"><a href="' . esc_url( add_query_arg( 'vendor_ids', $top_seller['vendor']->get_id() ) ) . '">' . esc_html( $top_seller['vendor']->get_name() ) . '</a></td>
                                <td class="sparkline"></td>
                            </tr>';
						}
					} else {
						echo '<tr><td colspan="3">' . esc_html__( 'No vendors found in the selected range', 'yith-woocommerce-product-vendors' ) . '</td></tr>';
					}
					?>
				</table>
			</div>
			<h4 class="section_title">
				<span><?php esc_html_e( 'Top Earners', 'yith-woocommerce-product-vendors' ); ?></span>
			</h4>
			<div class="section">
				<table cellspacing="0">
					<?php
					$top_earners = $this->get_report_data( false );
					if ( $top_earners ) {
						uasort( $top_earners, array( $this, 'sales_sort' ) );
						$limit = ! empty( $limit ) ? $limit : count( $top_earners );
						foreach ( array_slice( $top_earners, 0, $limit ) as $top_earner ) {
							echo '<tr class="' . ( $top_earner['vendor']->get_id() === $this->vendor_id ? 'active' : '' ) . '">
                                <td class="count">' . wp_kses_post( wc_price( $top_earner['sales'] ) ) . '</td>
                                <td class="name"><a href="' . esc_url( add_query_arg( 'vendor_ids', $top_earner['vendor']->get_id() ) ) . '">' . esc_html( $top_earner['vendor']->get_name() ) . '</a></td>
                                <td class="sparkline"></td>
                            </tr>';
						}
					} else {
						echo '<tr><td colspan="3">' . esc_html__( 'No vendors found in the selected range', 'yith-woocommerce-product-vendors' ) . '</td></tr>';
					}
					?>
				</table>
			</div>
			<script type="text/javascript">
				jQuery('.section_title').click(function () {
					var next_section = jQuery(this).next('.section');

					if ( jQuery(next_section).is(':visible') )
						return false;

					jQuery('.section:visible').slideUp();
					jQuery('.section_title').removeClass('open');
					jQuery(this).addClass('open').next('.section').slideDown();

					return false;
				});
				jQuery('.section').slideUp(100, function () {
					<?php if ( empty( $this->vendor_id ) ) : ?>
					jQuery('.section_title:eq(1)').click();
					<?php endif; ?>
				});
			</script>
			<?php
			// phpcs:enable WordPress.Security.NonceVerification
		}

		/**
		 * Get the main chart
		 *
		 * @return string|void
		 */
		public function get_main_chart() {
			global $wp_locale;

			if ( empty( $this->vendor_id ) ) {
				?>
				<div class="chart-container">
					<p class="chart-prompt"><?php esc_html_e( '&larr; Choose a vendor to view stats', 'yith-woocommerce-product-vendors' ); ?></p>
				</div>
				<?php
			} elseif ( isset( $this->report_data['series'] ) ) {
				// Prepare data for report.
				$vendor_item_counts  = $this->prepare_chart_data( $this->report_data['series'][ $this->vendor_id ], 'order_date', 'qty', $this->chart_interval, $this->start_date, $this->chart_groupby );
				$vendor_item_amounts = $this->prepare_chart_data( $this->report_data['series'][ $this->vendor_id ], 'order_date', 'commission', $this->chart_interval, $this->start_date, $this->chart_groupby );

				// Encode in json format.
				$chart_data = wp_json_encode(
					array(
						'vendor_item_counts'  => array_values( $vendor_item_counts ),
						'vendor_item_amounts' => array_values( $vendor_item_amounts ),
					)
				);
				?>
				<div class="chart-container">
					<div class="chart-placeholder main"></div>
				</div>
				<script type="text/javascript">
					var main_chart;

					jQuery(function () {
						var order_data = jQuery.parseJSON('<?php echo $chart_data; // phpcs:ignore ?>');

						var drawGraph = function (highlight) {

							var series = [
								{
									label: "<?php echo esc_js( __( 'Number of commissions granted', 'yith-woocommerce-product-vendors' ) ); ?>",
									data: order_data.vendor_item_counts,
									color: '<?php echo esc_attr( $this->chart_colours['vendor_sales'] ); ?>',
									bars: {
										fillColor: '<?php echo esc_attr( $this->chart_colours['vendor_sales'] ); ?>',
										fill: true,
										show: true,
										lineWidth: 0,
										barWidth: <?php echo $this->barwidth; ?> *
										0.5,
										align: 'center'
									},
									shadowSize: 0,
									hoverable: false
								},
								{
									label: "<?php echo esc_js( __( 'Commissions amount', 'yith-woocommerce-product-vendors' ) ); ?>",
									data: order_data.vendor_item_amounts,
									yaxis: 2,
									color: '<?php echo esc_attr( $this->chart_colours['vendor_amount'] ); ?>',
									points: {show: true, radius: 5, lineWidth: 3, fillColor: '#fff', fill: true},
									lines: {show: true, lineWidth: 4, fill: false},
									shadowSize: 0,
									<?php echo $this->get_currency_tooltip(); // phpcs:ignore ?>
								}
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
											color: '#ecf0f1',
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

		/**
		 * Array sort
		 *
		 * @param array $a First array to compare.
		 * @param array $b Second array to compare.
		 * @return integer
		 */
		public function sales_sort( $a, $b ) {
			if ( $a['sales'] === $b['sales'] ) {
				return 0;
			} elseif ( $a['sales'] < $b['sales'] ) {
				return 1;
			} else {
				return -1;
			}
		}

		/**
		 * Array sort
		 *
		 * @param array $a First array to compare.
		 * @param array $b Second array to compare.
		 * @return integer
		 */
		public function item_sort( $a, $b ) {
			if ( $a['items_number'] === $b['items_number'] ) {
				return 0;
			} elseif ( $a['items_number'] < $b['items_number'] ) {
				return 1;
			} else {
				return -1;
			}
		}

		/**
		 * Prepare the report data
		 */
		public function prepare_report_data() {
			$report_data   = array();
			$current_range = YITH_Reports()->get_current_date_range();

			$this->calculate_current_range( $current_range );

			$vendors = yith_wcmv_get_vendors( array( 'number' => -1 ) );

			/**
			 * Skip the commissions with this status
			 * from Commissions by vendor report
			 */
			$not_allowed_status = apply_filters(
				'yith_wcmv_report_commissions_by_vendor_not_allowed_status',
				array(
					'cancelled',
					'refunded',
					'pending',
				)
			);

			/* @var YITH_Vendor $vendor The vendor object */
			foreach ( $vendors as $vendor ) {
				$amount       = 0;
				$items_number = 0;

				$commission_ids = yith_wcmv_get_commissions(
					array(
						'vendor_id' => $vendor->get_id(),
						'status'    => 'all',
                        'number'    => -1
					)
				);


				foreach ( $commission_ids as $commision_id ) {
					$commission = yith_wcmv_get_commission( $commision_id );
					if ( ! $commission || in_array( $commission->get_status(), $not_allowed_status, true ) ) {
						continue;
					}

					$order = $commission->get_order();
					/**
					 * WC return start date and end date in midnight form.
					 * To compare it with wc order date I need to convert
					 * order date in midnight form too.
					 */
					$order_date = $order instanceof WC_Order ? strtotime( 'midnight', $order->get_date_created()->getTimestamp() ) : false;
					$order_date = apply_filters( 'yith_wcmv_report_commissions_by_vendor_order_date', $order_date, $commission );

					$is_valid_commission = $order_date && $order_date >= $this->start_date && $order_date <= $this->end_date;

					if ( $is_valid_commission ) {
						$items_number++;
						$amount += $commission->get_amount();
					}

					/* === Chart Data === */
					$series                                       = new stdClass();
					$series->order_date                           = $order instanceof WC_Order ? date_i18n( wc_date_format(), $order->get_date_created()->getTimestamp() ) : null;
					$series->qty                                  = 1;
					$series->commission                           = wc_format_decimal( $commission->get_amount() );
					$report_data['series'][ $vendor->get_id() ][] = $series;
				}

				if ( ! empty( $amount ) ) {
					$report_data[ $vendor->get_id() ] = array(
						'vendor'       => $vendor,
						'sales'        => $amount,
						'items_number' => $items_number,
					);
				}
			}
			$this->set_report_data( $report_data );
		}
	}
}

<?php
/**
 * YITH_Report_Sales_By_Date Class
 *
 * @author  YITH
 * @package YITH\MultiVendor
 * @version 4.0.0
 */

defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_Report_Vendors_Registered' ) ) {
	/**
	 * YITH_Report_Vendors_Registered Class
	 */
	class YITH_Report_Vendors_Registered extends WC_Admin_Report {

		/**
		 * Output the report
		 */
		public function output_report() {

			$this->chart_colours = array(
				'totals'   => '#3498db',
				'enabled'  => '#5cc488',
				'disabled' => '#e74c3c',
			);

			$current_range = YITH_Reports()->get_current_date_range();

			$this->calculate_current_range( $current_range );

			$args = array(
				'report'        => $this,
				'current_range' => $current_range,
				'ranges'        => YITH_Reports()->get_ranges(),
			);

			$this->get_chart_data();

			yith_wcmv_get_template( 'vendors-registered', $args, 'woocommerce/admin/reports' );
		}

		/**
		 * Get the legend for the main chart sidebar
		 *
		 * @return array
		 */
		public function get_chart_legend() {
			$legend = array(
				'totals'   => array(
					// translators: %s stand for the registered vendors number.
					'title'            => sprintf( __( '%s registered vendors', 'yith-woocommerce-product-vendors' ), '<strong>' . $this->chart_data['totals'] . '</strong>' ),
					// translators: %s stand for the total vendors number.
					'widget_title'     => sprintf( __( '%s total ', 'yith-woocommerce-product-vendors' ), '<strong>' . $this->chart_data['totals'] . '</strong>' ),
					'color'            => $this->chart_colours['totals'],
					'highlight_series' => 0,
				),

				'enabled'  => array(
					// translators: %s stand for the vendors with selling capability.
					'title'            => sprintf( __( '%s with selling capability', 'yith-woocommerce-product-vendors' ), '<strong>' . $this->chart_data['enabled'] . '</strong>' ),
					// translators: %s stand for the vendors with selling enabled.
					'widget_title'     => sprintf( __( '%s enabled', 'yith-woocommerce-product-vendors' ), '<strong>' . $this->chart_data['enabled'] . '</strong>' ),
					'color'            => $this->chart_colours['enabled'],
					'highlight_series' => 1,
				),

				'disabled' => array(
					// translators: %s stand for the vendors without selling capability.
					'title'            => sprintf( __( '%s without selling capability', 'yith-woocommerce-product-vendors' ), '<strong>' . $this->chart_data['disabled'] . '</strong>' ),
					// translators: %s stand for the vendors with selling disabled.
					'widget_title'     => sprintf( __( '%s disabled', 'yith-woocommerce-product-vendors' ), '<strong>' . $this->chart_data['disabled'] . '</strong>' ),
					'color'            => $this->chart_colours['disabled'],
					'highlight_series' => 2,
				),
			);

			return $legend;
		}

		/**
		 * The chart data
		 */
		public function get_chart_data() {
			global $wpdb;

			$vendors = array(
				'totals'  => count(
					yith_wcmv_get_vendors(
						array(
							'fields' => 'ids',
							'number' => -1,
						)
					)
				),
				'enabled' => count(
					yith_wcmv_get_vendors(
						array(
							'fields' => 'ids',
							'status' => 'enabled',
							'number' => -1,
						)
					)
				),
			);

			$vendors['disabled'] = absint( $vendors['totals'] - $vendors['enabled'] );

			$this->chart_data = $vendors;
			$sql              = "SELECT meta_value as post_date, count(wtm.term_id) as vendors_number
                    FROM {$wpdb->termmeta} as wtm
                    JOIN {$wpdb->term_taxonomy} as tt
                    ON wtm.term_id = tt.term_id
                    WHERE tt.taxonomy = %s
                    AND wtm.meta_key = %s
                    GROUP BY post_date";

			$results                    = $wpdb->get_results( $wpdb->prepare( $sql, YITH_Vendors_Taxonomy::TAXONOMY_NAME, 'registration_date' ) );
			$prepared_chart_data        = $this->prepare_chart_data( $results, 'post_date', 'vendors_number', $this->chart_interval, $this->start_date, $this->chart_groupby );
			$this->chart_data['series'] = wp_json_encode( array_values( $prepared_chart_data ) );
		}

		/**
		 * Get the main char information
		 *
		 * @return string|void
		 */
		public function get_main_chart() {
			global $wp_locale;
			?>
			<div class="chart-container">
				<div class="chart-placeholder main"></div>
			</div>
			<script type="text/javascript">

				var main_chart;

				jQuery(function () {
					var plot_data = <?php echo $this->chart_data['series']; ?>;
					var drawGraph = function (highlight) {
						var series = [
							{
								label: "<?php echo esc_js( __( 'Totals registered vendors', 'yith-woocommerce-product-vendors' ) ); ?>",
								data: plot_data,
								yaxis: 1,
								color: '<?php echo esc_attr( $this->chart_colours['totals'] ); ?>',
								points: {show: true, radius: 5, lineWidth: 3, fillColor: '#fff', fill: true},
								lines: {show: true, lineWidth: 4, fill: false},
								enable_tooltip: true,
								append_tooltip: " <?php esc_html_e( 'new vendors', 'yith-woocommerce-product-vendors' ); ?>",
								shadowSize: 0,
								hoverable: true
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
									show: true
								},
								grid: {
									color: '#aaa',
									borderColor: 'transparent',
									borderWidth: 0,
									hoverable: true
								},
								xaxes: [{
									mode: "time",
									color: '#aaa',
									position: "bottom",
									tickColor: 'transparent',
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
									}
								]
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

		/**
		 * [get_chart_widgets description]
		 *
		 * @return array
		 */
		public function get_chart_widgets() {
			$widgets = array();

			$widgets[] = array(
				'title'    => '',
				'callback' => array( $this, 'enabled_vs_disabled' ),
			);

			return $widgets;
		}

		/**
		 * Enabled Vs Disabled vendors
		 */
		public function enabled_vs_disabled() {

			$legend = $this->get_chart_legend();

			?>
			<div class="chart-container">
				<h3 style="text-align: center"><?php esc_html_e( 'Enabled Vs Disabled Vendors', 'yith-woocommerce-product-vendors' ); ?></h3>
				<div class="chart-placeholder enabled_vs_disabled pie-chart" style="height:200px; cursor: pointer;"></div>
				<ul class="pie-chart-legend">
					<li style="border-color: <?php echo esc_attr( $this->chart_colours['enabled'] ); ?>">
						<?php echo wp_kses_post( $legend['enabled']['widget_title'] ); ?>
					</li>
					<li style="border-color: <?php echo esc_attr( $this->chart_colours['disabled'] ); ?>">
						<?php echo wp_kses_post( $legend['disabled']['widget_title'] ); ?>
					</li>
				</ul>
			</div>
			<script type="text/javascript">
				jQuery(function () {
					jQuery.plot(
						jQuery('.chart-placeholder.enabled_vs_disabled'),
						[
							{
								data: "<?php echo esc_attr( $this->chart_data['enabled'] ); ?>",
								color: '<?php echo esc_attr( $this->chart_colours['enabled'] ); ?>'
							},
							{
								data: "<?php echo esc_attr( $this->chart_data['disabled'] ); ?>",
								color: '<?php echo esc_attr( $this->chart_colours['disabled'] ); ?>'
							}
						],
						{
							grid: {
								hoverable: true
							},
							series: {
								pie: {
									show: true,
									radius: 1,
									innerRadius: 0.6,
									label: {
										show: false
									}
								},
								enable_tooltip: true,
								append_tooltip: " <?php esc_html_e( 'vendors', 'yith-woocommerce-product-vendors' ); ?>"
							},
							legend: {
								show: false
							}
						}
					);

					jQuery('.chart-placeholder.customers_vs_guests').resize();
				});
			</script>
			<?php
		}
	}
}

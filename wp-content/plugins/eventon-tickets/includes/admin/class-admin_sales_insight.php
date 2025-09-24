<?php
/*
 * Sales Insight for Tickets
 * @version 2.4
*/

class EVOTX_Sales_Insight{
	function get_insight(){
		ob_start();

		$event_id = $_POST['event_id'];

		date_default_timezone_set('UTC');

		$EVENT = new EVOTX_Event($event_id);
		$curSYM = get_woocommerce_currency_symbol();

		// event time
			if( !$EVENT->is_repeating_event()){
				?>
				<div class='evotxsi_row timetoevent evopad15 evotac evo_borderb evo_bordert evomarb20'>
					<?php if( $EVENT->is_current_event('start')):
	
						$timenow = current_time( 'timestamp' );

						$start = $EVENT->get_prop('evcal_srow');

						$dif = $start - $timenow;

					?>
						<p class='evopad0i evomar0i'><?php _e('Time left till event start','evotx');?> <span class='evobr20 evofz12 evomarl20 static_field'><?php echo $this->get_human_time($dif);?></span></p>
					<?php else:?>
						<p><?php _e('Event has already started!','evotx');?></p>
					<?php endif;?>				
				</div>
				<?php
			}

		// sales by ticekt order
			$remainging_tickets = is_bool( $EVENT->has_tickets() )? 0: $EVENT->has_tickets();
			$orders = new WP_Query(array(
				'post_type'=>'evo-tix',
				'posts_per_page'=>-1,
				'meta_query'=>array(
					array(
						'key'=>'_eventid',
						'value'=>$event_id
					)
				)
			));

			$sales_data = array();
			$total_tickets_sold = 0;
			$total_tickets_sold_ready = 0;
			$checked_count = 0;

			$processed_order_ids = array();

			if($orders->have_posts()):
				while($orders->have_posts()): $orders->the_post();

					$order_id = get_post_meta($orders->post->ID, '_orderid', true);
					$order = new WC_Order( $order_id );

					// checked count
						$status = get_post_meta($orders->post->ID, 'status', true);
						if( $status == 'checked') $checked_count ++;

					// check if order post exists
					$order_status = $order->get_status();
					if(!$order_status) continue;

					// Process 1 order once only
						if(in_array($order_id, $processed_order_ids)) continue;						
						if(sizeof( $order->get_items() ) <= 0) continue;
				
					// for each ticket item in the order
						$_order_qty = $_order_cost = 0;

					// order information
						$order_datetime  = $order->get_date_created(); // Get order created date ( WC_DateTime Object ) 
    					$order_time = $order_datetime->getTimestamp();
    					
						$billing_country = $order->get_billing_country();
						$order_status = $order->get_status();

					// foreach order item  ticket sold
					foreach($order->get_items() as $item_id=>$item){
						$_order_event_id = ( isset($item['_event_id']) )? $item['_event_id']:'';
						$_order_event_id = !empty($_order_event_id)? $_order_event_id: get_post_meta( $item['product_id'], '_eventid', true);				    		
				    	if(empty($_order_event_id)) continue; // skip non ticket items

				    	if($_order_event_id != $event_id) continue;


				    	$_order_qty += (int)$item['qty'];
				    	$_order_cost += floatval($item['subtotal']);

				    	$sales_data[$item_id] = apply_filters('evotx_sales_insight_data_item',
				    		array(
					    		'qty'=> (int)$item['qty'],
					    		'cost'=> floatval($item['subtotal']),
					    		'order_id'=> $orders->post->ID,
					    		'time'=> $order_time,
					    		'country'=>$billing_country,
					    		'order_status'=> $order_status,
				    	), $item_id, $item, $EVENT, $order);

					}

					// completed & ready to go orders
					if( $order_status == 'completed'){
						$total_tickets_sold_ready += $_order_qty ;
					}

					$total_tickets_sold += $_order_qty;
					$processed_order_ids[] = $order_id;				


				endwhile;
				wp_reset_postdata();
			endif;


		//print_r($sales_data);

		// sales by order status
		if(sizeof($sales_data)>0){

			$chart_data = array();

			?>
			<div class='evotxsi_line evodfx'>
				<div class='evotxsi_box sales_by_status' style='background-color: #c7e1f8;'>
					<h2 class='evoff_1' style='margin:10px 0 30px;'><?php _e('Ticket sales by ticket order status','evotx');?></h2>	
					<?php 
					// value processing
						$sbs_data = array();
						foreach(array(
							'wc-completed'=> __('Tickets Sold','evotx'),
							'wc-onhold'=> __('Pending','evotx'),
							'wc-cancelled'=> __('Cancelled','evotx'),
							'wc-refunded'=> __('Refunded','evotx'),

						) as $type => $name){
							$_qty = $_cost = 0;
							foreach($sales_data as $oiid=>$d){

								if( $type == 'wc-onhold'){
									if(!in_array('wc-'.$d['order_status'], array('wc-on-hold','wc-pending','wc-processing','wc-failed')) ) continue; 
								}else{
									if('wc-'.$d['order_status'] != $type) continue;
								}
								

								$_qty += (int)$d['qty'];
								$_cost += floatval($d['cost']);
							}

							$sbs_data[$type] = array(
								'qty'=> $_qty,
								'pvalue'=> $curSYM.number_format($_cost,0,'.',''),
								'name'=> $name
							);	
						}

						$sbs_data['capacity']= array(
							'qty' => $total_tickets_sold + $remainging_tickets ,
							'pvalue'=> ($remainging_tickets== 0? __('No capacity limit','evotx'):'' ),
							'name' => __('Total Event Capacity','evotx')
						);

					?>	
					<div class='evodfx evofx_dr_r'>
						<div class='evofx_1 evodfx evofx_dr_c'>
							<p class='evodfx evofx_dr_c'>
								<b class='evomarb5 evolh1 evofz48 evofwb evoff_1'><?php echo $sbs_data['wc-completed']['pvalue'];?></b>
								<span class=''>
									<em class='highlighttext1 evofsn'><?php echo $sbs_data['wc-completed']['qty'];?> x</em><?php echo $sbs_data['wc-completed']['name'];?>
								</span>
							</p>
							<em class='evodb evomarb10'></em>
							<p class='evodfx evofx_dr_c'>
								<b class='evomarb5 evolh1 evoff_1 evofz30'><?php echo $sbs_data['capacity']['pvalue'];?></b>
								<span><em class='highlighttext1 evofsn'><?php echo $sbs_data['capacity']['qty'];?> x</em><?php echo $sbs_data['capacity']['name'];?></span>	
							</p>						
						</div>
						<div class='evodfx evofx_dr_c evogap5'>
					
							<?php foreach(array(
								'wc-onhold',
								'wc-cancelled',
								'wc-refunded',

							) as $sbs_type ):?>
							<p class='<?php echo $sbs_type;?> evodfx evofx_dr_c'>
								<b class='evomarb5 evolh1 evofz30 evoff_1'><?php echo $sbs_data[ $sbs_type ]['pvalue'];?></b>
								<span><em class='highlighttext1 evofsn'><?php echo $sbs_data[ $sbs_type ]['qty'];?> x</em><?php echo $sbs_data[ $sbs_type ]['name'];?></span>	
							</p>
							<?php endforeach;?>
						</div>
					</div>
				</div>

				<div class='evotxsi_box guestattd'>
					<h2 class='evoff_1' style='margin:10px 0 30px; '><?php _e('Guest Attendance Data','evotx');?></h2>
					
					<div class='evodfx evofx_dr_r evo_borderb'>
						<p class='evofx_b_50 evodfx evofx_dr_c'>
							<span class='evofz30 evofwb evoff_1'><?php echo $total_tickets_sold_ready;?></span>
							<span class=''><?php _e('Tickets Sold','evotx');?></span>
						</p>
						<p class='evofx_b_50 evodfx evofx_dr_c'>
							<span class='evofz30 evofwb evoff_1'><?php echo $checked_count;?></span>
							<span class=''><?php _e('Checked in Count','evotx');?></span>
						</p>
					</div>
					<div class=''>
						<?php 
						$this->dvis_bar(
							$total_tickets_sold_ready,
							$checked_count,
							__('Attendance','evotx')
						);
						?>
					</div>
				</div>
			</div>
			<div class='evotxsi_line evodfx'>
				<div class='evotxsi_box sales_by_time'>
					<h2 class='evoff_1 clrw evotac' style='font-weight:bold'><?php _e('Ticket Purchase Lead Time','evotx');?></h2>	
					<h3 class='clrw evotac evoop5'><?php _e('How far in advance tickets are being purchased.','evotx');?></h3>			
					<?php		
						$chart1_data_ppt_series_1 = array();
						$chart1_data_ppt_series_2 = array();
						$chart1_data_ppt_series_labels = array();

						// time adjust markup
						$time_adjust = $EVENT->get_event_time('start');

						$cd_count = array();
						$cd_value = array();

						$timerange = array(
							array(4838400,50000000,__('2+ Mo','evotx')),
							array(2419200,4838400,__('1-2 Mo','evotx')),
							array(1209600,2419200,__('2-4 Wk','evotx')),
							array(604800,1209600,__('1-2 Wk','evotx')),
							array(259200,604800,__('3-7 Day','evotx')),
							array(86400,259200,__('1-3 Day','evotx')),
							array(0,86400,__('> 1 Day','evotx')),
						);

						foreach( $timerange as $val){
							$chart1_data_ppt_series_labels[] = $val[2];
							$cd_count[ $val[0]] = 0;
							$cd_value[ $val[0]] = 0;
						}

							

						$_qty = $_cost = 0;

						$index = 0;
						foreach( $sales_data as $oiid=>$d){

							$order_time = $time_adjust - $d['time'] ;


							foreach( $timerange as $val){
								// if order start is equal or greater and order end if less than
								if( $order_time >= $val[0] && $order_time < $val[1] ){

									$cd_count[ $val[0]] = isset( $cd_count[ $val[0]]) ? $cd_count[ $val[0]] + $d['qty']: $d['qty'];
									$cd_value[ $val[0]] = isset( $cd_value[ $val[0]]) ? $cd_value[ $val[0]] + $d['cost']: $d['cost'];

									$_qty += $d['qty'];
									$_cost += $d['cost'];
								}
							}

						}

						foreach($cd_count as $key=>$val){
							$chart1_data_ppt_series_1[] = $val;
							$chart1_data_ppt_series_2[] = isset( $cd_value[ $key ] ) ? $cd_value[ $key ] : '';
						}

						$chart_data['chart_1'] = array(
							'labels'=> $chart1_data_ppt_series_labels,
							'datasets'=> array(
								array(
									'data' =>  $chart1_data_ppt_series_1,
									'label' =>'Ticket Sales Count',
									'borderColor' => 'rgba(255, 255, 255, 1)',
									'backgroundColor' => 'rgba(255, 255, 255, 1)',
									'yAxisID'=>'y',
									'tension'=> '0.4',
								),
								array(
									'data' =>  $chart1_data_ppt_series_2,
									'label' =>'Ticket Sales Value',
									'yAxisID'=>'y1',
									'borderColor' => 'rgba(255, 169, 23, 1)',
									'backgroundColor' => 'rgba(255, 169, 23, 1)',
									'tension'=> '0.4',
								)
							)
						);
					?>
					
					<canvas id="evotx_si_chart_1" class='evomart20' style='height:150px;'></canvas>
				</div>
			
				<div class='evotxsi_box sales_by_time sales_purchasetime'>
					<h2 class='evoff_1 clrw evotac' style='font-weight:bold'><?php _e('Peak Purchase time','evotx'); ?></h2>	
					<h3 class='clrw evotac evoop5'><?php printf(__('Timezone: %s / Value in: %s','evotx'), EVO()->calendar->cal_tz_string, $curSYM); ?></h3>	

					<?php		

						$chart_data_ppt_series_1 = array();
						$chart_data_ppt_series_2 = array();
						$chart_data_ppt_series_labels = array();

						$DD = new DateTime('now', EVO()->calendar->cal_tz);

						$hours_data = array();
						$hours_price = array();

						for ($i = 1; $i <= 24; $i++) {
						    $hours_data[$i] = 0; // Set each key to the same value
						    $hours_price[$i] = 0; // Set each key to the same value
						}

						foreach( $sales_data as $oiid=>$d){
							$DD->setTimestamp( $d['time'] );
							$order_24hour = $DD->format('H');

							$hours_data[ $order_24hour ] = isset($hours_data[ $order_24hour ]) ? $hours_data[ $order_24hour ] + $d['qty']: $d['qty'];

							$hours_price[ $order_24hour ] = isset($hours_price[ $order_24hour ]) ? $hours_price[ $order_24hour ] + $d['cost']: $d['cost'];
						}

						foreach($hours_data as $hr=>$dd){

							$chart_data_ppt_series_1[] = $dd;
							$chart_data_ppt_series_2[] = $hours_price[ $hr ];
							$chart_data_ppt_series_labels[] = $hr;
						}


						$chart_data['chart_2']  = array(
							'labels'=> $chart_data_ppt_series_labels,
							'datasets'=> array(
								array(
									'data' =>  $chart_data_ppt_series_1,
									'label' =>'Ticket Sales Count',
									'borderColor' => 'rgba(255, 255, 255, 1)',
									'backgroundColor' => 'rgba(255, 255, 255, 1)',
									'yAxisID'=>'y',
									'tension'=> '0.4',
								),
								array(
									'data' =>  $chart_data_ppt_series_2,
									'label' =>'Ticket Sales Value',
									'yAxisID'=>'y1',
									'borderColor' => 'rgba(255, 169, 23, 1)',
									'backgroundColor' => 'rgba(255, 169, 23, 1)',
									'tension'=> '0.4',
								)
							)
						);
					?>
					<canvas id="evotx_si_chart_2" class='evomart20' style='height:150px;'></canvas>
				</div>
			</div>
			<div class='evotxsi_line'>
				<div class='evotxsi_box sales_by_country' style='margin-top: 20px; background-color: #ffeb91;'>
					<h2 class='evoff_1'><?php _e('Sales by customer location','evotx');?></h2>	
					<h3 class='evofwn evoop5'><?php _e('Top 3 countries where customers have placed orders from','evotx');?></h3>			
					<div class='evodfx evofx_jc_fs evomart20'>
					<?php	
											
						$_country_data = array();
						
						foreach( $sales_data as $oiid=>$d){

							if(!isset($d['country'])) continue;

							$_country_data[ $d['country']]['qty'] = isset($_country_data[ $d['country']]['qty'])?
								$_country_data[ $d['country']]['qty'] + $d['qty'] : $d['qty'];

							$_country_data[ $d['country']]['cost'] = isset($_country_data[ $d['country']]['cost'])?
								$_country_data[ $d['country']]['cost'] + $d['cost'] : $d['cost'];
							
						}

						//$_country_data['CA']= array('qty'=>'3','cost'=>'70');
						//$_country_data['SL']= array('qty'=>'12','cost'=>'120');

						$country_qty = array();
						foreach($_country_data as $key=>$row){
							$country_qty[ $key] = $row['qty'];
						}

						array_multisort( $country_qty, SORT_DESC,$_country_data );
						
						$index = 0;
						foreach($_country_data as $country=>$data){

							if( empty($country )) continue;

							?>
							<span class='evobr50 evotac evopadt20 evoboxbb' style='opacity:<?php echo 1- ($index*0.3);?>'>
								<em><?php echo empty($country)? 'n/a': $country;?></em>
								<b><?php echo $data['qty'];?></b>
								<i><?php echo $curSYM. number_format($data['cost'], 2, '.','');?></i>
							</span>
							<?php
							$index++;
						}

					?>
					</div>
				</div>
			</div>
			<?php 
			do_action('evotx_sales_insight_before_end', $EVENT, $orders, $sales_data);
			?>

			<div class='evotxsi_row sales_msg evotac evomart20'><p><?php _e('NOTE: Sales insight data is calculated using evo-tix posts as primary measure count.','evotx');?></p></div>
			<?php
		}

		do_action('evotx_sales_insight_after', $EVENT, $orders, $sales_data);

		return array(
			'content'=> ob_get_clean(),
			'chart_data'=> $chart_data
		);
	}

	// data visualization elements
		function dvis_bar( $total, $yescount, $name){

			$_perc = 0;
			if( $total >0 )
				$_perc = round( ($yescount/$total) *100 , 2) ;

			echo "<div class='evotxsi_dvis_bar evomart10'>
			<span class='evomarb10 evodb'>". $name ."<em class='highlighttext1 evomarl10 evofsn evofwb'>{$_perc} %</em></span>
			<div class='evotxsi_dvis_bar_line evobr30 evoposr evomarb10'><em class='evodb evoposa' style='width:{$_perc}%'></em></div>
			<div class='evodfx evofx_jc_sb'>
				<p class=''>". $yescount ."</p>
				<p>".__('Total','evotx') .' '. $total ."</p>
			</div>
			</div>
			";
		}

	// return time difference in d/h/m
		function get_human_time($time){

			$output = '';
			$day = $time/(60*60*24); // in day
			$dayFix = floor($day);
			$dayPen = $day - $dayFix;
			if($dayPen > 0)
			{
				$hour = $dayPen*(24); // in hour (1 day = 24 hour)
				$hourFix = floor($hour);
				$hourPen = $hour - $hourFix;
				if($hourPen > 0)
				{
					$min = $hourPen*(60); // in hour (1 hour = 60 min)
					$minFix = floor($min);
					$minPen = $min - $minFix;
					if($minPen > 0)
					{
						$sec = $minPen*(60); // in sec (1 min = 60 sec)
						$secFix = floor($sec);
					}
				}
			}
			$str = "";
			if($dayFix > 0)
				$str.= $dayFix." day ";
			if($hourFix > 0)
				$str.= $hourFix." hour ";
			if($minFix > 0)
				$str.= $minFix." min ";
			//if($secFix > 0)	$str.= $secFix." sec ";
			return $str;
		}

}
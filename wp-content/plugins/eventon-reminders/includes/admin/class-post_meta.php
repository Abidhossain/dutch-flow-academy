<?php 
/** 
 * Post Meta Boxes
 * @version 0.8
 */
class evorm_meta_boxes{
	public function __construct(){
		//add_action('evors_event_metafields',array($this, 'rsvp_meta_box'), 10, 2);
		//add_action('evotx_event_metabox_end',array($this, 'tx_meta_box'), 10, 2);
		add_filter('eventon_event_metafields',array($this, 'save_fields'), 10, 2);

		add_action( 'add_meta_boxes', array($this, 'meta_boxes') );
	}

	function meta_boxes(){
		add_meta_box('evorm_mb1',__('All Event Reminders','eventon'), array($this, 'metabox_content'),'ajde_events', 'normal', 'high');
	}
	function metabox_content(){

		global $post;

		$event_id = $post->ID;
		$Event = new EVO_Event($event_id);

		//print_r($eventon);

		
		?>
		<div class='eventon_mb'>
		<div class="evors">			
			<div id='evorm_details' class='evorm_details evomb_body' style=''>
				
				<?php if( class_exists('EventON_rsvp')):?>			
	
				<h3 style='font-size:14px;'><?php _e('RSVP Reminders','eventon');?></h3>
				<div class='evo_negative_25'>
				<table width='100%' class='eventon_settings_table'>
				<?php $this->reminder_meta_box($event_id, 'rs', '');?>
				</table>
				</div>

				<?php endif;?>

				<?php if( class_exists('evotx')):?>			
	
					<h3 style='font-size:14px;'><?php _e('Ticket Reminders','eventon');?></h3>
					<div class='evo_negative_25'>
					<table width='100%' class='eventon_settings_table'>
					<?php $this->reminder_meta_box( $event_id, 'tx', '');?>
					</table>
					</div>

				<?php endif;?>
			</div>
		</div>
		</div>
		<?php
	}

	
	function tx_meta_box($event_id, $epmv){
		$this->reminder_meta_box( $event_id, 'tx',$epmv);
	}
	function rsvp_meta_box($epmv, $event_id){
		$this->reminder_meta_box( $event_id, 'rs', $epmv);
	}

	function reminder_meta_box($event_id, $addon, $epmv=''){
		// only for simple, non-repeating - events
		

		$Event = new EVO_Event($event_id);
		$CRON = new evo_cron();
		$fnc = new evorm_fnc();
		$all_cronjobs = $CRON->get_all_cron_hooks();
		
		$addon_name = $addon=='tx'? 'Tickets': 'RSVP';

		$showing_reminder_options = false;
		foreach(EVORM()->get_reminders() as $key=>$value){



			$check_enable = EVO()->cal->check_yn($value['var'], 'evcal_'.$addon ); 			
			$check_time = EVO()->cal->get_prop($value['var'].'_time', 'evcal_'.$addon );
			$check_msg = EVO()->cal->get_prop($value['var'].'_message','evcal_'. $addon ); 


			// check if enabled in settings and have time and message set in settings
			if($check_enable && $check_time && $check_msg){  
				$showing_reminder_options = true;	
					
				$field_variable = '_'.$value['var'].'-'.$addon;
				$cron_hook = 'evorm_reminder_'.$event_id. $field_variable;

				// Append a cron status next to reminder line
				$cron_status_addition = '';

				$styles = 'style="display:inline-block;font-size:12px; padding:2px 7px;border: 1px solid var(--evo_color_1);"';

				// preview reminder button

				

				$reminder_preview_btn = EVORM()->admin->preview_btn( array(
					'data'=> array(
						'event_id'=> 	$event_id,
						'var'=>			$value['var'],
						'addon'=> 		$addon
					),
					'dom_element'=> 'i',
					'extra_classes'=> 'evorm_preview_rem_trig fa fa-eye evocurp evohoop7',
					'class_attr'=> 'evolb_trigger '
				));

				//$reminder_preview_btn = "<i class='evorm_preview_rem_trig fa fa-eye evomarr5 evocurp evohoop7' data-d='".__('Preview this reminder email') ."'></i>";
				
				// if there are current cron jobs for this reminder
				if( array_key_exists($cron_hook, $all_cronjobs) ){

					if( isset($all_cronjobs[$cron_hook]['time']) ){

						$time = esc_html( get_date_from_gmt( date( 'Y-m-d H:i:s', $all_cronjobs[$cron_hook]['time'] ), 'Y-m-d h:s:a' ) );

						$cron_status_addition = $reminder_preview_btn . "<span {$styles} class=''>". __('Scheduled','evorm').' @ '. $time ."</span>";
					}else{
						$msg = __('Cron Job has no time','evorm');
						$cron_status_addition = "<span {$styles} class=''>". $msg."</span>";
					}
				}else{

					// get reminder properties to check recorded status of reminder cron job
					$reminder_prop = $fnc->get_reminder_prop($event_id, $field_variable);

					$msg = __('No Cron jobs','evorm');

					if($reminder_prop == 'completed') $msg = __('Sent!','evorm');
					if($reminder_prop == 'attempted') $msg = __('Attempted!','evorm');
					
					$cron_status_addition = "<span {$styles} class=''>". $msg."</span>";
				}

				?>
				<tr><td colspan='2'>
					<div class='evodfx evofx_jc_sb evofx_ai_c'>
						<?php 

						echo EVO()->elements->yesno_btn(array(
							'id'=>	$field_variable,
							'var'=> $Event->get_prop( $field_variable ),
							'default'=>'',
							'label'=> $value['label'].' for this event',
							'guide_position'=>'L',
							'input'=>true,
							'nesting'=> true
						));
						?>

						<div class='evodfx evofx_dr_r evogap10'>
							<?php 	echo $cron_status_addition;	?>
						</div>
					</div>
																
				</td></tr>				
				<?php

			}
		}

		// notice
		if($showing_reminder_options){
			?>
			<tr><td colspan='2'>
				<p class=' evo'><i><?php _e('Reminders Notice: Event time must be saved first and then enable reminders for the reminders cron jobs to be created. If you change event time, save changes once and save again to update cron job with new times.','evorm');?></i></p>											
			</td></tr>
			<?php
		}else{
			?>
			<tr><td colspan='2'>
				<p class=' evo'><i><?php _e('There are no active reminders.','evorm');?></i></p>											
			</td></tr>
			<?php
		}
		
	}

	// save fields
		public function save_fields($array, $event_id){

			// set reminder email schedule during save post 
			foreach(apply_filters('evorm_event_save_fields', array(
				'evorm_pre_1', 'evorm_pre_2', 'evorm_post_1', 'evorm_post_2'
			)) as $var){

				foreach( array('tx','rs') as $addon){

					// load addon based settings values
					EVO()->cal->load_more( 'evcal_'.$addon );

					// skip the reminders that are not enabled in settings
					if( !EVO()->cal->check_yn( $var , 'evcal_'.$addon ) ) continue;

					$field_variable = '_'.$var . '-'.$addon;

					if(!empty($_POST[$field_variable]) && $_POST[$field_variable]=='yes'){
						
						EVORM()->cron->schedule_reminders($event_id, $field_variable);
					}else{
						EVORM()->cron->unschedule_reminders($event_id, $field_variable);
					}

					$array[] = $field_variable;
				}
				
			}


			return $array;
		}
}
new evorm_meta_boxes();
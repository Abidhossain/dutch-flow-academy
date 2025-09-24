<?php
/**
* Metabox Product for Event
* @version 2.4
*/


global $post;
$product = wc_get_product( $post->ID );

$event_id = $product->get_meta('event_id');
if( !$event_id){

	printf(__('No events associated with this product','evotx'));
	return;
}

$event = new EVO_Event( $event_id);

$ticket_type = 'Ticket';


?>
<div class=''>
	<p class='evodfx evofx_jc_sb evogap10'>
		<span><?php _e('Event','evotx');?></span>
		<a class='evotar' href='<?php echo get_edit_post_link($event_id);?>'><?php echo $event_id;?></a>
	</p>
	<p class='evodfx evofx_jc_sb evogap10'>
		<span><?php _e('Event Product Type','evotx');?></span>
		<span class='evotar'><?php echo $ticket_type;?></span>
	</p>
</div>
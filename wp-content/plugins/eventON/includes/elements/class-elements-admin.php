<?php 
/**
 * Eventon elements for admin
 * @version 4.9.10
 * 
 */

class EVO_Elements_Admin{

	// meta box skeleton for event edit post only
	// add the loading meta box content skeleton
	function print_loading_metabox_skeleton( $data){
		extract( array_merge(array(
			'id'=>'',
			'loader_content_id'=>'',
			'class'=>'',
			'nonce_action'=> '',// verify with this action
			'nonce_name'=> '',// this is the post key
			'hidden_fields'=> array(),
			'post_id'=> '',
		), $data));

		$p_id = get_the_ID();
		$closedmeta = eventon_get_collapse_metaboxes($p_id);
		$closedmeta = is_array($closedmeta)? implode(',', $closedmeta):'';


		?>
		<div id='<?php echo $id;?>' data-eid='<?= $post_id;?>' class='<?php echo $class;?>'>
			<?php 
			// nonce key
			if( !empty($nonce_name) && !empty($nonce_action)) 
				wp_nonce_field( $nonce_action , $nonce_name ); 

			// hidden input fields
			if( count($hidden_fields)> 0){
				EVO()->elements->print_hidden_inputs( $hidden_fields );
			}
			?>

			<div id='<?php echo $loader_content_id;?>'>
				<?php 

				echo EVO()->elements->get_preload_html( array(
					's'=> array(
						array('w'=> 40, 'h'=>50),
						array('w'=> 100, 'h'=>50),
						array('w'=> 100, 'h'=>50),
						array('w'=> 100, 'h'=>50),
						array('w'=> 100, 'h'=>50),
					)
				));

				?>				
			</div>
		</div>
		<?php
	}

} 
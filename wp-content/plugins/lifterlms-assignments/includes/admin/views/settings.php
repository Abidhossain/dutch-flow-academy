<?php
/**
 * Assignment settings template
 *
 * @package LifterLMS_Assignments/Admin/Views
 * @since   1.0.0-beta.1
 * @version 1.0.0-beta.2
 */

defined( 'ABSPATH' ) || exit;
?>

<header class="llms-model-header" id="llms-assignment-header">

	<h3 class="llms-headline llms-model-title">
		<?php _e( 'Title', 'lifterlms-assignments' ); ?>: <span class="llms-input llms-editable-title" contenteditable="true" data-attribute="title" data-original-content="{{{ data.get( 'title' ) }}}" data-required="required">{{{ data.get( 'title' ) }}}</span>
	</h3>

	<label class="llms-switch llms-model-status">
		<span class="llms-label"><?php _e( 'Published', 'lifterlms-assignments' ); ?></span>
		<input data-off="draft" data-on="publish" name="status" type="checkbox"<# if ( 'publish' === data.get( 'status' ) ) { print( ' checked' ) } #>>
		<div class="llms-switch-slider"></div>
	</label>

	<div class="llms-action-icons">

		<# if ( ! data.has_temp_id() ) { #>
			<a class="llms-action-icon danger tip--bottom-left" data-tip="<?php esc_attr_e( 'Detach Assignment', 'lifterlms-assignments' ); ?>" href="#llms-detach-model">
				<i class="fa fa-chain-broken" aria-hidden="true"></i>
				<span class="screen-reader-text"><?php _e( 'Detach Assignment', 'lifterlms-assignments' ); ?></span>
			</a>
		<# } #>

		<a class="llms-action-icon danger tip--bottom-left" data-tip="<?php _e( 'Delete Assignment', 'lifterlms-assignments' ); ?>" href="#llms-trash-model" tabindex="-1">
			<i class="fa fa-trash" aria-hidden="true"></i>
			<span class="screen-reader-text"><?php _e( 'Delete Assignment', 'lifterlms-assignments' ); ?></span>
		</a>

	</div>

</header>

<div id="llms-assignment-settings-fields"></div>

<# if ( data.is_tasklist() ) { #>

	<section class="llms-model-settings active settings-group--tasks">

		<header class="llms-settings-group-header">

			<h4 class="llms-settings-group-title"><?php _e( 'Task List', 'lifterlms-assignments' ); ?></h4>

			<button class="llms-element-button small right" id="llms-add-assignment-task" type="button">
				<?php _e( 'Add Task', 'lifterlms-assignments' ); ?>
				<i class="fa fa-plus-circle" aria-hidden="true"></i>
			</button>

		</header>

		<ul class="llms-assignment-tasks"></ul>

	</section>

<# } #>


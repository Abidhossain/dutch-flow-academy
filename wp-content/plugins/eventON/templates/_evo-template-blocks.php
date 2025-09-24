<?php
/** 
 * EventON Template Blocks
 * @version 4.9.10
 */

class EVO_Temp_Blocks{
	//const PLUGIN_SLUG = 'eventon/eventon';
	const PLUGIN_SLUG = 'eventon';


 	// return the constructed template object for the query
 	public function get_single_event_template($slug=''){
 		
 		if( empty($slug) ) $slug = 'single-ajde_events';

 		$path = apply_filters('evo_blocktemplate_content_path', EVO()->plugin_path() . '/templates/blocks/'. $slug.'.html', $slug);

 		if (!file_exists($path)) {
	        EVO_Debug("EventON: Block template not found at $path");
	        return null; // Or fallback to a default
	    }

 		$template_content = file_get_contents( $path );
 		if ($template_content === false) {
	        EVO_Debug("EventON: Failed to read template file at $path");
	        return null;
	    }

	    // Validate template content to ensure it contains valid blocks
	    $template_blocks = parse_blocks($template_content);
	    if (empty($template_blocks)) {
	        error_log("Invalid block content in template: " . $slug);
	        return new WP_Error('invalid_block_content', __('Invalid block content in template.', 'eventon'));
	    }
 		

		$template 					= new WP_Block_Template();
	    $template->id 				= 'eventon//' . $slug;
	    $template->content 			= self::inject_theme_attribute_in_content($template_content);
	    $template->slug 			= $slug;
	    $template->path 			= $path;
	    $template->source 			= 'plugin';
	    $template->theme 			= 'EventON';
	    $template->type 			= 'wp_template';
	    $template->title 			= esc_html__('Event Page', 'eventon');
	    $template->description 		= esc_html__('Template used to display event pages.', 'eventon'); // Plain string
	    $template->status 			= 'publish';
	    $template->has_theme_file 	= true;
	    $template->is_custom 		= true;
	    $template->post_types 		= array('ajde_events');

		//EVO_Debug("EventON: Registered template - ID: $template->id, Path: $path");
		//EVO_Debug("EventON: Registered template - ID: $template->id, Description Type: " . gettype($template->description));
		return $template;
 	}

 	// parse wp_template content and inject the current theme stylesheet as theme attribute into teach wp_template_part
 	public static function inject_theme_attribute_in_content( $template_content){
		$has_updated_content = false;
		$new_content         = '';
		$template_blocks     = parse_blocks( $template_content );

		$blocks = static::flatten_blocks( $template_blocks );
		foreach ( $blocks as &$block ) {
			if (
				'core/template-part' === $block['blockName'] &&
				! isset( $block['attrs']['theme'] )
			) {
				$block['attrs']['theme'] = wp_get_theme()->get_stylesheet();
				$has_updated_content     = true;
			}
		}

		if ( $has_updated_content ) {
			foreach ( $template_blocks as &$block ) {
				$new_content .= serialize_block( $block );
			}

			return $new_content;
		}

		return $template_content;
	}

	//Returns an array containing the references of the passed blocks and their inner blocks.
	public static function flatten_blocks( &$blocks ) {
		$all_blocks = [];
		$queue      = [];

		foreach ( $blocks as &$block ) {
			$queue[] = &$block;
		}

		$queue_count = count( $queue );

		while ( $queue_count > 0 ) {
			$block = &$queue[0];
			array_shift( $queue );
			$all_blocks[] = &$block;

			if ( ! empty( $block['innerBlocks'] ) ) {
				foreach ( $block['innerBlocks'] as &$inner_block ) {
					$queue[] = &$inner_block;
				}
			}

			$queue_count = count( $queue );
		}

		return $all_blocks;
	}

	/**
	 * Removes templates that were added to a theme's block-templates directory, but already had a customised version saved in the database.
	 */
	public function remove_theme_templates_with_custom_alternative( $templates ) {

		// Get the slugs of all templates that have been customised and saved in the database.
		$customised_template_slugs = array_map(
			function( $template ) {
				return $template->slug;
			},
			array_values(
				array_filter(
					$templates,
					function( $template ) {
						// This template has been customised and saved as a post.
						return 'custom' === $template->source;
					}
				)
			)
		);

		// Remove theme (i.e. filesystem) templates that have the same slug as a customised one. We don't need to check
		// for `eventon` in $template->source here because eventon templates won't have been added to $templates
		// if a saved version was found in the db. This only affects saved templates that were saved BEFORE a theme
		// template with the same slug was added.
		return array_values(
			array_filter(
				$templates,
				function( $template ) use ( $customised_template_slugs ) {
					// This template has been customised and saved as a post, so return it.
					return ! ( 'theme' === $template->source && in_array( $template->slug, $customised_template_slugs, true ) );
				}
			)
		);
	}
}
<?php
/**
 * EventON Blocks Integration
 * @version  4.9.10
 */

class EVO_Blocks{
	private $namespace = 'eventon-blocks';
	private $blockname = 'eventon-main';
	function __construct(){

		if(!function_exists('register_block_type')) return false;
		add_action( 'init', array($this,'block_registering') );
		add_action( 'enqueue_block_editor_assets', array($this,'script_enqueue') );
		add_filter( 'block_categories_all', array($this,'evo_category'), 10, 2);
	}

	public function script_enqueue(){
		wp_enqueue_script( 'evo-'. $this->blockname .'-sidebar' );
	}
	public function block_registering(){


		wp_register_script(
	        'evo-'. $this->blockname,
	        EVO()->assets_path. 'lib/blocks/evo_blocks.js',
	        array( 'wp-blocks', 'wp-element','wp-plugins', 'wp-edit-post', 'react' )
	    );

		wp_localize_script(
            'evo-'. $this->blockname,
            'evoblock',
            array('evoblock_prev' => plugins_url(EVENTON_BASE).'/assets/images/placeholder.png')
        );

		register_block_type(
            $this->namespace .'/'. $this->blockname,
            array(
                'editor_script' => 'evo-'. $this->blockname,
                'description' => __('Main EventON block for displaying events.', 'eventon'), 
                'prev' => array('type' => 'boolean', 'default' => false)
            )
        );

		
	    // EventON Classic Template Block
	    register_block_type( 
	    	'eventon/classic-template',
	    	array(
	    		'description' => __('Template for rendering EventON classic content.', 'eventon'),
	    		'render_callback'=> array($this,'evo_block_render_callback')
	    	)
	    );
	    
	}

	public function evo_block_render_callback($attributes, $content){
		//EVO_Debug($attributes);

		$template_slug = isset($attributes['template']) ? sanitize_file_name($attributes['template']) : '';

		// Check for empty or invalid slug
	    if (empty($template_slug) || !preg_match('/^[a-zA-Z0-9_-]+$/', $template_slug)) {
	        error_log("Invalid template slug detected: " . $attributes['template']);
	        return '<!-- Invalid or missing template -->';
	    }

		$classic_file = $template_slug .'.php';

		$template = locate_template( $classic_file );

		// Fallback to plugin's template directory if not found in theme
	    if (!$template) {
	        $plugin_template_path = apply_filters('evo_block_render_template_path', 
	        	EVO()->plugin_path() . '/templates/' . $classic_file, 
	        	$template_slug, $classic_file);

	        //EVO_Debug($plugin_template_path);

	        // Ensure the file exists and is within the expected directory
	        if (file_exists($plugin_template_path) && strpos(realpath($plugin_template_path), realpath(dirname($plugin_template_path))) === 0
	    	) {
	            $template = $plugin_template_path;
	        } else {
	            return '<!-- Template not found -->';
	        }
	    }
		
		ob_start();
		
		load_template( $template );
		$template_content = ob_get_clean();	

		return $template_content;
	}
	function evo_category( $categories, $post ) {
		return array_merge(
			$categories,
			array(
				array(
					'slug' => 'eventon',
					'title' => __( 'EventON', 'eventon' ),
				),
			)
		);
	}
}

new EVO_Blocks();
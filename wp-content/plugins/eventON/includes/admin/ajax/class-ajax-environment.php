<?php 
/**
 * EventON Environment
 * @version 4.9.10
 */

class EVO_Admin_Ajax_Environment{

public function get_data(){
	$data = []; global $wpdb;

	// event count
	$event_posts_r = $wpdb->get_results( "SELECT ID FROM {$wpdb->posts} WHERE post_type='ajde_events'" );
	$events_count = ($event_posts_r && is_array($event_posts_r) )? count($event_posts_r):0;

	// event post meta count
	$pm_cunt_r = $wpdb->get_results( "SELECT pm.meta_id FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id WHERE p.post_type = 'ajde_events'" );
	$pm_count = ($pm_cunt_r && is_array($pm_cunt_r) )? count($pm_cunt_r):0;

	// Event post meta data size
    $postmeta_table_size = $wpdb->get_var("SELECT SUM(data_length + index_length) FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "' AND table_name = '{$wpdb->postmeta}'");
	$total_meta_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta}");
	$postmeta_percentage = ($pm_count / max(1, $total_meta_count));
	$est_meta_size = $postmeta_table_size * $postmeta_percentage;


	$data['EventON_version'] = EVO()->version;			

	$data['shead0'] = __('WordPress Environment');
	$data['WordPress_version'] = get_bloginfo( 'version' );
	$data['is_multisite'] = is_multisite()?'Yes':'No';
	$data['WordPress_memory_limit'] =  WP_MEMORY_LIMIT;
	$data['WordPress_Debug_mode'] = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'Yes':'No';
	$data['WordPress_Cron'] = ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) ? 'Yes':'No';
	$data['Active_plugins_count'] = count(get_option('active_plugins'));
	$data['Permalink_structure'] = get_option('permalink_structure') ?: 'Default';
	$db_size = $wpdb->get_var("SELECT SUM(data_length + index_length) FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
	$data['Database_size'] = size_format($db_size);
	$data['Total_postmeta_entries'] = $total_meta_count;
	$data['Total_postmeta_table_size'] = $this->convert_size_to_mb($postmeta_table_size);
				
	$data['shead1'] = __('Server Environment');
	$data['PHP_version'] = phpversion();
	$data['PHP_max_input_vars'] = ini_get( 'max_input_vars' ) . ' '. __('Characters');
	$data['Maximum_update_size'] = size_format( wp_max_upload_size() );
	$data['PHP_memory_limit'] = ini_get('memory_limit');
	$data['MySQL_version'] = $wpdb->db_version();
	$data['PHP_max_execution_time'] = ini_get('max_execution_time') . ' seconds';
	$data['Server_timezone'] = date_default_timezone_get();
	$data['SSL_enabled'] = is_ssl() ? 'Yes' : 'No';
	$data['CURL_enabled'] = in_array  ('curl', get_loaded_extensions() ) ? 'Yes':'No';

	

	$data['shead2'] = __('Post Data');
	$data['Events_count'] = $events_count;
	$data['Total_event_postmeta_DB_entries'] = $pm_count;			
	$data['Event_postmeta_est_data_size'] = $this->convert_size_to_mb($est_meta_size);
	$data['Event_postmeta_as_percentage_of_all_postmeta'] = number_format( $postmeta_percentage, 4) * 100 .'%';

	$data['shead3'] = __('Taxonomy Data');

	// Get taxonomies for ajde_events post type
	$taxonomies = get_object_taxonomies('ajde_events', 'names');
	$taxonomy_data = [];

	// Get evo_tax_meta size
	$evo_tax_meta = get_option('evo_tax_meta', []);
	$tax_meta_size = !empty($evo_tax_meta) && is_array($evo_tax_meta) ? strlen(serialize($evo_tax_meta)) : 0;


	foreach ($taxonomies as $taxonomy) {
		// Term count for this taxonomy
		$term_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT t.term_id) 
				FROM {$wpdb->terms} t 
				INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id 
				INNER JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id 
				INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID 
				WHERE tt.taxonomy = %s AND p.post_type = 'ajde_events'",
				$taxonomy
			)
		);
		$term_count = $term_count ? $term_count : 0;

		// Format taxonomy data as a single line
		$taxonomy_data[] = [
			'name' => $taxonomy,
			'formatted' => " {$term_count} (term count)"
		];
	}

	// Add taxonomy data to output
	$data['Taxonomies_for_ajde_events'] = count($taxonomies) . ' taxonomies';	
	$data['Evo_tax_meta_size'] = $this->convert_size_to_mb($tax_meta_size) . ' (est size)';
	foreach ($taxonomy_data as $index => $tax_data) {
		$data["{$tax_data['name']}"] = $tax_data['formatted'];
	}


	return $data;
}
private function convert_size_to_mb($size){
	$meta_size_mb = $size / 1048576;
	return $size ? number_format($meta_size_mb, 2) . ' MB' : '0.00 MB';
}

}
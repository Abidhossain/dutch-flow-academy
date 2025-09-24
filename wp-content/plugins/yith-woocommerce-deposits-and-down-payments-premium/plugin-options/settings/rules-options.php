<?php
/**
 * Deposits settings page
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\Deposits\Options
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCDP' ) ) {
	exit;
} // Exit if accessed directly

return apply_filters(
	'yith_wcdp_deposits_settings',
	array(
		'settings-rules' => array(
			'deposits_section_start' => array(
				'type' => 'title',
				'desc' => '',
				'id'   => 'yith_wcdp_deposits_settings',
			),
			'deposits_table'         => array(
				'type'                 => 'yith-field',
				'yith-type'            => 'list-table',
				'class'                => '',
				'list_table_class'     => 'YITH_WCDP_Deposit_Rules_Admin_Table',
				'list_table_class_dir' => YITH_WCDP_INC . 'admin/admin-tables/class-yith-wcdp-deposit-rules-admin-table.php',
				'id'                   => 'yith_wcdp_rate_rules',
			),
			'deposits_section_end'   => array(
				'type' => 'sectionend',
				'id'   => 'yith_wcdp_deposits_settings',
			),
		),
	)
);

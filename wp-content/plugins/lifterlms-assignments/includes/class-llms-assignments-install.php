<?php
/**
 * Plugin installation
 *
 * @package LifterLMS_Assignments/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.1.5
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin installation
 *
 * @since 1.0.0-beta.1
 * @since 1.0.0-beta.2 Update database table schema.
 * @since 1.0.0-beta.6 DB migration to add point weighting for existing assignments.
 * @since 1.1.4 Install assignment-related user capabilities during installation.
 * @since 1.1.5 Made private method `_106beta6_add_points()` static.
 */
class LLMS_Assignments_Install {

	/**
	 * Initialize the install class
	 * Hooks all actions
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return   void
	 */
	public static function init() {

		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );

	}

	/**
	 * Checks the current LLMS version and runs installer if required
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return   void
	 */
	public static function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && get_option( 'llms_assignments_version' ) !== LLMS_Assignments()->version ) {
			self::install();
			do_action( 'llms_assignments_updated' );
		}

	}

	/**
	 * Create LifterLMS DB tables
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public static function create_tables() {

		global $wpdb;

		$wpdb->hide_errors();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( self::get_schema() );

	}

	/**
	 * Get a string of table data that can be passed to dbDelta() to install LLMS tables
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.2 Update schema.
	 *
	 * @return string
	 */
	private static function get_schema() {

		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {

			if ( ! empty( $wpdb->charset ) ) {
				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$collate .= " COLLATE $wpdb->collate";
			}
		}

		$tables = "
CREATE TABLE `{$wpdb->prefix}lifterlms_assignments_submissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NULL DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `submitted` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `assignment_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(15) DEFAULT '',
  `grade` float DEFAULT NULL,
  `submission` text NOT NULL,
  `remarks` text NOT NULL,
  PRIMARY KEY (`id`)
) $collate;
";

		return $tables;

	}

	/**
	 * Core install function
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.6 Run db migration to add point weight for existing assignments.
	 * @since 1.1.4 Install user capabilities during installation.
	 *
	 * @return void
	 */
	public static function install() {

		if ( ! is_blog_installed() ) {
			return;
		}

		do_action( 'llms_assignments_before_install' );

		$ver = get_option( 'llms_assignments_version' );

		self::create_tables();

		// db upgrades by version.
		if ( version_compare( $ver, '1.0.6-beta.6', '<' ) ) {
			self::_106beta6_add_points();
		}

		LLMS_Assignments_Posts::register_post_type();
		LLMS_Roles::install();
		flush_rewrite_rules();

		self::update_version();

		do_action( 'llms_assignments_after_install' );

	}

	/**
	 * Update the LifterLMS version record to the latest version
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param string $version Version number.
	 * @return void
	 */
	public static function update_version( $version = null ) {
		delete_option( 'llms_assignments_version' );
		add_option( 'llms_assignments_version', is_null( $version ) ? LLMS_Assignments()->version : $version );
	}

	/**
	 * Database migration function
	 * Sets default point values for all quizzes & assignments attached to a lesson
	 *
	 * @since 1.0.0-beta.6
	 * @since 1.1.5 Made method static.
	 *
	 * @return void
	 */
	private static function _106beta6_add_points() {

		global $wpdb;

		foreach ( array( '_llms_quiz', '_llms_assignment' ) as $meta_key ) {

			$wpdb->query(
				$wpdb->prepare(
					"
				INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value)
					SELECT
						  m.meta_value AS post_id
					    , '_llms_points' AS 'meta_key'
					    , 1 AS 'meta_value'
					FROM {$wpdb->postmeta} AS m
					JOIN {$wpdb->posts} AS p ON m.meta_value = p.ID
					LEFT JOIN {$wpdb->postmeta} AS m2 ON m.meta_value = m2.post_id AND m2.meta_key = '_llms_points'
					WHERE m.meta_key = %s AND m.meta_value != 0
					  AND m2.meta_value IS NULL
					;",
					$meta_key
				)
			);

		}

	}

}

LLMS_Assignments_Install::init();

<?php
/**
 * Assignment notifications
 *
 * @package LifterLMS_Assignments/Classes/Notifications
 * @since    1.0.0-beta.6
 * @version  1.0.0-beta.6
 */

defined( 'ABSPATH' ) || exit;

/**
 * Assignment notifications
 *
 * @since    1.0.0-beta.6
 * @version  1.0.0-beta.6
 */
class LLMS_Assignments_Notifications {

	/**
	 * Constructor
	 *
	 * @since    1.0.0-beta.6
	 * @version  1.0.0-beta.6
	 */
	public function __construct() {

		add_action( 'llms_notifications_loaded', array( $this, 'load' ) );

	}

	/**
	 * Load custom notifications
	 *
	 * @param    obj $manager  Instance of LLMS()->notifications.
	 * @return   void
	 * @since    1.0.0-beta.6
	 * @version  1.0.0-beta.6
	 */
	public function load( $manager ) {

		$notifications = array(
			'assignment_submitted',
			'assignment_graded',
		);

		foreach ( $notifications as $trigger ) {

			$path = LLMS_ASSIGNMENTS_PLUGIN_DIR . 'includes/notifications/class-llms-assignments-notification-';

			$filename = str_replace( '_', '-', $trigger );

			$manager->load_controller( $trigger, $path . 'controller-' . $filename . '.php' );
			$manager->load_view( $trigger, $path . 'view-' . $filename . '.php', 'LLMS_Assignments' );

		}

	}

}

return new LLMS_Assignments_Notifications();

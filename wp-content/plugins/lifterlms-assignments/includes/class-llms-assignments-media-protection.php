<?php
/**
 * Handling authorization of the attachment media files, if they are protected.
 *
 * @package  lifterlms-assignments/Classes/MediaProtection
 * @since    2.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Assignments_Media_Protection class
 */
class LLMS_Assignments_Media_Protection {
	public function __construct() {
		add_filter( 'llms_attachment_is_access_allowed', array( $this, 'is_access_authorized' ), 10, 3 );
	}

	/**
	 * Check if the user has access to the attachment.
	 *
	 * @param  boolean $is_authorized  Whether or not the user has access to the attachment already.
	 * @param  int     $media_id  Attachment ID.
	 * @param  int     $user_id  User ID.
	 * @return boolean
	 */
	public function is_access_authorized( $is_authorized, $media_id, $user_id ) {
		if ( $is_authorized ) {
			return $is_authorized;
		}

		$attachment = get_post( $media_id );
		if ( $attachment && 'attachment' === $attachment->post_type && (
				user_can( $user_id, 'edit_course', $attachment->post_parent ) ||
				user_can( $user_id, 'edit_membership', $attachment->post_parent )
				) ) {
			return true;
		}

		return $is_authorized;
	}
}

return new LLMS_Assignments_Media_Protection();

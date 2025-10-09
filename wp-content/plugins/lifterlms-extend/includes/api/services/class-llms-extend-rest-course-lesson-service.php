<?php
/**
 * Course Service
 */

defined('ABSPATH') || exit;

class LLMS_Extend_REST_Course_Lesson_Service {
    public function get_lesson_details($lesson) {
      $course = llms_get_post( $lesson->get( 'parent_course' ) );
      $student = llms_get_student(get_current_user_id());
      $last_activity = $student->get_events(array(
          'per_page' => 1,
          'post_id'  => $course->get( 'id' )
      ));

      $prev_lesson = llms_get_post( $lesson->get_previous_lesson() );
      $next_lesson = llms_get_post( $lesson->get_next_lesson() );

      return array_merge(
          [
              'is_completed' => $lesson->is_complete(),
              'previous' => $prev_lesson ? $prev_lesson->get('title') : null,
              'next' => $next_lesson ? $next_lesson->get('title') : null,
              'course' => array(
                'course_id' => $course ? $course->get('id') : null,
                'course_title' => $course ? $course->get('title') : '',
                'course_progress' => $student ? $student->get_progress( $course->get('id'), 'course' ) : 0,
                'course_last_activity' => $last_activity ? date('Y-m-d H:i', strtotime($last_activity[0]->get( 'updated_date' ))) : false,
                'course_is_completed' => $student ? $student->is_complete( $course->get('id'), 'course' ) : false,
              ),
              'other_lessons' => $this->get_other_lessons( $course, $lesson ),
          ],
          $lesson->toArray()
      );
    }

    public function mark_lesson_as_completed($student, $lesson) {
        $student->mark_complete($lesson->get('id'), 'lesson');
    }

    public function mark_lesson_as_incomplete($student, $lesson) {
        $student->mark_incomplete($lesson->get('id'), 'lesson');
    }

    /**
     * Get lesson comments with nested replies
     * @param int $lesson_id
     * @return array
     */
    public function get_lesson_comments( $lesson_id ) {
        // Fetch comments for this lesson
        $comments = get_comments([
            'post_id' => $lesson_id,
            'status'  => 'approve',
            'orderby' => 'comment_date_gmt',
            'order'   => 'ASC',
            // 'hierarchical' => 'threaded',
        ]);

        // Build tree with author details
        $comments_tree = $this->format_comments_tree( $comments );

        return rest_ensure_response( $comments_tree );
    }

    /**
     * Add a comment or reply to a lesson
     *
     * @param int $lesson_id
     * @param string $content
     * @param int $parent_id
     * @return WP_Comment|WP_Error
     */
    public function add_lesson_comment( $lesson_id, $content, $parent_id = 0 ) {
      if ( ! is_user_logged_in() ) {
          return new WP_Error( 'not_logged_in', 'You must be logged in to comment.', [ 'status' => 401 ] );
      }

      $user = wp_get_current_user();

      $commentdata = [
          'comment_post_ID'      => $lesson_id,
          'comment_author'       => $user->display_name,
          'comment_author_email' => $user->user_email,
          'comment_content'      => wp_kses_post( $content ),
          'user_id'              => $user->ID,
          'comment_parent'       => (int) $parent_id,
          'comment_approved'     => 1,
      ];

      $comment_id = wp_insert_comment( $commentdata );

      if ( ! $comment_id ) {
          return new WP_Error( 'insert_failed', 'Unable to add comment.' );
      }

      return get_comment( $comment_id );
    }

    /**
     * Delete a comment (and optionally its replies)
     *
     * @param int $comment_id
     * @param bool $delete_children
     * @return bool|WP_Error
     */
    function delete_lesson_comment( $comment_id, $delete_children = true ) {
        $comment = get_comment( $comment_id );

        if ( ! $comment ) {
            return new WP_Error( 'not_found', 'Comment not found.' );
        }

        $user_id = get_current_user_id();

        if ( $comment->user_id !== $user_id && ! current_user_can( 'moderate_comments' ) ) {
            return new WP_Error( 'forbidden', 'You are not allowed to delete this comment.', [ 'status' => 403 ] );
        }

        if ( $delete_children ) {
            $children = get_comments([ 'parent' => $comment_id ]);
            foreach ( $children as $child ) {
                wp_delete_comment( $child->comment_ID, true );
            }
        }

        return wp_delete_comment( $comment_id, true );
    }


    public function get_other_lessons( $course, $current_lesson ) {
      if ( ! $course ) {
          return [];
      }

      $lessons = $course->get_lessons();
      $other_lessons = [];

      foreach ( $lessons as $lesson ) {
          if ( $lesson->get( 'id' ) !== $current_lesson->get( 'id' ) ) {
              $other_lessons[] = [
                  'id'    => $lesson->get( 'id' ),
                  'title' => $lesson->get( 'title' ),
                  'is_completed' => $lesson->is_complete(),
              ];
          }
      }

      return $other_lessons;
    }

    /**
     * Format comments into a nested tree structure
     *
     * @param array $comments Array of WP_Comment objects
     * @param int $parent_id Parent comment ID to start from
     * @return array Nested comments
     */
    private function format_comments_tree( $comments, $parent_id = 0 ) {
        $tree = [];

        foreach ( $comments as $comment ) {
            if ( (int) $comment->comment_parent === (int) $parent_id ) {
                $author_id = (int) $comment->user_id;
                $author = $author_id ? get_userdata( $author_id ) : null;

                $tree[] = [
                    'id'         => (int) $comment->comment_ID,
                    'parent_id'  => (int) $comment->comment_parent,
                    'content'    => wpautop( $comment->comment_content ),
                    'date'       => get_comment_date( 'Y-m-d H:i', $comment ),
                    'author'     => [
                        'id'     => $author ? $author->ID : 0,
                        'name'   => $author ? $author->display_name : $comment->comment_author,
                        'avatar' => get_avatar_url( $author ? $author->user_email : $comment->comment_author_email ),
                    ],
                    // recursive children
                    'replies' => $this->format_comments_tree( $comments, $comment->comment_ID ),
                ];
            }
        }

        return $tree;
    }

}
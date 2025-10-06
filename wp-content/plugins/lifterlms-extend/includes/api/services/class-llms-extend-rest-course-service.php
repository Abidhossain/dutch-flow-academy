<?php
/**
 * Course Service
 */

defined('ABSPATH') || exit;

class LLMS_Extend_REST_Course_Service { 

    public function get_my_courses($student) {
        $course_ids = $student->get_courses(
          /**
           * Filter the query args to retrieve the courses ids to be used for the "my_courses" loop.
           *
           * @since unknown
           *
           * @param array $args The query args.
           */
          apply_filters(
            'llms_my_courses_loop_courses_query_args',
            array(
              'limit' => 500,
            ),
            $student
          )
        );
        // return $course_ids;
        $courses = array();
        foreach ($course_ids['results'] as $course_id) {
            $course = llms_get_post($course_id);
            if ($course) {
                $last_activity = $student->get_events(array(
                    'per_page' => 1,
                    'post_id'  => $course->get( 'id' ),
                ));

                $video = null;
                if ( 'yes' === $course->get( 'tile_featured_video' ) ) {
                    $video = $course->get_video();

                }

                $courses[] = array(
                    'id' => $course->get('id'),
                    'title' => $course->get('title'),
                    'excerpt' => $course->get('excerpt'),
                    'difficulty' => $course->get_difficulty(),
                    'length' => $course->get('length'),
                    'enrolled_student_counts' => $course->get_student_count(),
                    'progress' => $student->get_progress( $course->get('id'), 'course' ),
                    'lesson_count' => $course->get_lessons_count(),
                    'last_activity' => $last_activity ? strtotime( $last_activity[0]->get( 'updated_date' ) ) : false,
                    'thumbnail' => get_the_post_thumbnail_url( $course->get('id'), 'medium' ),
                    'video' => $video,
                );
            }
        }

        return $courses;
    }

    public function get_course_details($course) {
        return array(
            'id' => $course->get('id'),
            'title' => $course->get('title'),
            'content' => $course->get('content'),
            'difficulty' => $course->get_difficulty(),
            'excerpt' => $course->get('excerpt'),
            'length' => $course->get('length'),
            'enrolled_student_counts' => $course->get_student_count(),
            'enrolled_students' => $this->get_formatted_students($course),
            'instructors' => $this->get_formatted_instructors($course),
            'categories' => $this->get_formatted_terms($course->get_categories()),
            'is_enrollment_open' => $course->is_enrollment_open(),
            'lessons' => $this->get_formatted_lessons($course),
            'tracks' => $course->get_tracks(),
            'tags' => $this->get_formatted_terms($course->get_tags()),
            'reviews' => $this->get_formatted_reviews($course),
        );
    }

    public function mark_lesson_as_completed($student, $lesson) {
        $student->mark_complete($lesson->get('id'), 'lesson');
    }

    public function mark_lesson_as_incomplete($student, $lesson) {
        $student->mark_incomplete($lesson->get('id'), 'lesson');
    }

    /**
     * Get formatted student data
     *
     * @return array
     */
    public function get_formatted_students($course) {
        $student_ids = $course->get_enrolled_students(5, 0);
        $formatted_students = array();

        foreach ($student_ids as $student_id) {
            $student = llms_get_student($student_id);
            if ($student) {
                $formatted_students[] = array(
                    'id' => $student_id,
                    'name' => $student->get('name'),
                    'avatar_url' => get_avatar_url($student_id, array('size' => 96))
                );
            }
        }

        return $formatted_students;
    }

    /**
     * Get formatted instructor data
     *
     * @return array
     */
    public function get_formatted_instructors($course) {
        $instructors = $course->get_instructors();
        $formatted_instructors = array();

        foreach ($instructors as $instructor_data) {
            $instructor = llms_get_instructor($instructor_data['id']);
            if ($instructor) {
                $formatted_instructors[] = array_merge(
                    [
                        "avatar" => get_avatar_url($instructor->get('id'), array('size' => 96))
                    ],
                    $instructor->toArray()
                );
            }
        }

        return $formatted_instructors;
    }

    /**
     * Format taxonomy terms
     *
     * @param array $terms Array of WP_Term objects or term arrays
     * @return array
     */
    public function get_formatted_terms($terms) {
        return array_map(function($term) {
            return array(
                'id' => is_object($term) ? $term->term_id : $term['term_id'],
                'name' => is_object($term) ? $term->name : $term['name'],
                'slug' => is_object($term) ? $term->slug : $term['slug'],
            );
        }, $terms);
    }

    public function get_formatted_lessons($course) {
        $lessons = $course->get_lessons();
        $formatted_lessons = array();

        foreach ($lessons as $lesson) {
            $formatted_lessons[] = array_merge(
                [
                    'is_completed' => $lesson->is_complete()
                ],
                $lesson->toArray()
            );
        }

        return $formatted_lessons;
    }
    /**
     * Get formatted reviews
     *
     * @return array
     */
    public function get_formatted_reviews($course) {
        $args = array(
            'posts_per_page' => get_post_meta($course->get('id'), '_llms_num_reviews', true),
            'post_type' => 'llms_review',
            'post_status' => 'publish',
            'post_parent' => $course->get('id'),
            'suppress_filters' => true,
        );

        $reviews = get_posts($args);
        $formatted_reviews = array();

        foreach ($reviews as $review) {
            $author_id = get_post_field('post_author', $review->ID);
            $formatted_reviews[] = array(
                'id' => $review->ID,
                'title' => get_the_title($review->ID),
                'content' => get_post_field('post_content', $review->ID),
                'date' => get_the_date('c', $review->ID),
                'author' => array(
                    'id' => $author_id,
                    'name' => get_the_author_meta('display_name', $author_id),
                    'avatar_url' => get_avatar_url($author_id, array('size' => 96))
                )
            );
        }

        return $formatted_reviews;
    }

}
<?php

namespace FluentSupportPro\App\Services\Integrations;

class LearnDash
{
    public function boot()
    {
        add_filter('fluent_support/customer_extra_widgets', array($this, 'getLDashCoursePurchaseWidgets'), 40, 2);

	    // Custom Fields Support For LearnDash
        $this->renderCustomFields();
    }

    public function renderCustomFields()
    {
	    add_filter('fluent_support/custom_field_types', function ($fieldTypes) {
		    $fieldTypes['learndash_courses'] = [
			    'is_custom'   => true,
			    'is_remote'   => true,
			    'custom_text' => __('LearnDash Courses will be shown at the ticket form', 'fluent-support-pro'),
			    'type'        => 'learndash_courses',
			    'label'       => __('LearnDash Courses', 'fluent-support-pro'),
			    'value_type'  => 'number'
		    ];
		    $fieldTypes['learndash_user_courses'] = [
			    'is_custom'   => true,
			    'is_remote'   => true,
			    'custom_text' => __('LearnDash User Courses will be shown at the ticket form', 'fluent-support-pro'),
			    'type'        => 'learndash_user_courses',
			    'label'       => __('LearnDash User Courses', 'fluent-support-pro'),
			    'value_type'  => 'number'
		    ];

		    return $fieldTypes;
	    }, 10, 1);

	    add_filter('fluent_support/render_custom_field_options_learndash_courses', function ($field, $customer) {

		    $courses = get_posts([
			    'post_status'    => 'publish',
			    'post_type'      => 'sfwd-courses'
		    ]);

		    if (!$courses) {
			    return $field;
		    }

		    $options = [];

		    foreach ($courses as $course) {

			    $options[] = [
				    'id'    => strval($course->ID),
				    'title' => $course->post_title
			    ];

		    }
		    if(!$options) return $field;

		    $field['type'] = 'select';
		    $field['filterable'] = true;
		    $field['rendered'] = true;
		    $field['options'] = $options;

		    return $field;

	    }, 10, 2);

	    add_filter('fluent_support/render_custom_field_options_learndash_user_courses', function ($field, $customer) {

		    $courses = learndash_user_get_enrolled_courses($customer->user_id);

            if(!$courses) return;

		    $userCourses = get_posts([
			    'post_status'    => 'publish',
			    'post_type'      => 'sfwd-courses',
			    'posts_per_page' => 100,
			    'post__in'       => $courses,
		    ]);

		    if (!$userCourses) {
			    return $field;
		    }

		    $options = [];

		    foreach ($userCourses as $course) {
			    $options[] = [
				    'id'    => strval($course->ID),
				    'title' => $course->post_title
			    ];
		    }

		    $field['type'] = 'select';
		    $field['filterable'] = true;
		    $field['rendered'] = true;
		    $field['options'] = $options;

		    return $field;

	    }, 10, 2);

	    $hooks = ['learndash_courses', 'learndash_user_courses'];

	    foreach ($hooks as $hook) {
		    add_filter('fluent_support/custom_field_render_'. $hook, function ($value){
			    if (!is_numeric($value)) {
				    return $value;
			    }

			    $courseId = absint($value);

			    if (!$courseId) return $value;

			    return '<a target="_blank" rel="nofollow" href="' . get_permalink($courseId) . '">' . get_the_title($courseId) . '</a>';
		    }, 10,1);
	    }

    }

    public function getLDashCoursePurchaseWidgets($widgets, $customer)
    {
        $courses = learndash_user_get_enrolled_courses($customer->user_id);
	    if(!$courses) return;

        $enrolledCourses = get_posts([
            'post_status'    => 'publish',
            'post_type'      => 'sfwd-courses',
            'posts_per_page' => 100,
            'post__in'       => $courses,
        ]);

        $courseData = [];
        foreach ($enrolledCourses as $course) {
            $courseData[] = [
                'title'  => esc_html($course->post_title),
                'status' => esc_html(learndash_course_status($course->ID, $customer->id, false))
            ];
        }

        if (!$courses || !$enrolledCourses || !$courseData) {
            return $widgets;
        }

        ob_start();
        ?>

        <ul>
            <?php foreach ($courseData as $data): ?>
                <li title="<?php echo __('Course Name: ', 'fluent-support-pro'). $data['title'] ?>" class="fs_widget_li">
                    <?php
                    echo '<code>'. __('Course Name:', 'fluent-support-pro') .'</code> '. $data['title'] . '<br>';
                    echo '<code>'. __('Status:', 'fluent-support-pro') .'</code> '. $data['status'] . '<br>';
                    ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
        $content = ob_get_clean();
        $widgets['lrndesh_purchases'] = [
            'header'    => __('LearnDash Courses', 'fluent-support-pro'),
            'body_html' => $content
        ];
        return $widgets;

    }

    public function addToWorkflow($customField, $key)
    {
        $options = [];

        $courses = get_posts([
            'post_status'    => 'publish',
            'post_type'      => 'sfwd-courses'
        ]);

        foreach ($courses as $course) {
            $options[$course->ID] = $course->post_title;
        }

        return [
            'title'     => $customField['label'],
            'data_type' => 'single_dropdown',
            'group'     => 'Custom Fields',
            'options'   => $options
        ];
    }
}

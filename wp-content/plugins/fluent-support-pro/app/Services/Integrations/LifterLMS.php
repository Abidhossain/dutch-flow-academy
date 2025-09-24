<?php

namespace FluentSupportPro\App\Services\Integrations;

class LifterLMS
{
    public function boot()
    {
        add_filter('fluent_support/customer_extra_widgets', array($this, 'getLLMSCoursePurchaseWidgets'), 70, 2);

        // Custom Fields Support For LifterLms
	    $this->renderCustomFields();
    }

    public function getUserCourses($customer)
    {
	    $student = llms_get_student($customer->user_id);

	    if (!$student){
            return;
        };
	    $courses = $student->get_courses();

        if(empty($student->get_enrollments()['results'])){
            return;
        }

	    return get_posts([
		    'post_status'    => 'publish',
		    'post_type'      => 'course',
		    'posts_per_page' => 100,
		    'post__in'       => $courses['results'],
	    ]);
    }

    public function renderCustomFields()
    {
	    add_filter('fluent_support/custom_field_types', function ($fieldTypes) {
		    $fieldTypes['llms_courses'] = [
			    'is_custom'   => true,
			    'is_remote'   => true,
			    'custom_text' => __('LifterLMS Courses will be shown at the ticket form', 'fluent-support-pro'),
			    'type'        => 'llms_courses',
			    'label'       => __('LifterLMS Courses', 'fluent-support-pro'),
			    'value_type'  => 'number'
		    ];
		    $fieldTypes['llms_user_courses'] = [
			    'is_custom'   => true,
			    'is_remote'   => true,
			    'custom_text' => __('LifterLMS User Courses will be shown at the ticket form', 'fluent-support-pro'),
			    'type'        => 'llms_user_courses',
			    'label'       => __('LifterLMS User Courses', 'fluent-support-pro'),
			    'value_type'  => 'number'
		    ];

		    return $fieldTypes;
	    }, 10, 1);

	    add_filter('fluent_support/render_custom_field_options_llms_courses', function ($field, $customer) {
            $courses = get_posts([
	            'post_type' => 'course',
	            'post_status'    => 'publish',
            ]);

            if (!$courses) return $field;

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

	    add_filter('fluent_support/render_custom_field_options_llms_user_courses', function ($field, $customer) {
		    $courses = $this->getUserCourses($customer);

		    if (!$courses) return $field;

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

	    $hooks = ['llms_courses', 'llms_user_courses'];

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

    public function getLLMSCoursePurchaseWidgets($widgets, $customer)
    {
	    $enrolledCourses = $this->getUserCourses($customer);

        if(!$enrolledCourses) return $widgets;

        $courseData = [];
        foreach ($enrolledCourses as $course) {
            $courseData[] = [
                'title'  => esc_html($course->post_title),
                'status' => esc_html(llms_get_enrollment_status_name(llms_get_student($customer->user_id)->get_enrollment_status($course->ID)))
            ];
        }

        ob_start();
        ?>

        <ul>
            <?php foreach ($courseData as $data): ?>
                <li title="<?php echo __('Course Name: ', 'fluent-support-pro'). $data['title'] ?>" class="fs_widget_li">
                    <?php
                    echo '<code>'. __('Course Name:', 'fluent-support-pro') .'</code> '. $data['title'] . '<br>';
                    echo '<code>'. __('Status:', 'fluent-support-pro') .'</code> '. $data['status'] . '';
                    ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
        $content = ob_get_clean();

        $widgets['llms_purchases'] = [
            'header'    => __('Lifter LMS Courses', 'fluent-support-pro'),
            'body_html' => $content
        ];
        return $widgets;
    }

    public function addToWorkflow($customField, $key)
    {
        $options = [];

        $courses = get_posts([
            'post_status'    => 'publish',
            'post_type'      => 'course'
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

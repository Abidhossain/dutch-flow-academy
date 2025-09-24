<?php

namespace FluentSupportPro\App\Services\Integrations;

class TutorLMS
{
	public function boot()
	{
		add_filter('fluent_support/customer_extra_widgets', array($this, 'getTutorLMSCoursePurchaseWidgets'), 100, 2);

		// Custom Fields Support For TutorLMS
		$this->renderCustomFields();
	}

	public function renderCustomFields()
	{
		add_filter('fluent_support/custom_field_types', function ($fieldTypes) {
			$fieldTypes['tutorlms_courses'] = [
				'is_custom'   => true,
				'is_remote'   => true,
				'custom_text' => __('Tutor LMS Courses will be shown at the ticket form', 'fluent-support-pro'),
				'type'        => 'tutorlms_courses',
				'label'       => __('Tutor LMS Courses', 'fluent-support-pro'),
				'value_type'  => 'number'
			];
			$fieldTypes['tutorlms_user_courses'] = [
				'is_custom'   => true,
				'is_remote'   => true,
				'custom_text' => __('Tutor LMS User Courses will be shown at the ticket form', 'fluent-support-pro'),
				'type'        => 'tutorlms_user_courses',
				'label'       => __('Tutor LMS User Courses', 'fluent-support-pro'),
				'value_type'  => 'number'
			];

			return $fieldTypes;
		}, 10, 1);

		add_filter('fluent_support/render_custom_field_options_tutorlms_courses', function ($field, $customer) {
			$courses = get_posts([
				'post_type'      => tutor()->course_post_type,
			]);

			if (!$courses) return $field;

			$options = [];

			foreach ($courses as $course) {
				$options[] = [
					'id'    => strval($course->ID),
					'title' => $course->post_title
				];
			}
			if (!$options) return $field;

			$field['type'] = 'select';
			$field['filterable'] = true;
			$field['rendered'] = true;
			$field['options'] = $options;

			return $field;
		}, 10, 2);

		add_filter('fluent_support/render_custom_field_options_tutorlms_user_courses', function ($field, $customer) {
			if (!$customer->user_id) return $field;
			$courses = tutor_utils()->get_enrolled_courses_ids_by_user($customer->user_id);

			if (!$courses) return $field;

			foreach ($courses as $course) {
				$options[] = [
					'id'    => strval(get_post($course)->ID),
					'title' => get_post($course)->post_title
				];
			}

			if (!$options) return $field;

			$field['type'] = 'select';
			$field['filterable'] = true;
			$field['rendered'] = true;
			$field['options'] = $options;

			return $field;
		}, 10, 2);

		$hooks = ['tutorlms_courses', 'tutorlms_user_courses'];

		foreach ($hooks as $hook) {
			add_filter('fluent_support/custom_field_render_' . $hook, function ($value) {

				if (!is_numeric($value)) {
					return $value;
				}

				$levelId = absint($value);

				if (!$levelId) return $value;
				return '<a target="_blank" rel="nofollow" href="' . get_permalink($levelId) . '">' . get_post($levelId)->post_title . '</a>';
			}, 10, 1);
		}
	}

	public function getTutorLMSCoursePurchaseWidgets($widgets, $customer)
	{
		$userId = $customer->user_id;

		if (!$userId) {
			return $widgets;
		}

		$courseIds = tutor_utils()->get_enrolled_courses_ids_by_user($userId);

		if (empty($courseIds)) {
			return $widgets;
		}

		$enrolledCourses = get_posts([
			'post_type'      => tutor()->course_post_type,
			'posts_per_page' => 100,
			'post__in'       => $courseIds,
		]);


		$courseData = [];
		foreach ($enrolledCourses as $course) {
			$enrolled = \FluentSupport\App\App::db()->table('posts')
				->where('post_parent', $course->ID)
				->where('post_author', $userId)
				->where('post_type', 'tutor_enrolled')
				->first();

			$courseData[] = [
				'id'    => esc_html($course->ID),
				'title' => esc_html($course->post_title),
			];
		}
		ob_start();
		?>

        <ul>
			<?php foreach ($courseData as $data) : ?>
                <li title="<?php echo __('Course Name: ', 'fluent-support-pro'). $data['title'] ?>" class="fs_widget_li">
					<?php
					echo '<code>'. __('Course Name:', 'fluent-support-pro') .'</code> '. $data['title'] . '<br>';
					echo ' <code>'. __('Status:', 'fluent-support-pro') .'</code>' . __('Enrolled', 'fluent-support-pro');
					?>
                </li>
			<?php endforeach; ?>
        </ul>
		<?php
		$content = ob_get_clean();

		$widgets['tlms_purchases'] = [
			'header'    => __('Tutor LMS Courses', 'fluent-support-pro'),
			'body_html' => $content
		];
		return $widgets;
	}

    public function addToWorkflow($customField, $key)
    {
        $options = [];

        $courses = get_posts([
            'post_type'      => tutor()->course_post_type,
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

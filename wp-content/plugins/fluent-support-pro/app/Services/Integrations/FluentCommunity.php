<?php

namespace FluentSupportPro\App\Services\Integrations;

use FluentCommunity\App\Models\SpaceUserPivot;
use FluentCommunity\Modules\Course\Services\CourseHelper;
use FluentCommunity\Modules\Course\Model\Course;

/**
 * Class FluentCommunity
 *
 * Handles integration with Fluent Community for Support Pro.
 *
 * @package FluentSupportPro\App\Services\Integrations
 */
class FluentCommunity
{
    /**
     * Initialize the integration.
     *
     * @return void
     */
    public function boot(): void
    {
        add_filter(
            'fluent_support/customer_extra_widgets',
            [$this, 'getFluentCommunityPurchaseWidgets'],
            70,
            2
        );

        // Custom Fields Support For LearnPress
        $this->renderCustomFields();
    }

    /**
     * Add Fluent Community course information to customer widgets.
     *
     * @param array $widgets The existing widgets.
     * @param object $customer The customer object.
     * @return array Modified widgets array.
     */
    public function getFluentCommunityPurchaseWidgets(array $widgets, $customer): array
    {
        $enrolledCourses = $this->getUserCourses($customer->user_id);
        if (empty($enrolledCourses)) {
            return $widgets;
        }

        $widgets['fcom_purchases'] = [
            'header' => __('Fluent Community Courses', 'fluent-support-pro'),
            'body_html' => $this->renderCoursesHtml($enrolledCourses)
        ];

        return $widgets;
    }

    /**
     * Get courses that the customer is enrolled in.
     *
     * @param object $customer Customer object with user_id property.
     * @return array Array of course data.
     */
    public function getUserCourses($userId): array
    {
        if (empty($userId)) {
            return [];
        }

        return SpaceUserPivot::query()
            ->with('space:id,title')
            ->where([
                'user_id' => $userId,
                'role' => 'student'
            ])
            ->get()
            ->map(function ($enrollment) use ($userId) {
                return [
                    'id' => $enrollment->space->id,
                    'title' => $enrollment->space->title,
                    'enrolled_at' => $enrollment->created_at->format('Y-m-d'),
                    'progress' => CourseHelper::getCourseProgress(
                        $enrollment->space->id,
                        $userId
                    ),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Render HTML for enrolled courses.
     *
     * @param array $enrolledCourses Array of course data.
     * @return string HTML content.
     */
    private function renderCoursesHtml(array $enrolledCourses): string
    {
        ob_start();
        ?>
        <ul>
            <?php foreach ($enrolledCourses as $data): ?>
                <li title="<?php echo esc_attr(sprintf(__('Course Name: %s', 'fluent-support-pro'), $data['title'])); ?>"
                    class="fs_widget_li">
                    <code><?php echo esc_html__('Course Name:', 'fluent-support-pro'); ?></code>
                    <?php echo esc_html($data['title']); ?><br>

                    <code><?php echo esc_html__('Progress:', 'fluent-support-pro'); ?></code>
                    <?php echo esc_html($data['progress']); ?>%<br>

                    <code><?php echo esc_html__('Enrolled At:', 'fluent-support-pro'); ?></code>
                    <?php echo esc_html($data['enrolled_at']); ?><br>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
        return ob_get_clean();
    }


    public function renderCustomFields ()
    {
        $this->registerCustomFields();
        $this->fluentCommunityCourseOptions();
        $this->fluentCommunityUserCourseOptions();

        $hooks = ['fcom_courses', 'fcom_user_courses'];

        foreach ($hooks as $hook) {
            add_filter('fluent_support/custom_field_render_' . $hook, function ($value) {

                if (!is_numeric($value)) {
                    return $value;
                }

                $courseId = absint($value);

                if (!$courseId) return $value;

                $course = Course::find($courseId);

                if (!$course) {
                    return $value;
                }

                $url = site_url('/portal/course/' . $course->slug . '/lessons');

                return '<a target="_blank" rel="nofollow" href="' . esc_url($url) . '">' . esc_html($course->title) . '</a>';
            }, 10, 1);
        }
    }

    private function registerCustomFields ()
    {
        add_filter('fluent_support/custom_field_types', function ($fieldTypes) {
            $fieldTypes['fcom_courses'] = [
                'is_custom'   => true,
                'is_remote'   => true,
                'custom_text' => __('Fluent Community Courses will be shown at the ticket form', 'fluent-support-pro'),
                'type'        => 'fcom_courses',
                'label'       => __('Fluent Community Courses', 'fluent-support-pro'),
                'value_type'  => 'number'
            ];
            $fieldTypes['fcom_user_courses'] = [
                'is_custom'   => true,
                'is_remote'   => true,
                'custom_text' => __('Fluent Community User Courses will be shown at the ticket form', 'fluent-support-pro'),
                'type'        => 'fcom_user_courses',
                'label'       => __('Fluent Community User Courses', 'fluent-support-pro'),
                'value_type'  => 'number'
            ];

            return $fieldTypes;
        }, 10, 1);
    }

    private function fluentCommunityCourseOptions()
    {
        add_filter('fluent_support/render_custom_field_options_fcom_courses', function ($field, $customer) {
            $courses = $this->getAllCourses();
            if (empty($courses)) {
                return $field;
            }

            $field['type'] = 'select';
            $field['filterable'] = true;
            $field['rendered'] = true;
            $field['options'] = array_map(function ($course) {
                return [
                    'id' => $course['id'],
                    'title' => $course['title']
                ];
            }, $courses);

            return $field;

        }, 10, 2);
    }

    private function fluentCommunityUserCourseOptions()
    {
        add_filter('fluent_support/render_custom_field_options_fcom_user_courses', function ($field, $customer) {
            if (!$courses = $this->getUserCourses($customer->user_id)) {
                return $field;
            }

            $field['type'] = 'select';
            $field['filterable'] = true;
            $field['rendered'] = true;
            $field['options'] = array_map(function ($course) {
                return [
                    'id' => $course['id'],
                    'title' => $course['title']
                ];
            }, $courses);

            return $field;
        }, 10, 2);
    }

    private function getAllCourses()
    {
        return Course::select('id', 'title')->get()->toArray();
    }

    public function addToWorkflow($customField, $key)
    {
        if(!defined('FLUENT_COMMUNITY_PLUGIN_VERSION')) {
            return [];
        }

        $options = [];

        $courses = $this->getAllCourses();

        foreach ($courses as $course) {
            $options[$course['id']] = $course['title'];
        }

        return [
            'title'     => $customField['label'],
            'data_type' => 'single_dropdown',
            'group'     => 'Custom Fields',
            'options'   => $options
        ];
    }
}

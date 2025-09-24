<?php
namespace FluentSupportPro\App\Services\Integrations;

class LearnPress
{
    public function boot()
    {
        add_filter('fluent_support/customer_extra_widgets', array($this, 'getLearnPressWidget'), 130, 2);

        // Custom Fields Support For LearnPress
        $this->renderCustomFields();
    }

    public function getLearnPressWidget ( $widgets, $customer )
    {
        $enrolledCourses = $this->getCourses( $customer->user_id );

        if ( !$enrolledCourses ) {
            return $widgets;
        }

        ob_start();
        ?>
        <ul>
            <?php foreach ($enrolledCourses as $data): ?>
                <li title="<?php echo __('Course Name: ', 'fluent-support-pro'). $data->post_title ?>" class="fs_widget_li">
                    <?php
                    echo '<code>'. __('Course Name:', 'fluent-support-pro') .'</code> '. $data->post_title . '<br>';
                    echo '<code>'. __('Graduation:', 'fluent-support-pro') .'</code> '. $data->graduation . '<br>';
                    echo '<code>'. __('Status:', 'fluent-support-pro') .'</code> '. $data->status . '<br>';
                    echo '<code>'. __('Started At:', 'fluent-support-pro') .'</code> '. $data->start_time . '<br>';
                    ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php
        $content = ob_get_clean();

        $widgets['lp_user_data'] = [
            'header' => __('LearnPress', 'fluent-support-pro'),
            'body_html' => $content
        ];

        return $widgets;
    }

    private function getCourses ( $customerUserId = false )
    {
        $enrollmentsQuery = \FluentSupport\App\App::getInstance('db')->table('learnpress_user_items')
            ->select(['learnpress_user_items.*', 'posts.post_title'])
            ->where('learnpress_user_items.item_type', 'lp_course')
            ->join('posts', 'posts.ID', '=', 'learnpress_user_items.item_id')
            ->orderBy('learnpress_user_items.user_item_id', 'DESC');

        if ( $customerUserId ) {
            $enrollmentsQuery->where('learnpress_user_items.user_id', $customerUserId);
        }

        return $enrollmentsQuery->get();
    }

    public function renderCustomFields ()
    {
        $this->registerCustomFields();

        $this->learnPressCourseOptions();

        $this->learnPressUserCourseOptions();

        $hooks = ['learnpress_courses', 'learnpress_user_courses'];

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

    private function registerCustomFields ()
    {
        add_filter('fluent_support/custom_field_types', function ($fieldTypes) {
            $fieldTypes['learnpress_courses'] = [
                'is_custom'   => true,
                'is_remote'   => true,
                'custom_text' => __('LearnPress Courses will be shown at the ticket form', 'fluent-support-pro'),
                'type'        => 'learnpress_courses',
                'label'       => __('LearnPress Courses', 'fluent-support-pro'),
                'value_type'  => 'number'
            ];
            $fieldTypes['learnpress_user_courses'] = [
                'is_custom'   => true,
                'is_remote'   => true,
                'custom_text' => __('LearnPress User Courses will be shown at the ticket form', 'fluent-support-pro'),
                'type'        => 'learnpress_user_courses',
                'label'       => __('LearnPress User Courses', 'fluent-support-pro'),
                'value_type'  => 'number'
            ];

            return $fieldTypes;
        }, 10, 1);
    }

    private function learnPressCourseOptions()
    {
        add_filter('fluent_support/render_custom_field_options_learnpress_courses', function ($field, $customer) {
            $courses = $this->getCourses();

            if ( ! $courses ) {
                return $field;
            }

            $options = [];

            foreach ( $courses as $course ) {
                $options[] = [
                    'id'    => strval($course->item_id),
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
    }

    private function learnPressUserCourseOptions()
    {
        add_filter('fluent_support/render_custom_field_options_learnpress_user_courses', function ($field, $customer) {

            $courses = $this->getCourses( $customer->user_id );

            if(!$courses) return;

            $options = [];

            foreach ($courses as $course) {
                $options[] = [
                    'id'    => strval($course->item_id),
                    'title' => $course->post_title
                ];
            }

            $field['type'] = 'select';
            $field['filterable'] = true;
            $field['rendered'] = true;
            $field['options'] = $options;

            return $field;

        }, 10, 2);
    }

    public function addToWorkflow($customField, $key)
    {
        $options = [];

        $courses = $this->getCourses();

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

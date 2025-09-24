<?php

namespace FluentSupportPro\App\Services\Integrations;

class RCPro
{

    public function boot()
    {
        add_filter('fluent_support/customer_extra_widgets', array($this, 'getRcpMembershipInfo'), 90, 2);

	    // Custom Fields Support For RC Pro
	    $this->renderCustomFields();
    }

    public function renderCustomFields()
    {
	    add_filter('fluent_support/custom_field_types', function ($fieldTypes) {
		    $fieldTypes['rcpro_levels'] = [
			    'is_custom'   => true,
			    'is_remote'   => true,
			    'custom_text' => __('Restrict Content Pro\'s levels will be shown at the ticket form', 'fluent-support-pro'),
			    'type'        => 'rcpro_levels',
			    'label'       => __('Restrict Content Pro Levels', 'fluent-support-pro'),
			    'value_type'  => 'number'
		    ];
		    $fieldTypes['rcpro_user_levels'] = [
			    'is_custom'   => true,
			    'is_remote'   => true,
			    'custom_text' => __('Restrict Content Pro User levels will be shown at the ticket form', 'fluent-support-pro'),
			    'type'        => 'rcpro_user_levels',
			    'label'       => __('Restrict Content Pro User Levels', 'fluent-support-pro'),
			    'value_type'  => 'number'
		    ];

		    return $fieldTypes;
	    }, 10, 1);

	    add_filter('fluent_support/render_custom_field_options_rcpro_levels', function ($field, $customer) {
		    $levels =  \FluentSupport\App\App::db()->table('restrict_content_pro')->select(['id', 'name'])->get();

		    if (!$levels) return $field;

		    $options = [];

		    foreach ($levels as $level) {
			    $options[] = [
				    'id'    => strval($level->id),
				    'title' => $level->name
			    ];
		    }
		    if(!$options) return $field;

		    $field['type'] = 'select';
		    $field['filterable'] = true;
		    $field['rendered'] = true;
		    $field['options'] = $options;

		    return $field;

	    }, 10, 2);

	    add_filter('fluent_support/render_custom_field_options_rcpro_user_levels', function ($field, $customer) {
		    $member = rcp_get_customer_by_user_id($customer->user_id);

		    if (!$member) return $field;

		    $memberships = $member->get_memberships();

		    if (empty($memberships)) return;

		    $options = [];

		    foreach ($memberships as $membership) {
			    $options[] = [
				    'id'    => strval($membership->get_id()),
				    'title' => $membership->get_membership_level_name()
			    ];
		    }
		    if(!$options) return $field;

		    $field['type'] = 'select';
		    $field['filterable'] = true;
		    $field['rendered'] = true;
		    $field['options'] = $options;

		    return $field;

	    }, 10, 2);

	    $hooks = ['rcpro_levels', 'rcpro_user_levels'];

	    foreach ($hooks as $hook) {
		    add_filter('fluent_support/custom_field_render_'. $hook, function ($value){

			    if (!is_numeric($value)) {
				    return $value;
			    }

			    $levelId = absint($value);

			    if (!$levelId) return $value;
			    $level =  \FluentSupport\App\App::db()->table('restrict_content_pro')->where('id', $levelId)->select(['name'])->first();

			    return $level->name;
		    }, 10,1);
	    }

    }

    public function getRcpMembershipInfo($widgets, $customer)
    {
        if (!$customer->user_id) return;

        $member = rcp_get_customer_by_user_id($customer->user_id);

        if (empty($member)) return;

        $memberships = $member->get_memberships();

        if (empty($memberships)) return;

        $membershipInfo = [];

        foreach ($memberships as $membership) {
            $membershipInfo[] = [
                'name'           => esc_html($membership->get_membership_level_name()),
                'status'         => esc_html($membership->get_status()),
                'activated_date' => esc_html(date_i18n(get_option('date_format'), strtotime($membership->get_activated_date())))
            ];
        }

        if (empty($membershipInfo)) return;

        ob_start();
        ?>

        <ul>
            <?php foreach ($membershipInfo as $info): ?>
                <li title="<?php $info['name'] ?>" class="fs_widget_li">
                    <?php
                    echo '<code>'.__('Level:', 'fluent-support-pro').'</code> '. $info['name'] . '<br>';
                    echo '<code>'. __('Status:', 'fluent-support-pro') .'</code> '. ucfirst($info['status']) . '<br>';
                    if ($info['status'] === 'active') {
                        echo '<code>'.__('Activated:', 'fluent-support-pro').'</code> ' . $info['activated_date'];
                    }
                    ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
        $content = ob_get_clean();

        $widgets['rcpro'] = [
            'header'    => __('Restrict Content Pro', 'fluent-support-pro'),
            'body_html' => $content
        ];
        return $widgets;
    }

    public function addToWorkflow($customField, $key)
    {
        $options = [];

        $levels =  \FluentSupport\App\App::db()->table('restrict_content_pro')->select(['id', 'name'])->get();

        foreach ($levels as $level) {
            $options[$level->id] = $level->name;
        }

        return [
            'title'     => $customField['label'],
            'data_type' => 'single_dropdown',
            'group'     => 'Custom Fields',
            'options'   => $options
        ];
    }
}

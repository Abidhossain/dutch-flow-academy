<?php

namespace FluentSupportPro\App\Services\Integrations;

class PMPro
{
    public function boot()
    {
        add_filter('fluent_support/customer_extra_widgets', array($this, 'getPmMembershipInfo'), 80, 2);

	    // Custom Fields Support For Paid Membership Pro
	    $this->renderCustomFields();
    }

    public function renderCustomFields()
    {
	    add_filter('fluent_support/custom_field_types', function ($fieldTypes) {
		    $fieldTypes['pmpro_levels'] = [
			    'is_custom'   => true,
			    'is_remote'   => true,
			    'custom_text' => __('Paid Membership Pro\'s levels will be shown at the ticket form', 'fluent-support-pro'),
			    'type'        => 'pmpro_levels',
			    'label'       => __('Paid Membership Pro Levels', 'fluent-support-pro'),
			    'value_type'  => 'number'
		    ];
		    $fieldTypes['pmpro_user_levels'] = [
			    'is_custom'   => true,
			    'is_remote'   => true,
			    'custom_text' => __('Paid Membership Pro\'s User levels will be shown at the ticket form', 'fluent-support-pro'),
			    'type'        => 'pmpro_user_levels',
			    'label'       => __('Paid Membership Pro User Levels', 'fluent-support-pro'),
			    'value_type'  => 'number'
		    ];

		    return $fieldTypes;
	    }, 10, 1);

	    add_filter('fluent_support/render_custom_field_options_pmpro_levels', function ($field, $customer) {
		    $levels = pmpro_getAllLevels();

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

	    add_filter('fluent_support/render_custom_field_options_pmpro_user_levels', function ($field, $customer) {
		    if (!$customer->user_id) return $field;

		    $levels = pmpro_getMembershipLevelsForUser($customer->user_id);

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

	    $hooks = ['pmpro_levels', 'pmpro_user_levels'];

	    foreach ($hooks as $hook) {
		    add_filter('fluent_support/custom_field_render_'. $hook, function ($value){

			    if (!is_numeric($value)) {
				    return $value;
			    }

			    $levelId = absint($value);

			    if (!$levelId) return $value;

			    $level = pmpro_getLevel($levelId);
			    return $level->name;
		    }, 10,1);
	    }

    }

    public function getPmMembershipInfo($widgets, $customer)
    {
        if (!$customer->user_id) return;

        $levels = pmpro_getMembershipLevelsForUser($customer->user_id);

        if(empty($levels)) return;

        $membershipInfo = [];

        foreach ($levels as $level){
            $membershipInfo[] = [
                'name'       => esc_html($level->name),
                'startdate' => esc_html( wp_date(get_option('date_format'), (int) $level->startdate)),
                'enddate'   => $level->enddate
                    ? esc_html(wp_date(get_option('date_format'), (int) $level->enddate ))
                    : '',
            ];
        }
        if(empty($membershipInfo)) return;

        ob_start();
        ?>

        <ul>
            <?php foreach ($membershipInfo as $info):?>
                <li title="<?php $info['name']?>" class="fs_widget_li">
                    <?php
                    echo '<code>'.__('Level:', 'fluent-support-pro').'</code> '. $info['name'] . '<br>';
                    echo '<code>'.__('Membership Start:','fluent-support-pro').'</code> '. $info['startdate'] . '<br>';
                    if($info['enddate']){
                        echo '<code>'.__('Membership End:', 'fluent-support-pro').'</code> '. $info['enddate'];
                    }
                    ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
        $content = ob_get_clean();

        $widgets['pmpro'] = [
            'header'    => __('Paid Membership Pro', 'fluent-support-pro'),
            'body_html' => $content
        ];
        return $widgets;
    }

    public function addToWorkflow($customField, $key)
    {
        $options = [];

        $levels = pmpro_getAllLevels();

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
